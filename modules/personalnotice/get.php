<?php

// Personal notice template
$pn = [
	'has_notice' => false,
	'notice_id' => 0,
	'type' => 0,
	'title' => '',
	'contents' => ''
];

// Get personal notice data
$pn_data = npps_query("
	SELECT * FROM `personal_notice` WHERE user_id = $USER_ID
");

if(count($pn_data) > 0)
{
	$pn['has_notice'] = true;
	$pn['notice_id'] = -1;
	$pn['title'] = $pn_data[0]['title'];
	$pn['contents'] = $pn_data[0]['contents'];
}

return [
	$pn,
	200
];
