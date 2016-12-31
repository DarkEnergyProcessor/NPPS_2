<?php
$target_idolize = intval($REQUEST_DATA['unit_owning_user_ids'][0] ?? 0);
$base_idolize = intval($REQUEST_DATA['base_owning_unit_user_id'] ?? 0);
if($target_idolize == $base_idolize)
{
	echo 'Invalid idolize member ID!';
	return false;
}
$unit_db = npps_get_database('unit');
$user_tables = npps_query("SELECT album_table, unit_table, deck_table FROM `users` WHERE user_id = $USER_ID")[0];
$album_table = $user_tables['album_table'];
$unit_table = $user_tables['unit_table'];
$deck_table = $user_tables['deck_table'];
$base_info = NULL;
$target_unit_id = 0;
$base_unit_id = 0;
{
	$temp = npps_query("SELECT unit_id, exp, next_exp, level, max_level, unit_skill_level, love, max_love, max_hp, favorite_flag FROM `$unit_table` WHERE unit_owning_user_id = $base_idolize ");
	$temp2 = npps_query("SELECT unit_id, exp, next_exp, level, max_level, unit_skill_level, love, max_love, max_hp, favorite_flag FROM `$unit_table` WHERE unit_owning_user_id = $target_idolize ");
    var_dump($temp2);
	var_dump($temp);
	$target_unit_id = $temp2[0]['unit_id'];
	$base_unit_id = $temp[0]['unit_id'];
	$base_info = [
		'unit_owning_user_id' => $base_idolize,
		'unit_id' => $temp[0]['unit_id'],
		'exp' => $temp[0]['exp'],
		'next_exp' => $temp[0]['next_exp'],
		'level' => $temp[0]['level'],
		'max_level' => $temp[0]['max_level'],
		'rank' => 1,		// Should be.
		'max_rank' => 2,	// Should be.
		'love' => $temp[0]['love'],
		'max_love' => $temp[0]['max_love'],
		'unit_skill_level' => $temp[0]['unit_skill_level'],
		'max_hp' => $temp[0]['max_hp'],
		'favorite_flag' => $temp[0]['favorite_flag'],
		'is_rank_max' => false,
		'is_love_max' => false,
		'is_level_max' => false
	];
}
if($target_unit_id != $base_unit_id)
{
	echo 'Card type doesn\'t match!';
	return false;
}
$user_info = NULL;
{
	$temp = npps_query("SELECT level, current_exp, next_exp, gold, paid_loveca + free_loveca, friend_points, max_unit, max_lp, max_friend FROM `users` WHERE user_id = $USER_ID")[0];
	
	$user_info = [
		'level' => $temp['level'],
		'exp' => $temp['current_exp'],
		'next_exp' => $temp['next_exp'],
		'game_coin' => $temp['gold'],
		'sns_coin' => $temp['paid_loveca + free_loveca'],
		'social_point' => $temp['friend_points'],
		'unit_max' => $temp['max_unit'],
		'energy_max' => $temp['max_lp'],
		'friend_max' => $temp['max_friend']
	];
}
$idolize_info = $unit_db->query("SELECT rank_up_cost, after_love_max, after_level_max FROM `unit_m` WHERE unit_id = $base_unit_id")[0];
/* If it's already idolized, error */
/*
if($base_info['max_level'] >=  $idolize_info[2])
{
	echo 'Already idolized!';
	return false;
}
*/
/* Also, if player doesn't have enough money, error */
if($user_info['game_coin'] < $idolize_info['rank_up_cost'])
{
	echo 'Not enough money!';
	return false;
}
/* Deduce player money */
$new_user_info = array_merge([], $user_info);
$new_user_info['game_coin'] -= $idolize_info['rank_up_cost'];
npps_query("UPDATE `users` SET gold = gold - {$idolize_info['rank_up_cost']} WHERE user_id = $USER_ID");
/* Set idolized flag */
$new_base_info = array_merge([], $base_info);
$new_base_info['rank'] = 2;
$new_base_info['is_rank_max'] = true;
npps_query("UPDATE `$unit_table` SET max_love = ?, max_level = ? WHERE unit_id = $base_idolize", 'ii', $idolize_info['after_love_max'], $idolize_info['after_level_max']);
/* Update in album */
{
	$flags = npps_query("SELECT flags FROM `$album_table` WHERE unit_id = $base_unit_id")[0]['flags'];
	
	if(($flags & 2) == 0)
		npps_query("UPDATE `$album_table` SET flags = ? WHERE unit_id = ?", 'ii', $flags | 2, $base_unit_id);
}
unit_remove($USER_ID, $target_idolize);
user_set_last_active($USER_ID, $TOKEN);
npps_query("UPDATE `{$unit_table}` SET rank = ?, display_rank = ?, max_love = {$base_info['max_love']}, is_rank_max = ? WHERE `unit_owning_user_id` = {$base_idolize}", "iii", 2,2,1);
return [
	[
		'before' => $base_info,
		'after' => $new_base_info,
		'before_user_info' => $user_info,
		'after_user_info' => $new_user_info,
		'use_game_coin' => $idolize_info['rank_up_cost'],
		'open_subscenario_id' =>  NULL,
		'get_exchange_point_list' => []
	],
	200
];
