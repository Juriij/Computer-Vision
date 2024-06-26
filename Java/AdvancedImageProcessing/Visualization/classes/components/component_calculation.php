<?php
$var_ids_and_names = array();
$fcn_ids_and_names = array();

function check_id_and_name(&$component){

    if(!isset($component->dcu_id)){
        $component->add_msg('WRONG_DCU');
        return FALSE;
    }
    if($component->dcu_id ==-1)return FALSE; //error already raised in setter method

    //$component->can_set_value = check_id_and_name($component);
    if (($component->type === 'variable')||
        ($component->type === 'variable_graph')||
        ($component->type === 'variable_param')){
        global $var_ids_and_names;
        if(empty($var_ids_and_names[$component->dcu_id]))
            $var_ids_and_names[$component->dcu_id] = construct_var_ids_and_names($component->dcu_id);
        $act_ids_and_names = $var_ids_and_names[$component->dcu_id]; //array($id => $name)and($name=>$id)
    }
    if (($component->type === 'function')||
        ($component->type === 'function_param')){
        global $fcn_ids_and_names;
        if(empty($fcn_ids_and_names[$component->dcu_id]))
            $fcn_ids_and_names[$component->dcu_id] = construct_fcn_ids_and_names($component->dcu_id);
        $act_ids_and_names = $fcn_ids_and_names[$component->dcu_id]; //array($id => $name)and($name=>$id)
    }

    if(!isset($component->var_id)){
        if(empty($component->var_name)){
            $component->add_msg('WRONG_ID'); //id has priority
        }else{
            if(!isset($act_ids_and_names['names'][$component->var_name])){
                $component->add_msg('WRONG_NAME');
            }else{
                $var = $act_ids_and_names['names'][$component->var_name];
                $component->var_id = $var['id'];
                if(isset($var['error']))
                    $GLOBALS['errors']['general'][] = $var['error'];
                return TRUE;
            }
        }
    }else{
        if($component->var_id ==-1)return FALSE; //error already raised in setter method
        if(!isset($act_ids_and_names['ids'][$component->var_id])){
            $component->add_msg('WRONG_ID');
        }else{
            $component->var_name = $act_ids_and_names['ids'][$component->var_id]['name'];
            return TRUE;
        }
    }
    return FALSE;
}


function component_calculation(){
    global $dispvars;
    global $var_ids_and_names;

    //check IDs and names
    foreach($dispvars as $component){  // backward compatibility - rename $dispvars to $components
        $component->can_set_value = FALSE;

        if ($component->type=='static') continue;
        if ($component->type=='batchserver') continue;
        if ($component->type=='rtudpio_server'){

            if(!isset($component->var_id)){
                $component->add_msg('WRONG_ID');
                return;
            }
            if($component->var_id ==-1)return; //error already raised in setter method

            if(!isset($component->param_id)){
                $component->add_msg('WRONG_ID');
                return;
            }
            if($component->param_id ==-1)return; //error already raised in setter method

            $component->var_name = $component->var_id;
            $component->can_set_value = TRUE;

            continue;
        }

        if ($component->type=='variable_graph') {
            $new_multi_id = array();
            foreach($component->multi_id as $one_multi_id){
                if(empty($one_multi_id)){
                    if(check_id_and_name($component))
                        $new_multi_id[] = array($component->dcu_id,$component->var_id,$component->var_name );
                }else{
                    //check var_name in multi_id [dcu_id, var_name, label]
                    $first_parameter = $one_multi_id[0];
                    $second_parameter = $one_multi_id[1];
                    $third_parameter = $one_multi_id[2];

                    if(empty($var_ids_and_names[$first_parameter]))
                        $var_ids_and_names[$first_parameter] = construct_var_ids_and_names($first_parameter);
                    if(is_int($second_parameter)){
                        if(isset($var_ids_and_names[$first_parameter]['ids'][$second_parameter])){

                        }else{
                            $component->add_msg('WRONG_ID');
                            continue;
                        }
                    }else{
                        if(isset($var_ids_and_names[$first_parameter]['names'][$second_parameter])){
                            $second_parameter = $var_ids_and_names[$first_parameter]['names'][$second_parameter]['id'];
                            $second_parameter = $second_parameter;
                        }else{
                            $component->add_msg('WRONG_NAME');
                            continue;
                        }
                    }
                    $new_multi_id[] = array($first_parameter, $second_parameter, $third_parameter);
                }
            }
            $component->multi_id = $new_multi_id;
            continue;
        }

        if (($component->type=='function')||
            ($component->type=='variable')||
            ($component->type=='function_param')||
            ($component->type=='variable_param'))
            $component->can_set_value = check_id_and_name($component);

        if ($component->type=='function')
            $component->can_set_value = FALSE;

        if((isset($component->unit)) && (is_array($component->unit)))
            $component->unit = implode(' ',$component->unit);
    }

    $fcn_list = array();
    $server_list = array();

    //set values
    foreach($dispvars as $component){
        if($component->can_set_value === TRUE){

            if ($component->type==='variable_param'){
                if (empty($var_list[$component->dcu_id])){
                    $var_list[$component->dcu_id] = array();
                }

                if (empty($var_list[$component->dcu_id][$component->var_id])){
                    $flash_ucc = new ucc_var_read($component->dcu_id);
                    $flash_ucc->request['paramCode'] = ($component->var_id  << 16) | 10000;  //parameters (FLASH)

                    if ($flash_ucc->ucc_call()){
                        $var_list[$component->dcu_id][$component->var_id] = $flash_ucc->dsa;
                    }else{
                        $var_list[$component->dcu_id][$component->var_id] = array('hello world');
                    }
                }

                $act_list = $var_list[$component->dcu_id][$component->var_id];
                if (!empty($act_list['message'])){
                    if (substr( $act_list['message']['value'], 0, 5 ) === "ERROR"){
                        $component->add_msg($act_list['message']['value']);
                    }
                }
                if(isset($act_list['varAlarm'])){
                    if ((isset($component->param_id))&&
                        (!empty($act_list['varAlarm']['value'][$component->param_id]))){
                        $varParam = $act_list['varAlarm']['value'][$component->param_id];
                        $component->set_value($varParam['alarm_value']['value']);
                        if ((isset($GLOBALS['panel_time_type']))&&($GLOBALS['panel_time_type'] != 'actual'))
                            $component->add_msg(T_('Value is actual, not historical.'), 'NOT HISTORICAL');
                    }else{
                        $component->add_msg('WRONG_PARAM_ID');
                        $component->can_set_value = FALSE;
                    }
                }else {
                    $component->add_msg('NO_DATA');
                }
            }
            if ($component->type==='function_param'){

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
                    if (substr( $act_list['message']['value'], 0, 5 ) === "ERROR"){
                        $component->add_msg($act_list['message']['value']);
                    }
                }

                if(isset($act_list['fcnParams'])){
                    if ((isset($component->param_id))&&
                        (!empty($act_list['fcnParams']['value'][$component->param_id]))){
                        $fcnParam = $act_list['fcnParams']['value'][$component->param_id];
                        $component->set_value($fcnParam['value']);
                        if ((isset($GLOBALS['panel_time_type']))&&($GLOBALS['panel_time_type'] != 'actual'))
                            $component->add_msg(T_('Value is actual, not historical.'), 'NOT HISTORICAL');

                        switch($fcnParam['type']){
                            case 1: //1=real value (min,max)
                            case 3: //3=integer value (min,max)
                                if($component->min_value==-0x7FFFFFFFFFFFFFFF)
                                    $component->set_min_value($fcnParam['attributes'][0]);
                                else{
                                    if($component->min_value<$fcnParam['attributes'][0])
                                        $component->add_msg(T_('set_min_value() too small, should be ').$fcnParam['attributes'][0]);
                                }
                                if($component->max_value==0x7FFFFFFFFFFFFFFF)
                                    $component->set_max_value($fcnParam['attributes'][1]);
                                else{
                                    if($component->max_value>$fcnParam['attributes'][1])
                                        $component->add_msg(T_('set_max_value() too big, should be ').$fcnParam['attributes'][1]);
                                }
                                break;
                            case 2: //2=binary value (text0, text1)
                                $component->set_min_value(0);
                                $component->set_max_value(1);
                                break;
                            case 4: //4=selection (nsel, textsel1, textsel2,...)
                                if($component->min_value==-0x7FFFFFFFFFFFFFFF)
                                    $component->set_min_value(1);
                                else{
                                    if($component->min_value<1)
                                        $component->add_msg(T_('set_min_value() too small, should be 1'));
                                }
                                if($component->max_value==0x7FFFFFFFFFFFFFFF)
                                    $component->set_max_value($fcnParam['attributes'][0]);
                                else{
                                    if($component->max_value>$fcnParam['attributes'][0])
                                        $component->add_msg(T_('set_max_value() too big, should be ').$fcnParam['attributes'][0]);
                                }
                                break;

                            case 5: //5=IP address
                            case 6: //6=text string (max size)
                            default:
                        }

                    }else{
                        $component->add_msg('WRONG_PARAM_ID');
                        $component->can_set_value = FALSE;
                    }
                }else {
                    $component->add_msg('NO_DATA');
                }
            }
            if (($component->type==='variable')&&($GLOBALS['panel_time_type']=='history')){
                $variable = $var_ids_and_names[$component->dcu_id]['ids'][$component->var_id];
                if(!isset($variable['state'])){
                    $variable = get_one_var_value($component->dcu_id,
                        $component->var_id,
                        'all',
                        $GLOBALS['panel_time_type'],
                        $GLOBALS['panel_time_timestamp']);
                }

                if (isset($variable['value'])&&(isset($variable['state']))){
                    $var_ids_and_names[$component->dcu_id]['ids'][$component->var_id]['value'] = $variable['value'];
                    $var_ids_and_names[$component->dcu_id]['ids'][$component->var_id]['state'] = $variable['state'];
                    $var_ids_and_names[$component->dcu_id]['ids'][$component->var_id]['time'] = $variable['time'];

                    $component->set_value($variable['value'], $variable['state']);
                    $new_sampletime = round($variable['time']/1000);//in seconds

                    if (gettype($GLOBALS['panel_time_min_time']) == 'string'){
                        $GLOBALS['panel_time_min_time'] = $new_sampletime;
                        $GLOBALS['panel_time_oldest_value'] = "$component->dcu_id.$component->var_id";
                        $GLOBALS['panel_time_max_time'] = $new_sampletime;
                    }else{
                        if ($new_sampletime<$GLOBALS['panel_time_min_time']){
                            $GLOBALS['panel_time_min_time'] = $new_sampletime;
                            $GLOBALS['panel_time_oldest_value'] = "$component->dcu_id.$component->var_id";
                        }
                        if ($new_sampletime>$GLOBALS['panel_time_max_time'])
                            $GLOBALS['panel_time_max_time'] = $new_sampletime;
                    }
                }else{
                    //let's not check one variable more times
                    $var_ids_and_names[$component->dcu_id]['ids'][$component->var_id]['state'] = -1;
                }

            }
            if ($component->type==='rtudpio_server'){

                if (empty($server_list)){
                    $server_list = read_rtudpio();
                }
                $server_id = $component->var_id.'.'.$component->param_id;
                if (!empty($server_list[$server_id])){
                    $component->set_value($server_list[$server_id]['actualValue'], 1);
                    if ((isset($GLOBALS['panel_time_type']))&&($GLOBALS['panel_time_type'] != 'actual'))
                        $component->add_msg(T_('Value is actual, not historical.'), 'NOT HISTORICAL');
                }else{
                    $component->add_msg('WRONG_ID');
                }
            }
        }
    }

    if (gettype($GLOBALS['panel_time_min_time']) == 'string'){
    }else{
        $GLOBALS['panel_time_deviation_value'] =
            abs($GLOBALS['panel_time_timestamp'] -
            (($GLOBALS['panel_time_max_time'] + $GLOBALS['panel_time_min_time'])/2)); //petra: alebo 2000
        $GLOBALS['panel_time_dispersion_value'] =
            $GLOBALS['panel_time_max_time'] - $GLOBALS['panel_time_min_time']; //petra: /1000 nech je rovnake ako ajax_get_variables

        if($GLOBALS['panel_time_deviation_value']>(2*$GLOBALS['variable_refresh_time']/1000))
            $GLOBALS['panel_time_deviation_class'] = 'panel_red';
        if($GLOBALS['panel_time_deviation_value']>259200)
            $GLOBALS['panel_time_deviation_value'] = T_('>3days');
    }
}


function display_content(){
    global $dispvars;
    foreach ($dispvars as $component){
        $component->display_component();
    }
}