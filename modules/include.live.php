<?php
/*
 * Null Pointer Private Server
 * Live shows!
 */

/// \file include.live.php

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
			INSERT INTO `live_information` (live_difficulty_id, user_id)
			VALUES ($live_difficulty_id, $user_id)
		");
		return true;
	}
	
	return false;
}

/* returns array: score, combo, clear. clear = 0 means never played (add "new" label) */
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
		$out[] = $current_rot[$current_days % count($current_rot)][0];
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

/* load notes array data from notes file or NULL if can't open notes file */
/// \brief Load beatmap of specificed live setting ID
/// \param live_setting_id The live setting ID to load it's beatmap
/// \returns Array containing the beatmap data or `NULL` on failure.
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
