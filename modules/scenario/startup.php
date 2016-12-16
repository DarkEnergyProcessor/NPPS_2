<?php
$user = npps_user::get_instance($USER_ID);
$scenario = intval($REQUEST_DATA['scenario_id'] ?? 0);

if(!is_integer($scenario) || $scenario == 0)
{
	echo 'Invalid scenario ID!';
	return ERROR_CODE_OUT_OF_RANG;
}

foreach(npps_separate(',', $user->scenario_tracking) as $s)
{
	$sid = $s;
	
	if(strcmp($s[0], '!') == 0)
		$sid = intval(substr($s, 1));
	
	if($sid == $scenario)
		return [
			[
				'scenario_id' => $scenario,
				'scenario_adjustment' => 50
			],
			200
		];
}

return ERROR_CODE_SCENARIO_NOT_AVAILABLE;
