<?php
// Usage: GET http://example.com/path/to/mdl_get.php?id=download_additional_id
// Returns 404 if specificed download id is unavailable in database

ini_set('html_errors', false);

$download_id = 0;

if(
	!isset($_GET['id']) ||
	($download_id = intval($_GET['id'])) <= 0
)
{
	http_response_code(400);
	exit;
}

require('dbw_sqlite3.php');

$main_db = new SQLite3DatabaseWrapper('additional.db_');

{
	$x = $main_db->query('SELECT name FROM `sqlite_master` WHERE type = \'table\' AND name = \'additional_cache\'');
	
	if(count($x) == 0)
		$main_db->query('
			CREATE TABLE `additional_cache` (
				download_additional_id INTEGER PRIMARY KEY,
				platform_type INTEGER NOT NULL,	-- 1 for iOS, 2 for Android
				package_type INTEGER NOT NULL,
				package_id INTEGER NOT NULL,
				data BLOB
			)
		');
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
	$buffer = $main_db->query("
		SELECT
			substr(data, ?, ?) as a,
			length(data) as b
		FROM `additional_cache`
		WHERE download_additional_id = $download_id",
		'ii',
		$partial_info[0] + 1, $partial_info[1] - $partial_info[0] + 1);
	
	if(count($buffer) > 0)
		header(sprintf('Content-Range: bytes %d-%d/%d', $partial_info[0], $partial_info[1], $buffer[0]['b']), true, 206);
	else
		goto not_found;
}
else
{
	$buffer = $main_db->query("
		SELECT
			data as a,
			length(data) as b
		FROM `additional_cache`
		WHERE download_additional_id = $download_id
	");
	
	if(count($buffer) == 0)
		goto not_found;
}

header('Accept-Range: bytes');
header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename=\"$download_id.zip\"");
header('Content-Length: '. strlen($buffer[0]['a']));

echo $buffer[0]['a'];
