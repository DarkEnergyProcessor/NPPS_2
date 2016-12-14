<?php
$award_data = npps_query("SELECT title_id, unlocked_title FROM `users` WHERE user_id = $USER_ID")[0];
$award_out = [];

foreach(explode(',', $award_data["unlocked_title"]) as $id)
{
	$award_out[] = [
		'award_id' => intval($id),
		'is_set' => $id == $award_data["title_id"],
		'insert_date' => to_datetime(0)
	];
}

return [
	[
		'award_info' => $award_out
	],
	200
];
?>