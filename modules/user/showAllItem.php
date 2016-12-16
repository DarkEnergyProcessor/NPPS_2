<?php
$user = npps_user::get_instance($USER_ID);
$item = npps_query("SELECT * FROM `{$user->item_table}`");
$items = [];

foreach($item as $item_add)
	$items[] = [
		'item_id' => $item_add["item_id"],
		'item_category_id' => 1,
		'item_sub_category_id' => 1,
		'amount' => $item_add['amount'],
		'insert_date' => to_datetime(0)
	];

return [
	[
		'items' => $items
	],
	200
];
