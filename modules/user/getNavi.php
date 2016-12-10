<?php

$unit_partner = npps_query("SELECT unit_partner FROM users WHERE user_id =".$USER_ID)[0]['unit_partner'];

$return = [
    'user' => [
        'user_id' => $USER_ID, 
        'unit_owning_user_id' => $unit_partner
    ]
];

return [
    
        $return
    , 
        200
        ];

?>