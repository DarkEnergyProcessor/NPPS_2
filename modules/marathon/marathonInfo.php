<?php
$token_event_list = [];

foreach(npps_query("
	SELECT event_id, event_ranking_table, token_image FROM `event_list`
	WHERE
		token_image IS NOT NULL AND
		start_time <= $UNIX_TIMESTAMP AND
		close_time > $UNIX_TIMESTAMP
	") as $ev)
{
	$user_token = 0;
	$user_point = 0;
	$token_info = explode(':', $ev['token_image']);
	
	if(($user_event_info = npps_query("
		SELECT total_points, current_token FROM `{$ev['event_ranking_table']}`
		WHERE user_id = $USER_ID
		"))
	)
		if(count($user_event_info) > 0)
		{
			$user_event_info = $user_event_info[0];
			$user_token = $user_event_info['current_token'];
			$user_point = $user_event_info['total_points'];
		}
	
	$token_event_list[] = [
		'event_id' => $ev['event_id'],
		'point_name' => $token_info[0],
		'point_icon_asset' => $token_info[1],
		'event_point' => $user_token,
		'total_event_point' => $user_point,
		'event_scenario' => [
			'progress' => 1,
			'event_scenario_status' => []
		]
	];
}

return [
	$token_event_list,
	200
];
