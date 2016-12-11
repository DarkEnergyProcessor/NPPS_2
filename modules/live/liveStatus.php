<?php
$normal_live = [];
$special_live = [];
$marathon_live = [];

// Normal live (in hits)
foreach(npps_query("
	SELECT
		live_difficulty_id,
		score,
		combo,
		times
	FROM `live_information`
	WHERE user_id = $USER_ID AND normal_live == 1
") as $livedata)
{
	$liveobj = npps_live_difficulty::get_instance(
		$livedata['live_difficulty_id'], 'live'
	);
	$normal_live[] = [
		'live_difficulty_id' => $livedata['live_difficulty_id'],
		'status' =>
			$livedata['times'] > 0 ? NPPS_LIVE_EVER_CLEAR : NPPS_LIVE_NEW,
		'hi_score' => $livedata['score'],
		'hi_combo_count' => $livedata['combo'],
		'clear_cnt' => $livedata['times'],
		'achieved_goal_id_list' => $liveobj->goal_reward(
			$livedata['score'], $livedata['combo'], $livedata['times']
		)
	];
}

// Special live (in B-Side, like master song)
foreach(npps_query("
	SELECT live_difficulty_id FROM `b_side_schedule`
	WHERE
		end_available_time > $UNIX_TIMESTAMP AND
		start_available_time < $UNIX_TIMESTAMP
") as $l)
{
	$lobj = npps_live_difficulty::get_instance($l['live_difficulty_id']);
	$lstatus = live_get_info($USER_ID, $l['live_difficulty_id']);
	
	$special_live[] = [
		'live_difficulty_id' => $l['live_difficulty_id'],
		'status' =>
			$l['times'] > 0 ? NPPS_LIVE_EVER_CLEAR : NPPS_LIVE_NEW,
		'hi_score' => $l['score'],
		'hi_combo_count' => $l['combo'],
		'clear_cnt' => $l['times'],
		'achieved_goal_id_list' => $lobj->goal_reward(
			$l['score'], $l['combo'], $l['times']
		)
	];
}

// Token live show (marathon)
$marathon_db = npps_get_database('event/marathon');
foreach(npps_query("
	SELECT
		easy_song_list,
		normal_song_list,
		hard_song_list,
		expert_song_list
	FROM `event_list` WHERE
		token_image IS NOT NULL AND
		start_time <= $UNIX_TIMESTAMP AND
		end_time > $UNIX_TIMESTAMP
") as $ev)
{
	foreach($ev as $livelist)
	{
		foreach(npps_separate(',', $livelist) as $live_difficulty_id)
		{
			$lobj = npps_live_difficulty::get_instance($live_difficulty_id);
			$stats = live_get_info($USER_ID, $live_difficulty_id);
			
			$marathon_live[] = [
				'live_difficulty_id' => $live_difficulty_id,
				'status' =>
					$stats['times'] > 0 ? NPPS_LIVE_EVER_CLEAR : NPPS_LIVE_NEW,
				'hi_score' => $stats['score'],
				'hi_combo_count' => $stats['combo'],
				'clear_cnt' => $stats['times'],
				'achieved_goal_id_list' => $lobj->goal_reward(
					$stats['score'], $stats['combo'], $stats['times']
				)
			];
		}
	}
}

return [
	[
		'normal_live_status_list' => $normal_live,
		'special_live_status_list' => $special_live,
		'marathon_live_status_list' => $marathon_live
	],
	200
];
