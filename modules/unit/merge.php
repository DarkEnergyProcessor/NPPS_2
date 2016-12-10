<?php
$unit_db = npps_get_database('unit');
$some_tables = npps_query("SELECT unit_table, deck_table, gold, level, current_exp, next_exp, paid_loveca + free_loveca, friend_points, max_unit, max_lp, max_friend, album_table FROM `users` WHERE user_id = $USER_ID")[0];
//var_dump($some_tables);
$unit_table = $some_tables['unit_table'];
$deck_table = $some_tables['deck_table'];
$album_table = $some_tables['album_table'];

$practice_list = $REQUEST_DATA['unit_owning_user_ids'] ?? [];
$practice_base = intval($REQUEST_DATA['base_owning_unit_user_id'] ?? 0);

if(count($practice_list) == 0 || $practice_base == 0)
{
	echo 'No practice target';
	return false;
}

$base_before = npps_query("SELECT * FROM `$unit_table` WHERE unit_owning_user_id = $practice_base")[0];
//var_dump($base_before);
$base_rarity = 1;
$base_idolized = false;
$base_promo = false;
$base_is_supprot_card = false;
$base_max_level = 30;
$base_attribute = 0;
$base_pattern = 0;
$base_skill = [$base_before['unit_skill_level'], $base_before['unit_skill_exp']]; //Skill level, skill exp
$base_skill_id = 0;
$base_hp = $base_before['max_hp']; //HP
$base_hp_max = $base_hp;

$total_gained_exp = 0;
$total_skill_gained = 0;
$needed_gold = 0;
$seal_gained = [0, 0, 0, 0]; //R, SR, UR, SSR

/* Get base card info */
{
	$uid = $base_before['unit_id'];
	$temp = $unit_db->query("SELECT attribute_id, after_level_max, unit_level_up_pattern_id, rarity, default_unit_skill_id, normal_card_id = rank_max_card_id, disable_rank_up, hp_max FROM `unit_m` WHERE unit_id = $uid")[0];
	//var_dump($temp);
	$base_promo = $temp['normal_card_id = rank_max_card_id'] > 0;
	$base_max_level = $base_before['max_level']; //Max level
	$base_attribute = $temp['attribute_id'];
	$base_idolized = $base_max_level >= $temp['after_level_max'];
	$base_pattern = $temp['unit_level_up_pattern_id'];
	$base_rarity = $temp['rarity'];
	$base_skill_id = $temp['default_unit_skill_id'] ?? 0;
	$base_is_support_card = $temp['disable_rank_up'] > 0;
	$base_hp_max = $temp['hp_max'];
}

foreach($practice_list as $used_own_id)
{
	if($used_own_id == $practice_base || $used_own_id == 0)
	{
		echo 'Invalid member ID';
		return false;
	}
	
	if(deck_card_in_deck($USER_ID, $used_own_id) == 2)
	{
		echo 'Practice list is in main deck!';
		return false;
	}
	
	$unit_id = npps_query("SELECT unit_id, level, max_level FROM `$unit_table` WHERE unit_owning_user_id = ?", 'i', $used_own_id)[0];
	//var_dump($unit_id);
	$unit_info = $unit_db->query("SELECT unit_level_up_pattern_id, attribute_id, rarity, default_unit_skill_id, normal_card_id, rank_max_card_id, disable_rank_up, before_level_max FROM `unit_m` WHERE unit_id = {$unit_id['unit_id']}")[0];
	$merge_info = $unit_db->query("SELECT merge_exp, merge_cost FROM `unit_level_up_pattern_m` WHERE unit_level_up_pattern_id = {$unit_info['unit_level_up_pattern_id']} AND unit_level == {$unit_id['level']}")[0];
	$unit_idolized = $unit_id['max_level'] > $unit_info['before_level_max'];
	
	/* 20% add if same attribute */
	$total_gained_exp += $unit_info['attribute_id'] == $base_attribute ? intval((float)$merge_info['merge_exp'] * 1.2) : $merge_info['merge_exp'];
	$needed_gold += $merge_info['merge_cost'];
	
	/* Check if it's possible to increase skill level */
	if($base_skill_id > 0 && $unit_info['default_unit_skill_id'] > 0 && $base_before['unit_skill_level'] < 8 && $base_rarity > 1) //$base_before[6] Skill level
		if(
			/* Same Skill */
			$unit_info[3] == $base_skill_id || 
			/* Rare support cards */
			($base_rarity == 2 && (
				($unit_id['unit_id'] == 379 && $base_attribute == 1) || /* Cocoa Yazawa, R Smile */
				($unit_id['unit_id'] == 380 && $base_attribute == 2) || /* Cotaro Yazawa, R Pure */
				($unit_id['unit_id'] == 381 && $base_attribute == 3)    /* Cocoro Yazawa, R Cool */
			)) ||
			/* SR support cards */
			($base_rarity == 3 && (
				($unit_id['unit_id'] == 383 && $base_attribute == 1) || /* Mika, SR Smile */
				($unit_id['unit_id'] == 384 && $base_attribute == 2) || /* Fumiko, SR Pure */
				($unit_id['unit_id'] == 385 && $base_attribute == 3) || /* Hideko, SR Cool */
				$unit_id['unit_id'] == 386								/* Hiroko Yamada, SR all attributes */
			)) ||
			/* UR support cards */
			($base_rarity == 3 && (
				($unit_id['unit_id'] == 387 && $base_attribute == 1) ||	/* Nico's mother, UR Smile */
				($unit_id['unit_id'] == 388 && $base_attribute == 2) || /* Kotori's mother, UR Pure */
				($unit_id['unit_id'] == 389 && $base_attribute == 3) ||	/* Maki's mother, UR Cool */
				$unit_id['unit_id'] == 390								/* Honoka's mother, UR all attributes */
			))
		)
			$total_skill_gained++;	// Increase skill EXP
	
	/* Check if it's possible to get seal */
	if($base_rarity > 1)
	{
		if($unit_info['normal_card_id'] == $unit_info['rank_max_card_id'])
			/* Is promo card. Give pink seal regardless of type */
			$seal_gained[0]++;
		else
		{
			/* Support card or normal card */
			$seal_index = &$seal_gained[$unit_info['rarity'] - 2];
			
			//if($unit_info[6] == 0 && $unit_idolized)
				/* Idolized. Not support card */
				//$seal_index += 2;
			//else
			/* Not idolized/support card */
			$seal_index++;
		}
	}
}

if($some_tables['gold'] < $needed_gold)
{
	echo 'Not enough money, you poor!';
	return false;
}

foreach($practice_list as $practice_unit_id)
	unit_remove($USER_ID, intval($practice_unit_id));

/* Deduce player money and add seals*/
npps_query("UPDATE `users` SET gold = gold - $needed_gold, normal_sticker = normal_sticker + {$seal_gained[0]}, silver_sticker = silver_sticker + {$seal_gained[1]}, gold_sticker = gold_sticker + {$seal_gained[3]}, purple_sticker = purple_sticker + {$seal_gained[2]} WHERE user_id = $USER_ID");

/* Calculate Super Success or Ultra Success */
$practice_bonus = 1;
$practice_chance = random_int(0, 100000) / 1000;
$bonus_value = 1;

if($practice_chance <= 2.0)
{
	$practice_bonus = 3;
	$total_gained_exp *= 2;
	$bonus_value = 2;
}
else if($practice_chance <= 8.0)
{
	$practice_bonus = 2;
	$total_gained_exp = intval((float)$total_gained_exp * 1.5);
	$bonus_value = 1.5;
}
/* No need else for normal Success */

/* Get card next_exp */
$new_hp = $base_hp_max;
$next_exp = 0;
$limit_exp = 2147483647; // No limit
$new_card_level = $base_before['level']; //level
{
	$temp = $unit_db->query("SELECT unit_level, next_exp, hp_diff FROM `unit_level_up_pattern_m` WHERE unit_level_up_pattern_id = $base_pattern AND next_exp > ? LIMIT 1", 'i', $total_gained_exp + $base_before['exp']); //$base_before[2] = current_exp
	$limit_exp_temp_data = $unit_db->query("SELECT unit_level, next_exp FROM `unit_level_up_pattern_m` WHERE unit_level_up_pattern_id = $base_pattern ORDER BY unit_level DESC LIMIT 2");
	
	$limit_exp = $limit_exp_temp_data[1]['next_exp'];
	$next_exp = $limit_exp_temp_data[0]['next_exp'];
	$new_card_level = $limit_exp_temp_data[0]['unit_level'];
	
	if(count($temp) > 0)
	{
		$new_hp -= $temp[0]['hp_diff'];
		$new_card_level = $temp[0]['unit_level'];
		$next_exp = $temp[0]['next_exp'];
	}
}

/* Get new skill level and EXP */
$skill_level_exp = $total_skill_gained + $base_before['unit_skill_exp']; //Skill EXP
$new_skill_level = $base_before['unit_skill_level']; //Skill level

while($skill_level_exp >= 2 ** ($new_skill_level - 1))
	$skill_level_exp -= $new_skill_level++;

if($new_skill_level >= 8)
{
	$new_skill_level = 8;
	$skill_level_exp = 0;
}

$new_max_level = $new_card_level >= $base_max_level;
$new_after_exp = $new_max_level ? $limit_exp : $base_before['exp'] + $total_gained_exp; //$base_before[2] = current_exp

/* Update card */
npps_query("UPDATE `$unit_table` SET level = ?, exp = ?, next_exp = ?, unit_skill_level = ?, unit_skill_exp = ?, max_hp = ? WHERE unit_owning_user_id = $practice_base", 'iiiiii', $new_card_level, $new_after_exp, $next_exp, $new_skill_level, $skill_level_exp, $new_hp);

/* Update album if max level */
if($new_max_level && $base_idolized)
{
	$temp_album_data = npps_query("SELECT flags FROM `$album_table` WHERE unit_id = {$base_before['unit_id']}")[0]['flags'];
	
	if(($temp_album_data & 8) == 0)
		npps_query("UPDATE `$album_table` SET flags = ? WHERE unit_id = ?", 'ii', $temp_album_data | 8, $base_before['unit_id']); //aka unit_id
}

/* Send response seal */
$seal_list = [];
foreach($seal_gained as $k => $v)
{
	if($v > 0)
		$seal_list[] = [
			'rarity' => $k + 2,
			'exchange_point' => $v
		];
}

user_set_last_active($USER_ID, $TOKEN);

npps_query("UPDATE users SET tutorial_state = -1 WHERE user_id = {$USER_ID}");

/* Out */
return [[ "code" => 20001,
         "message" => "Rebuilding merge response. You have to restatrt game"],403];
/* Rebuilding return */
/*
return [
	[
		'before' => [
			'unit_owning_user_id' => $practice_base,
			'unit_id' => $base_before['unit_id'], //unit_id
			'exp' => $base_before['exp'], //exp
			'next_exp' => $base_before['next_exp'], //next exp
			'level' => $base_before['level'], //level
			'max_level' => $base_before['max_level'], //max_level
			'rank' => $base_promo || $base_idolized ? 2 : 1,
			'max_rank' => 2,
			'love' => $base_before['love'], //love
			'max_love' => $base_before['max_love'], //max_love
			'unit_skill_level' => $base_before['unit_skill_level'], //skill unit lv
			'max_hp' => $base_before['max_hp'], //max_hp
			'favorite_flag' => $base_before['favorite_flag'], //fav flag
			'is_rank_max' => $base_idolized,
			'is_love_max' => $base_idolized ? $base_before['love'] >= $base_before['max_love'] : false, //bond aka love (actual -> new)
			'is_level_max' => $base_idolized ? $base_before['level'] >= $base_before['max_level'] : false,  //level (^)
			'is_removable_skill_capacity_max' => $base_before['is_removable_skill_capacity_max'] > 0
		],
		'after' => [
			'unit_owning_user_id' => $practice_base,
			'unit_id' => $base_before['unit_id'], //all 'base_before' follow prev. array
			'exp' => $new_after_exp,
			'next_exp' => $next_exp,
			'level' => $new_card_level,
			'max_level' => $base_before['max_level'],
			'rank' => $base_promo || $base_idolized ? 2 : 1,
			'max_rank' => 2,
			'love' => $base_before['love'],
			'max_love' => $base_before['max_love'],
			'unit_skill_level' => $new_skill_level,
			'max_hp' => $new_hp,
			'favorite_flag' => $base_before['favorite_flag'],
			'is_rank_max' => $base_idolized,
			'is_love_max' => $base_idolized ? $base_before['love'] >= $base_before['max_love'] : false,
			'is_level_max' => $base_idolized ? $new_card_level >= $base_before['max_level'] : false,
			'is_removable_skill_capacity_max' => $base_before['is_removable_skill_capacity_max'] > 0
		],
		'before_user_info' => [
			'level' => $some_tables['level'], //level
			'exp' => $some_tables['current_exp'],   //current_exp
			'next_exp' => $some_tables['next_exp'], //next_exp
			'game_coin' => $some_tables['gold'], //gold
			'sns_coin' => $some_tables['paid_loveca + free_loveca'], //paid_loveca + free_loveca
			'social_point' => $some_tables['friend_points'], //friend_point
			'unit_max' => $some_tables['max_unit'], //max_unit
			'energy_max' => $some_tables['max_lp'], //max_lp
			'friend_max' => $some_tables['max_friend'] //max_friend
		],
		'after_user_info' => [
			'level' => $some_tables['level'], //level
			'exp' => $some_tables['current_exp'],   //current_exp
			'next_exp' => $some_tables['next_exp'], //next_exp
			'game_coin' => $some_tables['gold'] - $needed_gold, //gold
			'sns_coin' => $some_tables['paid_loveca + free_loveca'], //paid_loveca + free_loveca
			'social_point' => $some_tables['friend_points'], //friend_point
			'unit_max' => $some_tables['max_unit'], //max_unit
			'energy_max' => $some_tables['max_lp'], //max_lp
			'friend_max' => $some_tables['max_friend'] //max_friend
		],
		'use_game_coin' => $needed_gold,
		'evolution_setting_id' => $practice_bonus,
		'bonus_value' => $bonus_value,
		'open_subscenario_id' => NULL,
		'get_exchange_point_list' => $seal_list
	],
	200
];
*/