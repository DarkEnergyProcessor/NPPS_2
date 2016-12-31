<?php
// microDL system functionality in NPPS
if(!npps_config('DLC_ENABLED'))
{
	echo 'DLC is disabled on this server';
	return false;
}

$is_valid_os = function(string $os): bool
{
	return strcmp($os, 'Android') == 0 || strcmp($os, 'iOS') == 0;
};

if(
	!isset($REQUEST_DATA['os']) ||
	!is_string($REQUEST_DATA['os']) ||
	!$is_valid_os($REQUEST_DATA['os']) ||
	
	!isset($REQUEST_DATA['path_list']) ||
	!is_array($REQUEST_DATA['path_list'])
)
	return false;

$download_prod = npps_config('DLC_ALLOW_DOWNLOAD_PROD');
$prod_cred = NULL;

if(!extension_loaded('curl'))
{
	npps_log('cURL extension is not loaded. DLC_ALLOW_DOWNLOAD_PROD will be ' .
		     'assumed off');
	$download_prod = false;
}

clearstatcache(true, 'data/dlc_credentials.ini');
if($download_prod)
{
	if(!file_exists('data/dlc_credentials.ini'))
	{
		config_invalid:
		
		npps_log('Invalid configuration detected. Check your npps.ini setting');
		npps_http_code(500);
		return false;
	}
	
	$cred = parse_ini_file('data/dlc_credentials.ini');
	
	if(isset($cred['Username'], $cred['Password']))
		$prod_cred = $cred;
	else
		goto config_invalid;
}

$external_url = NULL;
if($download_prod && ($external_url = npps_config('DLC_EXTERNAL_SERVER')))
{
	require('modules/download/sifemu/sifemu_simple.php');
	
	list($token, $uid) = sifemu_load(
		$prod_cred['Username'],
		$prod_cred['Password']
	);
	
	if($token === $uid)	// Equal to NULL
	{
		npps_log('Failed to login to prod server');
		npps_http_code(500);
		return false;
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => npps_config('DLC_EXTERNAL_SERVER') .
					   "mdl.php?os={$REQUEST_DATA['os']}?token=$token" .
					   "?user_id=$user_id",
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => ['mdl' =>json_encode($REQUEST_DATA['path_list'])],
	]);
	$resp = curl_exec($ch);
	
	if(curl_errno($ch) != CURLE_OK)
	{
		npps_log('Error occured while contacting external server');
		npps_http_code(500);
		return false;
	}
	
	return [
		[
			'url_list' => json_decode($resp, true)
		],
		200
	];
}

///////////////////////////////////////////////////
// Code below assumes DLC is hosted in localhost //
///////////////////////////////////////////////////

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
		return $cached_result = sprintf('%s:%d',
			$_SERVER['REMOTE_ADDR'], $_SERVER['SERVER_PORT']
		);
};

$mdl_db = new SQLite3DatabaseWrapper('dlc/mdl.db_');

// Init mDL database
{
	$x = $mdl_db->query('
		SELECT name FROM `sqlite_master`
		WHERE type = \'table\' AND name = \'mdl_cache\'
	');
	
	if(count($x) == 0)
		$mdl_db->query('
			CREATE TABLE `mdl_cache` (
				id INTEGER PRIMARY KEY AUTOINCREMENT,
				file TEXT,
				data BLOB
			)'
		);
}

// TODO
return ['mDL localhost TODO', 501];
