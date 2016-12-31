<?php

const CLIENT_VERSION = '8.2.0';
const X_MESSAGE_CODE = file_get_contents('X_MESSAGE_CODE');
const DOWNLOAD_GETURL = 'http://prod.en-lovelive.klabgames.net/main.php/download/getUrl';

/// \brief Micro download data from prod server
/// \param token The user token
/// \param user_id The user id
/// \param os The operating system (only `iOS` or `Android` is allowed)
/// \param list List of data to be downloaded
function download_geturl(string $token, int $user_id, string $os, array $list): array
{
	$url_list = [];	// Empty URL list
	
	if(count($list) == 0)
		return [];
	
	// Create empty list
	for($i = 0; $i < count($list); $i++)
		$url_list[$i] = '';
	
	// If invalid OS is specificed, return empty list
	if(strcmp($os, 'iOS') && strcmp($os, 'Android'))
		return $url_list;
	
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
	$request_data = json_encode([
		'os' => $os,
		'path_list' => $list
	]);
	$client_header = [
		'API-Model: straightforward',
		'Authorize: '. http_build_query([
			'consumerKey' => 'lovelive_test',
			'token' => $token
		]),
		'Client-Version: '. CLIENT_VERSION,
		'Expect:',
		'Platform-Type: 2',
		"User-ID: $user_id",
		sprintf('X-Message-Code: %s', hash_hmac('sha1', $request_data, X_MESSAGE_CODE))
	];
	
	// Initialize cURL
	$ch = curl_init(DOWNLOAD_GETURL);
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADERFUNCTION, $header_handler);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $client_header);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ['request_data' => $request_data]);
	
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
		return $url_list;
	
	$response_data = json_decode($response_data, true);
	
	// Check if response status code is not 200
	if($response_data['status_code'] != 200)
		return $url_list;
	
	return $response_data['response_data']['url_list'];
}
