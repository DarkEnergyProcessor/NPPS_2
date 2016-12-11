<?php
$user = npps_user::get_instance($USER_ID);
$new_scenario_data = npps_separate('.', $user->latest_scenario);
$last_scenario = $new_scenario_data[0];
$new_scenario = $new_scenario_data[1] ?? $last_scenario;

$scenario_data = [];

for($i = 1; $i <= $last_scenario; $i++)
	$scenario_data[] = [
		'scenario_id' => $i,
		'status' => 2
	];

if($new_scenario > $last_scenario)
	$scenario_data[] = [
		'scenario_id' => $new_scenario,
		'status' => 1
	];

return [
	[
		'scenario_status_list' => $scenario_data
	],
	200
];
