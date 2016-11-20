<?php
/* 
 * Null Pointer Private Server
 * Unit addition and removal
 */

/// \file include.unit.php

/// Singleton used to cache unit information
class npps_unit_tempinfo
{
	/// \brief Array to store unit cache information
	protected $unitlist;
	
	/// \brief Create new instance of unit cache
	protected function __construct() {
		$this->unitlist = [];
	}
	
	/// \brief Gets singleton instance of this class
	/// \returns Current npps_unit_tempinfo instance
	static public function instance(): npps_unit_tempinfo
	{
		static $x = NULL;
		
		if($x == NULL)
			$x = new npps_unit_tempinfo();
		
		return $x;
	}
	
	/// \brief Gets unit information, decrypt the row if necessary
	/// \param unit_id The unit ID to get it's information
	/// \returns see unit_database_get_info()
	public function getunit(int $unit_id)
	{
		if(isset($unitlist[$unit_id]))
			return $unitlist[$unit_id];
		
		$unit_db = npps_get_database('unit');
		$unit_info = $unit_db->query("SELECT * FROM `unit_m` WHERE unit_id = $unit_id");
		
		if(count($unit_info) == 0)
			return NULL;
		
		$unit_info = $unit_info[0];
		
		if($unit_info['release_tag'] != NULL && is_integer($unit_info['release_tag']))
		{
			// Decrypt row at first
			$decryption_id = npps_decryption_key($unit_info['release_tag']);
			$decrypted_data = json_decode(substr(openssl_decrypt($unit_info['_encryption_release_id'], 'aes-128-cbc', 0), 16));
			
			if($decrypted_data == NULL)
			{
				error_log("npps: cannot decrypt row unit_id = $unit_id", 4);
				echo "cannot decrypt row unit_id = $unit_id";
				
				return NULL;
			}
			
			foreach($decrypted_data as $k => $v)
				$unit_info[$k] = $v;
		}
		
		// Remove unnecessary information
		unset($unit_info['unit_id']);
		unset($unit_info['normal_card_id']);
		unset($unit_info['rank_max_card_id']);
		unset($unit_info['normal_icon_asset']);
		unset($unit_info['rank_max_icon_asset']);
		unset($unit_info['normal_unit_navi_asset_id']);
		unset($unit_info['rank_max_unit_navi_asset_id']);
		unset($unit_info['skill_asset_voice_id']);
		unset($unit_info['release_tag']);
		unset($unit_info['_encryption_release_id']);
		
		// Load unit level up pattern as array. It's guaranteed to be exist
		$levelup_pattern = $unit_db->query(
			'SELECT * FROM `unit_level_up_pattern_m` WHERE unit_level_up_pattern_id = ?',
			'i', $unit_info['unit_level_up_pattern_id']
		)[0];
		$new_levelup_pattern = [];	// Index is unit level, starts at 1
		$temp_exp_needed = 0;
		
		foreach($levelup_pattern as $v)
		{
			$new_levelup_pattern[$v['unit_level']] = [
				'next_exp' => $v['next_exp'],
				'need_exp' => $v['next_exp'] - $temp_exp_needed,
				'hp' => $unit_info['hp_max'] - $v['hp_diff'],
				'hp_diff' => $v['hp_diff'],
				'smile' => $unit_info['smile_max'] - $v['smile_diff'],
				'smile_diff' => $v['smile_diff'],
				'pure' => $unit_info['pure_max'] - $v['pure_diff'],
				'pure_diff' => $v['pure_diff'],
				'cool' => $unit_info['cool_max'] - $v['cool_diff'],
				'cool_diff' => $v['cool_diff'],
				'rank_up_cost' => $v['rank_up_cost'],
				'exchange_point_rank_up_cost' => $v['exchange_point_rank_up_cost']
			];
			$temp_exp_needed += $v['next_exp'];
		}
		
		$unit_info['unit_level_up_pattern'] = $new_levelup_pattern;
		
		return $this->unitlist[$unit_id] = $unit_info;
	}
};

/// \brief Class that represent unit in memberlist
class npps_user_unit
{
	/// Player user ID
	protected $user_id;
	/// Unit unique identifier for user
	protected $unit_owning_user_id;
	/// Array list of additional unit data
	protected $unit_data;
	/// Unit ID in unit database
	public $unit_id;
	
	/// \brief Creates object which links unit in user memberlist
	/// \param user_id Player user ID
	/// \param unit_owning_user_id The unit unique identifier valid for user ID
	/// \exception Exception Thrown if unit_owning_user_id is invalid.
	public function __construct(int $user_id, int $unit_owning_user_id)
	{
		
	}
};

/// \brief Get full unit information from the database
/// \param unit_id The unit ID you want to return it's data
/// \returns `NULL` if specificed unit ID is invalid or `array` from the unit database with these additions:
///          - unit_level_up_pattern = array of levelup pattern, the key is unit level
///              - need_exp = needed exp displayed in-game
///              - hp = unit hp displayed in-game
///              - smile = unit smile stats displayed in-game
///              - pure = unit pure stats displayed in-game
///              - cool = unit cool stats displayed in-game
function unit_database_get_info(int $unit_id)
{
	return npps_unit_tempinfo::instance()->getinfo($unit_id);
}

/// \brief Add unit to current player memerlist **without checking if player memberlist is full**.
/// \param user_id Player User ID
/// \param card_id The unit ID to add.
/// \returns `unit_owning_user_id` or 0 on failure.
function unit_add_direct(int $user_id, int $card_id): int
{
	global $DATABASE;
	global $UNIX_TIMESTAMP;
	
	$next_exp = NULL;
	$max_level = 1;
	$max_hp = 1;
	$max_bond = 25;
	
	{
		$unit_db = new SQLite3Database('data/unit.db_');
		
		$temp = $unit_db->execute_query("SELECT hp_max, unit_level_up_pattern_id, normal_card_id, rank_max_card_id, before_level_max, after_level_max, before_love_max, after_love_max FROM `unit_m` WHERE unit_id = $card_id")[0];
		$is_promo = $temp[2] == $temp[3];
		$next_exp = $unit_db->execute_query("SELECT next_exp, hp_diff FROM `unit_level_up_pattern_m` WHERE unit_level_up_pattern_id = {$temp[1]} LIMIT 1")[0];
		$max_level = $is_promo ? $temp[5] : $temp[4];
		$max_hp = $temp[0] - $next_exp[1];
		$max_bond = $is_promo ? $temp[7] : $temp[6];
	}
	
	$temp = npps_query("SELECT unit_table, album_table FROM `users` WHERE user_id = $user_id")[0];
	if(npps_query("INSERT INTO `{$temp[0]}` (card_id, next_exp, max_level, health_points, max_bond, added_time) VALUES(?, ?, ?, ?, ?, ?)", 'iiiiii', $card_id, $next_exp[0], $max_level, $max_hp, $max_bond, $UNIX_TIMESTAMP))
	{
		$unit_id = npps_query('SELECT LAST_INSERT_ID()')[0][0];
		$flags = 1;
		
		if($is_promo)
			$flags = 2;
		
		npps_query("INSERT OR IGNORE INTO `{$temp[1]}` VALUES (?, ?, 0)", 'ii', $card_id, $flags);
		
		return $unit_id;
	}
	else
		return 0;
}

/// \brief Removes unit in user memberlist. Also removes it in deck if necessary.
/// \param user_id Player user ID
/// \param unit_own_id The unit owning user ID
/// \returns `true` if success and removed, `false` if it's in main deck and not removed.
function unit_remove(int $user_id, int $unit_own_id): bool
{
	global $DATABASE;
	
	$info = npps_query("SELECT unit_table, deck_table, main_deck FROM `users` WHERE user_id = $user_id")[0];
	$deck_list = [];
	
	foreach(npps_query("SELECT deck_num, deck_members FROM `{$info[1]}`") as $a)
	{
		$b = explode(':', $a[1]);
		$deck_list[$a[0]] = $b;
		
		foreach($b as &$unit)
		{
			if($unit == $unit_own_id)
			{
				if($info[2] == $a[0])
					// In main deck. Cannot remove
					return false;
				else
					// Remove
					$unit = 0;
			}
		}
	}
	
	foreach($deck_list as $k => $v)
		deck_alter($user_id, $k, $v);
	
	// Last: update database
	npps_query("DELETE FROM `{$info[0]}` WHERE unit_id = $unit_own_id");
	
	return true;
}

/// \brief Add unit to current player memerlist.
///        If it's supporting members and `UNIT_SUPPORT_ALWAYSADD` is defined in config,
///        then this function **always** add member specificed.
/// \param user_id Player User ID
/// \param card_id The unit ID to add.
/// \param item_data see item_add_present_box() for more information
/// \returns unit_owning_user_id or 0 on failure (because the memberlist is full for example)
function unit_add(int $user_id, int $card_id, array $item_data = []): int
{
	global $DATABASE;
	
	$user_unit_info = npps_query("SELECT unit_table, max_unit FROM `users` WHERE user_id = $user_id")[0];
	$unit_current = npps_query("SELECT COUNT(unit_id) FROM `{$user_unit_info[0]}`")[0][0];
	
	if($unit_current >= $user_unit_info[1])
	{
		item_add_present_box($user_id, 1001, $item_data, 1, $card_id);
		return 0;
	}
	
	return card_add_direct($user_id, $card_id);
}

/// \brief Used to scout member or giving player live show reward
/// \returns SIF-compilant array for unit, randomly choosen.
function unit_random_regular(): array
{
	static $n_list = [];
	static $r_list = [];
	static $data_initialized = false;
	
	if($data_initialized == false)
	{
		$unit_db = npps_get_database('unit');
		
		foreach($unit_db->execute_query('SELECT unit_id, unit_level_up_pattern_id, hp_max, rarity, before_love_max, before_level_max FROM `unit_m` WHERE rarity < 3 AND normal_card_id <> rank_max_card_id') as $x)
		{
			$level_up_pattern = $unit_db->execute_query("SELECT next_exp, hp_diff FROM `unit_level_up_pattern_m` WHERE unit_level_up_pattern_id = {$x['unit_level_up_pattern_id']}")[0];
			
			$unit_data = [
				'unit_owning_user_id' => 0,
				'unit_id' => $x['unit_id'],
				'exp' => 0,
				'next_exp' => $level_up_pattern['next_exp'],
				'level' => 1,
				'max_level' => $x['before_level_max'],
				'rank' => 1,
				'max_rank' => 2,
				'love' => 0,
				'max_love' => $x['before_love_max'],
				'skill_level' => 1,
				'max_hp' => $x['hp_max'] - $level_up_pattern['hp_diff'],
				'is_rank_max' => false,
				'is_love_max' => false,
				'is_level_max' => false
			];
			
			switch($x['rarity'])
			{
				case 1:
				{
					$n_list[] = $unit_data;
					break;
				}
				case 2:
				{
					$r_list[] = $unit_data;
					break;
				}
				default: break;
			}
		}
		
		$data_initialized = true;
	}
	
	// 10% R, 90% N
	if(random_int(0, 100000) / 1000 - 90.0 <= 0.0)
		return $n_list[random_int(0, count($r_list) - 1)];
	else
		return $r_list[random_int(0, count($n_list) - 1)];
}
