<?php
// Usage: GET http://example.com/path/to/mdl_get.php?file=file
// Returns 404 if specificed file is unavailable in database

ini_set('html_errors', false);

// Get data from SQLite databse
if(!isset($_GET['file']))
{
	http_response_code(400);
	exit;
}

require('dbw_sqlite3.php');
$main_db = new SQLite3DatabaseWrapper('mdl.db_');
$is_decrypted = isset($_GET['decrypted']);

// Init database if necessary
{
	$x = $main_db->query('SELECT name FROM `sqlite_master` WHERE type = \'table\' AND name = \'mdl_cache\'');
	
	if(count($x) == 0)
		$main_db->query('CREATE TABLE `mdl_cache` (id INTEGER PRIMARY KEY AUTOINCREMENT, file TEXT, data BLOB)');
}

$partial_info = NULL;	// Array: [start_byte, end_byte]

if(isset($_SERVER['HTTP_RANGE']))
{
	if(preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $byte_range) == 1)
		$partial_info = [intval($byte_range[1]), intval($byte_range[2])];
}

$buffer = NULL;

if(false)
{
	not_found:
	
	http_response_code(404);
	exit;
}

if($partial_info)
{
	$buffer = $main_db->query('SELECT substr(data, ?, ?) as a, length(data) as b FROM `mdl_cache` WHERE file = ?', 'iis',
		$partial_info[0] + 1, $partial_info[1] - $partial_info[0] + 1, $_GET['file']);
	
	if(count($buffer) > 0)
		header(sprintf('Content-Range: bytes %d-%d/%d', $partial_info[0], $partial_info[1], $buffer[0]['b']), true, 206);
	else
		goto not_found;
}
else
{
	$buffer = $main_db->query('SELECT data as a, length(data) as b FROM `mdl_cache` WHERE file = ?', 's', $_GET['file']);
	
	if(count($buffer) == 0)
		goto not_found;
}

header('Accept-Range: bytes');
header('Content-Type: application/octet-stream');
header(sprintf('Content-Disposition: attachment; filename="%s"', basename($_GET['file'])));

if($is_decrypted)
{
	require('unidecrypt/HonokaMiku.php');
	
	$fileheader = $main_db->query('SELECT substr(data, 1, 16) as a FROM `mdl_cache` WHERE file = ?', 's', $_GET['file'])[0];
	
	$dctx = HonokaMiku\FindSuitable($_GET['file'], $fileheader['a']);
	
	if($dctx)
	{
		$start_decrypt_offset = 4;
		
		if($dctx->version == 3)
		{
			$start_decrypt_offset = 16;
			$dctx->final_setup($_GET['file'], substr($fileheader['a'], 4));
		}
		
		if($partial_info)
		{
			$dctx->goto_offset($partial_info[0]);
			
			$tempbuf = $main_db->query(
				'SELECT substr(data, ?, ?) as a FROM `mdl_cache` WHERE file = ?',
				'iis',
				$partial_info[0] + 1 + $start_decrypt_offset,
				$partial_info[1] - $partial_info[0] + 1,
				$_GET['file']
			)[0];
			$buffer[0]['a'] = $dctx->decrypt_block($tempbuf['a']);
			
			header(sprintf('Content-Range: bytes %d-%d/%d', $partial_info[0], $partial_info[1], $buffer[0]['b'] - $start_decrypt_offset), true, 206);
		}
		else
			$buffer[0]['a'] = $dctx->decrypt_block(substr($buffer[0]['a'], $start_decrypt_offset));
	}
}

header('Content-Length: '. strlen($buffer[0]['a']));
echo $buffer[0]['a'];
