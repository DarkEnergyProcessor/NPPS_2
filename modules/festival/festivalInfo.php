<?php
$medfes = npps_query("
	SELECT
		event_id,
		start_time,
		end_time,
		close_time,
		event_ranking_table,
		event_song_table
	FROM `event_list`
	WHERE
		start_time <= $UNIX_TIMESTAMP AND
		close_time > $UNIX_TIMESTAMP AND
		token_image IS NULL AND
		technical_song_list IS NULL
");

if(count($medfes) == 0)
	return [
		[],
		200
	];

// Only one MedFes can run at time
$medfes = $medfes[0];

// get event points
$player_points = 0;
{
	$temp = npps_query("
		SELECT total_points FROM `{$medfes['event_ranking_table']}`
		WHERE user_id = $USER_ID
	");
	
	if(count($temp) > 0)
		$player_points = $temp[0]['total_points'];
}

return [
	[
		'base_info' => [
			'event_id' => $medfes['event_id'],
			'asset_bgm_id' => 4,	// Try to replace it lol
			'event_point' => $player_points,
			'total_event_point' => $player_points,
			'whole_event_point' => 0,
			'max_skill_activation_rate' => 30
		]
		// TODO
		/*
		,'festival_previous_play' => [
			'difficulty' => 4,
			'live_count' => 1
		],
		*/
		/*
		'festival_play' => [
			'difficulty' => 4,
			'live_count' => 1,
			'live_difficulty_ids' => $queue_live_difficulty_id
		],
		*/
	],
	200
];
