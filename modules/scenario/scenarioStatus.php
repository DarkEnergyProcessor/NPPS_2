<?php
$user = npps_user::get_instance($USER_ID);
$new_scenario_data = explode(',', $user->scenario_tracking);

$scenario_data = [];

foreach($new_scenario_data as $s)
{
	if(strlen($s) == 0) continue;
	
	$status = 1;
	$sid = 0;
	
	if(strcmp($s[0], '!') == 0)
	{
		$sid = intval(substr($s, 1));
		$status = 2;
	}
	else
		$sid = intval($s);
	$scenario_data[] = [
		'scenario_id' => $sid,
		'status' => $status
	];
}

return [
	[
		'scenario_status_list' => $scenario_data
	],
	200
];
