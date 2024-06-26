<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');
include('./classes/UCC/ucc_default.php');

if(!check_security())die();
$element_type = filter_input(
    INPUT_POST,
    'element_type',
    FILTER_VALIDATE_REGEXP,
    array('options'=>array('regexp'=>"/^variable|function|rtudpio_server/"))
    );
if (empty($element_type)) {$element_type = 'unknown';}

$dcu_id = my_required_filter_input('dcu_id',FILTER_VALIDATE_INT);
$var_id = my_required_filter_input('var_id',FILTER_VALIDATE_INT);
$param_id = my_required_filter_input('param_id',FILTER_VALIDATE_INT);

$output = array(
    'number_format' => 0
);

switch($element_type){
    case 'variable':
        $dbLink_DB = dcu_opendb('DB');
        $dcuConns = get_dcu_conns();
        $flash_ucc = new ucc_var_read($dcu_id);
        $flash_ucc->request['paramCode'] = ($var_id  << 16) | 10000;  //parameters (FLASH)

        if ($flash_ucc->ucc_call()){
            $output['number_format'] = $flash_ucc->dsa['varOpt_part3']['bites']['format']['value'];
        }else {
            //unset($flash_ucc->dsa);
        }
        $dbLink_DB->close();
        break;
    case 'function':
    case 'function_param':
    case 'variable_param':
        $GLOBALS['errors']['general'][] = 'get function format not implemented';
        break;
    case 'rtudpio_server':
        $dbLink_Config = dcu_opendb('Config');
        $output['number_format'] = get_rtudpio_format($var_id, $param_id);
        $dbLink_Config->close();
        break;
    default:
        $GLOBALS['errors']['general'][] = T_('Unknown type.');

}

filter_errors();
$output = array_merge($output, array('errors'=>$GLOBALS['errors']));
//$output = $output + array('errors'=>$GLOBALS['errors']);  nedela: otestovat
print(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));