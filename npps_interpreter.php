<?php
/// PHP file to access NPPS functions via command-line. Meant to be invoked via
/// `php npps_interpreter.php`
/// \file npps_interpreter.php

if(strcmp(PHP_SAPI, 'cli')) exit;

define('WEBVIEW', true);
include('main.php');
$DATABASE = (include('database_wrapper.php'));
$DATABASE->initialize_environment();

$fh = fopen('php://stdin', 'r');
$cmd = '';
$bcLvl = 0;
echo 'PHP Interpreter';
while (true)
{
	echo PHP_EOL . '> ';
	$line = rtrim(fgets($fh));
	$bcLvl += substr_count($line, '{') - substr_count($line, '}');
	$cmd.= $line;
	if ($bcLvl > 0 or substr($cmd, -1) !== ';')
		continue;
	eval($cmd);
	$cmd = '';
}
