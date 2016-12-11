<?php
/*
 * Null Pointer Private Server
 * Live shows!
 */

/// \file include.live.php

/// \brief Class which represent live setting. Property values in here not meant
///        to be changed, also changing it gives no effect to the database.
class npps_live_setting
{
	/// The live_setting_id of this object
	public $live_setting_id;
	/// The live track name
	public $live_track_name;
	/// The live difficulty level
	public $difficulty;
	/// The live star level
	public $stage_level;
	/// The live attribute
	public $attribute;
	/// The live notes speed, in seconds.
	public $notes_speed;
	/// Score needed for S score
	public $s_score;
	/// Score needed for A score
	public $a_score;
	/// Score needed for B score
	public $b_score;
	/// Score needed for C score
	public $c_score;
	/// Combo needed for S combo
	public $s_combo;
	/// Combo needed for A combo
	public $a_combo;
	/// Combo needed for B combo
	public $b_combo;
	/// Combo needed for C combo
	public $c_combo;
	/// Lists of linked `live_difficulty_id` to this live setting
	protected $link_difficulty_id = [];
	/// Contain list of created npps_live_setting object
	static protected $list = [];
	
	/// \brief Create new npps_live_setting class
	/// \param live_setting_id The `live_setting_id` to create it's object
	/// \exception Exception Thrown if specificed `live_setting_id` doesn't
	///            exist
	protected function __construct(int $live_setting_id)
	{
		// Get live setting data
		$live_db = npps_get_database('live');
		$ls_data = $live_db->query("
			SELECT
				live_track_id, difficulty, stage_level, attribute_icon_id,
				notes_speed, c_rank_score, b_rank_score, a_rank_score,
				s_rank_score, c_rank_combo, b_rank_combo, a_rank_combo,
				s_rank_combo
			FROM `live_setting_m` WHERE
				live_setting_id = $live_setting_id
		");
		
		if(count($ls_data) == 0)
			throw new Exception("live_setting_id $live_setting_id not exist");
		
		$ls_data = $ls_data[0];
		
		// Set property
		$this->live_setting_id = $live_setting_id;
		$this->live_track_name = $live_db->query("
			SELECT name FROM `live_track_m`
			WHERE live_track_id = {$ls_data['live_track_id']}
		")[0]['name'];
		$this->difficulty = $ls_data['difficulty'];
		$this->stage_level = $ls_data['stage_level'];
		$this->attribute = $ls_data['attribute_icon_id'];
		$this->notes_speed = $ls_data['notes_speed'];
		$this->s_score = $ls_data['s_rank_score'];
		$this->a_score = $ls_data['a_rank_score'];
		$this->b_score = $ls_data['b_rank_score'];
		$this->c_score = $ls_data['c_rank_score'];
		$this->s_score = $ls_data['s_rank_combo'];
		$this->a_score = $ls_data['a_rank_combo'];
		$this->b_score = $ls_data['b_rank_combo'];
		$this->c_score = $ls_data['c_rank_combo'];
	}
	
	/// \brief Gets instance of npps_live_setting based from the specificed
	///        `live_setting_id`. If no npps_live_setting object is created
	///        with specificed `live_setting_id`, it will be created and cached.
	/// \param live_setting_id The `live_setting_id` to get it's instance
	/// \exception Exception Thrown if specificed `live_setting_id` doesn't
	///            exist
	static public function get_instance(int $live_setting_id): npps_live_setting
	{
		if(isset(npps_live_setting::$list[$live_setting_id]))
			return npps_live_setting::$list[$live_setting_id];
		
		return npps_live_setting::$list[$live_setting_id] = 
			new npps_live_setting($live_setting_id);
	}
	
	/// \brief Gets list of npps_live_difficulty object associated with this
	///        live setting object.
	/// \returns array containing list of npps_live_difficulty object
	///          associated with this live setting object.
	public function get_linked_live_difficulty(): array
	{
		$idlist = [];
		$live_db = npps_get_database('live');
		
		return $idlist;
	}
	
	/// \brief Check if notes data exist for current live setting object.
	/// \returns `true` if notes data exist, `false` otherwise
	public function notes_data_exist(): bool
	{
		$note_name = sprintf('Live_s%04d', $this->live_setting_id);

		if(file_exists("data/notes/$note_name.note"))
			return true;

		$db = npps_get_database('notes/notes');

		if(count($db->query("
			SELECT rootpage FROM `sqlite_master`
			WHERE type = 'table' AND name = '$note_name'
		")) > 0)
			return true;

		return false;
	}
	
	/// \brief Gets the notes data of current live setting object.
	/// \returns SIF-compilant array containing the beatmap data or `NULL` on
	///          failure.
	public function load_notes_data()
	{
		$note_name = sprintf('Live_s%04d', $this->live_setting_id);
		$filename = "data/notes/$note_name.note";
		$notes_data = [];

		if(file_exists($filename) == false)
		{
			if(file_exists('data/notes/notes.db_'))
			{
				$db = npps_get_database('notes/notes');

				if(count($db->query("
					SELECT rootpage FROM `sqlite_master`
					WHERE type = 'table' AND name = '$note_name'
				")) > 0)
				{
					foreach($db->query("
						SELECT * FROM `$note_name` ORDER BY timing_sec
					") as $note)
						$notes_data[] = [
							'timing_sec' => $note['timing_sec'],
							'notes_attribute' => $note['notes_attribute'],
							'notes_level' => 1,
							'effect' => $note['effect'],
							'effect_value' => $note['effect_value'] ?? 2,
							'position' => $note['position']
						];
				}
				else
					return NULL;
			}
			else
				return NULL;
		}
		else
		{
			$db = new SQLite3DatabaseWrapper($filename);

			foreach($db->query("
				SELECT * FROM `notes_list` ORDER BY timing_sec
			") as $note)
				$notes_data[] = [
					'timing_sec' => $note['timing_sec'],
					'notes_attribute' => $note['notes_attribute'],
					'notes_level' => 1,
					'effect' => $note['effect'],
					'effect_value' => $note['effect_value'] ?? 2,
					'position' => $note['position']
				];
		}

		return $notes_data;
	}
}

/// \brief Class which represent live difficulty. Property values in here not
///        meant to be changed, and it doesn't give any effect to the database.
class npps_live_difficulty
{
	/// The live difficulty id
	public $live_difficulty_id;
	/// The linked npps_live_setting object
	public $live_setting;
	/// \brief Energy type needed to play this song (LP/Token). 0 if cannot be
	///        determined.
	public $capital_type;
	/// \brief Energy value needed to play this song (LP/Token). 0 if cannot be
	///        determined.
	public $capital_value;
	/// Value to indicate if this song is available in hits section
	public $normal_live;
	/// Value to indicate if it's x4 live show (4x EXP, 4x Points)
	public $special_setting;
	/// Value to indicate that the notes data should be randomized
	public $random_flag;
	/// The song star level
	public $stage_level;
	/// Amount of this live show needed to S clear. 0 if cannot be determined.
	public $s_times;
	/// Amount of this live show needed to A clear. 0 if cannot be determined.
	public $a_times;
	/// Amount of this live show needed to B clear. 0 if cannot be determined.
	public $b_times;
	/// Amount of this live show needed to C clear. 0 if cannot be determined.
	public $c_times;
	/// Score needed for S score
	public $s_score;
	/// Score needed for A score
	public $a_score;
	/// Score needed for B score
	public $b_score;
	/// Score needed for C score
	public $c_score;
	/// Contains live goal reward data for current live difficulty
	protected $goal_reward;
	/// List of created npps_live_difficulty object
	static protected $list = [];
	
	/// \brief Dummy constructor, but protected
	protected function __construct() {}
	
	/// \brief Get live goal rewards data for specificed live difficulty id
	/// \param live_difficulty_id The live difficulty id to get it's goal reward
	///        data
	/// \returns live goal reward data or `NULL` on failure. The live goal
	///          reward data is sorted by score, combo, and times, then sorted
	///          by S, A, B, and C rank.
	static protected function get_goal_reward_list(int $live_difficulty_id)
	{
		$live_db = npps_get_database('live');
		
		$data = $live_db->query("
			SELECT * FROM `live_goal_reward_m`
			WHERE
				live_difficulty_id = $live_difficulty_id
			ORDER BY
				live_goal_type ASC,
				rank DESC
		");
		
		if(count($data) == 0)
			return NULL;
		else
			return $data;
	}
	
	/// \brief Search for live difficulty data in `normal_live_m` table and 
	///        `special_live_m` table.
	/// \param live_difficulty_id The live difficulty id to search it's data
	/// \returns The npps_live_difficulty object or `NULL` on failure
	static protected function get_instance_live(int $live_difficulty_id)
	{
		static $table = ['normal_live_m', 'special_live_m'];
		$live_db = npps_get_database('live');
		
		foreach($table as $x)
		{
			$live = $live_db->query("
				SELECT
					live_setting_id, capital_type, capital_value,
					special_setting, c_rank_complete, b_rank_complete,
					a_rank_complete, s_rank_complete
				FROM `$x` WHERE live_difficulty_id = $live_difficulty_id
			");
			
			if(count($live) > 0)
			{
				$live = $live[0];
				$obj = new npps_live_difficulty();
				$setting = npps_live_setting::get_instance(
					$live['live_setting_id']
				);
				
				npps_live_difficulty::$list[$live_difficulty_id] = $obj;
				$obj->live_difficulty_id = $live_difficulty_id;
				$obj->live_setting = $setting;
				$obj->capital_type = $live['capital_type'];
				$obj->capital_value = $live['capital_value'];
				$obj->special_setting = $live['special_setting'] == 1;
				$obj->normal_live = strcmp($x, 'normal_live_m') == 0;
				$obj->random_flag = false;
				$obj->stage_level = $setting->stage_level;
				$obj->s_times = $live['s_rank_complete'];
				$obj->a_times = $live['a_rank_complete'];
				$obj->b_times = $live['b_rank_complete'];
				$obj->c_times = $live['c_rank_complete'];
				$obj->s_score = $setting->s_score;
				$obj->a_score = $setting->a_score;
				$obj->b_score = $setting->b_score;
				$obj->c_score = $setting->c_score;
				$obj->goal_reward = npps_live_difficulty::get_goal_reward_list(
					$live_difficulty_id
				);
				
				return $obj;
			}
		}
		
		return NULL;
	}
	
	/// \brief Search for live difficulty data in `event_marathon_live_m` table.
	/// \param live_difficulty_id The live difficulty id to search it's data
	/// \returns The npps_live_difficulty object or `NULL` on failure
	static protected function get_instance_marathon(int $live_difficulty_id)
	{
		$marathon_db = npps_get_database('event/marathon');
		$live = $marathon_db->query("
			SELECT
				live_setting_id, capital_type, capital_value, stage_level
				special_setting, random_flag, c_rank_complete, b_rank_complete,
				a_rank_complete, s_rank_complete
			FROM `event_marathon_live_m` WHERE
			live_difficulty_id = $live_difficulty_id
		");
		
		if(count($live) == 0)
			return NULL;
		
		$live = $live[0];
		
		$obj = new npps_live_difficulty();
		$setting = npps_live_setting::get_instance($live['live_setting_id']);
		$obj->live_difficulty_id = $live_difficulty_id;
		$obj->live_setting = $setting;
		$obj->capital_type = $live['capital_type'];
		$obj->capital_value = $live['capital_value'];
		$obj->special_setting = $live['special_setting'] > 0;
		$obj->normal_live = false;
		$obj->random_flag = $live['random_flag'] > 0;
		$obj->stage_level = $live['stage_level'] ?? $setting->stage_level;
		$obj->s_times = $live['s_rank_complete'];
		$obj->a_times = $live['a_rank_complete'];
		$obj->b_times = $live['b_rank_complete'];
		$obj->c_times = $live['c_rank_complete'];
		$obj->s_score = $setting->s_score;
		$obj->a_score = $setting->a_score;
		$obj->b_score = $setting->b_score;
		$obj->c_score = $setting->c_score;
		$obj->goal_reward = npps_live_difficulty::get_goal_reward_list(
			$live_difficulty_id
		);
		
		return $obj;
	}
	
	/// \brief Search for live difficulty data in `event_battle_live_m` table.
	/// \param live_difficulty_id The live difficulty id to search it's data
	/// \returns The npps_live_difficulty object or `NULL` on failure
	static protected function get_instance_battle(int $live_difficulty_id)
	{
		$battle_db = npps_get_database('event/battle');
		$live = $battle_db->query("
			SELECT
				live_setting_id, stage_level random_flag, c_rank_score,
				b_rank_score, a_rank_score, s_rank_score
			FROM `event_battle_live_m` WHERE
			live_difficulty_id = $live_difficulty_id
		");
		
		if(count($live) == 0)
			return NULL;
		
		$live = $live[0];
		
		$obj = new npps_live_difficulty();
		$setting = npps_live_setting::get_instance($live['live_setting_id']);
		$obj->live_difficulty_id = $live_difficulty_id;
		$obj->live_setting = $setting;
		$obj->capital_type = 0;
		$obj->capital_value = 0;
		$obj->special_setting = false;
		$obj->normal_live = false;
		$obj->random_flag = $live['random_flag'] > 0;
		$obj->stage_level = $live['stage_level'] ?? $setting->stage_level;
		$obj->s_times = 0;
		$obj->a_times = 0;
		$obj->b_times = 0;
		$obj->c_times = 0;
		$obj->s_score = $live['s_rank_score'] ?? $setting->s_score;
		$obj->a_score = $live['a_rank_score'] ?? $setting->a_score;
		$obj->b_score = $live['b_rank_score'] ?? $setting->b_score;
		$obj->c_score = $live['c_rank_score'] ?? $setting->c_score;
		$obj->goal_reward = NULL;
		
		return $obj;
	}
	
	/// \brief Search for live difficulty data in `event_festival_live_m` table.
	/// \param live_difficulty_id The live difficulty id to search it's data
	/// \returns The npps_live_difficulty object or `NULL` on failure
	static protected function get_instance_festival(int $live_difficulty_id)
	{
		$fesival_db = npps_get_database('event/festival');
		$live = $fesival_db->query("
			SELECT
				live_setting_id, stage_level random_flag, c_rank_score,
				b_rank_score, a_rank_score, s_rank_score
			FROM `event_festival_live_m` WHERE
			live_difficulty_id = $live_difficulty_id
		");
		
		if(count($live) == 0)
			return NULL;
		
		$live = $live[0];
		
		$obj = new npps_live_difficulty();
		$setting = npps_live_setting::get_instance($live['live_setting_id']);
		$obj->live_difficulty_id = $live_difficulty_id;
		$obj->live_setting = $setting;
		$obj->capital_type = 0;
		$obj->capital_value = 0;
		$obj->special_setting = false;
		$obj->normal_live = false;
		$obj->random_flag = $live['random_flag'] > 0;
		$obj->stage_level = $live['stage_level'] ?? $setting->stage_level;
		$obj->s_times = 0;
		$obj->a_times = 0;
		$obj->b_times = 0;
		$obj->c_times = 0;
		$obj->s_score = $live['s_rank_score'] ?? $setting->s_score;
		$obj->a_score = $live['a_rank_score'] ?? $setting->a_score;
		$obj->b_score = $live['b_rank_score'] ?? $setting->b_score;
		$obj->c_score = $live['c_rank_score'] ?? $setting->c_score;
		$obj->goal_reward = NULL;
		
		return $obj;
	}
	
	/// \brief Search for live difficulty data in `event_challenge_live_m`
	///        table.
	/// \param live_difficulty_id The live difficulty id to search it's data
	/// \returns The npps_live_difficulty object or `NULL` on failure
	static protected function get_instance_challenge(int $live_difficulty_id)
	{
		$challenge_db = npps_get_database('event/challenge');
		$live = $challenge_db->query("
			SELECT
				live_setting_id, stage_level random_flag, c_rank_score,
				b_rank_score, a_rank_score, s_rank_score
			FROM `event_festival_live_m` WHERE
			live_difficulty_id = $live_difficulty_id
		");
		
		if(count($live) == 0)
			return NULL;
		
		$live = $live[0];
		
		$obj = new npps_live_difficulty();
		$setting = npps_live_setting::get_instance($live['live_setting_id']);
		$obj->live_difficulty_id = $live_difficulty_id;
		$obj->live_setting = $setting;
		$obj->capital_type = 0;
		$obj->capital_value = 0;
		$obj->special_setting = false;
		$obj->normal_live = false;
		$obj->random_flag = $live['random_flag'] > 0;
		$obj->stage_level = $live['stage_level'] ?? $setting->stage_level;
		$obj->s_times = 0;
		$obj->a_times = 0;
		$obj->b_times = 0;
		$obj->c_times = 0;
		$obj->s_score = $live['s_rank_score'] ?? $setting->s_score;
		$obj->a_score = $live['a_rank_score'] ?? $setting->a_score;
		$obj->b_score = $live['b_rank_score'] ?? $setting->b_score;
		$obj->c_score = $live['c_rank_score'] ?? $setting->c_score;
		$obj->goal_reward = NULL;
		
		return $obj;
	}
	
	/// \brief Gets instance of npps_live_difficulty object
	/// \param live_difficulty_id The live difficulty id
	/// \param target The database name to search for:
	///          - `live`: search only in live database
	///          - `marathon`: search only in marathon/token event database
	///          - `battle`: search only in battle/score match database
	///          - `festival`: search only in (medley) festival database
	///          - `challenge`: search only in challenge (festival) database
	///        .
	///        If none is specificed, it will be searched in all database above,
	///        in order.
	/// \returns The npps_live_difficulty object of specificed
	///          `live_difficulty_id`
	/// \exception Exception Thrown if specificed `live_difficulty_id` doesn't
	///            exist or invalid `target` specificed.
	static public function get_instance(
		int $live_difficulty_id,
		string $target = ''
	): npps_live_difficulty
	{
		if(isset(npps_live_difficulty::$list[$live_difficulty_id]))
			return npps_live_difficulty::$list[$live_difficulty_id];
		
		static $calltarget = [
			'live' => 'npps_live_difficulty::get_instance_live',
			'marathon' => 'npps_live_difficulty::get_instance_marathon',
			'battle' => 'npps_live_difficulty::get_instance_battle',
			'festival' => 'npps_live_difficulty::get_instance_festival',
			'challenge' => 'npps_live_difficulty::get_instance_challenge'
		];
		
		if(strlen($target) == 0)
		{
			// Find all
			foreach($calltarget as $x)
			{
				$obj = $x($live_difficulty_id);
				
				if($obj !== NULL)
					return npps_live_difficulty::$list[$live_difficulty_id]
						   = $obj;
			}
		}
		elseif(isset($calltarget[$target]))
		{
			$obj = $calltarget[$target]($live_difficulty_id);
			
			if($obj !== NULL)
				return npps_live_difficulty::$list[$live_difficulty_id] = $obj;
		}
		else
			throw new Exception("Invalid target $target");
		
		throw new Exception("live_difficulty_id $live_difficulty_id not found");
	}
	
	/// \brief Gets list of completed goal reward ID for specificed value
	/// \param cmplist The list of values to check (In-order: C, B, A, S)
	/// \param cmpval The value to compare
	/// \param base Where to start compare in goal reward array
	/// \returns List of completed goal reward ID with specificed value checked.
	/// \exception Exception Thrown if live difficulty doesn't have goal reward.
	protected function goal_reward_base(
		array $cmplist,
		int $cmpval,
		int $base): array
	{
		if($this->goal_reward == NULL)
			throw new Exception('No goal reward data');
		
		$cleared_list = [];
		
		for($i = 0; $i < 4; $i++)
		{
			if($cmplist[$i] >= $cmpval)
				$cleared_list[] =
					$this->goal_reward[$i + $base]['live_goal_reward_id'];
		}
		
		return $cleared_list;
	}
	
	/// \brief Get list of completed goal reward ID based from specificed score
	public function goal_reward_score(int $score): array
	{
		return $this->goal_reward_base(
			[$this->c_score, $this->b_score, $this->a_score, $this->s_score],
			$score, 0
		);
	}
	
	/// \brief Get list of completed goal reward ID based from specificed combo
	public function goal_reward_combo(int $combo): array
	{
		$set = $this->live_setting;
		
		return $this->goal_reward_base(
			[$set->c_combo, $set->b_combo, $set->a_combo, $set->s_combo],
			$combo, 4
		);
	}
	
	/// \brief Get list of completed goal reward ID based from specificed amount
	///        of plays
	public function goal_reward_times(int $times): array
	{
		return $this->goal_reward_base(
			[$this->c_times, $this->b_times, $this->a_times, $this->s_times],
			$times, 8
		);
	}
	
	/// \brief Combines npps_live_difficulty::goal_reward_score, 
	///        npps_live_difficulty::goal_reward_score, and 
	///        npps_live_difficulty::goal_reward_score
	public function goal_reward(int $score, int $combo, int $times): array
	{
		return array_merge(
			$this->goal_reward_score($score),
			$this->goal_reward_combo($combo),
			$this->goal_reward_times($times)
		);
	}
}

/// \brief Gets live show status for specificed live difficulty id
/// \param user_id The player user ID
/// \param live_difficulty_id The live difficulty id to get it's status
/// \returns array which contain these keys
///            - `score`: highest score achieved by player
///            - `combo`: highest combo achieved by player
///            - `times`: how many times this song is played
function live_get_status(int $user_id, int $live_difficulty_id): array
{
	$template = [
		'score' => 0,
		'combo' => 0,
		'times' => 0
	];
	
	$data = npps_query("
		SELECT score, combo, times FROM `live_information` WHERE
		user_id = $user_id AND live_difficulty_id = $live_difficulty_id
	");
	
	if(count($data) == 0) goto return_data;
	
	$data = $data[0];
	$template['score'] = $data['score'];
	$template['combo'] = $data['combo'];
	$template['times'] = $data['times'];
	
	return_data:
	return $template;
}

/// \brief Unlocks hits live show for specificed player
/// \param user_id The player user ID
/// \param live_difficulty_id The live difficulty ID to unlock
/// \returns `true` if it's locked and then unlocked, `false` if it's already
///          unlocked
function live_unlock(int $user_id, int $live_difficulty_id): bool
{
	$temp_data = npps_query("
		SELECT times FROM `live_information`
		WHERE
			live_difficulty_id = $live_difficulty_id AND
			user_id = $user_id
		");
	
	if(count($temp_data) == 0)
	{
		npps_query("
			INSERT INTO `live_information` (
				live_difficulty_id,
				user_id,
				normal_live
			)
			VALUES (
				$live_difficulty_id,
				$user_id,
				1
			)
		");
		return true;
	}
	
	return false;
}

/// \brief Gets live information for specificed user
/// \param user_id Player user ID
/// \param live_difficulty_id The live ID to check
/// \returns Associative array which contains score, combo, and clear.
function live_get_info(int $user_id, int $live_difficulty_id): array
{	
	$out = [
		'score' => 0,
		'combo' => 0,
		'clear' => 0
	];
	
	$data = npps_query("
		SELECT score, combo, times FROM `live_information`
		WHERE live_difficulty_id = $live_difficulty_id AND user_id = $user_id"
	);
	
	if(count($data) > 0)
	{
		$data = $data[0];
		
		$out['score'] = $data['score'];
		$out['combo'] = $data['combo'];
		$out['clear'] = $data['times'];
	}
	
	return $out;
}

/// \brief Sets the user live information
/// \param user_id The player user ID
/// \param live_difficulty_id The live ID to set it's info
/// \param score The player score
/// \param combo The player combo
/// \param times The player amount of plays
function live_set_info(
	int $user_id,
	int $live_difficulty_id,
	int $score, int $combo, int $times
)
{
	npps_query("
		REPLACE INTO `live_information` (
			user_id,
			live_difficulty_id,
			score,
			combo,
			times
		)
		VALUES (
			$user_id,
			$live_difficulty_id,
			$score,
			$combo,
			$times
		)
	");
}

/// \brief Get current daily rotation live IDs
/// \returns array contains `live_difficulty_id` for today's song rotation
function live_get_current_daily(): array
{
	global $UNIX_TIMESTAMP;
	
	$out = [];
	$group = npps_query('
		SELECT MAX(daily_category) as a, MIN(daily_category) as b
		FROM `daily_rotation`'
	)[0];
	$max_group = $group['a'];
	$min_group = $group['b'];
	
	for($i = $min_group; $i <= $max_group; $i++)
	{
		$current_days = intdiv($UNIX_TIMESTAMP, 86400);
		$current_rot = npps_query("
			SELECT live_difficulty_id FROM `daily_rotation`
			WHERE daily_category = $i"
		);
		$out[] = $current_rot[$current_days % count($current_rot)]['live_difficulty_id'];
	}
	
	return $out;
}

/// \brief Check if live ID specificed is playable by player
/// \param user_id The player user ID for check
/// \param live_difficulty_id Live ID to check
/// \returns 1 if live show is unlocked, 2 if it's available in B-Side, 3 if
///          it's currently in daily rotation, 4 if it's playable for events,
///          and 0 if it's unplayable
function live_search(int $user_id, int $live_difficulty_id): int
{
	global $UNIX_TIMESTAMP;
	
	// 1. Search user unlocked live shows!
	foreach(npps_query("
		SELECT live_difficulty_id FROM `live_information` WHERE normal_live = 1
	") as $live)
		if($live['live_difficulty_id'] == $live_difficulty_id)
			return 1;
	
	// 2. Search B-Side schedule
	foreach(npps_query("
		SELECT live_id FROM `b_side_schedule`
		WHERE
			start_available_time <= $UNIX_TIMESTAMP AND
			end_available_time > $UNIX_TIMESTAMP
	") as $live)
		if($live['live_difficulty_id'] == $live_difficulty_id)
			return 2;
	
	// 3. Search daily rotation
	foreach(live_get_current_daily() as $live_id)
		if($live_id == $live_difficulty_id)
			return 3;
	
	// 4. Search token event live shows
	foreach(npps_query("
		SELECT easy_song_list,
			   normal_song_list,
			   hard_song_list,
			   expert_song_list
		FROM `event_list`
		WHERE
			event_start <= $UNIX_TIMESTAMP AND
			event_end > $UNIX_TIMESTAMP AND
			token_image IS NOT NULL
	") as $event_data)
	{
		$easylist = npps_separate(',', $event_data['easy_song_list']);
		$normallist = npps_separate(',', $event_data['normal_song_list']);
		$hardlist = npps_separate(',', $event_data['hard_song_list']);
		$expertlist = npps_separate(',', $event_data['expert_song_list']);
		
		foreach([$easylist, $normallist, $hardlist, $expertlist] as $live_list)
			foreach($live_list as $live_id)
				if($live_id == $live_difficulty_id)
					return 4;
	}
	
	// Not found.
	return 0;
}

/// \brief Gets `live_setting_id` of specificed live ID
/// \param live_difficulty_id The live ID
/// \returns `live_setting_id` or 0 if not found
/// \deprecated Use npps_live_difficulty::$live_setting instead
function live_setting_id(int $live_difficulty_id): int
{
	$live_db = npps_get_database('live');
	npps_attach_database($live_db, 'event/battle', 'event/festival', 'event/marathon');
	
	$result = $live_db->query("
		SELECT live_setting_id FROM (
			SELECT live_difficulty_id, live_setting_id FROM
				`normal_live_m` UNION
			SELECT live_difficulty_id, live_setting_id FROM
				`special_live_m` UNION
			SELECT live_difficulty_id, live_setting_id FROM
				`event_battle_live_m` UNION
			SELECT live_difficulty_id, live_setting_id FROM
				`event_festival_live_m` UNION
			SELECT live_difficulty_id, live_setting_id FROM
				`event_marathon_live_m`
		) WHERE live_difficulty_id = $live_difficulty_id
	");
	
	if(count($result) == 0)
		return 0;
	
	return $result[0]['live_setting_id'];
}

/// \brief Check if notes data for specificed live setting ID exist
/// \param live_setting_id The live setting ID
/// \returns `true` if notes data exist, `false` otherwise
/// \deprecated Use npps_live_setting::notes_data_exist() instead
function live_notes_exist(int $live_setting_id): bool
{
	$note_name = sprintf('Live_s%04d', $live_setting_id);
	
	if(file_exists("data/notes/$note_name.note"))
		return true;
	
	$db = npps_get_database('notes/notes');
	
	if(count($db->query("
		SELECT rootpage FROM `sqlite_master`
		WHERE type = 'table' AND name = '$note_name'
	")) > 0)
		return true;
	
	return false;
}

/// \brief Load beatmap of specificed live setting ID
/// \param live_setting_id The live setting ID to load it's beatmap
/// \returns Array containing the beatmap data or `NULL` on failure.
/// \deprecated Use npps_live_setting::load_notes_data() instead
function live_load_notes(int $live_setting_id)
{
	$note_name = sprintf('Live_s%04d', $live_setting_id);
	$filename = "data/notes/$note_name.note";
	$notes_data = [];
	
	if(file_exists($filename) == false)
	{
		if(file_exists('data/notes/notes.db_'))
		{
			$db = npps_get_database('notes/notes');

			if(count($db->query("
				SELECT rootpage FROM `sqlite_master`
				WHERE type = 'table' AND name = '$note_name'
			")) > 0)
			{
				foreach($db->query("
					SELECT * FROM `$note_name` ORDER BY timing_sec
				") as $note)
					$notes_data[] = [
						'timing_sec' => $note['timing_sec'],
						'notes_attribute' => $note['notes_attribute'],
						'notes_level' => 1,
						'effect' => $note['effect'],
						'effect_value' => $note['effect_value'] ?? 2,
						'position' => $note['position']
					];
			}
			else
				return NULL;
		}
		else
			return NULL;
	}
	else
	{
		$db = new SQLite3Database($filename);
		
		foreach($db->query("
			SELECT * FROM `notes_list` ORDER BY timing_sec
		") as $note)
			$notes_data[] = [
				'timing_sec' => $note['timing_sec'],
				'notes_attribute' => $note['notes_attribute'],
				'notes_level' => 1,
				'effect' => $note['effect'],
				'effect_value' => $note['effect_value'] ?? 2,
				'position' => $note['position']
			];
	}
	
	return $notes_data;
}
