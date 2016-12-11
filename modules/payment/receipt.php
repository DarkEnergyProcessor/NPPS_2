<?php
$receipt_data = $REQUEST_DATA['receipt_data'];
$receipt_text_match = '/holy linus in heaven, blessed be thy name, give us our'
					. ' daily com.klab.lovelive.en.loveGem(\d{3}) as in heaven'
					. ' so be it on this earth.../';
if(preg_match($receipt_text_match, $receipt_data, $product_id_matches) == 0)
{
	echo "Invalid receipt! $receipt_data";
	return ERROR_CODE_PAYMENT_INVALID_APPLE_PRODUCT_ID;
}

$price_tier = [
	1 => 1,
	6 => 4,
	15 => 10,
	23 => 15,
	50 => 30,
	86 => 50
];

$loveca_count = intval($product_id_matches[1]);
$user = npps_user::get_instance($USER_ID);
$user->paid_loveca += $loveca_count;

return [
	[
		'status' => [],
		'product' => [
			'apple_product_id' => "com.klab.lovelive.en.loveGem{$product_id_matches[1]}",
			'google_product_id' => "com.klab.lovelive.en.loveGem{$product_id_matches[1]}",
			'name' => "$loveca_count Love Gems",
			'price' => 100,
			'price_tier' => strval($price_tier[$loveca_count] ?? '50'),
			'sns_coin' => $loveca_count,
			'insert_date' => '2013/10/24 21:16:00',
			'update_date' => '2013/10/24 21:16:00',
			'product_id' => "com.klab.lovelive.en.loveGem{$product_id_matches[1]}"
		]
	],
	200
];
