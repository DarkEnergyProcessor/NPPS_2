<?php

$album_table = npps_user::get_instance($USER_ID)->album_table;
$album_out = [];

foreach(npps_query("SELECT * FROM `$album_table`") as $album)
{
	$flags = $album['flags'];
	$album_out[] = [
		'unit_id' => $album['unit_id'],
		'rank_max_flag' => ($flags & 2) > 0,
		'love_max_flag' => ($flags & 4) > 0,
		'rank_level_max_flag' => ($flags & 8) > 0,
		'all_max_flag' => $flags == 15,
		'highest_love_per_unit' => $album['total_love'],
		'total_love' => $album['total_love']
	];
}

return [
	$album_out,
	200
];
