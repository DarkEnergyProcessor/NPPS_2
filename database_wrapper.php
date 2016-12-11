<?php
/*
 * Null-Pointer Private Server
 * Provides database support. MySQL or SQLite3 can be used as the database backend.
 */

/// \file database_wrapper.php

if(!defined('MAIN_INVOKED')) exit;

/// \brief Internal function to initialize NPPS for the first time
/// \param db The main database wrapper
/// \param direct_db The bare handle of the database
/// \exception Exception Thrown if database can't be initialized.
function common_initialize_environment(DatabaseWrapper $db, $direct_db = NULL)
{
	npps_begin_transaction();
	if(
		npps_file_query('data/initialize_npps.sql') &&
		npps_file_query('data/initialize_daily_login_bonus.sql')
	)
	{
		npps_end_transaction();
		
		return;
	}
	throw new Exception('Unable to initialize environment!');
}

$GLOBALS['common_sqlite3_concat_function'] = function(string ...$arg): string
{
	return implode($arg);
};

/// \brief Abstract class of the NPPS database wrapper
abstract class DatabaseWrapper
{
	/// The db handle. Can be handle to MySQL connection or SQLite database file
	protected $db_handle;
	/// The database ID. Automatically generated
	protected $db_id;
	
	abstract function __construct();
	
	/// Initialize the database for the first time
	abstract public function initialize_environment();
	
	/// \brief Execute query, additionally prepared.
	/// \param query The SQL string
	/// \param types The prepared statement value types or NULL if prepared statement is not used.
	/// \param values Value to be passed in the prepared statement
	/// \returns Array if it returns table or `true` if it's not (but the query success).
	///          `false` if the query is failed.
	/// \note If there's multiple SELECT, only the first result is returned
	abstract public function query(string $query, string $types = NULL, ...$values);
	
	/// \brief Returns string representation of this database wrapper
	abstract public function __toString();
	
	/// \brief Create custom ordering SQL string
	static public function custom_ordering(string $field_name, ...$order): string
	{
		if(is_array($order[0]))
			$order = $order[0];
		
		if(count($order) == 0)
			return '';
		
		$out = ["ORDER BY CASE `$field_name`"];
		$max_len = count($order);
		
		foreach($order as $key => $val)
		{
			if(is_string($val))
				$out[] = "WHEN `$val` THEN $key";
			else
				$out[] = "WHEN $val THEN $key";
		}
		
		$out[] = "ELSE $max_len END";
		return implode(' ', $out);
	}
	
	/// \brief Closes the database handle
	function __destruct() {}
};

/*****************************************
** Database Wrapper: MySQL Wrapper      **
*****************************************/
/// \brief NPPS MySQL database backend wrapper
class MySQLDatabaseWrapper extends DatabaseWrapper
{
	function __construct()
	{
		$this->db_handle = new mysqli(
			npps_config('DBWRAPPER_MYSQL_HOSTNAME'),
			npps_config('DBWRAPPER_MYSQL_USERNAME'),
			npps_config('DBWRAPPER_MYSQL_PASSWORD'),
			npps_config('DBWRAPPER_DBNAME'),
			npps_config('DBWRAPPER_MYSQL_PORT'));
		
		if($this->db_handle->connect_error)
			throw new Exception('Error ('.$this->db_handle->connect_errno.') '.$this->db_handle->connect_error);
		
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	}
	
	public function initialize_environment()
	{
		if(count($this->query('SHOW TABLES LIKE \'initialized\'')) > 0) return;
		
		// Clean all tables
		$this->query('BEGIN');
		foreach($this->query('SHOW TABLES') as $tables)
		{
			$table_name = reset($tables);
			$this->query("DROP TABLE $table_name");
		}
		
		common_initialize_environment($this, $this->db_handle);
		
		// Add initialized flag
		$this->query('CREATE TABLE `initialized` (a INT)');
		$this->query('COMMIT');
	}
	
	public function query(string $query, string $types = NULL, ...$values)
	{
		$query = str_ireplace(
			'INSERT OR IGNORE',
			'INSERT IGNORE',
			preg_replace(
				'/\?\d*/',
				'?',
				str_ireplace(
					'RANDOM',
					'RAND',
					$query
				)
			)
		);
		
		if(isset($values[0]) && is_array($values[0]))
			$values = $values[0];
		
		if($types != NULL)
		{
			if(($stmt = $this->db_handle->prepare($query)))
			{
				$stmt->bind_param($types, ...$values);
				
				if($stmt->execute())
				{
					$result = $stmt->get_result();
					
					if($result)
					{
						/* Has result */
						$out = $result->fetch_all(MYSQLI_ASSOC);
						$result->free();
						
						return $out;
					}
					
					return true;
				}
				
				if($this->db_handle->error)
					echo 'Error '.$this->db_handle->error, PHP_EOL;
				
				return false;
			}
			
			if($this->db_handle->error)
				echo 'Error '.$this->db_handle->error, PHP_EOL;
			
			return false;
		}
		else
		{
			$result = $this->db_handle->multi_query($query);
			
			if($result == false)
			{
				echo 'Error '.$this->db_handle->error, PHP_EOL;
				return false;
			}
			
			if(($result = $this->db_handle->use_result()))
			{
				$fields = $result->fetch_fields();
				$result_array = $result->fetch_all(MYSQLI_ASSOC);
				$result->free();
				
				/* Convert the datatypes if possible */
				if($fields)
				{
					/* Because associative array, also make fields as assoc */
					foreach($fields as $x)
						$fields[$x->name] = $x;
					
					/* Enum */
					foreach($result_array as &$values)
					{
						foreach($fields as $i => $types)
						{
							$target = &$values[$i];
							
							switch($types->type)
							{
								case MYSQLI_TYPE_TINY:
								case MYSQLI_TYPE_SHORT:
								case MYSQLI_TYPE_LONG:
								case MYSQLI_TYPE_INT24:
								{
									$target = intval($target);
									break;
								}
								case MYSQLI_TYPE_LONGLONG:
								{
									if(PHP_INT_MAX > 2147483647)
										// It's 64-bit. Convert it.
										$target = intval($target);
									
									break;
								}
								case MYSQLI_TYPE_DECIMAL:
								case MYSQLI_TYPE_NEWDECIMAL:
								case MYSQLI_TYPE_DOUBLE:
								case MYSQLI_TYPE_FLOAT:
								{
									$target = floatval($target);
									break;
								}
								default:
								{
									// Do mothing
									break;
								}
							}
						}
					}
				}
				
				return $result_array;
			}
			
			if($this->db_handle->error)
			{
				echo 'Error '.$this->db_handle->error, PHP_EOL;
				return false;
			}
			
			while($this->db_handle->more_results() && $this->db_handle->next_result()) {}
			
			if($this->db_handle->error)
			{
				echo 'Error '.$this->db_handle->error, PHP_EOL;
				return false;
			}
			
			/* No result. Return true */
			return true;
		}
	}
	
	public function __toString(): string
	{
		return 'DatabaseWrapper: Main MySQL';
	}
	
	public function __destruct()
	{
		$this->db_handle->close();
	}
};

/*****************************************
** Database Wrapper: SQLite3 Wrapper    **
*****************************************/
/// \brief NPPS SQLite3 database backend wrapper
class SQLite3DatabaseWrapper extends DatabaseWrapper
{
	protected $custom_filename;
	
	public function __construct(string $filename = NULL)
	{
		$custom_filename = false;
		$dbname = DBWRAPPER_DBNAME . '.db';
		
		if($filename)
		{
			$dbname = $filename;
			$this->custom_filename = true;
			$this->db_id = random_int(0, 2147483647);
		}
		
		$this->db_handle = new SQLite3(
			$dbname,
			$custom_filename ? SQLITE3_OPEN_READONLY :
			                  (SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE)
		);
		$this->db_handle->busyTimeout(5000);			// timeout: 5 seconds
		$this->db_handle->createFunction(
			'CONCAT',
			$GLOBALS['common_sqlite3_concat_function']
		);
	}
	
	public function initialize_environment()
	{
		if($this->custom_filename)
			throw new Exception('Cannot initialize environment when opening another DB file');
		
		{
			$result = $this->db_handle->query('SELECT name FROM `sqlite_master` WHERE tbl_name = \'initialized\'');
			$has_result = false;
			
			while($row = $result->fetchArray())
				$has_result = true;
			
			if($has_result)
				return;
		}
		
		fclose(fopen(DBWRAPPER_DBNAME.'.db', 'w'));
		
		if(!defined('DEBUG_ENVIRONMENT'))
			if($this->db_handle->version()['versionNumber'] < 3007000)
				throw new Exception('SQLite3 database wrapper requires SQLite v3.7.0 or later!');
			else
				// Journal mode: WAL; production environment only
				$this->db_handle->exec("PRAGMA journal_mode=WAL");
		else
			// Journal mode: memory; debug environment only
			$this->db_handle->exec('PRAGMA journal_mode=memory');
		
		common_initialize_environment($this);
		
		$this->db_handle->exec('CREATE TABLE `initialized` (a, b)');
	}
	
	public function query(string $query, string $types = NULL, ...$values)
	{
		/* Try to convert the MySQL-specific keyword to SQLite */
		$query = str_ireplace(
			"AUTO_INCREMENT",
			"AUTOINCREMENT",
			str_ireplace("LAST_INSERT_ID", "last_insert_rowid", $query)
		);
		
		if(isset($values[0]) && is_array($values[0]))
			$values = $values[0];
		
		if($types != NULL)
		{
			if(($stmt = $this->db_handle->prepare($query)))
			{
				foreach($values as $k => $v)
				{
					$datatype = SQLITE3_NULL;
					
					switch($types[$k])
					{
						case "b":
						{
							$datatype = SQLITE3_BLOB;
							break;
						}
						case "d":
						{
							$datatype = SQLITE3_FLOAT;
							break;
						}
						case "i":
						{
							$datatype = SQLITE3_INTEGER;
							break;
						}
						case "s":
						{
							$datatype = SQLITE3_TEXT;
							break;
						}
						default:
						{
							break;
						}
					}
					
					$stmt->bindValue($k + 1, $v, $datatype);
				}
				
				if(($result = $stmt->execute()))
				{
					if($result->numColumns())
					{
						/* There's result */
						$out = [];
						
						while($row = $result->fetchArray(SQLITE3_ASSOC))
							$out[] = $row;
						
						return $out;
					}
					
					return true;
				}
				
				return false;
			}
		}
		else
		{
			if(stripos($query, 'SELECT') === false)
				return $this->db_handle->exec($query);
			
			$result = $this->db_handle->query($query);
			
			if($result == false)
				return false;
			
			$out = [];
			
			while($row = $result->fetchArray(SQLITE3_ASSOC))
				$out[] = $row;
			
			return $out;
		}
	}
	
	public function __toString(): string
	{
		if($this->custom_filename)
			return "DatabaseWrapper: SQLite3 {$this->db_id}";
		else
			return "DatabaseWrapper: Main SQLite3";
	}
	
	public function __destruct()
	{
		$this->db_handle->close();
	}
};

class SecretboxDatabaseWrapper extends SQLite3DatabaseWrapper
{
	public function __construct()
	{
		$this->db_handle = new SQLite3(
			'npps_secretbox.db',
			(SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE)
		);
		$this->db_handle->busyTimeout(5000);			// timeout: 5 seconds
		$this->db_handle->createFunction(
			'CONCAT',
			$GLOBALS['common_sqlite3_concat_function']
		);
	}
	
	public function initialize_environment()
	{
		{
			$result = $this->db_handle->query('
				SELECT name FROM `sqlite_master`
				WHERE tbl_name = \'secretbox_initialized\'
			');
			$has_result = false;
			
			while($row = $result->fetchArray())
				$has_result = true;
			
			if($has_result)
				return;
		}
		
		fclose(fopen('npps_secretbox.db', 'w'));
		
		$this->db_handle->exec('PRAGMA journal_mode=off');
		$this->query('BEGIN');
		$this->db_handle->exec(file_get_contents('data/initialize_secretbox.sql'));
		$this->query('CREATE TABLE `secretbox_initialized` (a, b)');
		$this->query('COMMIT');
	}
	
	public function __toString(): string
	{
		return 'DatabaseWrapper: Secretbox SQLite3';
	}
}

// Initialize secretbox DB
{
	$temp = new SecretboxDatabaseWrapper();
	$temp->initialize_environment();
	npps_database_list::$db_list['secretbox'] = $temp;
}

if(npps_config('DBWRAPPER_USE_MYSQL'))
	return new MySQLDatabaseWrapper();
else
	return new SQLite3DatabaseWrapper();
