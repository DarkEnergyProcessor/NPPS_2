<?php
// Usage: GET http://example.com/path/to/additional.php?os=[Android|iOS]&user_id=userid&token=token&pkg_type=num&pkg_id=num&server_version=serverver
// Server version must be exist
// token, user_id, and server_version can be omitted, if you only want to download it if it's exist

ini_set('html_errors', false);
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

$get_platform_id_by_name = function(string $platform): int
{
	return strcmp($platform, 'Android') == 0 ? 2 : (strcmp($platform, 'iOS') == 0 ? 1 : 0);
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

$get_current_urldir = function() use($get_host_url): string
{
	$cdir = str_replace('\\', '/', dirname($_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI']));
	
	return sprintf('%s://%s%s/',
		($_SERVER['HTTPS'] ?? 0) ? 'https' : 'http',
		$get_host_url(),
		strcmp($cdir, '/') == 0 ? '' : $cdir
	);
};

$platform_id = NULL;

if(!isset(
		$_GET['os'],
		$_GET['pkg_type'],
		$_GET['pkg_id']
	) ||
	!($platform_id = $get_platform_id_by_name($_GET['os']))
)
{
	http_response_code(400);
	exit();
}

$pkg_type = intval($_GET['pkg_type']);
$pkg_id = intval($_GET['pkg_id']);

if($pkg_type == 0 && $pkg_id == 0)
{
	// Initial download not implemented.
	http_response_code(501);
	exit;
}

$data = $main_db->query("
	SELECT download_additional_id, '' as url, length(data) as size FROM `additional_cache`
	WHERE package_type = $pkg_type AND package_id = $pkg_id AND platform_type = $platform_id
");

if(count($data) == 0)
{	
	// If token and user_id specificed, download it
	if(
		!isset($_GET['token'], $_GET['user_id'], $_GET['server_version']) ||
		!is_numeric($_GET['user_id']) ||
		!preg_match('/^[0-9\.]+$/', $_GET['server_version']))
	{
		http_response_code(404);
		exit;
	}
	
	$server_version = $_GET['server_version'];
	$token = $_GET['token'];
	$user_id = intval($_GET['user_id']);
	
	$request_data = json_encode([
		'os' => $_GET['os'],
		'package_type' => $pkg_type,
		'package_id' => $pkg_id,
		'type' => 1,
		'region' => '392',
		'client_version' => $server_version
	]);
	$client_header = [
		'API-Model: straightforward',
		'Authorize: '. http_build_query([
			'consumerKey' => 'lovelive_test',
			'token' => $token
		]),
		"Client-Version: $server_version",
		'Expect:',
		'Platform-Type: 2',
		"User-ID: $user_id",
		'X-Message-Code: ' . hash_hmac('sha1', $request_data, file_get_contents('X_MESSAGE_CODE'))
	];
	$server_header = [];
	$header_handler = function($ch, string $header_data)
					  use(&$server_header): int
	{
		
		$len = strlen($header_data);
		
		if(strncmp($header_data, 'HTTP', 4) == 0)
			return $len;
		
		if($len > 2)
		{
			$header_data = str_replace(["\r\n", "\n", "\r"], '', $header_data);
			$hlist = explode(': ', $header_data, 2);
			$server_header[$hlist[0]] = $hlist[1];
		}
		
		return $len;
	};
	
	$ch = curl_init('http://prod.en-lovelive.klabgames.net/main.php/download/additional');
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_HEADERFUNCTION => $header_handler,
		CURLOPT_HTTPHEADER => $client_header,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => ['request_data' => $request_data]
	]);
	
	$response_data = curl_exec($ch);
	
	// Check http code
	if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200)
	{
		curl_close($ch);
		return $url_list;
	}
	
	curl_close($ch);
	
	// Check if server is maintenance
	if(isset($server_header['Maintenance']))
	{
		http_response_code(404);
		exit;
	}
	
	$response_data = json_decode($response_data, true);
	
	// Check if response status code is not 200
	if($response_data['status_code'] == 600)
	{
		http_response_code(404);
		exit;
	}
	
	$main_db->query('BEGIN');
	
	// Insert data
	$out_data = [];
	foreach($response_data['response_data'] as $dl)
	{
		$file_contents = file_get_contents($dl['url']);
		$main_db->query('INSERT INTO `additional_cache` VALUES(?, ?, ?, ?, ?)', 'iiiib',
			$dl['download_additional_id'],
			$platform_id,
			$pkg_type,
			$pkg_id,
			$file_contents
		);
		
		$out_data[] = [
			'download_additional_id' => $dl['download_additional_id'],
			'url' => $get_current_urldir() . "additional_get.php?id={$dl['download_additional_id']}",
			'size' => strlen($file_contents)
		];
	}
	
	$main_db->query('COMMIT');
	
	$data = $out_data;
}
else
{
	foreach($data as &$dl)
		$dl['url'] = $get_current_urldir() . "additional_get.php?id={$dl['download_additional_id']}";
}

if(count($data) == 0)
{
	http_response_code(404);
	exit;
}

echo json_encode($data, JSON_PRETTY_PRINT);
