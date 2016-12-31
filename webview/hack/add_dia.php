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
	$dia_unit = [791, 915, 924, 933, 951, 961, 973, 998, 1032, 1052, 1071];
	
	foreach ($dia_unit as $dia)
		if(unit_add($USER_ID, $dia))
			echo "Unit $dia added!\n";
		else
			echo "Error! $dia not added!\n";
	
	echo "</pre>";
}
