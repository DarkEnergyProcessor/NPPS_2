<?php
// Simple SifEmu for NPPS2

function sifemu_load(string $username, string $password): array
{
	clearstatcache(true, 'data/sifemu_credentials.json');
	if(
		file_exists('data/sifemu_credentials.json') &&
		(time() - filemtime('data/sifemu_credentials.json')) < 259200
	)
	{
		$f = fopen('data/sifemu_credentials.json', 'rb');
		flock($f, LOCK_SH);
		
		$userid = intval(trim(fgets($f)));
		$token = trim(fgets($f));
		
		flock($f, LOCK_UN);
		fclose($f);
		touch($_SERVER['DOCUMENT_ROOT'] . '/data/sifemu_credentials.json');
		
		return [$token, $userid];
	}
	
	$server_version = npps_client_version();
	$token = NULL;
	$user_id = NULL;
	$request_data = NULL;
	$client_header = [
		'API-Model: straightforward',
		'Authorize: '. http_build_query([
			'consumerKey' => 'lovelive_test'
		]),
		"Client-Version: $server_version",
		'Expect:',
		'Platform-Type: 2',
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
	
	// cURL handle
	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => 'http://prod.en-lovelive.klabgames.net/main.php/login/authkey',
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_HEADERFUNCTION => $header_handler,
		CURLOPT_HTTPHEADER => $client_header,
		CURLOPT_POST => 1
	]);
	$response_data = curl_exec($ch);
	
	if(
		curl_errno($ch) != CURLE_OK ||
		curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200 ||
		isset($server_header['Maintenance'])
	)
		return [NULL, NULL];
	
	$token = json_decode($response_data)->response_data->authorize_token;
	
	// Reuse
	$request_data = json_encode([
		'login_key' => $username,
		'login_passwd' => $password
	]);
	$client_header[1] = 'Authorize: ' . http_build_query([
		'consumerKey' => 'lovelive_test',
		'token' => $token
	]);
	$client_header[] = 'X-Message-Code: ' . hash_hmac(
		'sha1', $request_data, X_MESSAGE_CODE_KEY
	);
	
	curl_setopt_array($ch, [
		CURLOPT_URL => 'http://prod.en-lovelive.klabgames.net/main.php/login/authkey',
		CURLOPT_POSTFIELDS = ['request_data' => $request_data]
	]);
	$response_data = curl_exec($ch);
	
	if(
		curl_errno($ch) != CURLE_OK ||
		curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200 ||
		isset($server_header['Maintenance'])
	)
		return [NULL, NULL];
	
	$response_data = json_decode($response_data);
	
	if($response_data->status_code != 200)
		return [NULL, NULL];
	
	curl_close($ch);
	
	$token = $response_data->response_data->authorize_token;
	$user_id = $response_data->response_data->user_id;
	
	file_put_contents(
		'data/sifemu_credentials.json',
		strval($user_id) . "\n" . $token,
		LOCK_EX
	);
	
	return [$token, $user_id];
}
