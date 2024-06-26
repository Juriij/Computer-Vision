<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');
include('./classes/UCC/ucc_default.php');

if(!check_security())die();
$dbLink_Config = dcu_opendb('Config');
$dbLink_DB = dcu_opendb('DB');
$dcuConns = get_dcu_conns();

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
$number_format = my_required_filter_input('number_format',FILTER_VALIDATE_INT);
if($number_format==1){
    $new_value = my_required_filter_input('new_value',FILTER_VALIDATE_FLOAT, ['options' => ['decimal' => ['.', ',']]]);
}else{
    $new_value = my_required_filter_input('new_value',FILTER_VALIDATE_INT);
}

$output = array();

switch($element_type){
    case 'variable':
        $override_ucc = new ucc_var_override($dcu_id);
        $override_ucc->request['paramCode'] = ($var_id  << 16) | 10000;  //parameters (FLASH)

        global $dsa_var_modify;
        $override_ucc->dsa = $dsa_var_modify;

        //get from post
        $override_ucc->dsa['varOpt_part3']['bites']['format']['value'] = $number_format;
        $override_ucc->dsa['varOvrOpt_part1']['bites']['type']['value'] = 1;
        $override_ucc->dsa['varOvrOpt_part1']['bites']['operation']['value'] = 1;
        $override_ucc->dsa['varOvrOpt_part1']['bites']['seclevel']['value'] = 15;
        $override_ucc->dsa['varOvrVal']['value'] = $new_value;
        $override_ucc->dsa['varOvrTim']['value'] = 0;
        $override_ucc->dsa['varOvrTimStop']['value'] = 0xFFFFFFFF;

        //len kvoli evtparam
        $flash_override = $override_ucc->dsa['varOvrOpt_part1']['bites']['type']['value'];
        $flash_operation = $override_ucc->dsa['varOvrOpt_part1']['bites']['operation']['value'];

        $override_ucc->ucc_call();
        break;
    case 'function_param':
        //get function
        $function_get = new ucc_func_get($dcu_id);
        $function_get->request['paramCode'] = $var_id;
        if ($function_get->ucc_call()){
        }else{
            unset($function_get->dsa);
        }

        //set function
        $function_set = new ucc_func_set($dcu_id);
        $function_set->request['paramCode'] = $var_id;

        global $dsa_modify_fcnParams;
        $function_set->dsa = $dsa_modify_fcnParams;

        global $dsa_fcnParams;
        $i = 0;
        foreach ($function_get->dsa['fcnParams']['value'] as $fcnParam){
            $dsa_fcnParams['fcnParams_type']['value'] = $fcnParam['type'];
            $string_value = $function_set->build_string_value($dsa_fcnParams['fcnParams_type'], "fcnParams_type_$i");

            if($i == $param_id)
                $dsa_fcnParams['fcnParams_value']['value'] = $new_value;
            else
                $dsa_fcnParams['fcnParams_value']['value'] = $fcnParam['value'];

            switch ($fcnParam['type']){
                case 1: case 2: case 3: case 4:
                    $dsa_fcnParams['fcnParams_value']['data_type'] = 'real';
                    break;
                case 5: //IP
                    break;
                case 6:
                    $dsa_fcnParams['fcnParams_value']['data_type'] = 'string';
                    break;
            }
            $i+=1;
            $string_value .= $function_set->build_string_value($dsa_fcnParams['fcnParams_value'], "fcnParams_value_$i");
            $function_set->dsa['modify__fcnParams']['value'][] = $string_value;
        }
        $function_set->ucc_call();
        break;
    case 'rtudpio_server':
        write_rtudpio($var_id, $param_id, $new_value);
        break;
    default:
        $GLOBALS['errors']['general'][] = T_('Unknown type.');
}

$dbLink_DB->close();
filter_errors();
$output = array_merge($output, array('errors'=>$GLOBALS['errors']));
//$output = $output + array('errors'=>$GLOBALS['errors']);  nedela: otestovat
print(json_encode($output));