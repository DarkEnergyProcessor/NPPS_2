<?php

$base_muse = [
            'secret_box_tab_id' => 1,
            'title_img_asset' => "assets/image/secretbox/icon/s_ba_3_2.png",
            'title_img_se_asset' => "assets/image/secretbox/icon/s_ba_3_2se.png",
            'page_list' => [[
                'secret_box_page_id' => 65535,
                'page_layout' => 1,
                'default_img_info' => [
                    'banner_img_asset' => "assets/image/secretbox/icon/s_ba_3_2.png",
                    'banner_se_img_asset' => "assets/image/secretbox/icon/s_ba_3_2se.png",
                    'img_asset' => "assets/image/secretbox/top/s_con_n_3_2.png",
                    'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id=2"
                ],
                'limited_img_info' => [
                    [
                        'banner_img_asset' => "assets/image/secretbox/icon/s_ba_3_2.png",
                        'banner_se_img_asset' => "assets/image/secretbox/icon/s_ba_3_2se.png",
                        'img_asset' => "assets/image/secretbox/top/s_con_n_3_2.png",
                        'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id=2",
                        'start_date' => "2016-12-05 09:00:00",
                        'end_date' => "2025-12-31 23:59:59"
                    ]
                ],
                'effect_list' => [],
                'secret_box_list' => [
                    [
                    'secret_box_id'=> 65535,
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
                   "secret_box_id"=> 65534,
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
        ]]
    ];

$base_muse_coupon = ['secret_box_tab_id' => 2,
            'title_img_asset' => "assets/image/secretbox/tab/s_tab_03.png",
            'title_img_se_asset' => "assets/image/secretbox/tab/s_tab_03se.png",
            'page_list' => [        
                [
                'secret_box_page_id' => 12,
                'page_layout' => 0,
                'default_img_info' => [
                    'banner_img_asset' => null,
                    'banner_se_img_asset' => null,
                    'img_asset' => "assets/image/secretbox/top/s_con_n_22_1.png",
                    'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id=22"
                ],
                'limited_img_info' => [],
                'effect_list' => [],
                'secret_box_list' => [
                [
                   "secret_box_id"=> 22,
                   "name"=> "SR/UR Scouting",
                   "title_asset"=> "assets/image/secretbox/title/22.png",
                   "description"=> "SR and UR Club Members only scouting\nusing Scouting Coupons!\nPreviously released event members have\nlower appearance rates. SSR not included.",
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
                      "priority"=> 1,
                      "type"=> 2,
                      "item_id"=> 5,
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
                'secret_box_page_id' => 12,
                'page_layout' => 0,
                'default_img_info' => [
                    'banner_img_asset' => null,
                    'banner_se_img_asset' => null,
                    'img_asset' => "assets/image/secretbox/top/s_con_n_23_1.png",
                    'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id=23"
                ],
                'limited_img_info' => [],
                'effect_list' => [],
                'secret_box_list' => [
                [
                   "secret_box_id"=> 22,
                   "name"=> "Supporting Member Scouting",
                   "title_asset"=> "assets/image/secretbox/title/22.png",
                   "description"=> "Supporting Members only scouting with\nScouting Coupons!\nUsing a Supporting Member as a Practice\npartner for a member of the same Attribute\nwill give extra Skill Exp.",
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
                      "priority"=> 1,
                      "type"=> 2,
                      "item_id"=> 5,
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

$akua_base = [
            'secret_box_tab_id' => 1,
            'title_img_asset' => "assets/image/secretbox/icon/s_ba_62_2.png",
            'title_img_se_asset' => "assets/image/secretbox/icon/s_ba_62_2se.png",
            'page_list' => [[
                'secret_box_page_id' => 1,
                'page_layout' => 1,
                'default_img_info' => [
                    'banner_img_asset' => "assets/image/secretbox/icon/s_ba_62_2.png",
                    'banner_se_img_asset' => "assets/image/secretbox/icon/s_ba_62_2se.png",
                    'img_asset' => "assets/image/secretbox/top/s_con_n_62_2.png",
                    'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id=2"
                ],
                'limited_img_info' => [
                    [
                        'banner_img_asset' => "assets/image/secretbox/icon/s_ba_62_2.png",
                        'banner_se_img_asset' => "assets/image/secretbox/icon/s_ba_62_2se.png",
                        'img_asset' => "assets/image/secretbox/top/s_con_n_62_2.png",
                        'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id=2",
                        'start_date' => "2016-12-05 09:00:00",
                        'end_date' => "2025-12-31 23:59:59"
                    ]
                ],
                'effect_list' => [],
                'secret_box_list' => [
                    [
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
        ]]
    ];