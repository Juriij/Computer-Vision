<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');
include('./classes/components/component_default.php');
include('./classes/UCC/ucc_default.php');

if(!check_security())die();
$dbLink_Config = dcu_opendb('Config');
$dcuConns = array(1=>"Hello world"); // aby spravne preslo set_dcu_id
$output = array();
$server_list = array();

foreach ($_POST as $key=>$value){
    $component = create_component_from_dataset($key, $value);

    if(isset($value['param_id'])){
        $param_id = filter_var($value['param_id'],FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        if(isset($param_id)) $component->set_param_id($param_id);
        else $component->add_msg('WRONG_PARAM_ID');
    }else $component->add_msg(T_('Post not set').' param_id');

    if( (isset($component->var_id))&&
        (isset($component->param_id))){

        if (empty($server_list)){
            $server_list = read_rtudpio();
        }

        $server_id = $component->var_id.'.'.$component->param_id;
        if (!empty($server_list[$server_id])){
            $component->set_value($server_list[$server_id]['actualValue'], 1);
        }else{
            $component->add_msg('WRONG_ID');
        }
    }
    $output[$key] = $component->get_display_value();
    $output[$key] .= $component->get_messages();

}
$dbLink_Config->close();
filter_errors();
$output = $output + array('errors'=>$GLOBALS['errors']);
print(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));
?>