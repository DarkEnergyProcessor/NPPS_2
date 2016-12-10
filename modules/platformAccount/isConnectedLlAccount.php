<?php
$platform = npps_query("SELECT platform_code FROM `users` WHERE user_id = $USER_ID")[0]["platform_code"];

if($platform == NULL)
{
	/* Not connected */
	return [
		[
			'is_connected' => false
		],
		200
	];
}

$platform = explode(':', $platform);

if($platform[1] != $PLATFORM_CODE)
{
	/* Connected but platform code doesn't match */
	return [
		[
			'is_connected' => false
		],
		200
	];
}

/* Connected */
return [
	[
		'is_connected' => true
	],
	200
];
?>