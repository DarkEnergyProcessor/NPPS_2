<?php
$user = npps_user::get_instance($USER_ID);
$award_out = [];

foreach(npps_separate(',', $user->unlocked_title ?? '') as $id)
{
	$award_out[] = [
		'award_id' => intval($id),
		'is_set' => $id == $user->title_id,
		'insert_date' => to_datetime(0)
	];
}

return [
	[
		'award_info' => $award_out
	],
	200
];
