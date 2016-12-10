<?php
//$login_bonus_table = npps_query("SELECT login_bonus_table, login_count, create_date FROM `users` WHERE user_id = $USER_ID")[0];
return [[], 200];

/*

return [
	[
		'login_count' => $first_login,
		'days_from_first_login' => $first_login,
		'before_lbonus_point' => $main_lbonus_counter[0],
		'after_lbonus_point' => $main_lbonus_counter[1],
		'last_login_date' => to_datetime($login_bonus_table[1]),
		'show_next_item' => date('d') != intval(date('t')),
		'items' => [
			'point' => $main_lbonus_item
		],
		'card_info' => [
			'start_date' => to_datetime($start_date - ($start_date % 86400)),
			'end_date' => to_datetime($end_date - ($end_date % 86400) - 1),
			'lbonus_count' => count($main_lbonus_itemlist),
			'items' => $main_lbonus_itemlist
		],
		'sheets' => $special_lbonus,
		'bushimo_reward_info' => []
	],
	200
];
*/
?>

