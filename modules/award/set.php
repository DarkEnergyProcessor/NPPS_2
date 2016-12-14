<?php
$award_id = intval($REQUEST_DATA['award_id'] ?? 0);

if($award_id > 0)
{
	$user = npps_user::get_instance($USER_ID);
	$award_list = npps_separate(',', $user->unlocked_title);
	
	if(array_search(strval($award_id), $award_list) !== false)
	{
		$user->title_id = $award_id;
		
		return [
			[],
			200
		];
	}
}

echo 'Invalid award ID';
return ERROR_CODE_OUT_OF_RANG;
