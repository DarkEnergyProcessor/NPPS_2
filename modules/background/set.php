<?php
$back_id = intval($REQUEST_DATA['background_id'] ?? 0);

if($back_id > 0)
{
	$user = npps_user::get_instance($USER_ID);
	$back_list = npps_separate(',', $user->unlocked_background);
	
	if(array_search(strval($back_id), $back_list) !== false)
	{
		$user->background_id = $back_id;
		
		return [
			[],
			200
		];
	}
}

echo 'Invalid background ID';
return ERROR_CODE_OUT_OF_RANG;
