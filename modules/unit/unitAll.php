<?php
$user = npps_user::get_instance($USER_ID);
$unit_db = npps_get_database('unit');
$unit_list = [];

foreach(npps_query("SELECT * FROM `{$user->unit_table}`") as $unit)
{
	$unit_data = unit_database_get_info($unit['unit_id']);
	$is_promo = $unit_data['disable_rank_up'] == 1;
	$is_idolized = $unit['max_love'] == $unit_data['after_love_max'];
	
	$unit_list[] = [
		'unit_owning_user_id' => $unit['unit_owning_user_id'],
		'unit_id' => $unit['unit_id'],
		'exp' => $unit['exp'],
		'next_exp' => $unit['next_exp'],
		'level' => $unit['level'],
		'max_level' => $unit['max_level'],
		'rank' => ($is_promo || $is_idolized) ? 2 : 1,
		'max_rank' => 2,
        'display_rank' => $unit['display_rank'],
		'love' => $unit['love'],
		'max_love' => $unit['max_love'],
		'unit_skill_level' => $unit['unit_skill_level'],
        'unit_skill_exp' => $unit["unit_skill_exp"],
		'max_hp' => $unit['max_hp'],
		'is_rank_max' => $is_idolized,
		'favorite_flag' => $unit["favorite_flag"] > 0,
		'is_love_max' => $is_idolized ? $unit['love'] >= $unit['max_love'] : false,
		'is_level_max' => $is_idolized ? $unit['level'] >= $unit['max_level'] : false,
		'is_skill_level_max' => $unit_data['rarity'] > 1 ? $unit["unit_skill_level"] >= 8 : true,
        'is_removable_skill_capacity_max' => $unit['is_removable_skill_capacity_max'],
		'insert_date' => to_datetime($unit['insert_date']),
        'unit_removable_skill_capacity' => $unit['unit_removable_skill_capacity']
	];
}

return [
	$unit_list,
	200
];
