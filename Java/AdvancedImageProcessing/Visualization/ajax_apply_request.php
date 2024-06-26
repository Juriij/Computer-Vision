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
$cUnitId = my_required_filter_input('cUnitId',FILTER_VALIDATE_INT);
$cUnitIP = my_required_filter_input('cUnitIP',FILTER_VALIDATE_IP);
$password = my_required_filter_input('password',FILTER_DEFAULT);
$userCommad = my_required_filter_input('userCommad',FILTER_DEFAULT);
$paramCode = my_required_filter_input('paramCode',FILTER_VALIDATE_INT);
$cryptKey = my_required_filter_input('cryptKey',FILTER_DEFAULT);
$reqData = my_required_filter_input('reqData',FILTER_DEFAULT);
$var_name = my_required_filter_input('var_name',FILTER_DEFAULT);
$var_format = my_required_filter_input('var_format',FILTER_VALIDATE_INT);

if(($userCommad=='control variable override')&&($var_format==0)){
        $flash_ucc = new ucc_var_read($cUnitId);
        $flash_ucc->request['paramCode'] = $paramCode;

        if ($flash_ucc->ucc_call()){
            $var_format = $flash_ucc->dsa['varOpt_part3']['bites']['format']['value'];
        }
}

$output = array();

$ucc = new ucc_default($cUnitId);
$ucc->request['userId'] = construct_userId();
$ucc->request['cUnitId'] = $cUnitId;
$ucc->request['cUnitIP'] = $cUnitIP;
$ucc->request['password'] = $password;
$ucc->request['userCommad'] = $userCommad;
$ucc->request['paramCode'] = $paramCode;
$ucc->request['cryptKey'] = $cryptKey;
$ucc->request['reqData'] = $reqData;
$ucc->archive['var_name'] = $var_name;
$ucc->archive['var_format'] = $var_format;
$success = $ucc->ucc_call();
if ($success){
    delete_archived_request($requestId);
    $output['success']=1;
}

$dbLink_DB->close();
$dbLink_Config->close();
filter_errors();
$output = array_merge($output, array('errors'=>$GLOBALS['errors']));
//$output = $output + array('errors'=>$GLOBALS['errors']);  nedela: otestovat
print(json_encode($output,JSON_PARTIAL_OUTPUT_ON_ERROR));