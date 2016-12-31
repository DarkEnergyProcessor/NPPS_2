<?php
$GLOBALS['common_sqlite3_concat_function'] = function(string ...$arg): string
{
	return implode($arg);
};

class SQLite3DatabaseWrapper
{
	protected $db_handle;
	protected $custom_filename;
	
	public function __construct(string $filename)
	{	
		$this->db_handle = new SQLite3($filename);
		$this->db_handle->busyTimeout(5000);				// timeout: 5 seconds
		$this->db_handle->createFunction('CONCAT', $GLOBALS['common_sqlite3_concat_function']);
		$this->db_handle->exec('PRAGMA journal_mode=wal');
	}
	
	public function query(string $query, string $types = NULL, ...$values)
	{
		if(isset($values[0]) && is_array($values[0]))
			$values = $values[0];
		
		if($types != NULL)
		{
			if($stmt = $this->db_handle->prepare($query))
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
							unset($datatype);
							break;
						}
					}
					
					$stmt->bindValue($k + 1, $v, $datatype);
				}
				
				if($result = $stmt->execute())
				{
					if($result->numColumns())
					{
						/* There's result */
						$out = [];
						
						while($row = $result->fetchArray(SQLITE3_ASSOC))
							array_push($out, $row);
						
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
				array_push($out, $row);
			
			return $out;
		}
	}
	
	function __destruct()
	{
		$this->db_handle->close();
	}
};
