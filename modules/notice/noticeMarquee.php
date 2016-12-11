<?php
$marquee_list = [];
$user = npps_user::get_instance($USER_ID);

// Check if player is ever issuing passcode and it's almost expired
$passcode_expire = 0;
$expire_text = 'Your passcode issued at <color 00ff00>%s<color ffffff> UTC '
	         . 'expire at <color 00ff00>%s<color ffffff> UTC';

if($user->passcode_issue !== NULL)
	$passcode_expire = $user->passcode_issue - npps_config('PASSCODE_ISSUE_WARNING') + 31536000;

if($user->passcode_issue !== NULL && $passcode_expire >= $UNIX_TIMESTAMP)
	$marquee_list[] = [
		'marquee_id' => 0,
		'text' => sprintf($expire_text,
			gmstrftime('%B, %d %Y %H:%M:%S', $user->passcode_issue),
			gmstrftime('%B, %d %Y %H:%M:%S', $passcode_expire)
		),
		'text_color' => 0,
		'display_place' => 0,
		'start_time' => $TEXT_TIMESTAMP,
		'end_time' => to_utcdatetime($passcode_expire)
	];

// Get marquee notice list
foreach(npps_query("
	SELECT * FROM `marquee_notice`
	WHERE
		start_date >= $UNIX_TIMESTAMP AND
		end_date < $UNIX_TIMESTAMP
	") as $mq)
	$marquee_list[] = [
		'marquee_id' => $mq['marquee_id'],
		'text' => $mq['text'],
		'text_color' => 0,
		'display_place' => 0,
		'start_time' => to_utcdatetime($mq['start_date']),
		'end_time' => to_utcdatetime($mq['end_date'])
	];

return [
	[
		'item_count' => count($marquee_list),
		'marquee_list' => $marquee_list
	],
	200
];
