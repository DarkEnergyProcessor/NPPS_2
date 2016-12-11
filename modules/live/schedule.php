<?php
$live_db = npps_get_database('live');
$event_common_db = npps_get_database('event/event_common');
$marathon_db = npps_get_database('event/marathon');

$event_list = [];
$live_time = [];
$rlive_list = [];

// Get event list
foreach(npps_query("
	SELECT * FROM `event_list`
	WHERE
		start_time <= $UNIX_TIMESTAMP AND
		close_time > $UNIX_TIMESTAMP
	") as $ev
)
{
	$event_info = $event_common_db->query("
		SELECT
			event_category_id,
			name,
			banner_asset_name,
			banner_se_asset_name,
			result_banner_asset_name
		FROM `event_m`
		WHERE
			event_id = {$ev['event_id']}
	");
	
	// If there's no event found with specificed event ID, skip
	if(count($event_info) == 0)
	{
		echo "event_id {$ev['event_id']} not found in database";
		continue;
	}
	else
		$event_info = $event_info[0];
	
	$event_list[] = [
		'event_id' => $ev['event_id'],
		'event_category_id' => $event_info['event_category_id'],
		'name' => $event_info['name'],
		'open_date' => to_utcdatetime($ev['event_start']),
		'start_date' => to_utcdatetime($ev['event_start'] + 1),
		'end_date' => to_utcdatetime($ev['event_end']),
		'close_date' => to_utcdatetime($ev['event_close']),
		'banner_asset_name' => $event_info['banner_asset_name'],
		'banner_se_asset_name' => $event_info['banner_se_asset_name'],
		'result_banner_asset_name' => $event_info['result_banner_asset_name'],
		'description' => 'nil'
	];
	
	if($ev['token_image'] != NULL)
	{
		// Token event, get it's live list
		foreach(['easy_song_list','normal_song_list','hard_song_list','expert_song_list'] as $i)
			if($ev[$i] && strlen($ev[$i]) > 0)
				foreach(npps_separate(',', $ev[$i]) as $live_id)
				{
					$lobj = npps_live_difficulty::get_instance($live_id);
					$live_time[] = [
						'live_difficulty_id' => ($live_id),
						'start_date' => to_utcdatetime($ev['event_start']),
						'end_date' => to_utcdatetime($ev['event_end']),
						'is_random' => $lobj->random_flag,
						'dangerous' => $lobj->stage_level >= 11,
						'use_quad_point' => $lobj->special_setting
					];
				}
	}
}

// B-side songs
foreach(npps_query("
	SELECT * FROM `b_side_schedule`
	WHERE
		end_available_time > $UNIX_TIMESTAMP AND
		start_available_time < $UNIX_TIMESTAMP
") as $v)
{
	$lobj = npps_live_difficulty::get_instance($v['live_difficulty_id']);
	
	$live_time[] = [
		'live_difficulty_id' => intval($v['live_difficulty_id']),
		'start_date' => to_utcdatetime($v['start_available_time']),
		'end_date' => to_utcdatetime($v['end_available_time']),
		'is_random' => $lobj->random_flag,
		'dangerous' => $lobj->stage_level >= 11,
		'use_quad_points' => $lobj->special_setting
	];
}

// Daily rotation
$today_midnight = $UNIX_TIMESTAMP - ($UNIX_TIMESTAMP % 86400);
foreach(live_get_current_daily() as $live_id)
{
	$lobj = npps_live_difficulty::get_instance($live_id);
	
	$live_time[] = [
		'live_difficulty_id' => $live_id,
		'start_date' => to_utcdatetime($today_midnight),
		'end_date' => to_utcdatetime($today_midnight + 86399),
		'is_random' => $lobj->random_flag,
		'dangerous' => $lobj->stage_level >= 11,
		'use_quad_points' => $lobj->special_setting
	];
}

// Random live
$user = npps_user::get_instance($USER_ID);
$rlive_attr = (intdiv($today_midnight, 86400) % 3) + 1;
foreach(['muse_random_live_lock', 'aqua_random_live_lock'] as $t)
	if($user->$t)
		$rlive_list[] = [
			'attribute_id' => $rlive_attr,
			'start_date' => to_utcdatetime ($today_midnight),
			'end_date' => to_utcdatetime ($today_midnight + 86399)
		];

return [
	[
		'event_list' => $event_list,
		'live_list' => $live_time,
		'limited_bonus_list' => [],
		'random_live_list' => $rlive_list
	],
	200
];
