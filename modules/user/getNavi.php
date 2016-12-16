<?php
$user = npps_user::get_instance($USER_ID);

return [
	[
		'user' => [
			'user_id' => $USER_ID,
			'unit_owning_user_id' => $user->unit_partner
		]
	],
	200
];
