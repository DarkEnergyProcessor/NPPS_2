<?php
if(
	isset($REQUEST_DATA['invite_code']) &&
	is_string($REQUEST_DATA['invite_code']) &&
	preg_match('/^\d{9}/', $REQUEST_DATA['invite_code']) == 1
)
	$GLOBALS['INVITE_CODE'] = $REQUEST_DATA["invite_code"];
else
{
	echo 'Invalid invite_code';
	return false;
}

return npps_call_module('login/startWithoutInvite', $REQUEST_DATA);
