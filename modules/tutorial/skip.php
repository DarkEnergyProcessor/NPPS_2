<?php
$user = npps_user::get_instance($USER_ID);

if($user->tutorial_state == 1)
{
	npps_begin_transaction();
	
	// Rank up, set bond, and add member
	$user->add_exp(11);
	$user->gold += 600;
	
	if(unit_add($USER_ID, 13) > 0 && unit_add($USER_ID, 9) > 0)
	{
		// Alter bond with this pattern (set it): 3,3,3,3,10,3,3,3,3
		$deck_members = npps_separate(':', npps_query("
			SELECT deck_members FROM `{$user->deck_table}`
			WHERE deck_num = {$user->main_deck}
		")[0]['deck_members']);
		$deck_object = [];
		
		foreach($deck_members as $a)
			$deck_object[] = new npps_user_unit($USER_ID, $a);
		
		foreach([3, 3, 3, 3, 10, 3, 3, 3, 3] as $i => $love)
			$deck_object[i]->love = $love;
		
		npps_end_transaction();
	}
	else
	{
		npps_end_transaction();
		echo 'Failed to add card';
		http_response_code(500);
		return false;
	}
}
elseif($tutorial_state == 3)
{
	// Finish it and delete token
	npps_begin_transaction();
	$user->tutorial_state = (-1);
	token_destroy($TOKEN);
	npps_end_transaction();
	
	goto request_end;
}

$user->tutorial_state++;

request_end:
return [
	[],
	200
];
