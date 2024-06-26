<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');
include('./classes/components/component_default.php');

if(!check_security())die();
$dcuConns = get_dcu_conns();

$output = array(
    'deviation' => '???',
    'deviation_class' => '',
    'dispersion' => '???',
    'oldest' => '???',
);
$min_time = '???';
$max_time = '???';
$oldest_var = '?';

$nrows = FALSE;
$ncolumns = FALSE;
$width = FALSE;
$height = FALSE;
$max_value = FALSE;
$min_value = FALSE;
$image = FALSE;

$file_name = './actual_values.txt';
$array_values = NULL;

$json_values = @file_get_contents('./actual_values.txt'); //petra: premenovat subor na .json ???
if($json_values===FALSE){
    $GLOBALS['errors']['A']['NOT_FOUND'] = sprintf(T_('File %s not found.'),$file_name);
}else{
    /* DCUid - control variable id - [0]: actual value, [1]: actual state, [2]: sample time */
    $array_values = json_decode($json_values, TRUE);
}

if(isset($array_values))
foreach ($_POST as $key=>$value){

    $component = create_component_from_dataset($key, $value);

    if((isset($component->dcu_id)) && (isset($component->var_id))){
        $dcu_id = $component->dcu_id;
        $var_id = $component->var_id;

        if((!empty($array_values['dcus']["DCU$dcu_id"]))
            &&(!empty($array_values['dcus']["DCU$dcu_id"][$var_id]))){

            $variable = $array_values['dcus']["DCU$dcu_id"][$var_id];
            $new_value = $variable[0];
            $new_state = $variable[1];
            $new_sampletime = $variable[2];

            $component->set_value($new_value, $new_state);

            if (gettype($min_time) == 'string'){
                $min_time = $new_sampletime;
                $oldest_var = "$dcu_id.$var_id";
                $max_time = $new_sampletime;
            }else{
                if ($new_sampletime<$min_time){
                    $min_time = $new_sampletime;
                    $oldest_var = "$dcu_id.$var_id";
                }
                if ($new_sampletime>$max_time){
                    $max_time = $new_sampletime;
                }
            }
        }else{
            //$component->add_msg('NO_DATA'); //will detect NO DATA in get_display_value()
        }
    }
    $output[$key] = $component->get_display_value();
    $output[$key] .= $component->get_messages();

}

if (gettype($min_time) != 'string'){
    $output['deviation'] = abs($GLOBALS['now_time'] - (($max_time + $min_time)/2000));
    if($output['deviation']>(2*$GLOBALS['variable_refresh_time']/1000))
        $output['deviation_class'] = 'panel_red';
    $output['deviation'] = sprintf('%.3f', $output['deviation']);
    if($output['deviation']>259200)
        $output['deviation'] = T_('>3days');

    $output['dispersion'] = ($max_time - $min_time)/1000;
    $output['dispersion'] = sprintf('%.3f', $output['dispersion']);
    $output['oldest'] = $oldest_var;

}
filter_errors();
$output = $output + array('errors'=>$GLOBALS['errors']);
print(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));
?>