<?php
$banner_list = [];
$event_common_db = npps_get_database('event/event_common');

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
		'banner_type' => 0,
		'target_id' => $event['event_id'],
		'asset_path' => $event_info['banner_asset_name'],
		'asset_path_se' => $event_info['banner_se_asset_name'],
		'master_is_active_event' => $event['end_time'] > $UNIX_TIMESTAMP
	];
}


$secretbox_json = file_get_contents("data/secretbox.json");
$secretbox_array = json_decode($secretbox_json, true);
$list[] = $secretbox_array[0]["normal_secretbox"];
$id = 0;

foreach($list as $secretbox){
        $banner_list[] = [
		'banner_type' => 1,
		'target_id' => $id,
		'asset_path' => $secretbox["banner_image"],
		'asset_path_se' => $secretbox["banner_image_selected"],
	];
    $id++;
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
