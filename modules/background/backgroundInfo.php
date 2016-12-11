<?php
$user = npps_user::get_instance($USER_ID);
$bg_out = [];

foreach(npps_separate(',', $user->unlocked_background ?? '') as $id)
{
	$bg_out[] = [
		'background_id' => intval($id),
		'is_set' => $id == $user->background_id,
		'insert_date' => to_datetime(0)
	];
}

return [
	[
		'background_info' => $bg_out
	],
	200
];
