<?php
/*
 * SIF Private server
 * The webview.
 */

/** @@ Configuration @@ **/
// Requires "Authorize" header? Comment to disable
//define("REQUIRE_AUTHORIZE", true);

/** !! Configuration !! **/ /* End configuration */

define("WEBVIEW", true);

require('main.php');		// Requiring it in WebView won't load it's main function.
$DATABASE = require('database_wrapper.php');

ob_start('ob_gzhandler');
register_shutdown_function('ob_end_flush');

/// \brief NPPS WebView main function
function webview_main()
{
	global $DATABASE;
	global $REQUEST_HEADERS;
	
	$DATABASE->initialize_environment();
	
	$mod_act = explode('/', substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?') ?: strlen($_SERVER['REQUEST_URI'])));
	$MODULE = isset($mod_act[2]) ? $mod_act[2] : exit;
	$ACTION = isset($mod_act[3]) ? $mod_act[3] : exit;
	
	// It's module/action responsibility to check if it's necessary to cache the request.
	include("webview/$MODULE/$ACTION.php");
	
	exit;
}

if(npps_config('REQUIRE_AUTHORIZE'))
{
	if(isset($REQUEST_HEADERS["authorize"]) &&
	   isset($REQUEST_HEADERS["user-id"])
	)
	{
		$auth_data = authorize_function($REQUEST_HEADERS["authorize"]);
		
		if($auth_data)
		{
			if(isset($auth_data["nonce"]) && strcmp($auth_data["nonce"], "WV0") == 0 &&
			   isset($auth_data["token"]) && token_exist(strval($auth_data["token"]))
			)
				webview_main();
			else
				exit;
		}
		else
			exit;
	}
	else
		exit;
}
else
	webview_main();

?>