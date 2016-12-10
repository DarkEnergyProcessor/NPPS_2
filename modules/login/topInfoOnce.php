<?php
$user = npps_user::get_instance($USER_ID);
$achievement_cnt = 0;	// TODO
$non_complete_achievement_cnt = 0;	// TODO
$live_daily = $UNIX_TIMESTAMP >= ($user->last_live_play % 86400 + 86400);

return [
	[
		'new_achievement_cnt' => $achievement_cnt,
		'unaccomplished_achievement_cnt' => $non_complete_achievement_cnt,
		'handover_expire_status' => 0,	// TODO
		'live_daily_reward_exist' => $live_daily
	],
	200
];
