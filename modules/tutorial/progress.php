<?php
if(!isset($REQUEST_DATA['tutorial_state']) || !is_int($REQUEST_DATA['tutorial_state']))
{
	echo 'Invalid tutoial_state';
	return false;
}

$user = npps_user::get_instance($USER_ID);

if($user->tutorial_state + 1 != $REQUEST_DATA['tutorial_state'] &&
   $REQUEST_DATA['tutorial_state'] != (-1)
)
{
	echo 'Invalid tutorial_state';
	return false;
}

$user->tutorial_state = $REQUEST_DATA['tutorial_state'];

if($user->tutorial_state == 4)
{
	// tutorial_state 4: rank up
	$user->add_exp(11);
	$user->gold += 600;
}
else if($user->tutorial_state == 5)
{
	npps_begin_transaction();
	
	// tutorial_state 5: add 2 cards
	if(unit_add($USER_ID, 13) && unit_add($USER_ID, 9))
	{
		// Alter bond with this pattern (set it): 3,3,3,3,10,3,3,3,3 */
		$deck_members = npps_separate(':', npps_query("
			SELECT deck_members FROM `{$user->deck_table}`
			WHERE deck_num = {$user->main_deck}")[0]['deck_members']);
		$deck_object = [];
		
		foreach($deck_members as $a)
			$deck_object[] = new npps_user_unit($USER_ID, $a);
		
		foreach([3, 3, 3, 3, 10, 3, 3, 3, 3] as $i => $love)
			$deck_object[$i]->love = $love;
		
		npps_end_transaction();
	}
	else
	{
		npps_end_transaction();
		echo 'Failed to add unit';
		http_response_code(500);
		return false;
	}
}

return [
	[],
	200
];
