<?php
session_start();
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');
include('./classes/UCC/ucc_default.php');


$db_name = 'test'; //ostra: DB  testovacia: test
// Connect to DB
$dbLink_DB = dcu_opendb($db_name);
$dbLink_Config = dcu_opendb('Config');

function vypis_hodnoty(){
    global $test_get;
    global $test_after;
    $test_index = 0;
?>
<h1><?= $test_get->request['userCommad'].' '.$test_get->request['paramCode'] ?></h1>
<table border="1" width="100%">
    <tr>
        <th>
            what is it
        </th>
        <th>
            bytes (bites)
        </th>
        <th>
            hex
        </th>
        <th>
            integer
        </th>
        <th>
            hex after
        </th>
        <th>
            integer after
        </th>
    </tr>

<?php
foreach($test_get->dsa as $key=>$value){
    if ($value['data_type']=='string'){
        $text_size = n_bytes_to_int($test_get->response['resData'], $test_index, 2);
        $value['no_bytes'] = 2 + $text_size;
    }

    $array_size = 1;
    if (isset($value['array_size'])) $array_size = $value['array_size'];
    $all_bytes = $value['no_bytes'] * $array_size;

    echo '<tr>';
        echo '<td>';
            echo $key;
        echo '</td>';
        echo '<td>';
            echo $all_bytes.' bytes';
        echo '</td>';
        echo '<td style="max-width:600px;word-wrap:break-word;">';
            $substr = substr($test_get->response['resData'], $test_index, $all_bytes);
            echo bin2hex($substr);
            if (isset($value['bites'])){
                echo ' binary: ';
                for($i=0;$i<$value['no_bytes'];$i++){
                    //echo ' binary:'.decbin(ord($substr[$i])) ;
                    printf(' %08b ', ord($substr[$i]) );
                }
            }
        echo '</td>';
        echo '<td>';
        if (isset($value['bites'])){
            foreach($value['bites'] as $key2=>$value2){
                echo $key2.' = '.$value2['value'].'<br />';
            }
        }else{
            switch ($value['data_type']){
                case 'integer':
                case 'string':
                case 'IP':
                    echo $value['value'];
                    break;
                case 'chars_array':
                case 'integer_array':
                case 'string_array':
                case 'calculated':
                case 'time':
                case 'fcnParams':
                default:
                    echo '<pre>';
                    var_dump($value['value']);
                    echo '</pre>';
                    break;
            }
        }

        echo '</td>';
        echo '<td style="max-width:600px;word-wrap:break-word;">';  //hex after
            $substr = substr($test_after->response['resData'], $test_index, $all_bytes);
            echo bin2hex($substr);
            if (isset($value['bites'])){
                echo ' binary: ';
                for($i=0;$i<$value['no_bytes'];$i++){
                    //echo ' binary:'.decbin(ord($substr[$i])) ;
                    printf(' %08b ', ord($substr[$i]) );
                }
            }
        echo '</td>';
        echo '<td>';  //integer after
        if (isset($test_after->dsa[$key]['bites'])){
            foreach($test_after->dsa[$key]['bites'] as $key2=>$value2){
                echo $key2.' = '.$value2['value'].'<br />';
            }
        }else{
            switch ($test_after->dsa[$key]['data_type']){
                case 'integer':
                case 'string':
                case 'IP':
                    echo $test_after->dsa[$key]['value'];
                    break;
                case 'chars_array':
                case 'integer_array':
                case 'string_array':
                case 'calculated':
                case 'time':
                case 'fcnParams':
                default:
                    echo '<pre>';
                    var_dump($test_after->dsa[$key]['value']);
                    echo '</pre>';
                    break;
            }
        }

        echo '</td>';
    echo '</tr>';

    $test_index += $all_bytes;
}

?>

    <tr>
        <td>
            next bytes ...
        </td>
        <td>

        </td>
        <td>
            <?= bin2hex(substr($test_get->response['resData'], $test_index)) ?>
        </td>
        <td>
        </td>
        <td>
            <?= substr($test_get->response['resData'], $test_index) ?>
        </td>
    </tr>
</table>
<?php
}
    $override_ucc = new ucc_var_override(21);
    $override_ucc->request['paramCode'] = (12  << 16) | 10000;  //parameters (FLASH)

    global $dsa_var_modify;
    $override_ucc->dsa = $dsa_var_modify;

    //get from post
    $override_ucc->dsa['varOpt_part3']['bites']['format']['value'] = 1;
    $override_ucc->dsa['varOvrOpt_part1']['bites']['type']['value'] = 1;
    $override_ucc->dsa['varOvrOpt_part1']['bites']['operation']['value'] =1;
    $override_ucc->dsa['varOvrOpt_part1']['bites']['seclevel']['value'] =15;
    $override_ucc->dsa['varOvrVal']['value'] =15;
    $override_ucc->dsa['varOvrTim']['value'] =
        $override_ucc->dsa['varOvrTim']['constraints']['min'];
    $override_ucc->dsa['varOvrTimStop']['value'] =
        $override_ucc->dsa['varOvrTimStop']['constraints']['max'];

    $success = 1;
    $override_ucc->build_reqData();

    $override_ucc->register_event($success);
    echo "<pre>";
    var_dump($GLOBALS['errors']);
    echo "</pre>";


die('KONIEC');
/*
// READ SET READ UNIT TIME START
$test_get = new ucc_unit_get();
$test_get->request['paramCode'] = 1000000;
$test_get->test_get_res('read control unit parameters00', $db_name);
$test_get->translate_response();

global $dsa_unit_param;
$test_set = new ucc_unit_set();
$test_set->request['paramCode'] = 1000000;
$test_set->dsa = $dsa_unit_param;

$test_set->dsa['unit_param_cAppIntRunCode']['value'] = $test_get->dsa['unit_param_cAppIntRunCode']['value'];

echo "<h1>$test_set->request['userCommad'] $test_set->request['paramCode'] </h1>";
echo '<table border="1">';
$test_set->build_reqData_1000023_25();
echo "reqdata: ".bin2hex($test_set->request['reqData'])."<br />";
if($test_set->building_reqData_error === 0){
    echo 'WAIT -------------SETTING VALUES ------------------WAIT .............<br />';

    //$test_set->ucc_call();
    usleep(100000);
    echo 'WAIT -------------STILL SETTING VALUES ------------------WAIT .............<br />';
    usleep(100000);
}else{
        echo "<pre>";
        var_dump($GLOBALS['errors']);
        echo "</pre>";
}
echo '</table>';
/*
$test_after = new ucc_unit_get();
$test_after->request['paramCode'] = 1000015;
$test_after->test_get_res('read control unit parameters15', $db_name);
$test_after->translate_response();

vypis_hodnoty();
// READ SET READ UNIT TIME END
$test_get->calculate_front_end();
    echo "<pre>";
    var_dump($test_get->dsa);
    echo "</pre>";
/*

// READ SET READ VARIABLE START
$test_get = new ucc_var_read();
$test_get->request['paramCode'] = (13  << 16) | 10000;  // FLASH
$test_get->test_get_res('read control variable0', $db_name);
$test_get->translate_response();
*/
global $dsa_var_modify;
$dsa_var_modify['histrec']['value'] = -8;
$dsa_var_modify['varOpt_part3']['bites']['format']['value'] = 3;
$dsa_var_modify['varOpt_part3']['bites']['nalarm']['value'] = 1;
$dsa_var_modify['varOpt_part4']['bites']['readseclev']['value'] = 0xF;
$dsa_var_modify['varOpt_part4']['bites']['modifseclev']['value'] = 0xF;
$dsa_var_modify['monit']['value'] = 3;
$dsa_var_modify['adelay']['value'] = 3;
$dsa_var_modify['varOvrOpt_part1']['bites']['type']['value'] = 3;
$dsa_var_modify['varOvrOpt_part1']['bites']['operation']['value'] = 4;
$dsa_var_modify['varOvrOpt_part1']['bites']['seclevel']['value'] = 0xF;
$dsa_var_modify['varOvrOpt_part1']['bites']['nothing']['value'] = 0xa;
$dsa_var_modify['varOvrOpt_port']['value'] = 5;
$dsa_var_modify['varOvrVal']['value'] = 7;
$dsa_var_modify['varOvrTim']['value'] = 0;
$dsa_var_modify['varOvrTimStop']['value'] = 0xffffffff;
$dsa_var_modify['varInit']['value'] = 7;//0xFFFFFFFFFFFFFFFF;
$dsa_var_modify['pvarVar']['value'] = 0x341d2000;
$dsa_var_modify['varDesc_addr']['value'] = 0x0b03;
$dsa_var_modify['varDesc_size']['value'] = 0x3800;
$dsa_var_modify['varAlarm']['value'][0] = hex2bin('02fe00000000000000000000');

$test_set = new ucc_var_modify();
//$test_set = new ucc_var_override();
$test_set->request['paramCode'] = (12  << 16) | 10000;  //parameters (FLASH)
$test_set->dsa = $dsa_var_modify;
echo "<h1>$test_set->command $test_set->request['paramCode'] </h1>";
echo '<table border="1">';
$test_set->build_reqData();
echo bin2hex($test_set->request['reqData']);
if($test_set->building_reqData_error === 0){
    echo 'WAIT -------------SETTING VALUES ------------------WAIT .............<br />';

    //$test_set->ucc_call();
    usleep(100000);
    echo 'WAIT -------------STILL SETTING VALUES ------------------WAIT .............<br />';
    usleep(100000);
}else{
        echo "<pre>";
        var_dump($GLOBALS['errors']);
        echo "</pre>";
}
echo '</table>';
/*
$test_after = new ucc_var_read();
$test_after->request['paramCode'] = (13  << 16) | 10000;  // FLASH
$test_after->test_get_res('read control variable0', $db_name);
$test_after->translate_response();
vypis_hodnoty();
// READ SET READ VARIABLE END


/*
$test_get = new ucc_var_read();
$test_get->request['paramCode'] = (13  << 16) | 10001;  // RAM
$test_get->test_get_res('read control variable1', $db_name);
$test_get->translate_response(1);
vypis_hodnoty();

$test_get = new ucc_func_get();
$test_get->request['paramCode'] = 60;
$test_get->test_get_res('get control function parameters', $db_name);
$test_get->translate_response();
vypis_hodnoty();

*/
?>
