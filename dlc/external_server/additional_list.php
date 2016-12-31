<?php
// Usage: GET http://example.com/path/to/additional_list.php&pkg_type=package_type&exclude=num
// exclude is separated by comma
ini_set('html_errors', false);
header('Content-Type: text/plain');

$pkg_type = intval($_GET['pkg_type'] ?? -1);

if($pkg_type == (-1))
	exit('[]');

require('dbw_sqlite3.php');
$main_db = new SQLite3DatabaseWrapper('additional.db_');
$exclude_list = [];

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

$get_current_urldir = function() use($get_host_url): string
{
	$cdir = str_replace('\\', '/', dirname($_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI']));
	
	return sprintf('%s://%s%s/',
		($_SERVER['HTTPS'] ?? 0) ? 'https' : 'http',
		$get_host_url(),
		strcmp($cdir, '/') == 0 ? '' : $cdir
	);
};

if(isset($_GET['exclude']))
{
	$list = explode(',', $_GET['exclude']);
	
	foreach($list as $x)
		if(is_numeric($x))
			$exclude_list[] = intval($x);
}

$exclude_list = implode(',', $exclude_list);
$dataout = [];

foreach($main_db->query("
	SELECT download_additional_id, '' as url, length(data) as size FROM `additional_cache`
	WHERE package_type == $pkg_type AND package_id NOT IN($exclude_list)
") as $dl)
{
	$dl['url'] = $get_current_urldir() . "additional_get.php?id={$dl['download_additional_id']}";
	$dataout[] = $dl;
}

echo json_encode($dataout, JSON_PRETTY_PRINT);
