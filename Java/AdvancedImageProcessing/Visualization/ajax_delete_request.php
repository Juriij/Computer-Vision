<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');
include('./classes/UCC/ucc_default.php');

if(!check_security())die();
$dbLink_Config = dcu_opendb('Config');
$dbLink_DB = dcu_opendb('DB');

$requestId = my_required_filter_input('requestId',FILTER_VALIDATE_INT);

$output = array();
$output['success'] = delete_archived_request($requestId);

$dbLink_DB->close();
$dbLink_Config->close();
filter_errors();
$output = array_merge($output, array('errors'=>$GLOBALS['errors']));
//$output = $output + array('errors'=>$GLOBALS['errors']);  nedela: otestovat
print(json_encode($output,JSON_PARTIAL_OUTPUT_ON_ERROR));