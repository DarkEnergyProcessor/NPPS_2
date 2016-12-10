<?php

$secretbox_json = file_get_contents("data/secretbox.json");
$secretbox_array = json_decode($secretbox_json, true);
$list= $secretbox_array[0];
//var_dump($list);

$secretbox_structure = [
        'use_cache' => 0,
        'is_unit_max' => false,
        'item_list' => [],
        'gauge_info' => [
            'max_gauge_point' => 100,
            'gauge_point' => 0
        ],
        'member_category_list' => [
                [
    'member_category' => 1,
    'tab_list' => [
        'secret_box_page_id' => 1,
        'page_layout' => 1,
        'default_img_info' => [
               'banner_img_asset' => $list["normal_secretbox"]['banner_image'],
               'banner_se_img_asset' => $list["normal_secretbox"]['banner_image_selected'],
               'img_asset' => $list["normal_secretbox"]['banner_big'],
               'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id=2"
        ],
        'limited_img_info' => [],
        'effect_list' => [],
        'secret_box_list' => [
           'secret_box_id'=> 1,
           'name'=> "Regular Student Scouting",
           'title_asset'=> null,
           'description'=> "dummy",
           'start_date'=> "2013-06-05 00:00:00",
           'end_date'=> "2037-12-31 23:59:59",
           'add_gauge'=> 0,
           'multi_type'=> 1,
           'multi_count'=> 10,
           'is_pay_cost'=> true,
           'is_pay_multi_cost'=> false,
           'within_single_limit'=> 0,
           'within_multi_limit'=> 0,
           'cost'=> [
               'priority'=> 1,
               'type'=> 4,
               'item_id'=> null,
               'amount'=> 1,
               'multi_amount'=> 1000
           ],
           'pon_count'=> 0,
           'pon_upper_limit'=> 0,
           'display_type'=> 0
           ],
        [
           "secret_box_id"=> 2,
           "name"=> "Honor Student Scouting",
           "title_asset"=> null,
           "description"=> "dummy",
           "start_date"=> "2013-06-05 00:00:00",
           "end_date"=> "2037-12-31 23:59:59",
           "add_gauge"=> 10,
           "multi_type"=> 1,
           "multi_count"=> 11,
           "is_pay_cost"=> false,
           "is_pay_multi_cost"=> false,
           "within_single_limit"=> 1,
           "within_multi_limit"=> 1,
           "cost"=> [
               "priority"=> 3,
               "type"=> 1,
               "item_id"=> null,
               "amount"=> 5,
               "multi_amount"=> 50
           ],
           "pon_count"=> 0,
           "pon_upper_limit"=> 0,
           "display_type"=> 0
           ]
    ]
    ],
    [
    'member_category' => 2,
    'tab_list' => [
        'secret_box_page_id' => 1,
        'page_layout' => 1,
        'default_img_info' => [
               'banner_img_asset' => $list["normal_secretbox"]['banner_image'],
               'banner_se_img_asset' => $list["normal_secretbox"]['banner_image_selected'],
               'img_asset' => $list["normal_secretbox"]['banner_big'],
               'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id=2"
        ],
        'limited_img_info' => [],
        'effect_list' => [],
        'secret_box_list' => [
           'secret_box_id'=> 1,
           'name'=> "Regular Student Scouting",
           'title_asset'=> null,
           'description'=> "dummy",
           'start_date'=> "2013-06-05 00:00:00",
           'end_date'=> "2037-12-31 23:59:59",
           'add_gauge'=> 0,
           'multi_type'=> 1,
           'multi_count'=> 10,
           'is_pay_cost'=> true,
           'is_pay_multi_cost'=> false,
           'within_single_limit'=> 0,
           'within_multi_limit'=> 0,
           'cost'=> [
               'priority'=> 1,
               'type'=> 4,
               'item_id'=> null,
               'amount'=> 1,
               'multi_amount'=> 1000
           ],
           'pon_count'=> 0,
           'pon_upper_limit'=> 0,
           'display_type'=> 0
           ],
        [
           "secret_box_id"=> 2,
           "name"=> "Honor Student Scouting",
           "title_asset"=> null,
           "description"=> "dummy",
           "start_date"=> "2013-06-05 00:00:00",
           "end_date"=> "2037-12-31 23:59:59",
           "add_gauge"=> 10,
           "multi_type"=> 1,
           "multi_count"=> 11,
           "is_pay_cost"=> false,
           "is_pay_multi_cost"=> false,
           "within_single_limit"=> 1,
           "within_multi_limit"=> 1,
           "cost"=> [
               "priority"=> 3,
               "type"=> 1,
               "item_id"=> null,
               "amount"=> 5,
               "multi_amount"=> 50
           ],
           "pon_count"=> 0,
           "pon_upper_limit"=> 0,
           "display_type"=> 0
           ]
    ]
    ]
        ]
];

return [$secretbox_structure, 200];
