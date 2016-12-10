<?php
function make_initial_set(int $min, int $max){
	$initial_set = [];
	$unitlist_template = [13, 9, 8, 23, -1, 24, 21, 20, 19];
	for($i = $min; $i <= $max; $i++)
	{
		$current_unit = $unitlist_template;
		$current_unit[4] = $i;
		
		$initial_set[] = [
			'unit_initial_set_id' => $i,
			'unit_list' => $current_unit,
			'center_unit_id' => $i
		];
	}
	
	return $initial_set;
};

return [
	[
		'member_category_list' => [
			[
				'member_category' => 1,
				'unit_initial_set' => make_initial_set(49, 57)
			],
			[
				'member_category' => 2,
				'unit_initial_set' => make_initial_set(788, 796)
			]
		]
	],
	200
];
