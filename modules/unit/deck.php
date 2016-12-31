<?php
$modify_deck_list = $REQUEST_DATA['unit_deck_list'] ?? null;

if($modify_deck_list == null)
	return false;

$user = npps_user::get_instance($USER_ID);

foreach($modify_deck_list as $list)
{
	$pos = [0, 0, 0, 0, 0, 0, 0, 0, 0];
	
	foreach($list['unit_deck_detail'] as $units)
		$pos[$units['position'] - 1] = intval($units['unit_owning_user_id']);
	
	$pos_out = implode(':', $pos);
	
	if(strlen($list['deck_name']) > 0 && mb_strlen($list['deck_name']) <= 10)
		npps_query("
			UPDATE `{$user->deck_table}`
			SET
				deck_name = ?,
				deck_members = ?
			WHERE deck_num = ?",
			'ssi',
			$list['deck_name'],
			$pos_out,
			$list['unit_deck_id']);
	else
		npps_query("
			UPDATE `{$user->deck_table}`
			SET
				deck_members = ?
			WHERE deck_num = ?",
			'si',
			$pos_out,
			$list['unit_deck_id']);
	
	if($list['main_flag'] == 1)
		$user->main_deck = $list['unit_deck_id'];
}

return [
	[],
	200
];
