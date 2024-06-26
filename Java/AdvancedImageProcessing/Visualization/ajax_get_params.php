<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');
include('./classes/components/component_default.php');
include('./classes/UCC/ucc_default.php');

if(!check_security())die();
$dbLink_DB = dcu_opendb('DB');
$dcuConns = get_dcu_conns();
$output = array();
$fcn_list = array();
$var_list = array();

foreach ($_POST['fp_datasets'] as $key=>$value){
    $component = create_component_from_dataset($key, $value);

    if(isset($value['param_id'])){
        $param_id = filter_var($value['param_id'],FILTER_VALIDATE_INT);
        if($param_id=== FALSE){
            $component->add_msg('WRONG_PARAM_ID');
        }else{
            $component->set_param_id($param_id);
        }
    }else $component->add_msg(T_('Post not set').' param_id');

    if( (isset($component->dcu_id)) &&
        (isset($component->var_id))&&
        (isset($component->param_id))){

        if (empty($fcn_list[$component->dcu_id])){ //petra: alebo OLD
            $fcn_list[$component->dcu_id] = array();
        }
        if (empty($fcn_list[$component->dcu_id][$component->var_id])){
            $function_ucc = new ucc_func_get($component->dcu_id);
            $function_ucc->request['paramCode'] = $component->var_id;

            if ($function_ucc->ucc_call()){
                $fcn_list[$component->dcu_id][$component->var_id] = $function_ucc->dsa;
            }else{
                $fcn_list[$component->dcu_id][$component->var_id] = array('hello world');
            }
        }

        $act_list = $fcn_list[$component->dcu_id][$component->var_id];
        if (!empty($act_list['message'])){
            if (substr( strtoupper($act_list['message']['value']), 0, 5 ) === 'ERROR'){
                $component->add_msg($act_list['message']['value']);
            }
        }
        if(isset($act_list['fcnParams'])){
            if (isset($act_list['fcnParams']['value'][$component->param_id])){
                $new_value = $act_list['fcnParams']['value'][$component->param_id]['value'];
                $component->set_value($new_value);
            }else{
                $component->add_msg('Parameter param_id not set correctly');
            }
        }else {
            $component->add_msg('UCC error');
        }
    }
    $output[$key] = $component->get_display_value();
    $output[$key] .= $component->get_messages();
}

foreach ($_POST['vp_datasets'] as $key=>$value){
    $component = create_component_from_dataset($key, $value);

    if(isset($value['param_id'])){
        $param_id = filter_var($value['param_id'],FILTER_VALIDATE_INT);
        if($param_id=== FALSE){
            $component->add_msg('WRONG_PARAM_ID');
        }else{
            $component->set_param_id($param_id);
        }
    }else $component->add_msg(T_('Post not set').' param_id');

    if( (isset($component->dcu_id)) &&
        (isset($component->var_id))&&
        (isset($component->param_id))){

        if (empty($var_list[$component->dcu_id])){ //petra: alebo OLD
            $var_list[$component->dcu_id] = array();
        }
        if (empty($var_list[$component->dcu_id][$component->var_id])){
            $flash_ucc = new ucc_var_read($component->dcu_id);
            $flash_ucc->request['paramCode'] = ($component->var_id  << 16) | 10000;

            if ($flash_ucc->ucc_call()){
                $var_list[$component->dcu_id][$component->var_id] = $flash_ucc->dsa;
            }else{
                $var_list[$component->dcu_id][$component->var_id] = array('hello world');
            }
        }

        $act_list = $var_list[$component->dcu_id][$component->var_id];
        if (!empty($act_list['message'])){
            if (substr( strtoupper($act_list['message']['value']), 0, 5 ) === 'ERROR'){
                $component->add_msg($act_list['message']['value']);
            }
        }
        //if(isset($act_list['varAlarm']['value'])) //petra: treba?
        if(isset($act_list['varAlarm']['value'][$component->param_id])){
            if (isset($act_list['varAlarm']['value'][$component->param_id]['alarm_value'])){
                $new_value = $act_list['varAlarm']['value'][$component->param_id]['alarm_value']['value'];
                $component->set_value($new_value);
            }else{
                $component->add_msg('Parameter param_id not set correctly');
            }
        }else {
            $component->add_msg('UCC error');
        }
    }
    $output[$key] = $component->get_display_value();
    $output[$key] .= $component->get_messages();
}
$dbLink_DB->close();
filter_errors();
$output = $output + array('errors'=>$GLOBALS['errors']);
print(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));
?>