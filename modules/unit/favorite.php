<?php
if(
	!isset($REQUEST_DATA['unit_owning_user_id']) ||
	!is_integer($REQUEST_DATA['unit_owning_user_id'])
)
	return ERROR_CODE_UNIT_NOT_EXIST;

$unit = NULL;

try
{
	$unit = new npps_user_unit($USER_ID, $REQUEST_DATA['unit_owning_user_id']);
}
catch(Exception $e)
{
	npps_log($e->getMessage());
	return ERROR_CODE_UNIT_NOT_EXIST;
}

$unit->favorite_flag = intval(!$unit->favorite_flag);
return [[], 200];
