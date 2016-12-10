<?php
$bg_data = npps_query("SELECT background_id, unlocked_background FROM `users` WHERE user_id = $USER_ID")[0];
$bg_out = [];

foreach(explode(',', $bg_data["unlocked_background"]) as $id)
{
	$bg_out[] = [
		'background_id' => intval($id),
		'is_set' => $id == $bg_data["background_id"],
		'insert_date' => to_datetime(0)
	];
}

return [
	[
		'background_info' => $bg_out
	],
	200
];
?>