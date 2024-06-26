<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');

if(!check_security())die();
$dbLink_Config = dcu_opendb('Config');

function do_it(){
    $myoption = my_required_filter_input('myoption',FILTER_DEFAULT);
    $procedure_id = my_required_filter_input('procedure_id',FILTER_VALIDATE_INT);
    $result_structure = my_required_filter_input('result_structure',FILTER_DEFAULT);
    $myoption = explode('.',$myoption);
    $dcu_id = $myoption[0];
    $var_id = $myoption[1];
    $rows = get_ass_result2($procedure_id, $dcu_id, $var_id);

    $result_structure = explode(',',$result_structure);
    $result = array();
    $i=0;
    foreach($result_structure as $result_struct){
        $pos = strpos($result_struct,':');
        if($pos!==false){
            $key = substr($result_struct, 0, $pos);
            $result[$i] = array('label'=>$key, 'format'=>array(), 'values'=>array());//MATRIX_$i
            $rest = substr($result_struct, $pos+1);
            $pos = strpos($rest,':');
            if($pos!==false){
                $format = substr($rest, 0, $pos);
                $result[$i]['format'] = trim($format);
            }
            $i++;
        }
    }

    $matrix_formats = array();
    foreach($rows as $row){
        $matrix_values = array();
        $result_params = array();
        $result_row = explode(';',$row['result']);
        foreach($result_row as $result_part){
            $pos = strpos($result_part,':');
            if($pos!==false){
                $key = substr($result_part, 0, $pos);
                $value = substr($result_part, $pos+1);
                if(strpos($key,'MATRIX')!==false){
                    $index = explode('_',$key);
                    $index = $index[1];
                    $result[$index]['values'][$row['registerTime']] = trim($value);
                }
                if(strpos($key,'FORMATS')!==false)  //todo petra hynek: can be removed
                    $matrix_formats[$row['registerTime']] = explode(',',$value);
            }
        }
    }

    $get_params = 'file=content/dynamic/variable_graph.php';
    $get_params .=  '&amp;multi_id="+this.dataset.onclick_multi_id+"';
    $get_params .=  "&amp;label=DCU $dcu_id<br />Graph for variable $var_id";
    $get_params .=  '&amp;ylabel=units';
    $get_params .=  '&amp;time_frame=1000';
    $title=T_('Value evolution in graph');
    $onclick_multi_id = "[[$dcu_id, $var_id, \"$var_id\"]]";
    $text = T_('Show signal evolution');

    $output = "<a
                   title ='$title'
                   data-onclick_multi_id ='$onclick_multi_id'
                   onclick='window.open(\"index.php?$get_params\",
                   \"_blank\",
                   \"width=1050px,height=750px,left=20,top=20,toolbar=1,resizable=1,location=no,menubar=no\")'>
                   $text

                    &nbsp;&nbsp;&nbsp;<i class='fa fa-signal fa-2x' aria-hidden='true'></i>
                </a>";
    $output .= '<table class="sorttable">';
    foreach($result as $index=>$result_matrix){
        $output .= '<tr>';
        $output .= '<td>'.$result_matrix['label']
            . '<br /><span onclick="create_ass_graph(this)" style="cursor:pointer">CREATE GRAPH</span>'
            . '<span onclick="show_ass_graph(this)" style="display:none;cursor:pointer;">SHOW/HIDE GRAPH</span></td>';
        $output .= '<td><div style="max-height: 70px;overflow: scroll;">';
        foreach($result_matrix['values'] as $time=>$value){

            $output .= "$time, &nbsp; &nbsp; ";
            //$format = $matrix_formats[$time][$index]; //if format is defined in each message row
            $format = $result_matrix['format']; //if format is defined in result_structure
            if(strpos($format,'REAL')!==false){
                $value = substr($value,1,-1);  //remove brackets []
                $output .= $value;
            }
            elseif(strpos($format,'TIMESTAMP')!==false){
                $value = substr($value,1,-1);  //remove brackets []
                $value=intval($value);
                $output .= date($GLOBALS['date_format']." H:i:s", $value/1000);
            }
            elseif(strpos($format,'POLYNOMIAL')!==false){
                $value = json_decode($value);
                $format_array = explode('_',$format);
                $format_variable = $format_array[1];
                $exp = 0;
                $polynom = array();
                $output .= "<span style='line-height: 1.4;'>";
                foreach($value as $coef){
                    if($coef<>0)
                    $polynom[] = "$coef*$format_variable<sup>$exp</sup>";
                    $exp++;
                }
                $output .= implode(' + ',$polynom);
                $output .= "</span>";
            }
            elseif(strpos($format,'TRANSFER_FUNCTION')!==false){

                $matrix_rows = json_decode($value);
                $format_array = explode('_',$format);
                $format_variable = $format_array[2];

                $output .= "<span style='line-height: 1.4;'>";
                $menovatel = array();
                $exp = 0;
                foreach($matrix_rows[0] as $coef){
                    if($coef<>0)
                    $menovatel[] = "$coef*$format_variable<sup>$exp</sup>";
                    $exp++;
                }

                $citatel1 = array();
                $exp = 0;
                foreach($matrix_rows[1] as $coef){
                    if($coef<>0)
                    $citatel1[] = "$coef*$format_variable<sup>$exp</sup>";
                    $exp++;
                }

                $citatel2 = array();
                $exp = 0;
                foreach($matrix_rows[2] as $coef){
                    if($coef<>0)
                    $citatel2[] = "$coef*$format_variable<sup>$exp</sup>";
                    $exp++;
                }

                $output .= "(".implode(' + ',$citatel1) .") / (".implode(' + ',$menovatel).")";
                if(!empty($citatel2))
                    $output .= " + (".implode(' + ',$citatel2) .") / (".implode(' + ',$menovatel).")";
                $output .= "</span>";
            }
            $output .= "<br />\n";
        }
        $output .= '</div></td>';
        $output .= '</tr>';
        $output .= '<tr><td colspan=2><div></div> </td></tr>';
        $output .= '<tr><td colspan=2> </td></tr>';
    }
    $output .= '</table>';
    return array('result'=>$output);
}

$output = do_it();
$output = array_merge($output, array('errors'=>$GLOBALS['errors']));
//$output = $output + array('errors'=>$GLOBALS['errors']);  nedela: otestovat
print(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));