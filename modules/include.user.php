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
		'max_lp' => ['is_integer', 'i'],
		'max_friend' => ['is_integer', 'i'],
		'overflow_lp' => ['is_integer', 'i'],
		'full_lp_recharge' => ['is_integer', 'i'],
		'max_unit' => ['is_integer', 'i'],
		'max_unit_loveca' => ['is_integer', 'i'],
		'main_deck' => ['is_integer', 'i'],
		'normal_sticker' => ['is_integer', 'i'],
		'silver_sticker' => ['is_integer', 'i'],
		'gold_sticker' => ['is_integer', 'i'],
		'purple_sticker' => ['is_integer', 'i'],
		'tutorial_state' => ['is_integer', 'i'],
		'present_table' => [],
		'achievement_table' => [],
		'item_table' => [],
		'unit_table' => [],
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
		$temp_data = npps_query("
			SELECT * FROM `users`
			WHERE user_id = $user_id");
		
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
			'previous_exp' => $this->current_exp - user_exp_requirement($this->level),
			'next_exp' => $this->next_exp,		
			'game_coin' => $this->gold,
			'sns_coin' => $this->paid_loveca + $this->free_loveca,
			'paid_sns_coin' => $this->paid_loveca,
			'free_sns_coin' => $this->free_loveca,
			'social_point' => $this->friend_points,
			'unit_max' => $this->max_unit,
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
	public function add_lp() {
		global $UNIX_TIMESTAMP;
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
			friend_list,
			present_table,
			achievement_table,
			item_table,
			unit_table,
			deck_table,
			sticker_table,
			login_bonus_table,
			album_table
		) VALUES (?, ?, ?, ?, ?, ?, '', '', '', '', '', '', '', '', '', '', '')
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
		if(count(npps_query('SELECT user_id FROM `users` WHERE invite_code = ?', 's', $invite_code)) > 0)
			return false;
	
	// Create users table
	if(npps_file_query('data/initialize_user.sql',
		['user_id' => $user_id, 'invite_code' => $invite_code]
	   )
	)
		if(
			/* Add Bokura no LIVE Kimi to no LIVE (easy, normal, hard) */
			live_unlock($user_id, 1) &&	// easy
			live_unlock($user_id, 2) &&	// normal
			live_unlock($user_id, 3)	// hard
		)
			return true;
	return false;
}

/* Returns User ID or 0 if fail. Negative value means the user is banned */
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
function user_set_last_active(int $uid)
{
	global $UNIX_TIMESTAMP;
	
	npps_unit::get_instance($uid)->last_active = $UNIX_TIMESTAMP;
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

function user_sub_lp(int $user_id, int $amount)
{
	$lp_info = npps_query("SELECT full_lp_recharge, overflow_lp FROM `users` WHERE user_id = $user_id")[0];
	$overflow_amount = $lp_info[1] - $amount;
	
	if($overflow_amount < 0)
	{
		/* Decrease full_lp_recharge too */
		$time_recharge = $UNIX_TIMESTAMP + ($overflow_amount * (-1)) * 360;
		npps_query("UPDATE `users` SET full_lp_recharge = $time_recharge, overflow_lp = 0 WHERE user_id = $user_id");
		
		return;
	}
	
	/* Simply decrease the overflow LP */
	npps_query("UPDATE `users` SET overflow_lp = $overflow_amount WHERE user_id = $user_id");
}

function user_is_enough_lp(int $user_id, int $amount): bool
{
	global $UNIX_TIMESTAMP;
	
	$lp_info = npps_query("SELECT full_lp_recharge, overflow_lp, max_lp FROM `users` WHERE user_id = $user_id")[0];
	$overflow_amount = $lp_info[1] - $amount;
	
	if($overflow_amount >= 0)
		/* Enough LP */
		return true;
	
	if(($lp_info[0] + $amount * 360 - $UNIX_TIMESTAMP) >= ($lp_info[2] * 360))
		/* Enough LP */
		return true;
	
	/* Not enough LP */
	return false;
}

/* increase user experience and returns these infos (before and after) */
/* - exp
   - level
   - max_lp
   - max_friend
*/
function user_add_exp(int $user_id, int $exp): array
{
	$before_rank_up = npps_query("SELECT level, current_exp, max_lp, max_friend FROM `users` WHERE user_id = $user_id")[0];
	$current_level = $before_rank_up[0];
	$need_exp = user_exp_requirement_recursive($before_rank_up[0]);
	$now_exp = $before_rank_up[1] + $exp;
	
	while($now_exp >= $need_exp)
	{
		user_add_lp($user_id, 25 + intdiv(++$current_level, 2));
		$need_exp += user_exp_requirement($current_level);
	}
	
	$now_lp = 25 + intdiv($current_level, 2);
	$now_friend = 10 + intdiv($current_level, 5);
	
	npps_query("UPDATE `users` SET level = $current_level, current_exp = $now_exp, next_exp = $need_exp, max_lp = $now_lp, max_friend = $now_friend WHERE user_id = $user_id");
	
	return [
		'before' => [
			'exp' => $before_rank_up[0],
			'level' => $before_rank_up[1],
			'max_lp' => $before_rank_up[2],
			'max_friend' => $before_rank_up[3]
		],
		'after' => [
			'exp' => $now_exp,
			'level' => $current_level,
			'max_lp' => $now_lp,
			'max_friend' => $now_friend
		]
	];
}

/* Is player can do free gacha? */
function user_is_free_gacha(int $user_id): bool
{
	global $UNIX_TIMESTAMP;
	
	$temp = npps_query("SELECT next_free_gacha FROM `free_gacha_tracking` WHERE user_id = $user_id");
	
	return count($temp) == 0 ? true : $temp[0][0] >= $UNIX_TIMESTAMP;
}

/* Set "free gacha" flag to false */
function user_disable_free_gacha(int $user_id)
{
	global $DATABASE;
	global $UNIX_TIMESTAMP;
	
	npps_query('INSERT OR IGNORE INTO `free_gacha_tracking` VALUES (?, ?)', 'ii', $user_id, $UNIX_TIMESTAMP - ($UNIX_TIMESTAMP % 86400) + 86400);
}

function user_get_free_gacha_timestamp(int $user_id): int
{
	$temp = npps_query("SELECT next_free_gacha FROM `free_gacha_tracking` WHERE user_id = $user_id");
	
	return count($temp) == 0 ? 0 : $temp[0][0];
}

function user_get_gauge(int $user_id, bool $unmul = false): int
{
	$temp = npps_query("SELECT gauge FROM `secretbox_gauge` WHERE user_id = $user_id");
	
	return count($temp) == 0 ? 0 : ($unmul ? $temp[0][0] : $temp[0][0] * 10);
}

/* returns cycle how many times it already beyond 100 */
function user_increase_gauge(int $user_id, int $amount = 1): int
{
	$temp = user_get_gauge($user_id, true) + $amount;
	$cycle = 0;
	
	for(; $temp >= 10; $temp -= 10)
		$cycle++;
	
	npps_query('REPLACE INTO `secretbox_gauge` VALUES(?, ?)', 'ii', $user_id, $temp);
	return $cycle;
}

/* returns true if success, false if not enough loveca */
function user_sub_loveca(int $user_id, int $amount): bool
{
	$loveca = npps_query("SELECT paid_loveca, free_loveca FROM `users` WHERE user_id = $user_id")[0];
	
	if($loveca['paid_loveca'] >= $amount)
		$loveca['paid_loveca'] -= $amount;
	else
		if($loveca['free_loveca'] + $loveca['paid_loveca'] >= $amount)
		{
			$loveca['free_loveca'] -= $amount - $loveca['paid_loveca'];
			$loveca['paid_loveca'] = 0;
		}
		else
			return false;
	
	return !!npps_query('UPDATE `users` SET paid_loveca = ?, free_loveca = ? WHERE user_id = ?', 'iii', $loveca['paid_loveca'], $loveca['free_loveca'], $user_id);
}

/* returns the subscenario id, 0 if already unlocked, -1 on fail */
function user_subscenario_unlock(int $user_id, int $unit_id): int
{
	$subscenario_db = npps_get_database('subscenario');
	$subscenario_tracking = npps_query("SELECT subscenario_tracking FROM `users` WHERE user_id = $user_id")[0]['subscenario_tracking'];
	$subscenario_data = strlen($subscenario_tracking) > 0 ? explode(',', $subscenario_tracking) : [];
	$subscenario_id = $subscenario_db->execute_query("SELECT subscenario_id FROM `subscenario_m` WHERE unit_id = $unit_id");
	
	if(count($subscenario_id) > 0)
		$subscenario_id = $subscenario_id[0]['subscenario_id'];
	else
		return -1;
	
	$is_unlocked = false;
	array_walk($subscenario_data, function($v, $k) use($subscenario_id, &$is_unlocked)
		{
			if($is_unlocked == false && (strcmp(strval($v), strval($subscenario_id)) == 0 || strcmp(strval($v), "!$subscenario_id") == 0))
				$is_unlocked = true;
		}
	);
	
	if($is_unlocked)
		return 0;
	
	$subscenario_data[] = strval($subscenario_id);
	npps_query("UPDATE `users` SET subscenario_tracking = ? WHERE user_id = $user_id",
		's', implode(',', $subscenario_data));
	
	return $subscenario_id;
}
