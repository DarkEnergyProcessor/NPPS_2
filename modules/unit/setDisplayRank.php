<?php
if(
	!isset(
		$REQUEST_DATA['unit_owning_user_id'],
		$REQUEST_DATA['display_rank']
	) ||
	!is_integer($REQUEST_DATA['unit_owning_user_id']) ||
	!is_integer($REQUEST_DATA['display_rank']) ||
	$REQUEST_DATA['display_rank'] < 1 ||
	$REQUEST_DATA['display_rank'] > 2
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

$unit->display_rank = $REQUEST_DATA['display_rank'];

return [[], 200];
