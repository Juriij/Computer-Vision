<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');
include('./classes/UCC/ucc_default.php');

if(!check_security())die();
$dbLink_DB = dcu_opendb('DB');

$commparam = my_required_filter_input('commparam',FILTER_VALIDATE_INT);

$output = array();
$unit_ucc = new ucc_unit_get();
$unit_ucc->request['paramCode'] = $commparam;

if ($unit_ucc->ucc_call()){
    $unit_ucc->calculate_front_end();
    $output = $unit_ucc->dsa;
}

$dbLink_DB->close();
$output = array_merge($output, array('errors'=>$GLOBALS['errors']));
//$output = $output + array('errors'=>$GLOBALS['errors']);  nedela: otestovat
print(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));
?>