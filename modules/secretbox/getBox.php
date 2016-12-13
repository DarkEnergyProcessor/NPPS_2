<?php

include('base_data.php');

$box_data = npps_get_database('secretbox');

$muse_data = $box_data->query('SELECT secretbox_id, banner, banner_se, banner_top, name, description, title_asset FROM muse_secretbox');
$akua_data = $box_data->query('SELECT secretbox_id, banner, banner_se, banner_top, name, description, title_asset FROM aqua_secretbox');
$muse_bt_data = $box_data->query('SELECT secretbox_id, banner_top, name, description, title_asset FROM muse_blue_secretbox');
$akua_bt_data = $box_data->query('SELECT secretbox_id, banner_top, name, description, title_asset FROM aqua_blue_secretbox');

$muse_array = [];
$akua_array = [];
$muse_bt_array = [];
$akua_bt_array = [];

$page_counter = 3;

$muse_array[] = $base_muse;
$muse_array[] = $base_muse_coupon;

$muse_base_array = [
    'member_category' => 1,
    'tab_list' => []
];
$akua_base_array = [
    'member_category' => 2,
    'tab_list' => [$akua_base, $base_muse_coupon]
];

//Processinng μ's data

foreach ($muse_data as $data){
        $temp_base = [            
            'secret_box_tab_id' => $page_counter,
            'title_img_asset' => $data['banner'],
            'title_img_se_asset' => $data['banner_se'], 
            'page_list' => []
            ];
        
        $temp = [
                'secret_box_page_id' => 1,
                'page_layout' => 0,
                'default_img_info' => [
                    'banner_img_asset' => $data['banner'],
                    'banner_se_img_asset' => $data['banner_se'],
                    'img_asset' => $data['banner_top'],
                    'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id={$data['secretbox_id']}"
                ],
                'limited_img_info' => [
                    [
                        'banner_img_asset' => $data['banner'],
                        'banner_se_img_asset' => $data['banner_se'],
                        'img_asset' => $data['banner_top'],
                        'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id={$data['secretbox_id']}",
                        'start_date' => "2016-12-05 09:00:00",
                        'end_date' => "2025-12-31 23:59:59"
                    ]
                ],
                'effect_list' => [],
                'secret_box_list' => [
                    [
                    'secret_box_id'=> $data['secretbox_id'],
                    'name'=> $data['name'],
                    'title_asset'=> $data['title_asset'],
                    'description'=> $data['description'],
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
                         'type'=> 1,
                         'item_id'=> null,
                         'amount'=> 5,
                         'multi_amount'=> 50
                    ],
                   'pon_count'=> 0,
                   'pon_upper_limit'=> 0,
                   'display_type'=> 0,
                   'all_cost' => [[
                       'priority' => 1,
                       'type' => 1,
                       'item_id' => null,
                       'amount' => 5,
                       'multi_amount' => 50,
                       'multi_type' => 1,
                       'multi_count' => 11,
                       'is_pay_cost' => false,
                       'is_pay_multi_cost' => false,
                       'within_single_limit' => 1,
                       'within_multi_limit' => 1
                   ]
                   ],
                   'step' => null,
                   'term_count' => null,
                   'step_up_bonus_asset_path' => null,
                   'step_up_bonus_bonus_item_list' => null
                ],
            ]
        ];
        $temp_base['page_list'][0] = $temp;
        $muse_array[] = $temp_base;
        $page_counter++;
}

$muse_base_array['tab_list'] = $muse_array;

//var_dump($muse_base_array);

        $temp_base = [            
            'secret_box_tab_id' => $page_counter,
            'title_img_asset' => $muse_data[0]['banner'],
            'title_img_se_asset' => $muse_data[0]['banner_se'], 
            'page_list' => []
            ];
        
        $temp = [
                'secret_box_page_id' => 1,
                'page_layout' => 2,
                'default_img_info' => [
                    'banner_img_asset' => $muse_data[0]['banner'],
                    'banner_se_img_asset' => $muse_data[0]['banner_se'],
                    'img_asset' => $muse_data[0]['banner_top'],
                    'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id={$muse_data[0]['secretbox_id']}"
                ],
                'limited_img_info' => [
                    [
                        'banner_img_asset' => $muse_data[0]['banner'],
                        'banner_se_img_asset' => $muse_data[0]['banner_se'],
                        'img_asset' => $muse_data[0]['banner_top'],
                        'url' => "/webview.php/secretBox/index?template_id=31&secret_box_id={$muse_data[0]['secretbox_id']}",
                        'start_date' => "2016-12-05 09:00:00",
                        'end_date' => "2025-12-31 23:59:59"
                    ]
                ],
                'effect_list' => [],
                'secret_box_list' => [
                    [
                    'secret_box_id'=> $muse_data[0]['secretbox_id'],
                    'name'=> $muse_data[0]['name'],
                    'title_asset'=> $muse_data[0]['title_asset'],
                    'description'=> $muse_data[0]['description'],
                    'start_date'=> "2013-06-05 00:00:00",
                    'end_date'=> "2037-12-31 23:59:59",
                    'add_gauge'=> 0,
                    'multi_type'=> 1,
                    'multi_count'=> 10,
                    'is_pay_cost'=> true,
                    'is_pay_multi_cost'=> true,
                    'within_single_limit'=> 3,
                    'within_multi_limit'=> 3,
                    'cost'=> [
                         'priority'=> 1,
                         'type'=> 1,
                         'item_id'=> 12,
                         'amount'=> 5,
                         'multi_amount'=> 50
                    ],
                   'pon_count'=> 0,
                   'pon_upper_limit'=> 3,
                   'display_type'=> 10,
                   'all_cost' => [[
                       'priority' => 1,
                       'type' => 1,
                       'item_id' => 12,
                       'amount' => 5,
                       'multi_amount' => 50,
                       'multi_type' => 1,
                       'multi_count' => 11,
                       'is_pay_cost' => true,
                       'is_pay_multi_cost' => true,
                       'within_single_limit' => 1,
                       'within_multi_limit' => 3
                   ]
                   ],
                   'step' => null,
                   'term_count' => null,
                   'step_up_bonus_asset_path' => null,
                   'step_up_bonus_bonus_item_list' => null
                ],
            ]
        ];
        $temp_base['page_list'][0] = $temp;
        $muse_array[] = $temp_base;
        $page_counter++;

        $muse_base_array['tab_list'] = $muse_array;