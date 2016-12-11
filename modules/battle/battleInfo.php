<?php
$score_match_list = [];
$event_common_db = npps_get_database('event/event_common');

foreach(npps_query("
	SELECT
		event_id,
		event_ranking_table,
		easy_lp, normal_lp, hard_lp,
		expert_lp, technical_lp
	FROM `event_list`
	WHERE
		token_image IS NULL AND
		start_time <= $UNIX_TIMESTAMP AND
		close_time > $UNIX_TIMESTAMP
") as $ev)
{
	if($event_common_db->query("
		SELECT event_category_id FROM `event_m`
		WHERE event_id = {$ev['event_id']}
	")[0]['event_category_id'] == 2)
	{
		$event_point = 0;
		$diff_list = [];
		
		if($user_event_info = npps_query("
			SELECT total_points FROM `{$ev['event_ranking_table']}`
			WHERE user_id = $USER_ID
		"))
			if(count($user_event_info) > 0)
				$event_point = $user_event_info[0]['total_points'];
		
		foreach(
			['easy_lp', 'normal_lp', 'hard_lp', 'expert_lp', 'technical_lp']
		as $i => $j)
			if($ev[$j] != NULL)
				$diff_list[] = [
					'difficulty' => $i + 1,
					'capital_type' => 1,
					'capital_value' => $ev[$j]
				];
		
		$score_match_list[] = [
			'event_id' => $ev['event_id'],
			'point_name' => 'nil',
			'event_point' => 0,
			'total_event_point' => $event_point,
			'event_battle_difficulty_m' => $diff_list
		];
	}
}

return [
	$score_match_list,
	200
];
