<?php
$user = npps_user::get_instance($USER_ID);
$array_stickers = [];

$array_stickers [] = ['rarity' => 2,'exchange_point' => $user->normal_sticker];
$array_stickers [] = ['rarity' => 3,'exchange_point' => $user->silver_sticker];
// Try to flip the UR and SSR sticker.
$array_stickers [] = ['rarity' => 4,'exchange_point' => $user->purple_sticker];
$array_stickers [] = ['rarity' => 5,'exchange_point' => $user->gold_sticker];

return [
	[
		'exchange_point_list' => [$array_stickers]
	],
	200
];
