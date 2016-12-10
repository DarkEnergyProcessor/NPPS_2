<?php
$unit_db = npps_get_database('unit');
$unit_table = npps_query("SELECT unit_table FROM `users` WHERE user_id = $USER_ID")[0]["unit_table"];
$unit_list = [];

foreach(npps_query("SELECT * FROM `$unit_table`") as $unit)
{
    $rarity = $unit_db->query("SELECT rarity FROM `unit_m` WHERE unit_id = {$unit["unit_id"]}");
	$is_promo = count($unit_db->query("SELECT unit_id FROM `unit_m` WHERE unit_id = {$unit["unit_id"]} AND normal_card_id = rank_max_card_id")) > 0;
	$is_idolized = $unit_db->query("SELECT after_level_max FROM `unit_m` WHERE unit_id = {$unit["unit_id"]}") == $unit["max_level"];
	
	$unit_list[] = [
		'unit_owning_user_id' => $unit["unit_owning_user_id"], //0
		'unit_id' => $unit["unit_id"], //1
		'exp' => $unit["exp"], //2
		'next_exp' => $unit["next_exp"], //3
		'level' => $unit["level"], //4
		'max_level' => $unit["max_level"], //5
		'rank' => $is_promo || $is_idolized ? 2 : 1,
		'max_rank' => 2,
		'love' => $unit["love"], //9
		'max_love' => $unit["max_love"], //10
		'unit_skill_level' => $unit["unit_skill_level"], //6
		'max_hp' => $unit["max_hp"], //8
		'is_rank_max' => $is_idolized,
		'favorite_flag' => $unit["favorite_flag"] > 0, //11
		'is_love_max' => $is_idolized ? $unit["love"] >= $unit["max_love"] : false,
		'is_level_max' => $is_idolized ? $unit["level"] >= $unit["max_level"] : false,
		'is_skill_level_max' => $rarity > 1 ? $unit["unit_skill_level"] >= 8 : true,
		'insert_date' => to_datetime($unit["insert_date"]), //12,
        'is_removable_skill_capacity_max' => true,
        'unit_removable_skill_capacity' => $unit["unit_removable_skill_capacity"],
        'unit_skill_exp' => $unit["unit_skill_exp"],
        'display_rank' => $unit["display_rank"],
        //'' => $unit[], blank scheme

	];
}

return [
	$unit_list,
	200
];
?>