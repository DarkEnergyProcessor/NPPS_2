<?php
// If online_match.json doesn't exist, that means there's no online match
if(!file_exists('data/online_match.json'))
	return [[], 200];

$user = npps_user::get_instance($USER_ID);
// Decode online match data
$od = json_decode(file_get_contents('data/online_match.json'), true);

return [
	[
		'online' => [
			'live_difficulty_ids' => $od['live_difficulty_ids'],
			'high_score' => $user->online_play_hi_score,
			'play_count' => $user->online_play_count,
			'max_play_count' => $od['max_play_count'],
			'can_entry' => $UNIX_TIMESTAMP >= $od['live_end_time'] &&
						   $UNIX_TIMESTAMP < $od['entry_end_time'],
			'live' => [
				'start_date' => to_datetime($od['start_time']),
				'end_date' => to_datetime($od['live_end_time']),
				'asset_bgm_id' => 4,
				'banner_type' => 3,
				'banner_asset_name' => $od['live_banner_asset_name'],
				'banner_se_asset_name' => $od['live_banner_se_asset_name'],
				'description_asset' => $od['live_description_asset'],
				'instructions_url' => $od['live_instructions_url']
			],
			'entry' => [
				'start_date' => to_datetime($od['live_end_time']),
				'end_date' => to_datetime($od['entry_end_time']),
				'asset_bgm_id' => 4,
				'banner_type' => 3,
				'banner_asset_name' => $od['entry_banner_asset_name'],
				'banner_se_asset_name' => $od['entry_banner_se_asset_name'],
				'description_asset' => $od['entry_description_asset'],
				'instructions_url' => $od['entry_instructions_url']
			],
			'explanation_url' => $od['live_explanation_url'],
			'entry_url' => $od['entry_url'],
			'banner_order' => 0
		]
	],
	200
];
