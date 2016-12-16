<?php
//$deck_info = npps_query("SELECT deck_table, main_deck FROM `users` WHERE user_id = $USER_ID")[0];
$user = npps_user::get_instance($USER_ID);
$deck_data = [];

foreach(npps_query("
	SELECT * FROM `{$user->deck_table}`
	WHERE deck_members <> '0:0:0:0:0:0:0:0:0'
") as $deck)
{
	$deck_members = [];

	foreach(npps_separate(':', $deck['deck_members']) as $index => $units)
		if($units != 0)
			$deck_members[] = [
				'position' => $index + 1,
				'unit_owning_user_id' => $units
			];
	
	$deck_data[] = [
		'unit_deck_id' => $deck['deck_num'],
		'main_flag' => $user->main_deck == $deck['deck_num'],
		'deck_name' => $deck['deck_name'],
		'unit_owning_user_ids' => $deck_members
	];
}
return [
	$deck_data,
	200
];
