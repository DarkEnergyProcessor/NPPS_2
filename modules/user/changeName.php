<?php
if(!isset($REQUEST_DATA['name']))
{
	echo 'Name needed!';
	return false;
}

$newname = strval($REQUEST_DATA['name']);
$badwords = npps_separate(',', BADWORDS_LIST);

if(
	mb_strlen($newname, 'UTF-8') > 10 ||
	strpos_array(
		str_replace(' ', '', mb_strtolower($newname, 'UTF-8')),
		$badwords
	)
)
	// Name too long or contain invalid characters
	return ERROR_CODE_NG_WORDS;

$user = npps_user::get_instance($USER_ID);
$oldname = $user->name;

return [
	[
		'before_name' => $oldname,
		'after_name' => $user->name = $newname
	],
	200
];
