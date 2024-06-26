<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');

if(!check_security())die();

$data_or_file = filter_input(
    INPUT_POST,
    'response_type',
    FILTER_VALIDATE_REGEXP,
    array('options'=>array('regexp'=>"/^data|csv|excel$/"))
    );
if (empty($data_or_file)) {$data_or_file = 'data';}

$data_source = filter_input(
    INPUT_POST,
    'data_source',
    FILTER_VALIDATE_REGEXP,
    array('options'=>array('regexp'=>"/^all|archive$/"))
    );
if (empty($data_source)) {$data_source = 'archive';}

$time_frame = filter_input(INPUT_POST,'time_frame',FILTER_VALIDATE_INT);
if (empty($time_frame)) {$time_frame = 3000;}

$time_source = filter_input(
    INPUT_POST,
    'time_source',
    FILTER_VALIDATE_REGEXP,
    array('options'=>array('regexp'=>"/^actual|history$/"))
    );
if (empty($time_source)) {$time_source = 'actual';}

$history_timestamp = filter_input(INPUT_POST,'history_timestamp',FILTER_VALIDATE_INT);
if (empty($history_timestamp)) {$history_timestamp = time()-1000;}

if($time_source == 'actual') $history_timestamp = time() - $time_frame;

$ylabel = filter_input(
    INPUT_POST,
    'ylabel',
    FILTER_VALIDATE_REGEXP,
    array('options'=>array('regexp'=>"/^(a-z)*/"))
    );
if (empty($ylabel)) {$ylabel = 'Parameter unit';}

$count_multi_id = 0;
if(isset($_POST['multi_id'])){
    $multi_id = json_decode($_POST['multi_id']);
    $count_multi_id = count($multi_id);
}

$time_rounding = filter_input(INPUT_POST,'time_rounding',FILTER_VALIDATE_INT);
if (empty($time_rounding)) {$time_rounding = 0;}

$allsignals=array();
$alltimes=array();
$timevector = array();
$graphid = '';
$graph_data_array = array();
$graph_header_array = array('Date Time');
for($ig=0;$ig<$count_multi_id;$ig++){
    $dcu_id = $multi_id[$ig][0];
    $var_id = $multi_id[$ig][1];
    $var_name = $multi_id[$ig][2];
    $graphid .= "__$dcu_id"."_$var_id";
    //read signal
    $res = get_var_graph_data($dcu_id, $var_id, $data_source,
        $time_frame, $time_source, $history_timestamp, $time_rounding );

    //$alllabels[$ig] = "$dcu_id".".$var_id"." $var_name";
    $allsignals[$ig] = $res['signals'];
    $alltimes[$ig] = $res['times'];
    $iterator[$ig]=0;
    $graph_header_array[] = "$dcu_id".".$var_id"." $var_name";
    $count_allsignals = count($allsignals[$ig]);
    if ($count_allsignals==0){
        $average_text[$ig] = "Not calculated, no data.";
        $delta_text[$ig] = "Not calculated, no data.";
    }else{
        $first=$allsignals[$ig][0];
        $last=$allsignals[$ig][$count_allsignals-1];
        $delta_text[$ig] = $first - $last;
        if ($count_allsignals>100000){
            $average_text[$ig] = "Average not calculated, number of signals over 100000.";
        }else{
            $average_text[$ig] = array_sum($allsignals[$ig])/$count_allsignals;
        }
    }
}

while(1){
    $not_end = $count_multi_id;
    //vyber existujuci prvok, kludne aj posledny
    for($ig=0;$ig<$count_multi_id;$ig++){
        if(isset($alltimes[$ig][$iterator[$ig]])){
            $min = $alltimes[$ig][$iterator[$ig]];
        }else{
            $not_end--;
        }
    }
    if($not_end==0)break;
    //porovnaj a najdi najmensi cas
    for($ig=0;$ig<$count_multi_id;$ig++){
        if(isset($alltimes[$ig][$iterator[$ig]])){
            if($alltimes[$ig][$iterator[$ig]]<$min)
                $min = $alltimes[$ig][$iterator[$ig]];
        }
    }

    //pripoj pole hodnot s casom min
    //https://dygraphs.com/date-formats.html
    $graph_row_array = array(date("Y-m-d\TH:i:s.",$min/1000).sprintf("%03d", $min%1000));
    for($ig=0;$ig<$count_multi_id;$ig++){
        if((isset($alltimes[$ig][$iterator[$ig]]))
            &&($alltimes[$ig][$iterator[$ig]]==$min)){
            $graph_row_array[]=$allsignals[$ig][$iterator[$ig]];
            $iterator[$ig]++;
        }else{
            $graph_row_array[]='';
        }
    }
    $graph_data_array[] = $graph_row_array;
}
$statistics = ';<table class="stats"><tr><td>'.T_('Stats').'</td><td>First-last</td><td>'.T_('Average').'</td></tr>';
for($ig=0;$ig<$count_multi_id;$ig++){
    $statistics .= "<tr><td>".$graph_header_array[$ig+1].'</td><td>'.$delta_text[$ig]."</td><td>$average_text[$ig]</td></tr>";
}
$statistics .= '</table>';

    switch($data_or_file){
        case 'csv':
            $csv = implode(",",$graph_header_array)."\n";
            foreach($graph_data_array as $graph_row_array){
                $csv.=implode(",",$graph_row_array)."\n";
            }
            $allfilename = './'.$GLOBALS['datafiledir'] . "/$graphid.csv";
            $success = file_put_contents($allfilename, $csv);
            print($allfilename);
            break;
        case 'excel':
            define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
            require_once dirname(__FILE__) . '/classes/PHPExcel.php';

            $objPHPExcel = new PHPExcel();
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $objWorksheet->fromArray($graph_header_array,'','A1');
            $objWorksheet->fromArray($graph_data_array,'','A2');
            $objWorksheet->getColumnDimension('A')->setWidth(25);
            $objWorksheet->getStyle('A')->getNumberFormat()->setFormatCode("dd.mm.yyyy hh:mm:ss.00");

            $title = new PHPExcel_Chart_Title(T_('Value evolution in graph'));
            $dataSeriesLabels = array();
            $dataSeriesValues = array();
            $col='A';
            $rows = count($graph_data_array);
            for($ig=0;$ig<$count_multi_id;$ig++){
                $col=chr(ord($col) + 1);
                $objWorksheet->getColumnDimension($col)->setWidth(15);
                $dataSeriesLabels[$ig] = new PHPExcel_Chart_DataSeriesValues(
                    'String', 'Worksheet!$'.$col.'$1', NULL, 1);
                $dataSeriesValues[$ig] = new PHPExcel_Chart_DataSeriesValues(
                    'Number', 'Worksheet!$'.$col.'$2:$'.$col.'$'.($rows+1), NULL, $rows);
            }
            $xAxisTickValues = array(
                new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$A$2:$A$'.($rows+1), NULL, $rows),    //    Q1 to Q4
            );
            $series = new PHPExcel_Chart_DataSeries(
                PHPExcel_Chart_DataSeries::TYPE_LINECHART,  // plotType
                NULL,  // plotGrouping (Scatter charts don't have any grouping) PHPExcel_Chart_DataSeries::GROUPING_STANDARD
                range(0, count($dataSeriesValues)-1), // plotOrder
                $dataSeriesLabels,  // plotLabel
                $xAxisTickValues,  // plotCategory
                $dataSeriesValues,  // plotValues
                NULL,  // plotDirection
                NULL,  // smooth line
                PHPExcel_Chart_DataSeries::STYLE_LINEMARKER  // plotStyle
            );
            $plotArea = new PHPExcel_Chart_PlotArea(NULL, array($series));
            $legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_TOPRIGHT, NULL, false);
            $yAxisLabel = new PHPExcel_Chart_Title($ylabel);
            //    Create the chart
            $chart = new PHPExcel_Chart(
                'chart1',        // name
                $title,            // title
                $legend,        // legend
                $plotArea,        // plotArea
                true,            // plotVisibleOnly
                0,                // displayBlanksAs
                NULL,            // xAxisLabel
                $yAxisLabel        // yAxisLabel
            );

            //    Set the position where the chart should appear in the worksheet
            $col=chr(ord($col) + 2);
            $colN=chr(ord($col) + 15);
            $chart->setTopLeftPosition($col.'1');
            $chart->setBottomRightPosition($colN.'30');
            //    Add the chart to the worksheet
            $objWorksheet->addChart($chart);
            $allfilename = './'.$GLOBALS['datafiledir'] . "/$graphid.xlsx";
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');//'Excel2007'
            $objWriter->setIncludeCharts(TRUE);
            $objWriter->save($allfilename);
            print($allfilename);
            break;
        default:
            $csv = '';
            foreach($graph_data_array as $graph_row_array){
                $csv.=implode(",",$graph_row_array)."\n";
            }
            print($csv.$statistics);
            /*
            $output = array(
                'data' => $csv,
                'errors'=>$GLOBALS['errors'],
                'statistics' => 'blabla',
            );
            print(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));
             *
             */
    }
?>