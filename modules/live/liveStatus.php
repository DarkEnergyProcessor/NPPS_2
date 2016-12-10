<?php

$live_db = npps_get_database('live');
//$normal_live = [];
$special_live = [];
$event_live = [];

$normal_data = npps_query("SELECT live_difficulty_id, normal_live, score, combo, times FROM live_information WHERE user_id=$USER_ID");
$b_side_data = npps_query("SELECT * FROM b_side_schedule");

//Normal live data
//var_dump($normal_data);
//$arrays = count($normal_data);
//echo "ARRAY: $arrays";
//counter = 0;

/*
do{

$normal_live[] = [
		'live_difficulty_id' => $normal_data[$counter]["live_difficulty_id"],
		'status' => 1,
		'hi_score' => $normal_data[$counter]["score"],
		'hi_combo_count' => $normal_data[$counter]["combo"],
		'clear_cnt' => $normal_data[$counter]["times"],
		'achieved_goal_id_list' => []
	];
$counter++;
}while($counter < $arrays);
//die();
*/

$normal_live = '[
                    {
                        "live_difficulty_id": 2,
                        "status": 1,
                        "hi_score": 0,
                        "hi_combo_count": 0,
                        "clear_cnt": 0,
                        "achieved_goal_id_list": []
                    },
                    {
                        "live_difficulty_id": 3,
                        "status": 1,
                        "hi_score": 0,
                        "hi_combo_count": 0,
                        "clear_cnt": 0,
                        "achieved_goal_id_list": []
                    },
                    {
                        "live_difficulty_id": 4,
                        "status": 1,
                        "hi_score": 0,
                        "hi_combo_count": 0,
                        "clear_cnt": 0,
                        "achieved_goal_id_list": []
                    },
                    {
                        "live_difficulty_id": 5,
                        "status": 1,
                        "hi_score": 0,
                        "hi_combo_count": 0,
                        "clear_cnt": 0,
                        "achieved_goal_id_list": []
                    },
                    {
                        "live_difficulty_id": 6,
                        "status": 1,
                        "hi_score": 0,
                        "hi_combo_count": 0,
                        "clear_cnt": 0,
                        "achieved_goal_id_list": []
                    },
                    {
                        "live_difficulty_id": 350,
                        "status": 1,
                        "hi_score": 0,
                        "hi_combo_count": 0,
                        "clear_cnt": 0,
                        "achieved_goal_id_list": []
                    },
                    {
                        "live_difficulty_id": 1198,
                        "status": 1,
                        "hi_score": 0,
                        "hi_combo_count": 0,
                        "clear_cnt": 0,
                        "achieved_goal_id_list": []
                    },
                    {
                        "live_difficulty_id": 1199,
                        "status": 1,
                        "hi_score": 0,
                        "hi_combo_count": 0,
                        "clear_cnt": 0,
                        "achieved_goal_id_list": []
                    },
                    {
                        "live_difficulty_id": 1200,
                        "status": 1,
                        "hi_score": 0,
                        "hi_combo_count": 0,
                        "clear_cnt": 0,
                        "achieved_goal_id_list": []
                    },
                    {
                        "live_difficulty_id": 1201,
                        "status": 1,
                        "hi_score": 0,
                        "hi_combo_count": 0,
                        "clear_cnt": 0,
                        "achieved_goal_id_list": []
                    },
                    {
                        "live_difficulty_id": 1,
                        "status": 1,
                        "hi_score": 0,
                        "hi_combo_count": 0,
                        "clear_cnt": 0,
                        "achieved_goal_id_list": []
                    }
                 ]';

$json_array = json_decode($normal_live, true);

return [
	[
		'normal_live_status_list' => $json_array,
		'special_live_status_list' => $special_live,
		'marathon_live_status_list' => $event_live
	],
	200
];

?>
