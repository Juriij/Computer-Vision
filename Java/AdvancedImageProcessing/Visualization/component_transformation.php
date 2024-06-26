<?php
//TRANSFORMACIA ZO STAREJ VIZUALIZACIE NA NOVU  // backward compatibility
$new_dispvars = array();
$initialization_component = new component_default();
global $dispvars;
global $dispvar_init;

if (isset($dispvar_init)){
    if (isset($dispvar_init->font)) $initialization_component->set_font_family($dispvar_init->font);
    if (isset($dispvar_init->fontsize))
        $initialization_component->set_font_size((int)preg_replace("/[^0-9]/", "", $dispvar_init->fontsize));
    if (isset($dispvar_init->unitfontsize))
        $initialization_component->set_unit_font_size((int)preg_replace("/[^0-9]/", "", $dispvar_init->unitfontsize));
    if (isset($dispvar_init->textcolor)) $initialization_component->set_font_color($dispvar_init->textcolor);
    if (isset($dispvar_init->element_backgroundcolor))
        $initialization_component->set_font_background($dispvar_init->element_backgroundcolor);
}
if(isset($g_unit_id)) $initialization_component->set_dcu_id($g_unit_id);
if(isset($real_display)) $initialization_component->set_number_format($real_display);
if(isset($timetravel_off)) $GLOBALS['show_panel_time'] = !$timetravel_off;


foreach($dispvars as $dispvar){
    if (!is_array($dispvar)){
        if (is_object($dispvar)) $new_dispvars[] = $dispvar;
        continue;
    }

    switch ($dispvar['disptype']){
        case 1: $component = new component_number($initialization_component);break;
        case 2: $component = new component_binary($initialization_component);
            if (isset($dispvar['zero'])) $component->set_zero_text($dispvar['zero']);
            if (isset($dispvar['one'])) $component->set_one_text($dispvar['one']);
            break;
        case 3: $component = new component_binary($initialization_component);
            if (isset($dispvar['zero'])) $component->set_zero_image($dispvar['zero']);
            if (isset($dispvar['one'])) $component->set_one_image($dispvar['one']);
            break;
        case 4: $component = new component_binary($initialization_component);
            $component->set_onclick('autoswitch');
            if (isset($dispvar['zero'])) $component->set_zero_image($dispvar['zero']);
            if (isset($dispvar['one'])) $component->set_one_image($dispvar['one']);
            break;
        case 5:
            $component = new component_animation($initialization_component);break;
        case 6:
            // idealne by bolo previest na interval
            $GLOBALS['errors']['general'][] = T_('Sorry, disp_realvalinlimitsimg_template transformation not programmed.');
            $component = new component_animation($initialization_component);break;

        case 7: $component = new component_interval($initialization_component);break;
        case 50: $component = new component_batchserver($initialization_component);break;
        case 100: $component = new component_text($initialization_component, 'function');break;
        case 201:
            $component = new component_graph($initialization_component);break;
        case 200:case 300: $component = new component_text($initialization_component);break;
        case 310: $component = new component_image($initialization_component);break;
        case 401:
            $GLOBALS['errors']['general'][] = T_('Sorry, disp_actionbutton_template transformation not programmed.');
            $component = new component_text($initialization_component);break;
        case 501: $component = new component_number($initialization_component, 'rtudpio_server');break;
        case 502: $component = new component_binary($initialization_component, 'rtudpio_server');
            $component->set_onclick('autoswitch');
            if (isset($dispvar['zero'])) $component->set_zero_image($dispvar['zero']);
            if (isset($dispvar['one'])) $component->set_one_image($dispvar['one']);
            break;
        case 503:
            $component = new component_animation($initialization_component, 'rtudpio_server');break;
        case 504:
        case 505:
            $component = new component_interval($initialization_component, 'rtudpio_server');
            break;
        default:
            $myfile = fopen("aaapreklad.txt", "a");
            fwrite($myfile, "\nzabudnuty disptype:");
            fwrite($myfile, print_r($dispvar['disptype'], true));
            fwrite($myfile, print_r($dispvar, true));
            fclose($myfile);
    }


    $new_dispvars[] = $component;

    if (isset($dispvar['x'])) $component->set_left($dispvar['x']);
    if (isset($dispvar['y'])) $component->set_top($dispvar['y']);
    if (isset($dispvar['unitid'])) $component->set_dcu_id($dispvar['unitid']);
    if ((isset($dispvar['id']))&&($dispvar['id']>=0)){
        if($dispvar['disptype']>=500){
            $rtudp_id = strval($dispvar['id']);
            $rtudp_ids = explode(".", $rtudp_id);
            $component->set_id($rtudp_ids[0]);
            if(!isset($rtudp_ids[1])) $rtudp_ids[1] = 0;
            $component->set_param_id($rtudp_ids[1]);
        }else{
            if (is_array($dispvar['id'])) $component->set_multi_id($dispvar['id']);
            else if($dispvar['id']>=0) $component->set_id($dispvar['id']);
        }
    }

    if (!empty($dispvar['name'])) $component->set_name($dispvar['name']);
    if ((!empty($dispvar['fcnparamid']))&&($dispvar['fcnparamid']>=0)){
       $component->set_param_id($dispvar['fcnparamid']-1);
       $component->type='function_param';
    }
    if (isset($dispvar['label'])) {
        if($dispvar['disptype']==7){
            foreach($dispvar['label'] as $interval){
                $component->add_interval_text($to=$interval[0], $text=$interval[1]);
                if (isset($interval[2]))
                    $component->set_interval_font_color($interval[2]);
                if (isset($interval[3]))
                    $component->set_interval_font_size((int)preg_replace("/[^0-9]/", "", $interval[3]));
                if (isset($interval[4]))
                    $component->set_interval_font_style($interval[4]);
            }
        }else
            $component->set_label($dispvar['label']);
    }
    if (!empty($dispvar['phunit'])) $component->set_unit($dispvar['phunit']);
    if (!empty($dispvar['ylabel'])) $component->set_unit($dispvar['ylabel']);
    $my_onclick = NULL;
    if (!empty($dispvar['panel'])) $my_onclick = $dispvar['panel'];
    if (!empty($dispvar['link'])) $my_onclick = $dispvar['link'];
        //[panel] => setvalue_7.7
        //[panel] => setvalue_1.0(4003.01)
        //[panel] => editvalue(4003.01)
        //[panel] => 4003.18
        //[panel] => slider_x_int(4003.01)
        //[panel] => online(0.01)
        //modifyrtudpio
    if (!empty($my_onclick)){
        $position = strpos($my_onclick,'?');
        if($position !== false){
            $file = substr($my_onclick, 0, $position);
            $params = explode('&', substr($my_onclick, $position+1));
            foreach($params as $param){
                $param = explode('=', $param);
                switch($param[0]){
                    case 'faceplate_width':
                        $component->set_faceplate_width($param[1]);
                        break;
                    case 'faceplate_height':
                        $component->set_faceplate_height($param[1]);
                        break;
                    default:
                        if($file=='value_graph.php'){
                            switch($param[0]){
                                case 'VP1':
                                    $second_param = $param[1];
                                    break;
                                case 'VP2':
                                    $third_param = $param[1];
                                    $component->add_GET_parameter('label',$param[1]);
                                    break;
                                case 'VP3':
                                    $ylabel = $param[1];
                                    break;
                                case 'VP4':
                                    $first_param = $param[1];
                                    break;
                                default:
                            }
                        }else{
                            $component->add_GET_parameter($param[0],$param[1]);
                        }
                }
            }
        }else{
            $file = $my_onclick;
        }

        switch ($file) {
            //case (preg_match('/value_graph.*/', $my_onclick) ? true : false) :
            case 'signalpanel.php':
            case 'value_graph.php':
                $component->set_onclick('variable_graph');
                if(empty($third_param))$third_param='';
                if((!empty($first_param))&&(!empty($second_param)))
                    $component->onclick_multi_id = array(array($first_param,$second_param,$third_param));

                break;
            case 'cvarpanel_ovrd.php' :
                $component->set_onclick('panel_var_ovrd');
                break;
            case 'cvarpanel.php' :
                $component->set_onclick('panel_var_complete');
                break;
            case 'cvarpanel_editvalue' :
            case 'cvarpanel_editvalue.php' :
                $component->set_onclick('editvalue');
                break;
            case 'cfunpanel.php' :
                $component->set_onclick('panel_function');
                break;
            case 'cfunpanel_time_program.php' :
                $component->set_onclick('panel_function_schedule');
                break;
            default:
                $component->set_onclick($file);
        }
    }

    if (isset($dispvar['textcolor'])) $component->set_font_color($dispvar['textcolor']);
    if (!empty($dispvar['fontsize'])) $component->set_font_size((int)preg_replace("/[^0-9]/", "", $dispvar['fontsize']));
    if (isset($dispvar['mininput'])) $component->set_min_value($dispvar['mininput']); //must precede minvalue
    if (isset($dispvar['maxinput'])) $component->set_max_value($dispvar['maxinput']); //must precede maxvalue
    if (isset($dispvar['minvalue'])) $component->set_min_value($dispvar['minvalue']);
    if (isset($dispvar['maxvalue'])) $component->set_max_value($dispvar['maxvalue']);

    if (isset($dispvar['img'])) $component->set_image($dispvar['img']);
    if ((isset($dispvar['framewidth']))&&($dispvar['framewidth']!=-1))
        //$component->set_width($dispvar['framewidth']);
        $GLOBALS['errors']['general'][] = T_('"Framewidth" not needed. Use width instead.');
    if ((isset($dispvar['frameheight']))&&($dispvar['frameheight']!=-1))
        //$component->set_height($dispvar['frameheight']);
        $GLOBALS['errors']['general'][] = T_('"Frameheight" not needed. Use height instead.');
    if ((isset($dispvar['width']))&&($dispvar['width']!=-1)) {
        switch ($dispvar['disptype']){
            case 3:
            case 4:
            case 502:
            case 504:
                $component->set_zero_image_width($dispvar['width']);
                $component->set_one_image_width($dispvar['width']);
                break;
            default:
                $component->set_width($dispvar['width']);
        }
    }

    if ((isset($dispvar['height']))&&($dispvar['height']!=-1)) {
        switch ($dispvar['disptype']){
            case 3:
            case 4:
                $component->set_zero_image_height($dispvar['height']);
                $component->set_one_image_height($dispvar['height']);
                break;
            default:
                $component->set_height($dispvar['height']);
        }
    }
    if (isset($dispvar['nrows'])) $component->set_nrows($dispvar['nrows']);
    if (isset($dispvar['ncolumns'])) $component->set_ncolumns($dispvar['ncolumns']);

    if (isset($dispvar['numform'])) $component->set_number_format($dispvar['numform']);
    if (isset($dispvar['overwrite'])) $component->set_overwrite($dispvar['overwrite']);

    if (isset($dispvar['graphid']))
        $GLOBALS['errors']['general'][] = T_('"Graphid" not needed.');
    if (isset($dispvar['archiveonly'])) $component->set_archiveonly($dispvar['archiveonly']);
    if (isset($dispvar['timespan'])) $component->set_time_frame($dispvar['timespan']);

    if (isset($dispvar['msg_icon_OVERRIDE'])) $component->set_msg_icon('OVERRIDE', $dispvar['msg_icon_OVERRIDE']);
    if (isset($dispvar['msg_icon_ALARM'])) $component->set_msg_icon('ALARM', $dispvar['msg_icon_ALARM']);
    if (isset($dispvar['msg_icon_MISSED_CONNECTIONS']))
        $component->set_msg_icon('MISSED_CONNECTIONS', $dispvar['msg_icon_MISSED_CONNECTIONS']);
    if (isset($dispvar['msg_icon_OVERWRITE'])) $component->set_msg_icon('OVERWRITE', $dispvar['msg_icon_OVERWRITE']);
    if (isset($dispvar['msg_icon_VALUE_BELOW_MIN']))
        $component->set_msg_icon('VALUE_BELOW_MIN', $dispvar['msg_icon_VALUE_BELOW_MIN']);
    if (isset($dispvar['msg_icon_VALUE_ABOVE_MAX']))
        $component->set_msg_icon('VALUE_ABOVE_MAX', $dispvar['msg_icon_VALUE_ABOVE_MAX']);
    if (isset($dispvar['msg_icon_WRONG_DCU'])) $component->set_msg_icon('WRONG_DCU', $dispvar['msg_icon_WRONG_DCU']);
    if (isset($dispvar['msg_icon_WRONG_ID'])) $component->set_msg_icon('WRONG_ID', $dispvar['msg_icon_WRONG_ID']);
    if (isset($dispvar['msg_icon_WRONG_NAME'])) $component->set_msg_icon('WRONG_NAME', $dispvar['msg_icon_WRONG_NAME']);
    if (isset($dispvar['msg_icon_WRONG_PARAM_ID']))
        $component->set_msg_icon('WRONG PARAM ID', $dispvar['msg_icon_WRONG_PARAM_ID']);
    if (isset($dispvar['msg_icon_NO_DATA'])) $component->set_msg_icon('NO_DATA', $dispvar['msg_icon_NO_DATA']);
    if (isset($dispvar['msg_icon_default'])) $component->set_msg_icon('default', $dispvar['msg_icon_default']);

    if (isset($dispvar['additional_refresh'])) $component->set_additional_refresh($dispvar['additional_refresh']);
    //state, minx, miny, maxx, maxy, action,  positioning
    //widthmin/max, heightmin/max, sampletime,
    //graphtype: 0=individual graphs for each signal, 1=all signals in one graph
}
$dispvars = $new_dispvars;



?>