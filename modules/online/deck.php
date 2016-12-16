<?php
// We need to check if online match is going on
if(!file_exists('data/online_match.json'))
	return ERROR_CODE_ONLINE_LIVE_HAS_GONE;

$od = json_decode(file_get_contents('data/online_match.json'), true);

// Check online match time
if($UNIX_TIMESTAMP < $od['start_time'] ||
   $UNIX_TIMESTAMP > $od['live_end_time']
)
	return ERROR_CODE_ONLINE_LIVE_HAS_GONE;

$user = npps_user::get_instance($USER_ID);

// Check if player still have chance to play
if($user->online_play_count >= $od['max_play_count'])
	return ERROR_CODE_ONLINE_PLAY_COUNT_LIMIT_OVER;

$od['guest_info']['friend_status'] = 0;

return [
	[
		'unit_deck' => $od['unit_deck'],
		'guest_info' => $od['guest_info']
	],
	200
];
