<?php
$user = npps_user::get_instance($USER_ID);

$present_count = npps_query("
	SELECT COUNT(incentive_idx) as a FROM `{$user->present_table}`
	WHERE collected IS NULL
")[0]['a'];
$pm_count = npps_query("
	SELECT COUNT(notice_id) as a FROM `notice_list`
	WHERE receiver_user_id = $USER_ID AND is_pm == 1 AND is_new == 1
")[0]['a'];
$activity_count = npps_query("
	SELECT COUNT(notice_id) as a FROM `notice_list`
	WHERE receiver_user_id = $USER_ID AND is_pm == 0 AND is_new == 1
")[0]['a'];
$friend_approve_cnt = npps_query("
	SELECT COUNT(from_user_id) as a FROM `{$user->friend_table}`
	WHERE is_approved == 0
")[0]['a'];

return [
	[
		'friend_action_cnt' => $activity_count + $pm_count,
		'friend_greet_cnt' => $pm_count,
		'friend_variety_cnt' => $activity_count,
		'present_cnt' => $present_count,
		'free_muse_gacha_flag' => $UNIX_TIMESTAMP >= $user->muse_free_gacha,
		'free_aqours_gacha_flag' => $UNIX_TIMESTAMP >= $user->aqua_free_gacha,
		'server_datetime' => $TEXT_TIMESTAMP,
		'server_timestamp' => $UNIX_TIMESTAMP,
		'next_free_muse_gacha_timestamp' => $user->muse_free_gacha,
		'next_free_aqours_gacha_timestamp' => $user->aqua_free_gacha,
		'notice_friend_datetime' => $TEXT_TIMESTAMP,
		'notice_mail_datetime' => $TEXT_TIMESTAMP,
		'friends_approval_wait_cnt' => $friend_approve_cnt
	],
	200
];
