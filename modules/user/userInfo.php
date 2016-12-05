<?php
$user = npps_user::get_instance($USER_ID);

return [
	[
		'user' => $user->user_info()
	],
	200
];
