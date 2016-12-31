<?php
// Usage: POST http://example.com/path/to/mdl.php?os=[Android|iOS]&user_id=userid&token=token
// Request Data: mdl=[asset1, asset2, ..., assetn]

ini_set('html_errors', false);

require('dlc/download_geturl.php');

$main_db = new SQLite3DatabaseWrapper('dlc/mdl.db_');

// Init database if necessary
{
	$x = $main_db->query('SELECT name FROM `sqlite_master` WHERE type = \'table\' AND name = \'mdl_cache\'');
	
	if(count($x) == 0)
		$main_db->query('CREATE TABLE `mdl_cache` (id INTEGER PRIMARY KEY AUTOINCREMENT, file TEXT, data BLOB)');
}

// Returns downloaded data
$download_from_url = function(string $url)
{
	$ch = curl_init($url);
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	$out = curl_exec($ch);
	$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	curl_close($ch);
	
	if($status_code != 200)
		return NULL;
	
	return $out;
};

$is_valid_os = function(string $os): bool
{
	return strcmp($os, 'Android') == 0 || strcmp($os, 'iOS');
};

$get_host_url = function(): string
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
};

if(!(
	isset($_POST['mdl']) &&
	isset($_GET['user_id']) &&
	isset($_GET['token']) &&
	isset($_GET['os']) &&
	$is_valid_os($_GET['os'])
))
{
	error_processing:
	
	http_response_code(400);
	exit();
}

$sif = NULL;
$request_links = json_decode($_POST['mdl'], true);

if($request_links == NULL)
	goto error_processing;

$requested_links = [];
$inexistent_link = [];

$get_current_urldir = function() use($get_host_url): string
{
	$cdir = str_replace('\\', '/', dirname($_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI']));
	
	return sprintf('%s://%s%s/',
		$_SERVER['HTTPS'] ? 'https' : 'http',
		$get_host_url(),
		strcmp($cdir, '/') == 0 ? '' : $cdir
	);
};

foreach($request_links as $i => $link)
{
	$requested_links[$i] = '';
	$blob_data = $main_db->query('SELECT id FROM `mdl_cache` WHERE file = ?', 's', $link);
	
	if(count($blob_data) > 0)
		$requested_links[$i] = sprintf($get_current_urldir() . 'mdl_get.php?file=%s', urlencode($link));
	else
		$inexistent_link[] = [$i, $link];
}
	
if(count($inexistent_link) > 0)
{
	$needed_links = [];
	
	foreach($inexistent_link as $l)
		$needed_links[] = $l[1];
	
	
	$prod_links = download_geturl($_GET['token'], $_GET['user_id'], $_GET['os'], $needed_links);
	$main_db->query('BEGIN');
	
	foreach($prod_links as $n => $v)
	{
		if(strlen($v) > 0)
		{
			$buffer = $download_from_url($v);
			
			if($buffer != NULL)
			{
				$main_db->query('INSERT INTO `mdl_cache` (file, data) VALUES(?, ?)', 'sb', $needed_links[$n], $buffer);
				$requested_links[$inexistent_link[$n][0]] = sprintf('http://%s/microDL/mdl_get.php?file=%s', $get_host_url(), $needed_links[$n]);
			}
		}
	}
	
	$main_db->query('COMMIT');
}

echo json_encode($requested_links, JSON_PRETTY_PRINT);
