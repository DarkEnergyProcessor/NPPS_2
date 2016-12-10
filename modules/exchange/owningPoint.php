<?php

$sticker_data = npps_query("SELECT normal_sticker, silver_sticker, gold_sticker, purple_sticker FROM users WHERE user_id = $USER_ID")[0];
$array_stickers = [];

$array_stickers [] = ['rarity' => 2,'exchange_point' => $sticker_data['normal_sticker']];
$array_stickers [] = ['rarity' => 3,'exchange_point' => $sticker_data['silver_sticker']];
$array_stickers [] = ['rarity' => 4,'exchange_point' => $sticker_data['gold_sticker']];
$array_stickers [] = ['rarity' => 5,'exchange_point' => $sticker_data['purple_sticker']];

return [['exchange_point_list' => [$array_stickers]],200];
?>