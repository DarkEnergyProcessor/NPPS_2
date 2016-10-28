<?php
/* Meant to be inherited, and overridden if necessary */
abstract class AchievementHandler
{
	/* Returns these arrays (increase it's counter if necessary):
		completed
			achievement_id
			count
			is_accomplished
			insert_date
			end_date
			remaining_time
			is_new
			for_display
			reward_list (string containing item_id:count[:info_id]
			reward_box
		new
			achievement_id
			count
			is_accomplished
			insert_date
			end_date
			remaining_time
			is_new
			for_display
			reward_list (string containing item_id:count[:info_id]
	*/
	abstract public function handle_achievement(int $achievement_id, ...$params);
	
	/* Get the current achievement_id internal count */
	public function query_count(int $achievement_id);
	
	/* Unlocks achievement for user */
	public function open_achievement(int $achievement_id, int $end_time = 0): bool
	{
		global $UNIX_TIMESTAMP;
		
		$achievement_db = npps_get_database('achievement');
		$achievement_table = npps_query("SELECT assignment_table FROM `users` WHERE user_id = $user_id")[0]['assignment_table'];
		
		if(count(npps_query("SELECT achievement_id FROM `$achievement_table` WHERE achievement_id = $achievement_id")) > 0)
			return false;
		
		$new_end_time = NULL;
		if($end_time > 0)
			$new_end_time = $end_time;
		
		
	}
	
	/* Returns same array as `handle_achievement` except that it doesn't increment count */
	abstract public function loop_achievement(int $achievement_id, ...$params);
};

function achievement_get_handler(int $achievement_category): AchievementHandler
{
	static $ACHIEVEMENT_INSTANCE_LIST = [];
	
	if(isset($ACHIEVEMENT_INSTANCE_LIST[$achievement_category]))
		return $ACHIEVEMENT_INSTANCE_LIST[$achievement_category];
	else
		return $ACHIEVEMENT_INSTANCE_LIST[$achievement_category] = (require("achievement/handler/$achievement_category.php"));
}

/* Returns array of the new achievement info OR NULL if it's already unlocked.
	achievement_id
	count
	is_accomplished
	insert_date
	end_date
	remaining_time (always NULL)
	is_new
	for_display
	reward_list (array)
*/
function achievement_unlock(int $user_id, int $achievement_id, int $end_timestamp = 0)
{
	global $DATABASE;
	global $TEXT_TIMESTAMP
	
	$achievement_db = npps_get_database('achievement');
	$achievement_table = npps_query("SELECT assignment_table FROM `users` WHERE user_id = $user_id")[0]['assignment_table'];
	$achievement_temp = npps_query("SELECT assignment_id FROM `$achievement_table` WHERE assignment_id = $achievement_id");
	
	if(count($achievement_temp) == 0)
	{
		/* Unlock it */
		$achievement_info = $achievement_db->execute_query("SELECT * FROM `achievement_m` WHERE achievement_id == $achievement_id")[0];
		$handler = achievement_get_handler($achievement_info[5]);
		$handler->open_achievement($achievement_id);
		
		return [
			'achievement_id' => $achievement_id,
			'count' => 0,
			'is_accomplished' => false,
			'insert_date' => $TEXT_TIMESTAMP,
			'end_date' => $end_timestamp > 0 ? to_datetime($end_timestamp) : NULL;
			'remaining_time' => NULL,
			'is_new' => true,
			'for_display' => $achievement_info[21]
		];
	}
	
	return NULL;
}

/* Trigger specific achievement type */
function achievement_trigger(int $user_id, int $category): array
{
	$achievement_db = npps_get_database('achievement');
	
	$achievement_list = $achievement_db->execute_query("SELECT achievement_id, params1, params2, params3, params4, params5, params6, params7, params8, params9, params10 FROM `achievement_m`");
	$inst = achievement_get_handler($category);
	$parameters = [
		$achievement_list['achievement_id'],
		$achievement_list['params1'],
		$achievement_list['params2'],
		$achievement_list['params3'],
		$achievement_list['params4'],
		$achievement_list['params5'],
		$achievement_list['params6'],
		$achievement_list['params7'],
		$achievement_list['params8'],
		$achievement_list['params9'],
		$achievement_list['params10']
	];
	$out = [];
	
	foreach($achievement_list as $a)
		$out[] = $inst->handle_achievement(...$parameters)
	
	while(true)
	{
		$temp = $inst->loop_achievement(...$parameters);
		
		if(count($temp) == 0)
			break;
		
		$out[] = $temp;
	}
	
	return $out;
}
