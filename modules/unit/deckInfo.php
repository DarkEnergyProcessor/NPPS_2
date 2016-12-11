<?php
$deck_info = npps_query("SELECT deck_table, main_deck FROM `users` WHERE user_id = $USER_ID")[0];
$deck_data = [];

foreach(npps_query("SELECT * FROM `{$deck_info["deck_table"]}` WHERE deck_members <> \"0:0:0:0:0:0:0:0:0\"") as $deck)
{
	$deck_members = [];
	
    //var_dump($deck);

	foreach(explode(':', $deck['deck_members']) as $index => $units)
		if($units != 0)
			$deck_members[] = [
				'position' => $index + 1,
				'unit_owning_user_id' => intval($units)
			];
	
	$deck_data[] = [
		'unit_deck_id' => $deck['deck_num'],
		'main_flag' => $deck_info["deck_table"] == $deck['deck_num'],
		'deck_name' => $deck['deck_name'],
		'unit_owning_user_ids' => $deck_members
	];
}
return [
	$deck_data,
	200
];
