<?php
$scenario_tracking = intval(npps_query("SELECT latest_scenario FROM `users` WHERE user_id = $USER_ID")[0]["latest_scenario"]);
$new_scenario = intval(substr(strstr($scenario_tracking, '.') ?: ".$scenario_tracking", 1));

$scenario_data = [];

for($i = 1; $i <= $scenario_tracking; $i++)
	$scenario_data[] = [
		'scenario_id' => $i,
		'status' => 2
	];

if($new_scenario > $scenario_tracking)
	$scenario_data[] = [
		'scenario_id' => $new_scenario,
		'status' => 1
	];

for($i = 184; $i <= 188; $i++)
    	$scenario_data[] = [
		'scenario_id' => $i,
		'status' => 2
	];


/*
$scenario_data[] = json_decode('{
                        "scenario_id": 184,
                        "status": 2
                    },
                    {
                        "scenario_id": 185,
                        "status": 2
                    },
                    {
                        "scenario_id": 186,
                        "status": 2
                    },
                    {
                        "scenario_id": 187,
                        "status": 2
                    },
                    {
                        "scenario_id": 188,
                        "status": 2
                    }',false);
*/
return [
	[
		'scenario_status_list' => $scenario_data
	],
	200
];
?>