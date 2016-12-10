<?php

$sns_aray = json_decode('{
                        "product_id": "None",
                        "name": "Nothing",
                        "price": "Free",
                        "product_type": 2,
                        "item_list": []
                    }', false);

return [
	[
		'sns_product_list' => [
           $sns_aray
		],
		
		'product_list' => []
	],
	200
];
?>