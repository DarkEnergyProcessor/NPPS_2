<?php
$user = npps_user::get_instance($USER_ID);

return [
	[
		'tos_id' => 1,
		'is_agree' => $user->tos_agree >= 1
	],
	200
];
