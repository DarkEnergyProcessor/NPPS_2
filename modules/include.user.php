<?php
/*
 * Null Pointer Private server
 * User-related common functions
 */

/// \file include.user.php

/// Object which represents user
final class npps_user
{
	/// Changeable fields in get/set. Empty array means unchangeable.
	/// See npps_user_unit::$changeable for more information about these systems
	static protected $changeable = [
		'user_id' => [],
		'login_key' => ['is_string', 's'],
		'login_pwd' => ['is_string', 's'],
		'passcode' => [],
		'passcode_issue' => [],
		'platform_code' => [],
		'locked' => ['is_integer', 'i'],
		'tos_agree' => ['is_integer', 'i'],
		'create_date' => [],
		'first_choosen' => ['is_integer', 'i'],
		'name' => ['is_string', 's'],
		'bio' => ['is_string', 's'],
		'invite_code' => [],
		'last_active' => ['is_integer', 'i'],
		'login_count' => ['is_integer', 'i'],
		'background_id' => ['is_integer', 'i'],
		'title_id' => ['is_integer', 'i'],
		'current_exp' => [],
		'next_exp' => [],
		'level' => [],
		'gold' => ['is_integer', 'i'],
		'friend_points' => ['is_integer', 'i'],
		'paid_loveca' => ['is_integer', 'i'],
		'free_loveca' => ['is_integer', 'i'],
		'max_lp' => [],
		'max_friend' => [],
		'overflow_lp' => ['is_integer', 'i'],
		'full_lp_recharge' => [],
		'last_live_play' => ['is_integer', 'i'],
		'muse_random_live_lock' => ['is_integer', 'i'],
		'aqua_random_live_lock' => ['is_integer', 'i'],
		'max_unit' => ['is_integer', 'i'],
		'max_unit_loveca' => ['is_integer', 'i'],
		'main_deck' => ['is_integer', 'i'],
		'unit_partner' => ['is_integer', 'i'],
		'secretbox_gauge' => ['is_integer', 'i'],
		'muse_free_gacha' => ['is_integer', 'i'],
		'aqua_free_gacha' => ['is_integer', 'i'],
		'online_play_count' => ['is_integer', 'i'],
		'online_play_hi_score' => ['is_integer', 'i'],
		'normal_sticker' => ['is_integer', 'i'],
		'silver_sticker' => ['is_integer', 'i'],
		'gold_sticker' => ['is_integer', 'i'],
		'purple_sticker' => ['is_integer', 'i'],
		'tutorial_state' => ['is_integer', 'i'],
		'scenario_tracking' => [],
		'subscenario_tracking' => [],
		'friend_table' => [],
		'present_table' => [],
		'achievement_table' => [],
		'item_table' => [],
		'unit_table' => [],
		'unit_support_table' => [],
		'sis_table' => [],
		'deck_table' => [],
		'sticker_table' => [],
		'login_bonus_table' => [],
		'album_table' => []
	];
	/// Variable to track users object
	static protected $users = [];
	/// User data
	protected $user_data;
	/// Player user ID
	protected $user_id;
	
	/// \brief Creates new npps_user instance
	/// \param user_id The player user ID
	/// \exception Exception thrown if user ID doesn't exist
	protected function __construct(int $user_id)
	{
		$temp_data = npps_query("SELECT * FROM `users` WHERE user_id = $user_id");
		
		if(count($temp_data) == 0)
			throw new Exception("User ID $user_id doesn't exist");
		
		$this->user_id = $user_id;
		$this->user_data = $temp_data[0];
	}
	
	/// \brief Gets npps_user object of the specificed user ID
	/// \returns npps_user with specificed user ID
	/// \exception Exception thrown if user ID doesn't exist
	static public function get_instance(int $user_id): npps_user
	{
		if(!isset(npps_user::$users[$user_id]))
			npps_user::$users[$user_id] = new npps_user($user_id);
		
		return npps_user::$users[$user_id];
	}
	
	/// \brief Gets SIF-compilant user info
	/// \returns SIF-compilant array suitable for user/userInfo
	public function user_info()
	{
		global $UNIX_TIMESTAMP;

		$lp_time_charge = $this->full_lp_recharge - $UNIX_TIMESTAMP;
		$total_lp = intdiv($lp_time_charge, 360);

		if($lp_time_charge < 0 || $this->overflow_lp > 0)
		{
			$lp_time_charge = 0;
			$total_lp = $this->max_lp;
		}

		$total_lp += $this->overflow_lp;

		return [
			'user_id' => $this->user_id,
			'name' => $this->name,
			'level' => $this->level,
			'exp' => $this->current_exp,
			'previous_exp' => user_exp_requirement($this->level - 1),
			'next_exp' => $this->next_exp,		
			'game_coin' => $this->gold,
			'sns_coin' => $this->paid_loveca + $this->free_loveca,
			'paid_sns_coin' => $this->paid_loveca,
			'free_sns_coin' => $this->free_loveca,
			'social_point' => $this->friend_points,
			'unit_max' => $this->max_unit,
			'current_energy' => $total_lp,
			'energy_max' => $this->max_lp,
			'energy_full_time' => to_datetime($this->full_lp_recharge),
			'energy_full_need_time' => $lp_time_charge,
			'over_max_energy' => $total_lp,
			'friend_max' => $this->max_friend,
			'invite_code' => $this->invite_code,
			'tutorial_state' => $this->tutorial_state
		];
	}
	
	/// \brief Adds LP to specificed user
	/// \param amount Amount of LP to be added to player
	public function add_lp(int $amount)
	{
		global $UNIX_TIMESTAMP;
		
		$lp_amount_current = (int)floor(
			($this->full_lp_recharge - $UNIX_TIMESTAMP) / 360
		);
		
		if($lp_amount_current <= 0)
		{
			// LP is full. Add to overflow LP
			$this->overflow_lp += $amount;
			return;
		}
		
		// Check if amount is enough to full charge LP
		$amount_time = $amount * 360;
		$time_remaining = $this->full_lp_recharge - $amount_time;
		
		if($time_remaining < $UNIX_TIMESTAMP)
		{
			// There's overflow
			$overflow_amount = (int)floor(
				($UNIX_TIMESTAMP - $time_remaining) / 360
			);
			
			$this->set_protected('full_lp_recharge', $UNIX_TIMESTAMP, 'i');
			$this->overflow_lp = $overflow_amount;
		}
		
		// Decrease time
		$this->set_protected('full_lp_recharge', $time_remaining, 'i');
	}
	
	/// \brief Subtract LP of user
	/// \param amount Amount of LP to be subtracted
	public function sub_lp(int $amount)
	{
		global $UNIX_TIMESTAMP;
		
		$overflow_amount = $this->overflow_lp - $amount;
		
		if($overflow_amount < 0)
		{
			// Decrease LP time
			$time_recharge = $UNIX_TIMESTAMP + $overflow_amount * -360;
			$this->set_protected('full_lp_recharge', $time_recharge, 'i');
			$this->overflow_lp = 0;
			
			return;
		}
		
		// Simply decrease overflow LP
		$this->overflow_lp = $overflow_amount;
	}
	
	/// \brief Check if player has enough LP
	/// \param amount The amount of LP to check
	/// \returns `true` if there's enough LP with specificed amount, `false`
	///          otherwise
	public function is_enough_lp(int $amount): bool
	{
		global $UNIX_TIMESTAMP;
		
		$overflow_amount = $this->overflow_lp - $amount;
		
		if($overflow_amount >= 0)
			// enough LP
			return true;
		
		if(($this->full_lp_recharge + $amount * 360 - $UNIX_TIMESTAMP) >=
		   ($this->max_lp * 360)
		)
			// enough LP
			return true;
		
		// not enough LP
		return false;
	}
	
	/// \brief Adds exp to specificed user. Also adds LP if there's rank up.
	/// \param exp Amount of EXP to be added
	/// \returns Array with `before` and `after` key which contains these keys
	///          - exp
	///          - level
	///          - max_lp
	///          - max_friend
	public function add_exp(int $exp): array
	{
		$current_level = $this->level;
		$need_exp = user_exp_requirement_recursive($this->level);
		$now_exp = $this->current_exp + $exp;
		
		while($now_exp >= $need_exp)
		{
			$this->add_lp(25 + intdiv(++$current_level, 2));
			$need_exp += user_exp_requirement($current_level);
		}
		
		$this->set_protected('next_exp', $need_exp, 'i');
		
		$now_lp = 25 + intdiv($current_level, 2);
		$now_friend = 10 + intdiv($current_level, 5);
		$before_data = [
			'exp' => $this->current_exp,
			'level' => $this->level,
			'max_lp' => $this->max_lp,
			'max_friend' => $this->max_friend
		];
		
		return [
			'before' => $before_data,
			'after' => [
				'exp' => $this->set_protected('current_exp', $now_exp, 'i'),
				'level' => $this->set_protected('level', $current_level, 'i'),
				'max_lp' => $this->set_protected('max_lp', $now_lp, 'i'),
				'max_friend' => $this->set_protected(
									'max_friend', $now_friend, 'i'
								)
			]
		];
	}
	
	/// \brief Unlock subscenario of specificed unit ID
	/// \param unit_id The unit ID of it's subscenario to unlock
	/// \returns `subscenario_id` on success, 0 if already unlocked, -1 on fail
	public function unlock_subscenario(int $unit_id)
	{
		$ss_db = npps_get_database('subscenario');
		$ss_id = $ss_db->query("
			SELECT subscenario_id FROM `subscenario_m`
			WHERE unit_id = $unit_id"
		);
		
		if(count($ss_id) > 0)
			$ss_id = $ss_id[0]['subscenario_id'];
		else
			return -1;
		
		$ss_track = $this->user_data['subscenario_tracking'];
		$ss_data = strlen($ss_track) > 0 ? explode(',', $ss_track) : [];
		$is_unlocked = false;
		
		array_walk($ss_data,
			function($v, $k) use($ss_id, &$is_unlocked)
			{
				if($is_unlocked == false && (
					strcmp(strval($v), strval($ss_id)) == 0 ||
					strcmp(strval($v), "!$ss_id") == 0)
				)
					$is_unlocked = true;
			}
		);
		
		if($is_unlocked)
			return 0;
		
		$ss_data[] = strval($ss_id);
		$iss_data = implode(',', $ss_data);
		npps_query(
			"UPDATE `users` SET subscenario_tracking = ?
			WHERE user_id = $user_id", 's', $iss_data
		);
		$this->user_data['subscenario_tracking'] = $iss_data;
		
		return $ss_id;
	}
	
	/// PHP __get magic methods
	public function __get(string $name)
	{
		if(isset(npps_user::$changeable[$name]))
			return $this->user_data[$name];
		
		return NULL;
	}
	
	/// PHP __set magic methods
	public function __set(string $name, $val)
	{
		if(isset(npps_user::$changeable[$name]) &&
		   !empty(npps_user::$changeable[$name]) &&
		   npps_user::$changeable[$name][0]($val)
		)
		{
			// Update value in this class and database
			$this->user_data[$name] = $val;
			npps_query("
				UPDATE `users` SET $name = ?
				WHERE user_id = {$this->user_id}
			", npps_user::$changeable[$name][1], $val);
			
			return $val;
		}
		
		throw new Exception("Property $name can't be set or doesn't exist");
	}
	
	/// \brief Sets protected property/field
	/// \param name The field name
	/// \param val The value
	/// \param type The value SQL datatype char (`i` for integer for example)
	/// \returns `val`
	/// \exception Exception Thrown if specificed property doesn't exist
	protected function set_protected(string $name, $val, string $type)
	{
		if(isset(npps_user::$changeable[$name]))
		{
			// Update value in this class and database
			$this->user_data[$name] = $val;
			npps_query("
				UPDATE `users` SET $name = ?
				WHERE user_id = {$this->user_id}
			", $type, $val);
			
			return $val;
		}
		
		throw new Exception("Property $name doesn't exist");
	}
};

/// \brief Creates new user. For /login/startUp module action
/// \param key The user key
/// \param pwd The user internal password
/// \returns The User ID or 0 on failure
function user_create(string $key, string $pwd): int
{
	global $UNIX_TIMESTAMP;
	
	$unix_ts = $UNIX_TIMESTAMP;
	$user_data = [
		$key,		// login_key
		$pwd,		// login_pwd
		$unix_ts,	// create_date
		$unix_ts,	// last_active
		11,			// next_exp
		$unix_ts,	// full_lp_recharge
	];
	if(npps_query("
		INSERT INTO `users` (
			login_key,
			login_pwd,
			create_date,
			last_active,
			next_exp,
			full_lp_recharge,
			unlocked_title,
			unlocked_background,
			friend_table,
			present_table,
			achievement_table,
			item_table,
			unit_table,
			unit_support_table,
			sis_table,
			deck_table,
			sticker_table,
			login_bonus_table,
			album_table
		) VALUES
			(?, ?, ?, ?, ?, ?,
			 '', '', '', '', '', '', '', '', '', '', '', '', ''
		)
		",
		'ssiiii', $user_data))
	{
		$user_id = npps_query('SELECT LAST_INSERT_ID() as user_id');
		
		if(count($user_id) > 0)
			return $user_id[0]['user_id'];
	}
	return 0;
}

/// \brief Configure user. It supports startWithoutInvite and startSetInvite
/// \param user_id The player user ID previously created from user_create()
/// \param invite_code Custom invite code to specify
/// \returns `true` on success, `false` on failure (like `invite_code` already
///          exist)
function user_configure(int $user_id, string $invite_code = NULL): bool
{
	if($invite_code == NULL)
	{
		do
		{
			$invite_code = sprintf('%09d', random_int(0, 999999999));
		}
		while(count(npps_query(
			'SELECT user_id FROM `users` WHERE invite_code = ?',
			's',
			$invite_code)) > 0
		);
	}
	else
		if(count(npps_query('
			SELECT user_id FROM `users`
			WHERE invite_code = ?',
			's',
			$invite_code)
		) > 0)
			return false;
	
	// Create users table
	npps_begin_transaction();
	if(npps_file_query('data/initialize_user.sql',
		['user_id' => $user_id, 'invite_code' => $invite_code]
	   )
	)
	{
		// Unlock live shows!
		$status = 
			// µ's songs
			live_unlock($user_id, 1) &&
			live_unlock($user_id, 2) &&
			live_unlock($user_id, 3) &&
			live_unlock($user_id, 350) &&
			// Aqours songs
			live_unlock($user_id, 1197) &&
			live_unlock($user_id, 1190) &&
			live_unlock($user_id, 1191) &&
			live_unlock($user_id, 1192) &&
			live_unlock($user_id, 1193) &&
			live_unlock($user_id, 1194) &&
			live_unlock($user_id, 1195) &&
			live_unlock($user_id, 1196) &&
			live_unlock($user_id, 1198) &&
			live_unlock($user_id, 1199) &&
			live_unlock($user_id, 1200) &&
			live_unlock($user_id, 1201)
		;
		
		if($status)
		{
			npps_end_transaction();
			return true;
		}
	}
	npps_end_transaction();
	
	return false;
}

/// \brief Gets user ID from the specificed username, password, and token.
///        Used for verification
/// \param uid The player login key/username
/// \param pwd The player login password
/// \param tkn The current token
/// \returns `user_id` on success, 0 on fail, -`user_id` if the player account
///          is locked.
function user_id_from_credentials(string $uid, string $pwd, string $tkn): int
{
	$arr = npps_query('SELECT login_key FROM `logged_in` WHERE token = ?', 's', $tkn);
	
	if($arr && isset($arr[0]))
	{
		$user_id = npps_query('
			SELECT user_id, locked FROM `users`
			WHERE login_key = ? AND login_pwd = ?', 'ss', $uid, $pwd
		);
		
		if($user_id && isset($user_id[0]))
		{
			$uid_target = $user_id[0]['user_id'];
			
			if($user_id[0]['locked'])
				return -$uid_target;
			
			return $uid_target;
		}
	}
	
	return 0;
}

/// \brief Sets user last active time to current time
/// \param uid The player user ID
/// \deprecated Use `last_active` property in npps_user instead.
function user_set_last_active(int $uid)
{
	global $UNIX_TIMESTAMP;
	
	npps_user::get_instance($uid)->last_active = $UNIX_TIMESTAMP;
}

/// \brief Gets required EXP for the specificed rank
/// \param rank The rank to get it's required EXP
/// \returns Required EXP for next rank
function user_exp_requirement(int $rank): int
{
	if($rank <= 0) return 0;
	
	return intval(floor((21 + $rank ** 2.12) / 2 + 0.5));
}

/// \brief Gets required EXP for the specificed rank from rank 1
/// \param rank The rank to get it's required EXP
/// \returns Required EXP for next rank, starting from rank 1
function user_exp_requirement_recursive(int $rank): int
{
	if($rank <= 0) return 0;
	
	$sum = 0;
	
	for($i = 1; $i <= $rank; $i++)
		$sum += user_exp_requirement($i);
	
	return $sum;
}
