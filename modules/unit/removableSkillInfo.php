<?php 

$equip = "{}";

$equip_array = json_decode($equip, true);
$sis_final = [];
$sis_list = npps_query("SELECT sis_table FROM users WHERE user_id = {$USER_ID}")[0]["sis_table"];
$sis_array = npps_query("SELECT * FROM {$sis_list}");

foreach($sis_array as $sukuskill){
    $sis_final[] = [
        'unit_removable_skill_id' => $sukuskill["unit_removable_skill_id"],
        'total_amount' => $sukuskill['total_amount'],
        'equipped_amount' => $sukuskill['equipped_amount']
    ];
}

return [
    [
        'owning_info' => $sis_final,
        'equipment_info' => $equip_array
    ],
    200];