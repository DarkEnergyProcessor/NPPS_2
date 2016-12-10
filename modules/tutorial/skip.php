<?php
$tutorial_state = npps_query("SELECT tutorial_state FROM `users` WHERE user_id = $USER_ID")[0]['tutorial_state'];

if($tutorial_state == 1)
{
	/* Rank up, set bond, and add member */
	npps_user::get_instance($USER_ID)->add_exp(11);
	npps_query("UPDATE `users` SET gold = gold + 600 WHERE user_id = $USER_ID");
	
	if(unit_add_direct($USER_ID, 13) > 0 && unit_add_direct($USER_ID, 9) > 0)
	{
		/* Alter bond with this pattern (set it): 3,3,3,3,10,3,3,3,3 */
		$deck_table = npps_query('SELECT deck_table, unit_table, main_deck, album_table FROM `users` WHERE user_id = ?', 'i', $USER_ID)[0];
		$deck_members = explode(':', npps_query("SELECT deck_members FROM `{$deck_table['deck_table']}` WHERE deck_num = {$deck_table['main_deck']}")[0]['deck_members']);
		$unit_list = [];
		
		foreach($deck_members as $a)
			$unit_list[] = npps_query("SELECT unit_id FROM `{$deck_table['unit_table']}` WHERE unit_id = $a");//[0]['unit_id'];
			//var_dump($unit_list);
		
		if(
			!(npps_query('BEGIN') &&
			npps_query("UPDATE `{$deck_table['unit_table']}` SET love = 3 WHERE unit_id IN(?, ?, ?, ?, ?, ?, ?, ?)", 'iiiiiiii', $deck_members[0], $deck_members[1], $deck_members[2], $deck_members[3], $deck_members[5], $deck_members[6], $deck_members[7], $deck_members[8]) &&
			npps_query("UPDATE `{$deck_table['unit_table']}` SET love = 10 WHERE unit_id = ?", 'i', $deck_members[4]) &&
			npps_query("UPDATE `{$deck_table['album_table']}` SET total_love = 3 WHERE unit_id IN(?, ?, ?, ?, ?, ?, ?, ?)", 'iiiiiiii', $unit_list[0], $unit_list[1], $unit_list[2], $unit_list[3], $unit_list[5], $unit_list[6], $unit_list[7], $unit_list[8]) &&
			npps_query("UPDATE `{$deck_table['album_table']}` SET total_love = 10 WHERE unit_id = ?", 'i', $unit_list[4]) &&
			npps_query('COMMIT'))
		)
		{
			echo 'Failed to alter bond!';
			http_response_code(500);
			return false;
		}
	}
	else
	{
		echo 'Failed to add card';
		http_response_code(500);
		return false;
	}
}
elseif($tutorial_state == 3)
{
	/* Finish it and delete token */
	npps_query("UPDATE `users` SET tutorial_state = -1 WHERE user_id = $USER_ID");
	token_destroy($TOKEN);
	
	return [
		[],
		200
	];
}

npps_query("UPDATE `users` SET tutorial_state = tutorial_state + 1 WHERE user_id = $USER_ID");

return [
	[],
	200
];
?>
