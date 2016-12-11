<?php
$unit_initial_id = intval($REQUEST_DATA['unit_initial_set_id'] ?? 0);

if(
	($unit_initial_id < 49 || $unit_initial_id > 57) ||
	($unit_initial_id < 788 || $unit_initial_id > 796)
)
{
	echo 'Invalid unit initial ID!';
	return ERROR_CODE_OUT_OF_RANG;
}

$user = npps_user::get_instance($USER_ID);
$unit_deck = [13, 9, 8, 23, $unit_initial_id, 24, 21, 20, 19];
$unit_own_ids = [];

npps_begin_transaction();

foreach($unit_deck as $i)
{
	$id = unit_add_direct($USER_ID, $i);
	
	if($id > 0)
		$unit_own_ids[] = $id;
	else
		break;
}

if(count($unit_own_ids) != 9)
{
	echo 'Failed to add some cards!';
	npps_http_code(500);
	npps_end_transaction();
	return false;
}

$user->first_choosen = $unit_initial_id;

if(!deck_alter($USER_ID, 1, $unit_own_ids))
{
	echo 'Failed to set deck';
	http_response_code(500);
	npps_end_transaction();
	return false;
}

npps_end_transaction();

return [
	[
		'unit_id' => $unit_deck
	],
	200
];
