<?php
/*
 * Null Pointer Private Server
 * Live shows!
 */

/// \file include.live.php

function live_unlock(int $user_id, int $live_id): bool
{
	global $DATABASE;
	
	$live_table = $DATABASE->execute_query("SELECT live_table FROM `users` WHERE user_id = $user_id")[0][0];
	return $DATABASE->execute_query("INSERT INTO `$live_table` (live_id) VALUES (?)", 'i', $live_id);
}

/* returns array: score, combo, clear. clear = 0 means never played (add "new" label) */
function live_get_info(int $user_id, int $live_id): array
{
	global $DATABASE;
	
	$out = [
		'score' => 0,
		'combo' => 0,
		'clear' => 0
	];
	
	$live_table = $DATABASE->execute_query("SELECT live_table FROM `users` WHERE user_id = $user_id")[0][0];
	$data = $DATABASE->execute_query("SELECT * FROM `$live_table` WHERE live_id = $live_id");
	
	if(count($data) > 0)
	{
		$data = $data[0];
		
		$out['score'] = $data['score'];
		$out['combo'] = $data['combo'];
		$out['clear'] = $data['times'];
	}
	
	return $out;
}

/* Returns list of current daily rotation in group. */
/* The ID returned is live difficulty id or simply called "live ID" */
function live_get_current_daily(): array
{
	global $DATABASE;
	global $UNIX_TIMESTAMP;
	
	$out = [];
	$group = $DATABASE->execute_query('SELECT MAX(daily_category), MIN(daily_category) FROM `daily_rotation`')[0];
	$max_group = $group[0];
	$min_group = $group[1];
	
	for($i = $min_group; $i <= $max_group; $i++)
	{
		$current_days = intdiv($UNIX_TIMESTAMP, 86400);
		$current_rot = $DATABASE->execute_query("SELECT live_id FROM `daily_rotation` WHERE daily_category = $i");
		$out[] = $current_rot[$current_days % count($current_rot)][0];
	}
	
	return $out;
}

/* Search for live show and returns true if it can be played. */
function live_search(int $user_id, int $live_difficulty_id): bool
{
	global $UNIX_TIMESTAMP;
	$live_table = npps_query("SELECT live_table FROM `users` WHERE user_id = $user_id")[0]['live_table'];
	
	// 1. Search user unlocked live shows!
	foreach(npps_query("SELECT live_id FROM `$live_table` WHERE normal_live = 1") as $live)
		if($live['live_id'] == $live_difficulty_id)
			return true;
	
	// 2. Search B-Side schedule
	foreach(npps_query("SELECT live_id FROM `b_side_schedule` WHERE start_available_time <= $UNIX_TIMESTAMP AND end_available_time > $UNIX_TIMESTAMP") as $live)
		if($live['live_id'] == $live_difficulty_id)
			return true;
	
	// 3. Search daily rotation
	foreach(live_get_current_daily() as $live_id)
		if($live_id == $live_difficulty_id)
			return true;
	
	// 4. Search token event live shows
	foreach(npps_query(
<<<QUERY
		SELECT easy_song_list, normal_song_list, hard_song_list, expert_song_list FROM `event_list`
			WHERE event_start <= $UNIX_TIMESTAMP AND event_end > $UNIX_TIMESTAMP AND token_image IS NOT NULL
QUERY
		) as $event_data)
	{
		$easylist = npps_separate(',', $event_data['easy_song_list']);
		$normallist = npps_separate(',', $event_data['normal_song_list']);
		$hardlist = npps_separate(',', $event_data['hard_song_list']);
		$expertlist = npps_separate(',', $event_data['expert_song_list']);
		
		foreach([$easylist, $normallist, $hardlist, $expertlist] as $live_list)
			foreach($live_list as $live_id)
				if($live_id == $live_difficulty_id)
					return true;
	}
	
	// Not found.
	return false;
}

/* returns live_setting_id from live_difficulty_id or 0 if not found*/
function live_setting_id(int $live_difficulty_id): int
{
	$live_db = npps_get_database('live');
	npps_attach_database($live_db, 'event/battle', 'event/festival', 'event/marathon');
	
	$result = $live_db->execute_query(<<<QUERY
		SELECT live_setting_id FROM (
			SELECT live_difficulty_id, live_setting_id FROM `normal_live_m` UNION
			SELECT live_difficulty_id, live_setting_id FROM `special_live_m` UNION
			SELECT live_difficulty_id, live_setting_id FROM `event_battle_live_m` UNION
			SELECT live_difficulty_id, live_setting_id FROM `event_festival_live_m` UNION
			SELECT live_difficulty_id, live_setting_id FROM `event_marathon_live_m`
		) WHERE live_difficulty_id = $live_difficulty_id
QUERY
	);
	
	if(count($result) == 0)
		return 0;
	
	return $result[0]['live_setting_id'];
}

function live_notes_exist(int $live_setting_id)
{
	$note_name = sprintf('Live_s%04d', $live_setting_id);
	
	if(file_exists("data/notes/$note_name.note"))
		return true;
	
	$db = npps_get_database('notes/notes');
	
	if(count($db->execute_query("SELECT rootpage FROM `sqlite_master` WHERE type = 'table' AND name = '$note_name'")) > 0)
		return true;
	
	return false;
}

/* load notes array data from notes file or NULL if can't open notes file */
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
			
			if(count($db->execute_query("SELECT rootpage FROM `sqlite_master` WHERE type = 'table' AND name = '$note_name'")) > 0)
			{
				foreach($db->execute_query("SELECT * FROM `$note_name` ORDER BY timing_sec") as $note)
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
		
		foreach($db->execute_query("SELECT * FROM `notes_list` ORDER BY timing_sec") as $note)
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
