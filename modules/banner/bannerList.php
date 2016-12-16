<?php
$banner_list = [];
$event_common_db = npps_get_database('event/event_common');

// Events
foreach(npps_query("
	SELECT event_id, end_time FROM `event_list`
	WHERE end_time > $UNIX_TIMESTAMP AND start_time <= $UNIX_TIMESTAMP")
	as $event
)
{
	$event_info = $event_common_db->query("
		SELECT banner_asset_name, banner_se_asset_name FROM `event_m`
		WHERE event_id = {$event['event_id']}"
	)[0];
	$banner_list[] = [
		'banner_type' => NPPS_BANNER_EVENT,
		'target_id' => $event['event_id'],
		'asset_path' => $event_info['banner_asset_name'],
		'asset_path_se' => $event_info['banner_se_asset_name'],
		'master_is_active_event' => $event['end_time'] > $UNIX_TIMESTAMP
	];
}

$secretbox_array = json_decode(file_get_contents('data/secretbox.json'), true);
$muse_secretbox = $secretbox_array[0]['normal_secretbox'];
$id = 0;

// Muse secretbox
$banner_list[] = [
	'banner_type' => NPPS_BANNER_SECRETBOX,
	'target_id' => -1,
	'asset_path' => $muse_secretbox['banner_image'],
	'asset_path_se' => $muse_secretbox['banner_image_selected'],
];

// Aqua secretbox, TODO

// Enum secretbox DB
$secretbox_db = npps_get_database('secretbox');

foreach(['muse_secretbox', 'aqua_secretbox'] as $i => $box)
{
	$abox = $secretbox_db->query("
		SELECT secretbox_id, banner, banner_se FROM `$box`
		WHERE
			$UNIX_TIMESTAMP >= start_time AND
			$UNIX_TIMESTAMP < end_time
	");
	
	foreach($abox as $x)
		$banner_list[] = [
			'banner_type' => NPPS_BANNER_SECRETBOX,
			'target_id' => $x['secretbox_id'] | (16777216 << $i),
			'asset_path' => $x['banner'],
			'asset_path_se' => $x['banner_se']
		];
}

return [
	[
		'time_limit' => to_datetime(
			$UNIX_TIMESTAMP - ($UNIX_TIMESTAMP % 86400) + 86399
		),
		'member_category_list' => [
			[
				'member_category' => 1,
				'banner_list' => $banner_list
			],
			[
				'member_category' => 2,
				'banner_list' => $banner_list
			]
		]
	],
	200
];
