<?php
$user = npps_user::get_instance($USER_ID);

return [
	[
		'tos_id' => 1,
		'is_agreed' => $user->tos_agree >= 1
	],
	200
];
