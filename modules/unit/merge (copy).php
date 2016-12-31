<?php
$unit_db = npps_get_database('unit');
$some_tables = npps_query("SELECT unit_table, deck_table, gold, level, current_exp, next_exp, paid_loveca + free_loveca, friend_points, max_unit, max_lp, max_friend, album_table FROM `users` WHERE user_id = $USER_ID")[0];
//var_dump($some_tables);
$unit_table = $some_tables['unit_table'];
$deck_table = $some_tables['deck_table'];
$album_table = $some_tables['album_table'];

$practice_list = $REQUEST_DATA['unit_owning_user_ids'] ?? [];
$practice_base = intval($REQUEST_DATA['base_owning_unit_user_id'] ?? 0);

/* Getting unit data */
$base_info = npps_query("SELECT * FROM {$unit_table} WHERE unit_owning_user_id = {$practice_base}")[0];
$base_temp_data = npps_unit_tempinfo::instance();
$unit_data = $base_temp_data->getUnit($base_info['unit_id']);
die(var_dump($unit_data));
return [
	[
	'before' => $before_unit_data,
	'after' => $after_unit_data,
	'before_user_info' => $before_user_data,
	'after_user_info' => $after_unit_data,
	'use_game_coin' => 0, //at the moment everything is free lol
	'evolution_setting_id' => 1,
	'bonus_value' => 1,
	'open_subscenario_id' => null,
	'get_exchange_point_list' => [], //still empty at the moment
	'unit_removable_skill' => [$skill_info]
	],
200];