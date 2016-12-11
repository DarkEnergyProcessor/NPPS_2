<?php
$user = npps_user::get_instance($USER_ID);
$platform = $user->platform_code;

if($platform === NULL)
{
	// Not connected
	return [
		[
			'is_connected' => false
		],
		200
	];
}

$platform = npps_separate(':', $platform);

if($platform[1] != $PLATFORM_ID)
{
	// Connected but platform code doesn't match
	return [
		[
			'is_connected' => false
		],
		200
	];
}

// Connected
return [
	[
		'is_connected' => true
	],
	200
];
