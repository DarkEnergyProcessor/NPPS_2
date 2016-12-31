<?php
require('dbw_sqlite3.php');
$main_db = new SQLite3DatabaseWrapper('mdl.db_');

{
	$x = $main_db->query('SELECT name FROM `sqlite_master` WHERE type = \'table\' AND name = \'mdl_cache\'');
	
	if(count($x) == 0)
		$main_db->query('CREATE TABLE `mdl_cache` (id INTEGER PRIMARY KEY AUTOINCREMENT, file TEXT, data BLOB)');
}

$list = [];

foreach($main_db->query('SELECT file FROM `mdl_cache`') as $a)
	$list[] = $a['file'];

echo json_encode($list, JSON_PRETTY_PRINT);
