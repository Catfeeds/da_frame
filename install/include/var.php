<?php
$env_items = array();
$dirfile_items = array(
		array('type' => 'dir', 'path' => 'RuntimeBE/Cache'),
		array('type' => 'dir', 'path' => 'RuntimeBE/Data'),
		array('type' => 'dir', 'path' => 'RuntimeBE/Logs'),
		array('type' => 'dir', 'path' => 'RuntimeBE/Temp'),
		
		array('type' => 'dir', 'path' => 'RuntimeFE/Cache'),
		array('type' => 'dir', 'path' => 'RuntimeFE/Data'),
		array('type' => 'dir', 'path' => 'RuntimeFE/Logs'),
		array('type' => 'dir', 'path' => 'RuntimeFE/Temp'),
		
		array('type' => 'dir', 'path' => 'Uploads'),
		array('type' => 'dir', 'path' => 'Uploads/circle'),
		array('type' => 'dir', 'path' => 'Uploads/cms'),
		array('type' => 'dir', 'path' => 'Uploads/delivery'),
		array('type' => 'dir', 'path' => 'Uploads/ext'),
		array('type' => 'dir', 'path' => 'Uploads/live'),
		array('type' => 'dir', 'path' => 'Uploads/microshop'),
		array('type' => 'dir', 'path' => 'Uploads/mobile'),
		array('type' => 'dir', 'path' => 'Uploads/shop'),
		
		array('type' => 'dir', 'path' => 'sql_bak'),
		array('type' => 'dir', 'path' => 'install'),
		array('type' => 'dir', 'path' => 'im'),
		array('type' => 'dir', 'path' => 'Api'),
);

$func_items = array(
		array('name' => 'mysqli_connect'),
		array('name' => 'fsockopen'),
		array('name' => 'gethostbyname'),
		array('name' => 'file_get_contents'),
		array('name' => 'mb_convert_encoding'),
		array('name' => 'json_encode'),
		array('name' => 'curl_init'),
);