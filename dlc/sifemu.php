<?php
/*
 * Null-Pointer Private Server
 * SifEmu, SIF Request Emulator
 */

/// \file sifemu.php

class SifEmu
{
	/* Change these variables if necessary */
	protected static $SERVER_ADDRESS = "http://prod.en-lovelive.klabgames.net/main.php/";
	protected static $APPLICATION_ID = "834030294";
	protected static $XMESSAGECODE = X_MESSAGE_CODE_KEY;
	protected static $USERNAME = "";
	protected static $PASSWORD = "";
	protected static $CLIENT_VERSION = "8.0.0";
	
	/* Don't change below */
	public $token;
	public $cmdnum;
	public $curl;
	public $nonce;
	public $user_id;
	
	protected $temp_header;
	protected $save_file;
	
	public function __construct(bool $call_authkey = false)
	{
		$filename = $_SERVER['DOCUMENT_ROOT'].'/dlc/save.json';
		
		$this->nonce = 1;
		$this->cmdnum = 2;
		$this->temp_header = NULL;
		$this->save_file = fopen($filename, 'c+');
		
		flock($this->save_file, LOCK_EX);
		fseek($this->save_file, 0, SEEK_SET);
		
		if($call_authkey)
			$this->login_authkey();
	}
	
	public function __destruct()
	{
		$this->save();
		
		flock($this->save_file, LOCK_UN);
		fclose($this->save_file);
	}
	
	public static function load_new(): SifEmu
	{
		$inst = new SifEmu();
		$readed_data = stream_get_contents($inst->save_file);
		
		if(strlen($readed_data) > 0)
		{
			fseek($inst->save_file, 0, SEEK_SET);
			
			$data = json_decode($readed_data, true);
			
			$inst->cmdnum = $data['cmdnum'];
			$inst->nonce = $data['nonce'];
			$inst->token = $data['token'];
			$inst->user_id = $data['user_id'];
			
			return $inst;
		}
		
		$inst->login_authkey();
		$inst->login_login();
		
		return $inst;
	}
	
	protected function init_curl()
	{
		$x = curl_init();
		curl_setopt($x, CURLOPT_POST, 1);
		curl_setopt($x, CURLOPT_RETURNTRANSFER, 1);
		
		$this->curl = $x;
	}
	
	protected function init_headerfunction()
	{
		if($this->temp_header)
			fclose($this->temp_header);
		
		$this->temp_header = fopen('php://memory','rb+');
	}
	
	protected function headerfunction($curl, string $data)
	{
		return fwrite($this->temp_header, $data);
	}
	
	protected function defaultheader(): array
	{
		return [
			'Expect:',
			'API-Model: straightforward',
			'Debug: 1',
			'Bundle-Version: 4.0.2',
			'Client-Version: '.self::$CLIENT_VERSION,
			'OS-Version: libcURL with PHP '.PHP_VERSION,
			'OS: Android',
			'Platform-Type: 2',
			'Application-ID: '.self::$APPLICATION_ID,
			'Time-Zone: '.date_default_timezone_get(),
			'Region: 392'
		];
	}
	
	protected function authorize(): string
	{
		$b = ['Authorize: consumerKey=lovelive_test&timeStamp=',strval(time()),'&version=1.1','',"&nonce={$this->nonce}"];
		
		if($this->token != NULL)
			$b[3] = "&token={$this->token}";
		
		return implode('', $b);
	}
	
	public function commandnum(): string
	{
		return self::$USERNAME.'.'.strval(time()).'.'.$this->cmdnum;
	}
	
	protected function httpheader($request_data = NULL): array
	{
		$a = $this->defaultheader();
		$a[] = $this->authorize();
		
		if($this->user_id != NULL)
			$a[] = 'User-ID: '.$this->user_id;
		
		if($request_data != NULL)
			$a[] = 'X-Message-Code: '.hash_hmac('sha1', $request_data, self::$XMESSAGECODE);
		
		return $a;
	}
	
	/* request_data must be an array */
	protected function request_lowlevel(string $endpoint, $request_data = NULL): array
	{
		$this->init_curl();
		$this->init_headerfunction();
		
		$ch = $this->curl;
		$request_header = $this->httpheader($request_data);
		
		curl_setopt($ch, CURLOPT_URL, self::$SERVER_ADDRESS.$endpoint);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $request_header);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'headerfunction']);
		
		if($request_data)
			curl_setopt($ch, CURLOPT_POSTFIELDS, ['request_data' => $request_data]);
		
		$response_data = curl_exec($ch);
		$decoded_response = json_decode($response_data ?: '[]', true);
		$output = [
			$decoded_response,
			curl_getinfo($ch, CURLINFO_HTTP_CODE),
			$this->temp_header
		];
		
		if(isset($decoded_response['status_code']))
			$output[1] = $decoded_response['status_code'];
		
		curl_close($ch);
		return $output;
	}
	
	/* Returns response_data or NULL*/
	public function request(string $endpoint, array $request_data)
	{
		$module_action = explode('/', $endpoint);
		
		$request_data['module'] = $module_action[0];
		$request_data['action'] = $module_action[1];
		
		$data = $this->request_lowlevel($endpoint, json_encode($request_data));
		
		if($data[1] != 200)
		{
			error_log(sprintf('Status code not 200. Data: %s\nHeader: %s', json_encode($data[0]), $data[2]), 4);
			
			return NULL;
		}
		
		return $data[0]['response_data'];
	}
	
	/* Returns response_data or NULL */
	public function request_common(string $endpoint, array $request_data, bool $timestamp_add = true, bool $cmdnum_add = true)
	{
		if($timestamp_add)
			$request_data['timeStamp'] = time();
		
		if($cmdnum_add)
			$request_data['commandNum'] = $this->commandnum();
		
		$out = $this->request($endpoint, $request_data);
		
		if($out)
		{
			$this->nonce++;
			
			if($cmdnum_add)
				$this->cmdnum++;
		}
		
		return $out;
	}
	
	public function save()
	{
		ftruncate($this->save_file, 0);
		rewind($this->save_file);
		fwrite($this->save_file, json_encode([
			'cmdnum' => $this->cmdnum,
			'nonce' => $this->nonce,
			'token' => $this->token,
			'user_id' => $this->user_id
		]));
		fseek($this->save_file, 0, SEEK_SET);
	}
	
	/********************************************
	 ** <module>_<action> function starts here **
	 ********************************************/
	 
	public function login_authkey()
	{
		$data = $this->request_lowlevel('login/authkey');
		
		if($data[1] != 200)
			throw new Exception(sprintf('Status code not 200. Data: %s\nHeader: %s', json_encode($data[0]), $data[2]), 4);
		
		$this->token = $data[0]['response_data']['authorize_token'];
		$this->nonce++;
	}
	
	public function login_login()
	{
		$data = $this->request_lowlevel('login/login', json_encode(['login_key' => self::$USERNAME, 'login_passwd' => self::$PASSWORD]));
		
		if($data[1] != 200)
			throw new Exception(sprintf('Status code not 200. Data: %s\nHeader: %s', json_encode($data[0]), $data[2]), 4);
		
		$this->token = $data[0]['response_data']['authorize_token'];
		$this->user_id = $data[0]['response_data']['user_id'];
		$this->nonce++;
	}
	
	/* Returns download links */
	public function download_additional(string $os, int $type, int $package_type, int $package_id)
	{
		$request_data = [
			'os' => $os,
			'package_type' => $package_type,
			'package_id' => $package_id,
			'type' => strval($type),
			'region' => '392',
			'client_version' => self::$CLIENT_VERSION
		];
		
		return $this->request_common('download/additional', $request_data);
	}
	
	/* Returns download links */
	public function download_geturl(string $os, array $paths)
	{
		$req_data = [
			'os' => $os,
			'path_list' => $paths
		];
		
		return $this->request_common('download/getUrl', $req_data, false, false);
	}
};
