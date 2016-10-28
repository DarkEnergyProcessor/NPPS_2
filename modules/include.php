<?php
/*
 * Null-Pointer Private server
 * Contains common functions
 */

function strpos_array(string $haystack, array $needles) {
	if(is_array($needles)) {
		if(count($needles) == 0)
			return false;
		foreach ($needles as $str) {
			if ( is_array($str) ) {
				$pos = strpos_array($haystack, $str);
			} else {
				$pos = strpos($haystack, $str);
			}
			if ($pos !== FALSE) {
				return $pos;
			}
		}
	} else {
		return strpos($haystack, $needles);
	}
}

function to_datetime(int $timestamp): string
{
	if($timestamp < 86400) $timestamp += 86400;
	
	return date('Y-m-d H:i:s', $timestamp);
}

function to_utcdatetime(int $timestamp): string
{
	if($timestamp < 86400) $timestamp += 86400;
	
	return gmdate('Y-m-d H:i:s', $timestamp);
}

function time_elapsed_string(int $datetime, bool $full = false): string {
	$now = new DateTime;
	$ago = new DateTime("@$datetime");
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

/* Get SQLite3 database handle */
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

// Attach database from npps_get_database() if not attached.
function npps_attach_database(DatabaseWrapper $db, string $another_db, string ...$db_list)
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
		$attached_db[$string_representation][$another_db] = $db->execute_query("ATTACH DATABASE `data/$another_db.db_` as $re");
	}
	
	foreach($db_list as $name)
	{
		if(isset($attached_db[$string_representation][$name]) == false)
		{
			$re = str_replace('/', '_', $name);
			$attached_db[$string_representation][$name] = $db->execute_query("ATTACH DATABASE `data/$name.db_` as $re");
		}
	}
}

// Equivalent to $DATABASE->execute_query(...)
function npps_query(string $query, string $list = NULL, ...$arglist)
{
	$DATABASE = $GLOBALS['DATABASE'];
	
	if($list !== NULL)
		return $DATABASE->execute_query($query, $list, ...$arglist);
	else
		return $DATABASE->execute_query($query);
}

// Similar to npps_query, but specify SQL file and variables to be substituted inside
// And also no prepared statement as it might contain multiple statements
function npps_file_query(string $filename, array $variable_list = [])
{
	$__x = file_get_contents($filename);
	$__res = NULL;
	
	{
		extract($variable_list);
		$res = eval(<<<HELLO
return <<<DATA
BEGIN;
$__x
COMMIT;
DATA
HELLO
		);
	}
	
	return npps_query($__res);
}

// Like explode, but converts value to number if necessary
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

// Get server hostname (and port if necessary)
function npps_gethost(): string
{
	static $cached_result = NULL;
	
	if($cached_result != NULL)
		return $cached_result;
	
	if(isset($_SERVER['HTTP_HOST']))
		return $cached_result = $_SERVER['HTTP_HOST'];
	
	if($_SERVER['SERVER_PORT'] == 80)
		return $cached_result = $_SERVER['REMOTE_ADDR'];
	else
		return $cached_result = sprintf('%s:%d', $_SERVER['REMOTE_ADDR'], $_SERVER['SERVER_PORT']);
}

// Sets HTTP response
function npps_http_code(int $response_code, string $response_message = '')
{
	http_response_code($response_code);
	
	if(strlen($response_message) > 0)
		header($_SERVER['SERVER_PROTOCOL']." $response_code $response_message");
}

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

function npps_begin_transaction()
{
	npps_nested_transaction::instance()->begin();
}

function npps_end_transaction()
{
	npps_nested_transaction::instance()->commit();
}

function npps_commit_transaction()
{
	npps_nested_transaction::instance()->commit_force();
}

// Get configuration value or NULL
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
		
		if(strlen($config_name) == 0)
			return;	// Only initializing
	}
	
	if($CURRENT_MODULE && $CURRENT_ACTION)
		$module_action = "$CURENT_MODULE/$CURRENT_ACTION";
	
	if($access_outside)
	{
		// Outside config access.
		$class_access = explode('/', $config_name);
		
		switch(count($class_access))
		{
			case 1:
			{
				if(isset($config_list[$class_access[0]]) && !is_array($config_list[$class_access[0]]))
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
		if(isset($config_list[$config_name]) && !is_array($config_list[$config_name]))
			return $config_list[$config_name];
		
		// No matches.
		return NULL;
	}
}

// Calls module/action (and only module, not handler) and returns it's value
// Returns integer if request can't be statisfied.
// Returns NULL on failure
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

require('modules/include.card.php');
require('modules/include.deck.php');
require('modules/include.item.php');
require('modules/include.live.php');
require('modules/include.token.php');
require('modules/include.user.php');
