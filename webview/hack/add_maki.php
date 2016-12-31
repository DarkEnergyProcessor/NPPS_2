<?php
// Just RayFirefist leftovers lol
if($USER_ID == NULL)
	if(
		isset($_GET['user_id']) &&
		is_numeric($_GET['user_id'])
	)
		$USER_ID = intval($_GET['user_id']);

if($USER_ID)
{
	echo "<pre>";
	$maki_unit = [
		36 , 45 , 54 , 63 , 81 , 88 , 98 , 106, 121, 136, 147, 167, 170, 188,
		194, 216, 220, 231, 240, 257, 259, 270, 284, 291, 295, 304, 313, 336,
		344, 358, 362, 368, 395, 399, 400, 405, 423, 435, 456, 478, 482, 484,
		488, 499, 513, 520, 529, 548, 566, 572, 594, 600, 611, 618, 628, 643,
		666, 676, 686, 697, 703, 705, 718, 733, 744, 756, 774, 785, 809, 816,
		832, 837, 840, 854, 880, 900, 908, 944, 990, 1014,1060,1061,1075
	];
	
	foreach($maki_unit as $maki)
		if(unit_add($USER_ID, $maki))
			echo "Unit $maki added!\n";
		else
			echo "Error! $maki not added!\n";
	
	echo "</pre>";
}
