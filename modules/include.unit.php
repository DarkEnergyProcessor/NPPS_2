<?php
/* 
 * Null Pointer Private Server
 * Unit addition and removal
 */

/// \file include.unit.php

/// Singleton used to cache unit information
final class npps_unit_tempinfo
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
		$unit_info = $unit_db->query(
			"SELECT * FROM `unit_m` WHERE unit_id = $unit_id"
		);
		
		if(count($unit_info) == 0)
			return NULL;
		
		$unit_info = $unit_info[0];
		
		if($unit_info['release_tag'] != NULL &&
		   is_integer($unit_info['release_tag'])
		)
		{
			// Decrypt row at first
			$decryption_id = npps_decryption_key($unit_info['release_tag']);
			$decrypted_data = json_decode(substr(
				openssl_decrypt(
					$unit_info['_encryption_release_id'], 'aes-128-cbc', 0
				), 16)
			);
			
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
			'SELECT * FROM `unit_level_up_pattern_m` WHERE
				unit_level_up_pattern_id = ?',
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
	/// Available fields. Key is the row name in SQL, value is the datatype.
	/// Value is array with format [callback compare, sql datatype].
	/// If value is empty array, then it's assumed to be read-only.
	protected static $changeable = [
		'unit_id' => [],
		'exp' => [],
		'next_exp' => [],
		'level' => [],
		'max_level' => [],
		'rank' => ['is_integer', 'i'],
		'max_rank' => [],
		'display_rank' => ['is_integer', 'i'],
		'unit_skill_level' => ['is_integer', 'i'],
		'unit_skill_exp' => ['is_integer', 'i'],
		'max_hp' => [],
		'love' => ['is_integer', 'i'],
		'max_love' => [],
		'unit_removable_skill_capacity' => ['is_integer', 'i'],
		'is_rank_max' => [],
		'is_love_max' => [],
		'is_level_max' => [],
		'is_skill_level_max' => [],
		'is_removable_skill_capacity_max' => [],
		'favorite_flag' => ['is_integer', 'i'],
		'insert_date' => []
	];
	/// Table name of the player unit table
	protected $unit_table;
	/// Unit unique identifier for user
	protected $unit_owning_user_id;
	/// Array list of additional unit data
	protected $unit_data;
	
	/// \brief Creates object which links unit in user memberlist
	/// \param user_id Player user ID
	/// \param unit_owning_user_id The unit unique identifier valid for user ID
	/// \exception Exception Thrown if unit_owning_user_id is invalid.
	public function __construct(int $user_id, int $unit_owning_user_id)
	{
		$this->unit_table = npps_query("
			SELECT unit_table FROM `users`
			WHERE user_id = $user_id
		")[0];
		
		if(strlen($this->unit_table) == 0)
			throw Exception("User ID $user_id is not fully initialized");
		
		$temp = npps_query("
			SELECT * FROM `{$this->unit_table}`
			WHERE unit_owning_user_id = $unit_owning_user_id
		");
			
		if(count($temp) == 0)
			throw Exception("unit_owning_user_id $unit_owning_user_id ".
				"in user ID $user_id does not exist");
		
		$this->unit_data = $temp[0];
		$this->unit_owning_user_id = $unit_owning_user_id;
	}
	
	/// PHP __get magic methods
	public function __get(string $name)
	{
		if(isset(npps_user_unit::$changeable[$name]))
			return $this->unit_data[$name];
		
		return NULL;
	}
	
	/// PHP __set magic methods
	public function __set(string $name, $val)
	{
		if(isset(npps_user_unit::$changeable[$name]) &&
		   !empty(npps_user_unit::$changeable[$name]) &&
		   npps_user_unit::$changeable[$name][0]($val)
		)
		{
			// Update value in this class and database
			$this->unit_data[$name] = $val;
			npps_query("
				UPDATE `{$this->unit_table}` SET $name = ?
				WHERE unit_owning_user_id = {$this->unit_owning_user_id}
			", npps_user_unit::$changeable[$name][1], $val);
			
			return $val;
		}
		
		throw Exception("Property $name can't be set or doesn't exist");
	}
};

/// \brief Get full unit information from the database
/// \param unit_id The unit ID you want to return it's data
/// \returns `NULL` if specificed unit ID is invalid or `array` from the unit
///          database with these additions:
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

/// \brief Add unit to current player memerlist **without checking if player
///        memberlist is full**.
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
		$unit_db = new SQLite3DatabaseWrapper('data/unit.db_');
		
		$temp = $unit_db->query("SELECT hp_max, unit_level_up_pattern_id, normal_card_id, rank_max_card_id, before_level_max, after_level_max, before_love_max, after_love_max FROM `unit_m` WHERE unit_id = $card_id")[0];
		$is_promo = $temp["normal_card_id"] == $temp["rank_max_card_id"];
		$next_exp = $unit_db->query("SELECT next_exp, hp_diff FROM `unit_level_up_pattern_m` WHERE unit_level_up_pattern_id = {$temp["unit_level_up_pattern_id"]} LIMIT 1")[0];
		$max_level = $is_promo ? $temp["after_level_max"] : $temp["before_level_max"];
		$max_hp = $temp["hp_max"] - $next_exp["hp_diff"];
		$max_bond = $is_promo ? $temp["after_love_max"] : $temp["before_love_max"];
		//$sis_max = TODO; FOR NOW IS 8
	}
	
	$temp = npps_query("SELECT unit_table, album_table FROM `users` WHERE user_id = $user_id")[0];
	if(npps_query("INSERT INTO `{$temp["unit_table"]}` (unit_id, next_exp, max_level, max_hp, max_love, insert_date, unit_removable_skill_capacity) VALUES(?, ?, ?, ?, ?, ?, ?)", 'iiiiiii', $card_id, $next_exp["next_exp"], $max_level, $max_hp, $max_bond, $UNIX_TIMESTAMP, 8))
	{
		$unit_id = npps_query('SELECT LAST_INSERT_ID()')[0]['last_insert_rowid()'];
		$flags = 1;
		
		if($is_promo)
			$flags = 2;
		
		npps_query("INSERT OR IGNORE INTO `{$temp["album_table"]}` VALUES (?, ?, 0)", 'ii', $card_id, $flags);
		
		return $unit_id;
	}
	else
		return 0;
}

/// \brief Removes unit in user memberlist. Also removes it in deck if necessary.
/// \param user_id Player user ID
/// \param unit_own_id The unit owning user ID
/// \returns `true` if success and removed, `false` if it's in main deck and not
///          removed.
function unit_remove(int $user_id, int $unit_own_id): bool
{
	global $DATABASE;
	
	$info = npps_query(
		"SELECT unit_table, deck_table, main_deck FROM `users` WHERE
			user_id = $user_id"
	)[0];
	$deck_list = [];
	
	foreach(npps_query("SELECT deck_num, deck_members FROM `{$info['deck_table']}`") as $a)
	{
		$b = explode(':', $a['deck_members']);
		$deck_list[$a['deck_num']] = $b;
		
		foreach($b as &$unit)
		{
			if($unit == $unit_own_id)
			{
				if($info['main_deck'] == $a['deck_num'])
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
	npps_query("DELETE FROM `{$info['unit_table']}` WHERE unit_id = $unit_own_id");
	
	return true;
}

/// \brief Add unit to current player memerlist.
///        If it's supporting members and `VERSION_4_SERVER` is defined in
///        config, then this function **always** add member specificed.
/// \param user_id Player User ID
/// \param card_id The unit ID to add.
/// \param item_data see item_add_present_box() for more information
/// \returns unit_owning_user_id or 0 on failure (because the memberlist is full for example)
function unit_add(int $user_id, int $card_id, array $item_data = []): int
{
	$user_unit_info = npps_query("SELECT unit_table, max_unit FROM `users` WHERE user_id = $user_id")[0];
	$unit_current = npps_query("SELECT COUNT(unit_id) FROM `{$user_unit_info[0]}`")[0][0];
	
	if($unit_current >= $user_unit_info[1])
	{
		item_add_present_box($user_id, 1001, $item_data, 1, $card_id);
		return 0;
	}
	
	return unit_add_direct($user_id, $card_id);
}

/// \brief Used to scout member or giving player live show reward
/// \param level The unit level to be given. Defaults to 1
/// \returns SIF-compilant array for unit, randomly choosen.
function unit_random_regular(int $level = 1): array
{
	static $n_list = [];
	static $r_list = [];
	static $data_initialized = false;

	if($data_initialized == false)
	{
		$unit_db = npps_get_database('unit');
		
		foreach($unit_db->query('
			SELECT unit_id, rarity FROM `unit_m` WHERE
			rarity < 3 AND normal_card_id <> rank_max_card_id
		') as $x)
		{
			switch($x['rarity'])
			{
				case 1:
					$n_list[] = $x['unit_id'];
					break;
				case 2:
					$r_list[] = $x['unit_id'];
					break;
				default:
					break;
			}
		}
	}
	
	// 10% R, 90% N
	$result = NULL;
	if(random_int(0, 100000) / 1000 - 90.0 <= 0.0)
		$result = $n_list[random_int(0, count($r_list) - 1)];
	else
		$result = $r_list[random_int(0, count($n_list) - 1)];
	
	$unit_data = unit_database_get_info($result);
	$unit_pattern = $unit_data['unit_level_up_pattern'];
	$sif_compilant = [
		'unit_owning_user_id' => 0,
		'unit_id' => $result,
		'exp' => $unit_pattern[$level - 1]['next_exp'] ?? 0,
		'next_exp' => $unit_pattern[$level]['next_exp'],
		'level' => $level,
		'max_level' => $x['before_level_max'],
		'rank' => 1,
		'max_rank' => 2,
		'love' => 0,
		'max_love' => $x['before_love_max'],
		'skill_level' => 1,
		'max_hp' => $unit_data['hp_max'] - $unit_pattern[$level]['hp_diff'],
		'is_rank_max' => false,
		'is_love_max' => false,
		'is_level_max' => false
	];
	
	return $sif_compilant;
}
