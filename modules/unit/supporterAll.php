<?php

$supporter_info = npps_query("SELECT unit_support_table FROM `users` WHERE user_id = $USER_ID")[0];
$supporter_data = [];

//var_dump(npps_query("SELECT * FROM `{$supporter_info["unit_support_table"]}`"));
//die();

foreach(npps_query("SELECT * FROM `{$supporter_info["unit_support_table"]}`") as $supporter)
{
	$supporter_members = [];
	
    var_dump($supporter);
	
	$supporter_data[] = [
		'unit_id' => $supporter["unit_id"],
		'amount' => $supporter_info["amount"],
	];
}
return [
	['unit_support_list' => $supporter_data],
	200
];

?>
