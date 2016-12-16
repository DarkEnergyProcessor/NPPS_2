<?php
$live_difficulty_id = $REQUEST_DATA['live_difficulty_id'] ?? 0;

if(!is_integer($live_difficulty_id) || $live_difficulty_id == 0)
{
	echo 'Invalid live_difficulty_id';
	return false;
}

// If online_match.json doesn't exist, that means there's no online match
if(!file_exists('data/online_match.json'))
	return ERROR_CODE_ONLINE_LIVE_HAS_GONE;

$od = json_decode(file_get_contents('data/online_match.json'), true);

// Check online match time
if($UNIX_TIMESTAMP < $od['start_time'] ||
   $UNIX_TIMESTAMP > $od['live_end_time']
)
	return ERROR_CODE_ONLINE_LIVE_HAS_GONE;

$user = npps_user::get_instance($USER_ID);

if($user->online_play_count >= $od['max_play_count'])
	return ERROR_CODE_ONLINE_PLAY_COUNT_LIMIT_OVER;

// Load live difficulty data
$lobj = NULL;
try
{
	$lobj = npps_live_difficulty::get_instance($live_difficulty_id, 'online');
}
catch (Exception $ex)
{
	return ERROR_CODE_LIVE_NOT_FOUND;
}

// Check notes data existence
$lset = $lobj->live_setting;
if(!$lset->notes_data_exist())
	return ERROR_CODE_LIVE_NOTES_LIST_NOT_FOUND;

// Add play count
$user->online_play_count++;

return [
	[
		'rank_info' => [
			[
				'rank' => 5,
				'rank_min' => 0,
				'rank_max' => $lobj->c_score - 1
			],
			[
				'rank' => 4,
				'rank_min' => $lobj->c_score,
				'rank_max' => $lobj->b_score - 1
			],
			[
				'rank' => 3,
				'rank_min' => $lobj->b_score,
				'rank_max' => $lobj->a_score - 1
			],
			[
				'rank' => 2,
				'rank_min' => $lobj->a_score,
				'rank_max' => $lobj->s_score - 1
			],
			[
				'rank' => 1,
				'rank_min' => $lobj->s_score,
				'rank_max' => 0
			]
		],
		'live_info' => [
			'live_difficulty_id' => $live_difficulty_id,
			'is_random' => $lobj->random_flag,
			'dangerous' => $lobj->stage_level >= 11,
			'notes_speed' => 0.8,
			'notes_list' => $lset->load_notes_data()
		],
		'reward_info' => [
			'reward_count' => 1
		]
	],
	200
];
