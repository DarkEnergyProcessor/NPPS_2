<?php
/*
 * Null-Pointer Private server
 * Contains common functions
 */

/// \file include.php

/// \brief Same as strpos() but accepts array as the neddles.
/// \param haystack The string to search in
/// \param needles List of string to be searched
/// \returns Position of string occured, or false if not found
function strpos_array(string $haystack, array $needles)
{
	$pos = NULL;
	
	if(count($needles) == 0)
		return false;
	
	foreach($needles as $str) {
		if(is_array($str))
			$pos = strpos_array($haystack, $str);
		else
			$pos = strpos($haystack, $str);
		
		if($pos !== false)
			return $pos;
	}
}

/// \brief Converts UNIX timestamp to SIF-compilant datetime relative to
///        **local server** time.
/// \param timestamp UNIX timestamp to convert
/// \returns SIF-compilant datetime strong
/// \note The result datetime always starts at January 2nd 1970.
function to_datetime(int $timestamp): string
{
	if($timestamp < 86400) $timestamp += 86400;
	
	return date('Y-m-d H:i:s', $timestamp);
}

/// \brief Converts UNIX timestamp to SIF-compilant datetime relative to UTC
///        time.
/// \param timestamp UNIX timestamp to convert
/// \returns SIF-compilant datetime strong
/// \note The result datetime always starts at January 2nd 1970.
function to_utcdatetime(int $timestamp): string
{
	if($timestamp < 86400) $timestamp += 86400;
	
	return gmdate('Y-m-d H:i:s', $timestamp);
}

/// \brief Converts timestamp to string which gives human readable
///        representation of time difference (like 2d ago, ...).
/// \param timestamp UNIX timestamp to convert
/// \param full Do not use single letter for time identification (like d = day)?
/// \returns Human readable string
function time_elapsed_string(int $timestamp, bool $full = false): string
{
	$now = new DateTime;
	$ago = new DateTime("@$timestamp");
	$diff = $now->diff($ago);

	$diff->w = floor($diff->d / 7);
	$diff->d -= $diff->w * 7;

	$string = [
		'y' => 'year',
		'm' => 'month',
		'w' => 'week',
		'd' => 'day',
		'h' => 'hour',
		'i' => 'minute',
		's' => 'second',
	];
	foreach ($string as $k => &$v) {
		if ($diff->$k) {
			$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
		} else {
			unset($string[$k]);
		}
	}

	if (!$full) $string = array_slice($string, 0, 1);
	return $string ? implode(', ', $string) . ' ago' : 'just now';
}

/// \brief Get database handle of specificed database.
/// \param db_name The database name to get it's handle
/// \returns DatabaseWrapper object.
/// \sa DatabaseWrapper
function npps_get_database(string $db_name = ''): DatabaseWrapper
{
	static $db_list = [];
	
	if(isset($db_list[$db_name]))
		return $db_list[$db_name];
	else
	{
		if(strlen($db_name) == 0)
			return $db_list[''] = $GLOBALS['DATABASE'];
		
		$xdb = new SQLite3Database("data/$db_name.db_");
		return $db_list[$db_name] = $xdb;
	}
}

/// \brief Attach database from npps_get_database() if not attached.
/// \param db Database handle
/// \param another_db Database name to attach
/// \param db_list Lists of another database name to attach
/// \note This function does nothing if main database is specificed.
function npps_attach_database(DatabaseWrapper $db, string $another_db,
							  string ...$db_list)
{
	static $attached_db = [];
	
	if($db == $GLOBALS['DATABASE'])
		return;	// attach impossible; SQL-specific
	
	$string_representation = $db->__toString();
	
	if(isset($attached_db[$string_representation]) == false)
		$attached_db[$string_representation] = [];
	
	if(isset($attached_db[$string_representation][$another_db]) == false)
	{
		$re = str_replace('/', '_', $another_db);
		$attached_db[$string_representation][$another_db] = $db->query(
			"ATTACH DATABASE `data/$another_db.db_` as $re"
		);
	}
	
	foreach($db_list as $name)
	{
		if(isset($attached_db[$string_representation][$name]) == false)
		{
			$re = str_replace('/', '_', $name);
			$attached_db[$string_representation][$name] = $db->query(
				"ATTACH DATABASE `data/$name.db_` as $re"
			);
		}
	}
}

/// \brief Executes query in main database. Equivalent to $DATABASE->query()
/// \param query SQL query
/// \param list datatype list if prepared statement is used. Follows MySQL
///        datatype character
/// \param arglist argument list passed to prepared statement (if used)
/// \returns The result of the query
function npps_query(string $query, string $list = NULL, ...$arglist)
{
	$DATABASE = $GLOBALS['DATABASE'];
	
	if($list !== NULL)
		return $DATABASE->query($query, $list, ...$arglist);
	else
		return $DATABASE->query($query);
}

/// \brief Similar to npps_query, but specify SQL file and variables to be
///        substituted inside. Prepared statement is not supported as it might
///        contain multiple statements.
/// \param filename filename which contains the SQL string
/// \param variable_list PHP variables to be substituted inside the SQL string
/// \returns The result of the query
function npps_file_query(string $filename, array $variable_list = [])
{
	$__x = file_get_contents($filename);

	{
		extract($variable_list);
		return npps_query(eval("
return <<<DATA
$__x
DATA
;
		"));
	}
}

/// \brief Like explode, but converts value to number if necessary
/// \param delimiter The boundary string
/// \param str The input string
/// \returns Splitted strings as array.
function npps_separate(string $delimiter, string $str): array
{
	if(strlen($str) > 0)
	{
		$datalist = explode($delimiter, $str);
		
		array_walk($datalist, function(&$v, $k)
		{
			if(is_numeric($v))
				$v = $v + 0;
		});
		
		return $datalist;
	}
	
	return [];
}

/// \brief Get server hostname (and port if necessary)
/// \returns Server hostname, and port if it's not hosted in usual port.
function npps_gethost(): string
{
	static $cached_result = NULL;
	
	if($cached_result != NULL)
		return $cached_result;
	
	if(isset($_SERVER['HTTP_HOST']))
		return $cached_result = $_SERVER['HTTP_HOST'];
	
	if($_SERVER['SERVER_PORT'] == ($_SERVER['HTTPS'] ? 443 : 80))
		return $cached_result = $_SERVER['REMOTE_ADDR'];
	else
		return $cached_result = sprintf('%s:%d',
			$_SERVER['REMOTE_ADDR'], $_SERVER['SERVER_PORT']
		);
}

/// \brief Sets HTTP response, with an additional custom response message
/// \param response_code Response code to set
/// \param response_message Custom response message to set
function npps_http_code(int $response_code, string $response_message = '')
{
	http_response_code($response_code);
	
	if(strlen($response_message) > 0)
		header($_SERVER['SERVER_PROTOCOL']." $response_code $response_message");
}

/// \brief Logs specificed message to `message` field in the response data
///        and in web server log
/// \param text Text to log
function npps_log(string $text)
{
	error_log($text, 4);
	echo $text;
}

/// \brief Class used for npps_begin_transaction(), npps_end_transaction(),
///        and npps_commit_transaction()
final class npps_nested_transaction
{
	private $nested_count = 0;
	
	private function __construct()
	{
		$this->nested_count = 0;
	}
	
	public function __destruct()
	{
		if($this->nested_count > 0)
		{
			npps_query('COMMIT');
			throw Exception('Unbalanced nested transaction');
		}
	}
	
	public function begin()
	{
		if($this->nested_count == 0)
			npps_query('BEGIN');
		
		$this->nested_count++;
	}
	
	public function commit()
	{
		if($this->nested_count == 0)
			throw Exception('Unbalanced nested transaction');
		if($this->nested_count == 1)
			npps_query('COMMIT');
		
		$this->nested_count--;
	}
	
	public function commit_force()
	{
		if($nested_count > 0)
		{
			npps_query('COMMIT');
			$nested_count = 0;
		}
	}
	
	public static function instance(): npps_nested_transaction
	{
		static $x = NULL;
		
		if($x === NULL)
			$x = new npps_nested_transaction();
		
		return $x;
	}
};

/// \brief Starts transaction. The internal counter is added by 1.
/// \exception Exception if the transaction is unbalanced
function npps_begin_transaction()
{
	npps_nested_transaction::instance()->begin();
}

/// \brief Ends transaction. The internal counter is subtracted by 1.
///        If internal counter reaches zero, then transaction to DB is started.
/// \exception Exception Thrown if the transaction is unbalanced
///            (called without npps_begin_transaction())
function npps_end_transaction()
{
	npps_nested_transaction::instance()->commit();
}

/// \brief Identical to npps_commit_transaction(), but always starts transaction
///        to DB and set internal counter to 0.
function npps_commit_transaction()
{
	npps_nested_transaction::instance()->commit_force();
}

/// \brief Get configuration value from configuration file(npps.ini)
/// \param config_name The configuration name
/// \param access_outside Makes the configuration to allow access to all
///        configurations
/// \returns Value of the configuration (can be NULL) or NULL if configuration
///          name is not exists.
function npps_config(string $config_name = '', bool $access_outside = false)
{
	static $config_list = NULL;
	global $CURRENT_MODULE;
	global $CURRENT_ACTION;
	
	$module_action = NULL;
	
	if($config_list == NULL)
	{
		$config_list = parse_ini_file('data/npps.ini', true, INI_SCANNER_TYPED);
		
		foreach($config_list as $k => $v)
			// Only define for global scope
			if(!is_array($v))
				define($k, $v);
	}
	
	if(strlen($config_name) == 0)
		return $config_list;	// Return configuration list instead
	
	if($CURRENT_MODULE && $CURRENT_ACTION)
		$module_action = "$CURRENT_MODULE/$CURRENT_ACTION";
	
	if($access_outside)
	{
		// Outside config access.
		$class_access = explode('/', $config_name);
		
		switch(count($class_access))
		{
			case 1:
			{
				if(
					isset($config_list[$class_access[0]]) &&
					!is_array($config_list[$class_access[0]])
				)
					return $config_list[$class_access[0]];
				
				break;
			}
			case 2:
			{
				if(isset($config_list[$class_access[0]]))
					if(isset($config_list[$class_access[0]][$class_access[1]]))
						return $config_list[$class_access[0]][$class_access[1]];
				
				break;
			}
			default:
			{
				$concat_mod_act = "{$class_access[0]}/{$class_access[1]}";
				
				if(isset($config_list[$concat_mod_act]))
					if(isset($config_list[$concat_mod_act][$class_access[2]]))
						return $config_list[$concat_mod_act][$class_access[2]];
				
				break;
			}
		}
		
		// No matches
		return NULL;
	}
	else
	{
		if($module_action)
		{
			// First priority: get config in form "module/action"
			if(isset($config_list[$module_action]))
				if(isset($config_list[$module_action][$config_name]))
					return $config_list[$module_action][$config_name];
			
			// Second priority: get config in form "module"
			if(isset($config_list[$CURRENT_MODULE]))
				if(isset($config_list[$CURRENT_MODULE][$config_name]))
					return $config_list[$CURRENT_MODULE][$config_name];
		}
		
		// Third priority: get config in global
		if(
			isset($config_list[$config_name]) &&
			!is_array($config_list[$config_name])
		)
			return $config_list[$config_name];
		
		// No matches.
		return NULL;
	}
}

/// \brief Calls module/action (and only module, not handler) and returns it's
///        response.
/// \param module_action <module>/<action> to be called
/// \param request_data The module request_data, if any
/// \returns Response data in `array` on success, `integer` if request
///          can't be statisfied, or `NULL` on failure.
function npps_call_module(string $module_action, array $request_data = [])
{
	global $REQUEST_HEADERS;
	global $UNIX_TIMESTAMP;
	global $TEXT_TIMESTAMP;
	global $CURRENT_MODULE;
	global $CURRENT_ACTION;
	
	$previous_module = $CURRENT_MODULE;
	$previous_action = $CURRENT_ACTION;
	
	$call_target = explode('/', $module_action);
	$REQUEST_DATA = [];
	
	if(count($call_target) < 2)
		return NULL;
	
	$REQUEST_DATA['module'] = $CURRENT_MODULE = $call_target[0];
	$REQUEST_DATA['action'] = $CURRENT_ACTION = $call_target[1];
	
	foreach($request_data as $k => $v)
		$REQUEST_DATA[$k] = $v;
	
	$val = NULL; {$val = include("modules/$module_action.php");}
	
	$CURRENT_MODULE = $previous_module;
	$CURRENT_ACTION = $previous_action;
	
	if(is_integer($val))
		return $val;
	else if(is_array($val))
	{
		if($val[1] == 600)
			return $val[0]['error_code'];
		else
			return $val[0];
	}
	else if(!$val)
		return NULL;
}

/// \brief Get decryption key (v4.0.x only)
/// \param index The decryption key ID
/// \param raw Base64 decode the decryption key first?
/// \returns Base64 encoded string (if raw is false) of the decryption key.
function npps_decryption_key(int $index = 0, bool $raw = true)
{
	static $decryption_key_list = NULL;
	
	if($index == 0)
	{
		// Return the decryption key list instead
		if($decryption_key_list == NULL)
			// But, if it's NULL, load it first
			$decryption_key_list = parse_ini_file('data/decryption_key.ini');
		
		return $decryption_key_list;
	}
	
	if(isset($decryption_key_list[$index]))
		if($raw)
			return base64_decode($decryption_key_list[$index]);
		else
			return $decryption_key_list[$index];
	else
		return NULL;
}

require('modules/include.unit.php');
require('modules/include.deck.php');
require('modules/include.item.php');
require('modules/include.live.php');
require('modules/include.token.php');
require('modules/include.user.php');
