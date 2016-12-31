<?php
//Getting unit and user data
$base_unit = $REQUEST_DATA['base_owning_unit_user_id'];
$sticker_used = $REQUEST_DATA['exchange_point_id'];
$user_data = npps_user::get_instance($USER_ID);
$before_user_info = $user_data->user_info_rankup();
$unit_table = npps_query("SELECT unit_table FROM `users` WHERE user_id = $USER_ID")[0]['unit_table'];
$exchange_array = npps_query("SELECT normal_sticker, silver_sticker, gold_sticker, purple_sticker FROM users WHERE user_id = {$USER_ID}")[0];
//Array structure: $exange_array[rarity_card][rarity_sticker]
$exange_array = [2 => 
[2 => 1], 
3 => 
[2 => 20, 3 => 1], 
4 => 
[2 => 500, 3 => 15, 4 => 1, 5 => 5], 
5 => 
[2 => 100, 3 => 5, 5 => 1]
];
//User base array
/*
$user_array = [
    'level' => $user_data->$level,
    'exp' => $user_data->$exp,
    'previous_exp' => $user_data->,
    'next_exp' => $user_data->,
    'game_coin' => $user_data->,
    'sns_coin' => $user_data->,
    'social_point' => $user_data->,
    'unit_max' => $user_data->,
    'energy_max' => $user_data->,
    'friend_max' => $user_data->,
    'tutorial_state' => -1,
    'energy_full_time' => $user_data->,
    'over_max_energy' => $user_data->,
    'unlock_random_live_muse' => 1,
    'unlock_random_live_aqours' => 1
];
*/
$unit_user_data = npps_query("SELECT unit_id, exp, next_exp, level, max_level, rank, love, max_love, unit_skill_exp, unit_skill_level, max_hp, unit_removable_skill_capacity, favorite_flag, display_rank, is_rank_max FROM {$unit_table} WHERE unit_owning_user_id = {$base_unit}")[0];
$unit_data = npps_get_database("unit")->query("SELECT rarity, before_love_max, after_love_max, before_level_max, after_level_max, max_removable_skill_capacity, exchange_point_rank_up_cost FROM unit_m WHERE unit_id = {$unit_user_data['unit_id']}")[0];

$using_amount_sticker = $exange_array["{$unit_data['rarity']}"][$sticker_used];

if($unit_user_data['rank'] == 2){
    $skill_add = 1;
    $level_add = 0;
    $love_add = 1;
}
elseif ($unit_user_data['rank'] == 1){
    $skill_add = 0;
    $level_add = 20;
    $love_add = 2;
}

$skill_slot = $unit_user_data['unit_removable_skill_capacity'] + $skill_add;
$max_level = $unit_user_data['max_level'] + $level_add;
$max_love = $unit_user_data['max_love'] * $love_add;

npps_query("UPDATE {$unit_table} SET 
unit_removable_skill_capacity = {$skill_slot}, 
max_level = {$max_level}, 
rank = 2, 
display_rank = 2, 
max_love = {$max_love},
is_rank_max = 1
WHERE unit_owning_user_id = {$base_unit}");

switch($sticker_used){
    case 2: $sticker_using = "normal_sticker";
    break;
    case 3: $sticker_using = "silver_sticker";
    break;
    case 4: $sticker_using = "purple_sticker";
    break;
    case 5: $sticker_using = "gold_sticker";
    break;
    default: $sticker_using = "normal_sticker";
    break;
}
npps_query("UPDATE users SET {$sticker_using}={$exchange_array[$sticker_using]}-{$using_amount_sticker} WHERE user_id = {$USER_ID}");
$after_user_info = $user_data->user_info_rankup();


return [[
    'before' => [
        'unit_owning_user_id'=> $base_unit,
        'unit_id'=> $unit_user_data['unit_id'],
        'exp'=> $unit_user_data['exp'],
        'next_exp'=> $unit_user_data['next_exp'],
        'level'=> $unit_user_data['level'],
        'max_level'=> $unit_user_data['max_level'],
        'rank'=> $unit_user_data['rank'],
        'max_rank'=> 2,
        'love'=> $unit_user_data['love'],
        'max_love'=> $unit_user_data['max_love'],
        'unit_skill_exp'=> $unit_user_data['unit_skill_exp'],
        'unit_skill_level'=> $unit_user_data['unit_skill_level'],
        'max_hp'=> $unit_user_data['max_hp'],
        'unit_removable_skill_capacity'=> $unit_user_data['unit_removable_skill_capacity'] - 1,
        'favorite_flag'=> $unit_user_data['favorite_flag'] > 0,
        'display_rank'=> $unit_user_data['display_rank'],
        'is_rank_max'=> $unit_user_data['is_rank_max'] > 0,
        'is_love_max'=> false,
        'is_level_max'=> false
    ],
    'after' => [
        'unit_owning_user_id'=> $base_unit,
        'unit_id'=> $unit_user_data['unit_id'],
        'exp'=> $unit_user_data['exp'],
        'next_exp'=> $unit_user_data['next_exp'],
        'level'=> $unit_user_data['level'],
        'max_level'=> $unit_user_data['max_level'] + $level_add,
        'rank'=> 2,
        'max_rank'=> 2,
        'love'=> $unit_user_data['love'],
        'max_love'=> $unit_user_data['max_love'] * $love_add,
        'unit_skill_exp'=> $unit_user_data['unit_skill_exp'],
        'unit_skill_level'=> $unit_user_data['unit_skill_level'],
        'max_hp'=> $unit_user_data['max_hp'],
        'unit_removable_skill_capacity'=> $unit_user_data['unit_removable_skill_capacity'] + $skill_add,
        'favorite_flag'=> $unit_user_data['favorite_flag'] > 0,
        'display_rank'=> 2,
        'is_rank_max'=> true,
        'is_love_max'=> false,
        'is_level_max'=> false
    ],
    'before_user_info' => 
        $before_user_info,
    'after_user_info' => 
        $after_user_info
],
200];
