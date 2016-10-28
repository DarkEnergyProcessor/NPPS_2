<?php
/*
 * Null-Pointer Private Server
 * Copyright © 2037 Dark Energy Processor Corporation
 */

/** @@ SIF Error Codes @@ **/
define('ERROR_CODE_NO_ERROR', 0);       // OK
define('ERROR_CODE_LIB_ERROR', 1);      // Lib error
define('ERROR_CODE_LOGIN_INVALID', 407);	// Invalid credentials
define('ERROR_CODE_NETWORK_ERROR', 501);        // Network error
define('ERROR_CODE_LOGIN_FAILED', 502); // Login failed
define('ERROR_CODE_UNAVAILABLE', 503);  // Unavailable
define('ERROR_CODE_TIMEOUT', 504);      // Server error
define('ERROR_CODE_GAME_LOGIC_ERROR', 1001);    // Game logic error
define('ERROR_CODE_DUPLICATE_USER_NAME', 1100); // The name is used by another user.
define('ERROR_CODE_UNAVAILABLE_WORDS', 1101);   // Unavailable words are contained.
define('ERROR_CODE_ENERGY_FULL', 1102);
define('ERROR_CODE_NOT_ENOUGH_LOVECA', 1103);
define('ERROR_CODE_INCENTIVE_NONE', 1201);      // indentive none
define('ERROR_CODE_OPEN_OTHERS', 1202); // Open other's incentive
define('ERROR_ALLIANCE_DUMMY', 1900);   // alliance api failure
define('ERROR_ALLIANCE_DUMMY2', 1901);  // alliance api failure
define('ERROR_ALLIANCE_DUMMY3', 1902);  // alliance api failure
define('ERROR_CODE_SCENARIO_NOT_FOUND', 2300);  // scenario data not found
define('ERROR_CODE_SUBSCENARIO_NOT_FOUND', 2301);
define('ERROR_CODE_TEST', 2800);
define('ERROR_CODE_TEST2', 2801);
define('ERROR_CODE_LIVE_NOT_FOUND', 3400);
define('ERROR_CODE_LIVE_NOT_ENOUGH_MAX_ENERGY', 3401);
define('ERROR_CODE_LIVE_NOT_ENOUGH_CURRENT_ENERGY', 3402);
define('ERROR_CODE_LIVE_NOT_ENOUGH_LOVECA', 3403);
define('ERROR_CODE_LIVE_NOT_ENOUGH_TOKEN', 3412);
define('ERROR_HANDOVER_EXPIRE', 4401);
define('ERROR_HANDOVER_NONE', 4402);
define('ERROR_HANDOVER_SELF', 4403);
/** !! SIF Error Codes !! **/

define('MAIN_INVOKED', true, true);

/* Hopefully nginx fix. Source: http://www.php.net/manual/en/function.getallheaders.php#84262 */
if(!function_exists('getallheaders'))
{
	function getallheaders(): array
	{
		$headers = [];
		foreach ($_SERVER as $name => $value)
		{
			if (substr($name, 0, 5) == 'HTTP_')
			{
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
	   }
	   return $headers;
	}
} 

$MAINTENANCE_MODE = false;
$REQUEST_HEADERS = array_change_key_case(getallheaders(), CASE_LOWER);
$REQUEST_SUCCESS = false;
$RESPONSE_ARRAY = [];
$DATABASE = NULL;
$UNIX_TIMESTAMP = time();
$TEXT_TIMESTAMP = date('Y-m-d H:i:s', $UNIX_TIMESTAMP);

$CURRENT_MODULE = NULL;
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

$HANDLER_SHUTDOWN = function()
{
	global $MAINTENANCE_MODE;
	global $REQUEST_HEADERS;
	global $REQUEST_SUCCESS;
	global $RESPONSE_ARRAY;
	
	if($MAINTENANCE_MODE) exit;	// Don't do anything on maintenance
	
	header('Content-Type: application/json; charset=utf-8');
	header(sprintf("Date: %s", gmdate('D, d M Y H:i:s T')));
	
	$contents = ob_get_contents();
	error_log($contents, 4);
	
	if(!npps_config('DEBUG_ENVIRONMENT'))
		$contents = "";
	
	if($REQUEST_SUCCESS == false)
	{
		$output = [
			'message' => $contents
		];
		
		if(http_response_code() == 200)
			http_response_code(error_get_last() == NULL ? 403 : 500);	// If it's not previously set
		
		ob_end_clean();
		$asd = json_encode($output);
		
		echo $asd;
		exit;
	}
		
	header("status_code: {$RESPONSE_ARRAY["status_code"]}");
	
	if(strlen($contents) > 0)
		$RESPONSE_ARRAY['message'] = $contents;
	
	ob_end_clean();
	ob_start('ob_gzhandler');
	
	$output = json_encode($RESPONSE_ARRAY);
	if(strlen($output) > 2)
	{
		header(sprintf('X-Message-Code: %s', hash_hmac('sha1', $output, X_MESSAGE_CODE_KEY)));
		header(sprintf('X-Message-Sign: %s', base64_encode(str_repeat("\x00", 128))));
		
		echo $output;
	}
	
	ob_end_flush();
	
	exit;
};

$MAIN_SCRIPT_HANDLER = function(string $BUNDLE, int& $USER_ID, $TOKEN, string $OS, int $PLATFORM_ID, string $OS_VERSION, string $TIMEZONE, string $module, $action = NULL): bool
{
	global $REQUEST_HEADERS;
	global $RESPONSE_ARRAY;
	global $DATABASE;
	global $UNIX_TIMESTAMP;
	global $TEXT_TIMESTAMP;
	
	$request_data = [];
	
	if(isset($_POST['request_data']))
	{
		if(npps_config('X_MESSAGE_CODE_CHECK'))
		{
			if(!isset($REQUEST_HEADERS['x-message-code']))
			{
				echo 'X-Message-Code header required!';
				http_response_code(400);
				return false;
			}
			
			if(strcmp($REQUEST_HEADERS['x-message-code'], hash_hmac('sha1', $_POST['request_data'], X_MESSAGE_CODE_KEY)))
			{
				echo "Invalid X-Message-Code";
				http_response_code(400);
				return false;
			}
		}
		
		$request_data = json_decode(mb_convert_encoding($_POST['request_data'], 'UTF-8', 'UTF-8'), true);
		
		if($request_data == NULL)
		{
			echo "Invalid JSON data: {$_POST['request_data']}";
			return false;
		}
	}
	
	if(npps_config('REQUEST_LOGGING'))
	{
		// TODO
	}
	
	// Handler first. A handler doesn't need to have valid token
	if(is_string($action) && strcmp($request_data['module'] ?? '', $module) && strcmp($request_data['action'] ?? '', $action))
	{
		$modname = "handlers/$module/$action.php";
		
		if(is_file($modname))
		{
			$REQUEST_DATA = $request_data;
			$CURRENT_MODULE = $module;
			$CURRENT_ACTION = $action;
			$val = NULL; {$val=include($modname);}
			
			$CURRENT_MODULE = NULL;
			$CURRENT_ACTION = NULL;
			
			if($val === false)
				return false;
			
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
			
			return true;
		}
	}
	
	/* Verify credentials existence */
	if($TOKEN === NULL || token_exist($TOKEN) == false || $USER_ID == 0)
	{
		invalid_credentials:
		echo 'Invalid login, password, user_id, and/or token!';
		return false;
	}
	else
	{
		$cred = npps_query('SELECT login_key, login_pwd FROM `logged_in` WHERE token = ?', 's', $TOKEN)[0];
		$connected_uid = user_id_from_credentials($cred['login_key'], $cred['login_pwd']);
		
		if($connected_uid == 0)
			goto invalid_credentials;
		else if($connected_uid < 0)
		{
			http_response_code(423);
			header("HTTP/1.1 423 Locked");
			return false;
		}
	}
	
	/* ok now modules */
	if(strcmp($module, 'api') == 0)
	{
		/* Multiple module/action calls */
		if(count($request_data) > MILTI_REQUEST_LIMIT)
		{
			echo '/api request limit exceeded!';
			return false;
		}
		
		$RESPONSE_ARRAY["response_data"] = [];
		$RESPONSE_ARRAY["status_code"] = 200;
		
		/* Call all handler in order */
		foreach($request_data as $rd)
		{
			$modname = "modules/{$rd["module"]}/{$rd["action"]}.php";
			
			if(is_file($modname))
			{
				$REQUEST_DATA = $rd;
				$CURRENT_MODULE = $module;
				$CURRENT_ACTION = $action;
				$val = NULL; {$val=include($modname);}
				
				$CURRENT_MODULE = NULL;
				$CURRENT_ACTION = NULL;
				
				if($val === false)
					return false;
				
				if(is_integer($val))
					$RESPONSE_ARRAY["response_data"][] = [
						"result" => ['error_code' => $val],
						"status" => 600,
						"commandNum" => false,
						"timeStamp" => $UNIX_TIMESTAMP
					];
				else
					$RESPONSE_ARRAY["response_data"][] = [
						"result" => $val[0],
						"status" => $val[1],
						"commandNum" => false,
						"timeStamp" => $UNIX_TIMESTAMP
					];
			}
			else
			{
				echo "One of the module not found: {$rd['module']}/{$rd['action']}";
				return false;
			}
		}
		
		return true;
	}
	else if($action !== NULL && isset($request_data['module']) && isset($request_data['action']) &&
			strcmp($request_data['module'], $module) == 0 && strcmp($request_data['action'], $action) == 0)
	{
		/* Single module call in form /main.php/module/action */
		$modname = "modules/$module/$action.php";
			
		if(is_file($modname))
		{
			$REQUEST_DATA = $request_data;
			$CURRENT_MODULE = $module;
			$CURRENT_ACTION = $action;
			$val = NULL; {$val=include($modname);}
			
			$CURRENT_MODULE = NULL;
			$CURRENT_ACTION = NULL;
			
			if($val === false)
				return false;
			
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
			
			return true;
		}
		
		echo "Module not found! $module/$action", PHP_EOL;
		return false;
	}
	else
	{
		echo 'Invalid module/action';
		return false;
	}
};

/* Returns string if array is supplied; Returns array if string is supplied */
/* Returns false if the authorize parameter is invalid */
function authorize_function($authorize)
{
	if(is_array($authorize))
	{
		/* Assemble authorize string */
		return http_build_query($authorize);
	}
	elseif(is_string($authorize))
	{
		/* Disassemble authorize string */
		parse_str($authorize, $new_assemble);
		
		/* Check the authorize string */
		if(
			(isset($new_assemble["consumerKey"]) && strcmp($new_assemble["consumerKey"], CONSUMER_KEY) == 0) &&
			(isset($new_assemble["version"]) && strcmp($new_assemble["version"], "1.1") == 0) &&
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

if(!defined('WEBVIEW'))
{
	function main()
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
		
		/* Set timezone */
		if(npps_config('DEFAULT_TIMEZONE'))
			date_default_timezone_set(DEFAULT_TIMEZONE);
		
		/* Check if it's maintenance */
		if(file_exists("Maintenance") || file_exists("Maintenance.txt") ||
		   file_exists("maintenance") || file_exists("maintenance.txt")
		)
		{
			header("Maintenance: 1");
			$MAINTENANCE_MODE = true;
			exit;
		}
		
		/* Check the authorize */
		if(isset($REQUEST_HEADERS["authorize"]))
			$AUTHORIZE_DATA = authorize_function($REQUEST_HEADERS["authorize"]);
		if($AUTHORIZE_DATA === false)
		{
			echo "Authorize header needed!";
			exit;
		}
		$TOKEN = $AUTHORIZE_DATA["token"] ?? NULL;
		
		/* Check the bundle version */
		if(!isset($REQUEST_HEADERS["bundle-version"]))
		{
			echo 'Bundle-Version header needed!';
			exit;
		}
		
		/* Check if client-version is OK */
		if(isset($REQUEST_HEADERS["client-version"]))
		{
			if(npps_config('SERVER_VERSION'))
			{
				//header("Server-Version: ".SERVER_VERSION);
				$ver1 = explode('.', SERVER_VERSION);
				$ver2 = explode('.', $REQUEST_HEADERS["client-version"]);
				$trigger_version_up = NULL;
				
				for($i = 0; $i < 3; $i++)
				{
					if(strcmp($ver1[$i], '*') != 0 && $ver1[$i] != $ver2[$i])
					{
						$trigger_version_up = str_replace('*', '0', SERVER_VERSION);
						break;
					}
				}
				
				$trigger_version_up = $trigger_version_up ?? $REQUEST_HEADERS["client-version"] ?? SERVER_VERSION;
				header("Server-Version: $trigger_version_up");
			}
			else
				header("Server-Version: {$REQUEST_HEADERS["client-version"]}");
		}
		else
		{
			echo "Client-Version header needed!";
			exit;
		}
		
		/* get the module and the action. Use different scope */
		{
			preg_match('!main.php/(\w+)/?(\w*)!', $_SERVER["REQUEST_URI"], $x);
			
			if(isset($x[1]))
				$MODULE_TARGET = $x[1];
			else
			{
				echo "Module needed!";
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
		
		
		/* Load database wrapper and initialize it */
		$DATABASE = require('database_wrapper.php');
		$DATABASE->initialize_environment();
		
		/* Call handler. Parameters: bundle-version, user_id, token, os, platform-id, os-version, time-zone = "unknown", module = "api", action = NULL */
		$REQUEST_SUCCESS = $MAIN_SCRIPT_HANDLER(
			$REQUEST_HEADERS["bundle-version"],
			$USER_ID,
			$TOKEN,
			$REQUEST_HEADERS["os"] ?? "unknown",
			$REQUEST_HEADERS["platform-type"] ?? -1,
			$REQUEST_HEADERS["os-version"] ?? "unknown",
			$REQUEST_HEADERS["time-zone"] ?? "unknown",
			$MODULE_TARGET ?? "api",
			$ACTION_TARGET
		);
		
		/* Check if user id changed */
		if($USER_ID > 0)
			header("user_id: $USER_ID");
		
		/* Reassemble authorize function */
		{
			$new_authorize = [];
			
			foreach($AUTHORIZE_DATA as $k => $v)
				$new_authorize[$k] = $v;
			
			$new_authorize["requestTimeStamp"] = $new_authorize["timeStamp"];
			$new_authorize["timeStamp"] = time();
			$new_authorize["user_id"] = $USER_ID > 0 ? $USER_ID : "";
			
			if(is_string($TOKEN))
				$new_authorize["token"] = $TOKEN;
			
			header(sprintf("authorize: %s", authorize_function($new_authorize)));
		}
		
		/* Exit. Let the shutdown function do the rest */
		exit;
	}

	register_shutdown_function($HANDLER_SHUTDOWN);
	ob_start();
	main();
}
