<?php

$unit_own_id = $REQUEST_DATA['unit_owning_user_id'];
echo "UPDATE users SET unit_partner = $unit_own_id WHERE user_id = $USER_ID";
npps_query("UPDATE users SET unit_partner = $unit_own_id WHERE user_id = $USER_ID");

return [
    [

    ],
    200
];