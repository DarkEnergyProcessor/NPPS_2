<?php
/*
 * Null-Pointer Private Server
 * Copyright © 2037 Dark Energy Processor Corporation
 */

/// \file main.php

ini_set('html_errors', false);
define('MAIN_INVOKED', '0.0.1 alpha', true);

// Fixes nginx.
// Source: http://www.php.net/manual/en/function.getallheaders.php#84262
if(!function_exists('getallheaders'))
{
	function getallheaders(): array
	{
		$headers = [];
		foreach ($_SERVER as $name => $value)
		{
			if (substr($name, 0, 5) == 'HTTP_')
			{
				$headers[str_replace(' ', '-', ucwords(
					strtolower(
						str_replace('_', ' ', substr($name, 5))
					)
				))] = $value;
			}
	   }
	   return $headers;
	}
} 

/// Boolean value to store maintenance flag
$MAINTENANCE_MODE = false;
/// Array of headers that client sent
$REQUEST_HEADERS = array_change_key_case(getallheaders(), CASE_LOWER);
/// Flag to indicate the request is ok or not
$REQUEST_SUCCESS = false;
/// Array of the response data. Converted to JSON later
$RESPONSE_ARRAY = [];
/// Main database variable
$DATABASE = NULL;
/// Current timestamp in seconds since January 1st, 1970
$UNIX_TIMESTAMP = time();
/// SIF-compilant current datetime
$TEXT_TIMESTAMP = date('Y-m-d H:i:s', $UNIX_TIMESTAMP);

/// Temporary variable used for npps_config() to detect current module
$CURRENT_MODULE = NULL;
/// Temporary variable used for npps_config() to detect current action
$CURRENT_ACTION = NULL;

set_error_handler(function($errNo, $errStr, $errFile, $errLine)
{
	http_response_code(500);
	throw new ErrorException("$errStr in $errFile on line $errLine", $errNo);
});

set_exception_handler(function($x)
{
	http_response_code(500);
	throw $x;
});

/// \brief Function to handle shutdown procedure
function npps_shutdown()
{
	global $MAINTENANCE_MODE;
	global $REQUEST_HEADERS;
	global $REQUEST_SUCCESS;
	global $RESPONSE_ARRAY;
	
	// We still need to be in this directory
	assert(chdir(dirname(__FILE__)));
	
	if($MAINTENANCE_MODE) exit;	// Don't do anything on maintenance
	
	header('Content-Type: application/json; charset=utf-8');
	header(sprintf("Date: %s", gmdate('D, d M Y H:i:s T')));
	
	$contents = ob_get_contents();
	error_log($contents, 4);
	
	if(!npps_config('DEBUG_ENVIRONMENT'))
	{
		$contents = "";
	}
	
	// Check if the request fails
	if($REQUEST_SUCCESS != 1)
	{
		$output = NULL;
		
		if($REQUEST_SUCCESS == 0)
		{
			header('status_code: 720');
			$output = [
				'response_data' => ['error_code' => 200],
				'status_code' => 720
			];
			
			if(strlen($contents) > 0)
				$output['message'] = $contents;
		}
		else
			$output = [
				'message' => $contents
			];
		
		if(http_response_code() == 200 && $REQUEST_SUCCESS == (-1))
			// If it's not previously set, then set it
			npps_http_code(error_get_last() == NULL ? 403 : 500);
		
		ob_end_clean();
		$asd = json_encode($output);
		
		echo $asd;
		exit;
	}
	
	header("status_code: {$RESPONSE_ARRAY["status_code"]}");
	
	// If there is some leftover buffer, send it too
	if(strlen($contents) > 0)
		$RESPONSE_ARRAY['message'] = $contents;
	
	// Now send the decryption keys
	$RESPONSE_ARRAY['release_info'] = [];
	
	if(npps_config('SEND_RELEASE_INFO'))
		foreach(npps_decryption_key() as $id => $key)
			$RESPONSE_ARRAY['release_info'][] = ['id' => $id, 'key' => $key];
	
	ob_end_clean();
	ob_start('ob_gzhandler');
	
	$output = json_encode($RESPONSE_ARRAY);
	if(strlen($output) > 2)
	{
		header(sprintf('X-Message-Code: %s',
			hash_hmac('sha1', $output, X_MESSAGE_CODE_KEY))
		);
		header(sprintf('X-Message-Sign: %s',
			base64_encode(str_repeat("\x00", 128)))
		);
		
		echo $output;
	}
	
	ob_end_flush();
	
	exit;
}

/// \brief NPPS main script handler. Not merged to npps_main() to prevent
///        variable pollution
/// \param USER_ID Player user ID
/// \param TOKEN Player token
/// \param PLATFORM_ID Platform-Type value from header
/// \param module module/handler to be accessed
/// \param action action in module/handler to be accessed. `NULL` if module is
///        `api`
/// \returns 1 if request success, 0 if request failed in handler, -1 if
///          request failed in module.
function npps_main_script_handler(
	int& $USER_ID,
	$TOKEN,
	int $PLATFORM_ID,
	string $module,
	$action = NULL): int
{
	global $REQUEST_HEADERS;
	global $RESPONSE_ARRAY;
	global $UNIX_TIMESTAMP;
	global $TEXT_TIMESTAMP;
	
	$request_data = [];
	// Used to isolate variables when using include
	$isolator_call = function(string $___FILE, $REQUEST_DATA)
		use($UNIX_TIMESTAMP, $TEXT_TIMESTAMP, &$TOKEN, &$USER_ID)
	{
		return (include($___FILE));
	};
	
	if(isset($_POST['request_data']))
	{
		if(npps_config('X_MESSAGE_CODE_CHECK'))
		{
			if(!isset($REQUEST_HEADERS['x-message-code']))
			{
				echo 'X-Message-Code header required!';
				http_response_code(400);
				return 0;
			}
			
			if(strcmp($REQUEST_HEADERS['x-message-code'],
				hash_hmac('sha1', $_POST['request_data'], X_MESSAGE_CODE_KEY)
			))
			{
				echo 'Invalid X-Message-Code';
				http_response_code(400);
				return 0;
			}
		}
		
		$request_data = json_decode(
			mb_convert_encoding($_POST['request_data'], 'UTF-8', 'UTF-8'),
			true
		);
		
		if($request_data === NULL)
		{
			echo "Invalid JSON data: {$_POST['request_data']}";
			return 0;
		}
	}
	
	if(npps_config('REQUEST_LOGGING'))
	{
		// TODO
	}
	
	// Handler first. A handler doesn't need to have valid token
	if(is_string($action))
	{
		$modname = "handlers/$module/$action.php";
		
		if(is_file($modname))
		{
			$CURRENT_MODULE = $module;
			$CURRENT_ACTION = $action;
			$val = $isolator_call($modname, $request_data);
			
			$CURRENT_MODULE = NULL;
			$CURRENT_ACTION = NULL;
			
			if($val === false)
				return 0;
			
			if(is_integer($val))
			{
				$RESPONSE_ARRAY['response_data'] = ['error_code' => $val];
				$RESPONSE_ARRAY['status_code'] = 600;
			}
			else
			{
				$RESPONSE_ARRAY['response_data'] = $val[0];
				$RESPONSE_ARRAY['status_code'] = $val[1];
			}
			
			return 1;
		}
	}
	
	/* Verify credentials existence */
	if($TOKEN === NULL || token_exist($TOKEN) == false || $USER_ID == 0)
	{
		invalid_credentials:
		echo 'Invalid login, password, user_id, and/or token!';
		return 0;
	}
	else
	{
		$cred = npps_query('
			SELECT login_key, login_pwd FROM `logged_in` WHERE token = ?',
			's', $TOKEN
		)[0];
		$connected_uid = user_id_from_credentials(
			$cred['login_key'],
			$cred['login_pwd'],
			$TOKEN
		);
		
		if($connected_uid == 0)
			goto invalid_credentials;
		else if($connected_uid < 0)
		{
			npps_http_code(423, 'Account Locked');
			return 0;
		}
	}
	
	// Module mode
	if(strcmp($module, 'api') == 0)
	{
		// Multiple module/action calls
		if(count($request_data) > MULTI_REQUEST_LIMIT)
		{
			echo '/api request limit exceeded!';
			return 0;
		}
		
		$RESPONSE_ARRAY['response_data'] = [];
		$RESPONSE_ARRAY['status_code'] = 200;
		
		// Call all modules in order
		foreach($request_data as $i => $rd)
		{
			if(isset($rd['module']) && isset($rd['action']))
			{
				$modname = "modules/{$rd['module']}/{$rd['action']}.php";

				if(is_file($modname))
				{
					$REQUEST_DATA = $rd;
					$CURRENT_MODULE = $module;
					$CURRENT_ACTION = $action;
					$val = $isolator_call($modname, $rd);

					$CURRENT_MODULE = NULL;
					$CURRENT_ACTION = NULL;

					if($val === false)
						return false;

					if(is_integer($val))
						$RESPONSE_ARRAY['response_data'][] = [
							'result' => ['error_code' => $val],
							'status' => 600,
							'commandNum' => false,
							'timeStamp' => $UNIX_TIMESTAMP
						];
					else
						$RESPONSE_ARRAY['response_data'][] = [
							'result' => $val[0],
							'status' => $val[1],
							'commandNum' => false,
							'timeStamp' => $UNIX_TIMESTAMP
						];
				}
				else
				{
					echo "Req idx $i: {$rd['module']}/{$rd['action']} not found";
					return -1;
				}
			}
			else
			{
				echo "Req idx $i: module/action key not found";
				return -1;
			}
		}
		
		goto request_ok;
	}
	else if($action !== NULL)
	{
		// Single module call in form /main.php/module/action
		$modname = "modules/$module/$action.php";
			
		if(is_file($modname))
		{
			$REQUEST_DATA = $request_data;
			$CURRENT_MODULE = $module;
			$CURRENT_ACTION = $action;
			$val = $isolator_call($modname, $request_data);
			
			$CURRENT_MODULE = NULL;
			$CURRENT_ACTION = NULL;
			
			if($val === false)
				return -1;
			
			if(is_integer($val))
			{
				$RESPONSE_ARRAY["response_data"] = ['error_code' => $val];
				$RESPONSE_ARRAY["status_code"] = 600;
			}
			else
			{
				$RESPONSE_ARRAY["response_data"] = $val[0];
				$RESPONSE_ARRAY["status_code"] = $val[1];
			}
			
			goto request_ok;
		}
		
		echo "Module not found! $module/$action", PHP_EOL;
		return -1;
	}
	else
	{
		echo 'Invalid module/action';
		return 0;
	}
	
	request_ok:
	npps_user::get_instance($USER_ID)->last_active = $UNIX_TIMESTAMP;
	
	return 1;
}

/// \brief Function to process Authorize header
/// \returns Returns string if array is supplied. Returns array if string is
///          supplied. **Returns false if the authorize parameter is invalid**
function npps_authorize_function($authorize)
{
	if(is_array($authorize))
	{
		// Assemble authorize string
		return http_build_query($authorize);
	}
	elseif(is_string($authorize))
	{
		// Disassemble authorize string
		parse_str($authorize, $new_assemble);
		
		// Check the authorize string
		if(
			(
				isset($new_assemble["consumerKey"]) &&
				strcmp($new_assemble["consumerKey"], CONSUMER_KEY) == 0
			) &&
			(
				isset($new_assemble["version"]) &&
				strcmp($new_assemble["version"], "1.1") == 0
			) &&
			isset($new_assemble["nonce"]) &&
			isset($new_assemble["timeStamp"])
		)
			return $new_assemble;
		
		return false;
	}
}

// Load includes
require_once('modules/include.php');

// Initialize configuration
npps_config();

/// \brief NPPS main function. Preparation to process user request is done here
function npps_main()
{
	global $MAINTENANCE_MODE;
	global $REQUEST_HEADERS;
	global $REQUEST_SUCCESS;
	global $RESPONSE_ARRAY;
	global $DATABASE;
	global $MAIN_SCRIPT_HANDLER;
	
	// Will be modified later by the server_api handler
	$USER_ID = 0;
	$TOKEN = NULL;
	$AUTHORIZE_DATA = NULL;
	
	$MODULE_TARGET = NULL;
	$ACTION_TARGET = NULL;
	
	// Set timezone
	if(npps_config('DEFAULT_TIMEZONE'))
		date_default_timezone_set(DEFAULT_TIMEZONE);
	
	// Check if it's maintenance
	if(($MAINTENANCE_MODE = (file_exists("Maintenance") ||
							file_exists("Maintenance.txt") ||
							file_exists("maintenance") ||
							file_exists("maintenance.txt"))
	))
	{
		header('Maintenance: 1');
		exit;
	}
	
	// Check the authorize
	if(isset($REQUEST_HEADERS['authorize']))
		$AUTHORIZE_DATA = npps_authorize_function($REQUEST_HEADERS['authorize']);
	if($AUTHORIZE_DATA === false)
	{
		echo 'Authorize header needed!';
		exit;
	}
	$TOKEN = $AUTHORIZE_DATA["token"] ?? NULL;
	
	// Check if client-version is OK
	if(isset($REQUEST_HEADERS['client-version']))
	{
		if(npps_config('SERVER_VERSION'))
		{
			$ver1 = explode('.', SERVER_VERSION);
			$ver2 = explode('.', $REQUEST_HEADERS['client-version']);
			$trigger_version_up = NULL;
			
			for($i = 0; $i < 3; $i++)
			{
				if(strcmp($ver1[$i], '*') != 0 && $ver1[$i] != $ver2[$i])
				{
					$trigger_version_up = str_replace('*', '0', SERVER_VERSION);
					break;
				}
			}
			
			$trigger_version_up = $trigger_version_up ??
								  $REQUEST_HEADERS['client-version'] ??
								  SERVER_VERSION;
			header("Server-Version: $trigger_version_up");
		}
		else
			header("Server-Version: {$REQUEST_HEADERS['client-version']}");
	}
	else
	{
		echo 'Client-Version header needed!';
		exit;
	}
	
	// Check Platform-Type header
	if(!isset($REQUEST_HEADERS['platform-type']))
	{
		echo 'Platform-Type header needed!';
		exit;
	}
	else if(!is_numeric($REQUEST_HEADERS['platform-type']))
	{
		echo 'Invalid Platform-Type!';
		exit;
	}
	
	// get the module and the action. Use different scope to prevent variables
	// pollution
	{
		$x = [];
		preg_match('!main.php/(\w+)/?(\w*)!', $_SERVER['REQUEST_URI'], $x);
		
		if(isset($x[1]))
			$MODULE_TARGET = $x[1];
		else
		{
			echo 'Module needed!';
			exit;
		}
		
		if(isset($x[2]) && strlen($x[2]) > 0)
			$ACTION_TARGET = $x[2];
	}
	
	if(isset($REQUEST_HEADERS['user-id']) || isset($AUTHORIZE_DATA['user_id']))
	{
		if(isset($REQUEST_HEADERS['user-id']))
			if(preg_match('/\d+/', $REQUEST_HEADERS['user-id']) == 1)
				$USER_ID = intval($REQUEST_HEADERS['user-id']);
			else
			{
				echo 'Invalid user ID';
				exit;
			}
	}
	
	
	// Load database wrapper and initialize it
	$DATABASE = require('database_wrapper.php');
	$DATABASE->initialize_environment();
	
	// Call main script handler
	$REQUEST_SUCCESS = npps_main_script_handler(
		$USER_ID,
		$TOKEN,
		$REQUEST_HEADERS['platform-type'],
		$MODULE_TARGET ?? 'api',
		$ACTION_TARGET
	);
	
	// Check if user id changed
	if($USER_ID > 0)
		header("user_id: $USER_ID");
	
	// Reassemble authorize header
	{
		$new_authorize = [];
		
		foreach($AUTHORIZE_DATA as $k => $v)
			$new_authorize[$k] = $v;
		
		$new_authorize['requestTimeStamp'] = $new_authorize['timeStamp'];
		$new_authorize['timeStamp'] = time();
		$new_authorize['user_id'] = $USER_ID > 0 ? $USER_ID : "";
		
		if(is_string($TOKEN))
			$new_authorize['token'] = $TOKEN;
		
		header(sprintf('authorize: %s', npps_authorize_function($new_authorize)));
	}
	
	// Exit. Let the shutdown function do the rest
	exit;
}

if(!defined('WEBVIEW'))
{
	register_shutdown_function('npps_shutdown');
	ob_start();
	npps_main();
}
