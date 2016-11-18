<?php
/*
 * Null Pointer Private Server
 * Item-related functions
 */

/// \file include.item.php

/// \brief Translates item datas to SIF-compilant array
/// \param add_type Item add_type value
/// \param amount Item amount
/// \param item_id Item ID
/// \returns NULL if can't translate to array, or array with these keys
///          - item_id
///          - add_type
///          - amount
///          - item_category_id
function item_translate_to_array(int $add_type, int $amount = 1, int $item_id = NULL)
{
	switch($add_type)
	{
		case 1000:
		case 1001:
		{
			if($info_id === NULL)
				return NULL;
			
			return [
				'item_id' => $item_id,
				'add_type' => $add_type,
				'amount' => $amount,
				'item_category_id' => 0
			];
		}
		case 3000:
		{
			return [
				'item_id' => 3,
				'add_type' => $add_type,
				'amount' => $amount,
				'item_category_id' => 3
			];
		}
		case 3001:
		{
			return [
				'item_id' => 4,
				'add_type' => $add_type,
				'amount' => $amount,
				'item_category_id' => 4
			];
		}
		case 3002:
		{
			return [
				'item_id' => 2,
				'add_type' => $add_type,
				'amount' => $amount,
				'item_category_id' => 2
			];
		}
		case 5100:
		case 5200:
		{
			if($info_id === NULL)
				return NULL;
			
			return [
				'item_id' => $item_id,
				'add_type' => $add_type,
				'amount' => 1,
				'item_category_id' => 0
			];
		}
		default:
		{
			return NULL;
		}
	}
}

/// \brief Add items to present box.
/// \param user_id Player user ID
/// \param add_type Item add_type
/// \param item_data array with these keys
///                  - message - Present message. Defaults to "Present Box Item" (optional field)
///                  - expire - unix timestamp when the item is expired or NULL (optional field; default to NULL)
/// \param amount Item amount
/// \param item_id Additional item ID. Depends on add_type
/// \returns Item incentive ID
function item_add_present_box(int $user_id, int $add_type, array $item_data = [], int $amount = 1, int $item_id = NULL): int
{
	global $DATABASE;
	
	$present_table = npps_query("SELECT present_table FROM `users` WHERE user_id = $user_id")[0][0];
	$data = [
		$add_type,
		$item_id,
		$amount,
		$item_data['message'] ?? 'Present Box Item',
		$item_data['expire'] ?? NULL
	];
	
	npps_query("INSERT INTO `$present_table` (item_type, card_num, amount, message, expire) VALUES (?, ?, ?, ?, ?)", 'iiisi', $data);
	return npps_query('SELECT LAST_INSERT_ID()')[0][0];
}

function item_default_expiration(int $item_id, int $info_id = NULL)
{
	global $UNIX_TIMESTAMP;
	
	switch($item_id)
	{
		case 3000:
		case 3002:
		{
			return $UNIX_TIMESTAMP + 5184000;	// 60 days
		}
		default:
		{
			return NULL;
		}
	}
}

/* collect item, like gold, loveca, and more. returns false on fail */
function item_collect(int $user_id, int $add_type, int $item_additional_id, int $amount = 1)
{
	global $DATABASE;
	
	$unit_table_max = npps_query("SELECT unit_table, max_unit FROM `users` WHERE user_id = $user_id")[0];
	$unit_table = $unit_table_max['unit_table'];
	$max_unit = $unit_table_max['max_unit'];

	switch($add_type)
	{
		case 1000:
		{
			// item
			switch($item_additional_id)
			{
				case 1:
				{
					// scouting ticket
					npps_query("UPDATE `users` SET scouting_ticket = scouting_ticket + $amount WHERE user_id = $user_id");
					return true;
				}
				case 2:
				{
					goto collect_friend_points;
				}
				case 3:
				{
					goto collect_gold;
				}
				case 4:
				{
					goto collect_loveca;
				}
				case 5:
				{
					// scouting coupon
					npps_query("UPDATE `users` SET scouting_coupon = scouting_coupon + $amount WHERE user_id = $user_id");
					return true;
				}
				default: return false;
			}
		}
		case 1001:
		{
			// club members
			$current_members = npps_query("SELECT COUNT(unit_id) FROM `$unit_table`")[0][0];
			$added_members = [];
			
			if($current_members + $amount > $max_unit)
				return false;
			
			npps_begin_transaction();
			
			for($i = 0; $i < $amount; $i++)
				$added_members[] = card_add_direct($user_id, $item_additional_id);
			
			npps_end_transaction();
			
			return $added_members;
		}
		case 3000:
		{
			collect_gold:
			npps_query("UPDATE `users` SET gold = gold + $amount WHERE user_id = $user_id");
			return true;
		}
		case 3001:
		{
			collect_loveca:
			npps_query("UPDATE `users` SET free_loveca = free_loveca + $amount WHERE user_id = $user_id");
			return true;
		}
		case 3002:
		{
			collect_friend_points:
			npps_query("UPDATE `users` SET friend_point = friend_point + $amount WHERE user_id = $user_id");
			return true;
		}
		// TODO: add more
		default:
		{
			// not implememted or invalid
			return  false;
		}
	}
}
