<?php

$package_type = $REQUEST_DATA['package_type'];
$exclude_id = $REQUEST_DATA['excluded_package_ids'];
$host = 'http://download.makoo.eu/patch/';	// For pkg type 4

switch ($package_type)
{
	case 1:
	case 2:
	case 3:
	{
		goto normal_batch;
		break;
	}
	case 4:
	{
		goto rff_patch;
		break;
	}
}

normal_batch:
// Original NPPS download/batch implementation
return [[],200];

// RayFireFist download ID 4 implementation
rff_patch:
//For ID 4
$patch_database = npps_get_database('patch');
$id_to_download = [];
$ids_final = [];
$count = 0;

foreach($patch_database->query("SELECT id FROM patch_id_list") as $ids)
    $id_to_download[] = $ids;

if(count($exclude_id) == 0)
	goto start;

foreach($id_to_download as $id)
{
	if($id && !isset($exclude_id[$count]))
		$ids_final[] = $id;
	
    $count++;
}

$download_structure = [];

start:
foreach ($ids_final as $k => $id)
{
	$data_fetched = $patch_database->query("SELECT * FROM patch_id_download_list WHERE patch_id = {$id['id']}");
	foreach ($data_fetched as $data)
	{
		/*    
		 * cURLing for get size
		 */
		// Get cURL resource
		$curl = curl_init();
		
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => 'http://download.makoo.eu/patch/filesize.php',
			CURLOPT_POSTFIELDS => http_build_query(['link_directory' => $data['download_link']])
		]);
		
		
		// Send the request & save response to $resp
		$size = curl_exec($curl);
		echo "File: {$data['download_link']} : {$size}";
		
		$download_structure[] = [
			'url' => $host . $data['download_link'],
			'size' => $size,
			'download_id' => $data['download_id']
		];
	}
}

return [$download_structure,200];
