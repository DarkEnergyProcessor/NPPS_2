<?php

$npps_user = npps_user::get_instance($USER_ID);

$remove_skill = $REQUEST_DATA['remove'];
$add_skill = $REQUEST_DATA['equip'];
$sis_list = $npps_user->sis_table;
$unit_list = $npps_user->unit_table;

foreach ($remove_skill as $remove){
    $get_sis_equip_num = npps_query('SELECT equipped_amount FROM '.$sis_list.' WHERE unit_removable_skill_id = '.$remove["unit_removable_skill_id"].'')[0]['equipped_amount'];
    $sis_to_remove = ','.$remove['unit_removable_skill_id'];
    $unit = $remove['unit_owning_user_id'];
    $new_eqip = $get_sis_equip_num - 1;
    npps_query('UPDATE '.$sis_list.' SET equipped_amount = '.$new_eqip.' WHERE unit_removable_skill_id = '.$remove["unit_removable_skill_id"].'');
    $unit_sis_list = npps_query('SELECT unit_removable_skill_list FROM '.$unit_list.' WHERE unit_owning_user_id = '.$remove["unit_owning_user_id"].'')[0]['unit_removable_skill_list'];
    $new_unit_sis_list = chop($unit_sis_list, $sis_to_remove);
    npps_query('UPDATE '.$unit_list.' SET unit_removable_skill_list = "'.$new_unit_sis_list.'" WHERE unit_owning_user_id = '.$remove["unit_owning_user_id"].'');
}

foreach ($add_skill as $add){
    $get_sis_equip_num = npps_query('SELECT equipped_amount FROM '.$sis_list.' WHERE unit_removable_skill_id = '.$add["unit_removable_skill_id"].'')[0]['equipped_amount'];
    $sis_to_add = $add['unit_removable_skill_id'];
    $new_eqip = $get_sis_equip_num + 1;
    npps_query('UPDATE '.$sis_list.' SET equipped_amount = '.$new_eqip.' WHERE unit_removable_skill_id = '.$add["unit_removable_skill_id"].'');
    $unit_sis_list = npps_query('SELECT unit_removable_skill_list FROM '.$unit_list.' WHERE unit_owning_user_id = '.$add["unit_owning_user_id"].'')[0]['unit_removable_skill_list'];
    $new_unit_sis_list = $unit_sis_list.','.$sis_to_add;
    npps_query('UPDATE '.$unit_list.' SET unit_removable_skill_list = "'.$new_unit_sis_list.'" WHERE unit_owning_user_id = '.$add["unit_owning_user_id"].'');
}


return [[],200];