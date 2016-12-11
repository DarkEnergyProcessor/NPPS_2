<?php
if(isset($REQUEST_DATA['tos_id']) && is_int($REQUEST_DATA['tos_id']))
{
	npps_user::get_instance($USER_ID)->tos_agree = 1;
	
	return [[], 200];
}

echo 'tos_id is invalid';
return ERROR_CODE_OUT_OF_RANG;
