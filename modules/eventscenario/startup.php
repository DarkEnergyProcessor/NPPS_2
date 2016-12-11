<?php
if(!npps_config('UNLOCK_ALL_EVENTSCENARIO'))
{
	echo 'Unlock all eventscenario is not enabled in this server!';
	return ERROR_CODE_SUBSCENARIO_NOT_AVAILABLE;
}

$event_scenario_id = intval($REQUEST_DATA['event_scenario_id'] ?? 0);
$pseudo_event_id = intdiv($event_scenario_id - 1, 5) - 9;

if($event_scenario_id < 1 || $event_scenario_id > 45)
	return ERROR_CODE_SUBSCENARIO_NOT_FOUND;

return [
	[
		'event_scenario_list' => [
			'event_id' => $pseudo_event_id,
			'progress' => 4,
			'status' => 2,
			'event_scenario_id' => $event_scenario_id
		],
		'scenario_adjustment' => 50
	],
	200
];
