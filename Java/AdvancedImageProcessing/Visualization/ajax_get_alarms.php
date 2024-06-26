<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');
include('./classes/components/component_default.php');
include ('./'.$GLOBALS['user_directory'].'/alarms_definition.php');

$GLOBALS['security_lowest_level'] = 10; //allow only users with higher security level
$GLOBALS['security_exception_ids'] = array();
//check_allow() je nizsie


$dbLink_Config = dcu_opendb('Config');
$dbLink_DB = dcu_opendb('DB');
$dbLink_Hist = array();
$dcuConns = get_dcu_conns();

$alarms = array();

foreach($dcuConns as $dcu_id => $dcuConn){
    $dcu_alarms = array();
    //alternative query: https://www.xaprb.com/blog/2006/12/07/how-to-select-the-firstleastmax-row-per-group-in-sql/
    if (empty($GLOBALS['dbLink_Hist'][$dcu_id]))
        $GLOBALS['dbLink_Hist'][$dcu_id] = dcu_opendb('Hist', $dcu_id);
    $dbLink = $GLOBALS['dbLink_Hist'][$dcu_id];

    $query = 'SELECT * FROM ActualValues WHERE actState>=65536';
    $result = $dbLink->query($query);
    if (($result) and ($result->num_rows)){
        $where = ' WHERE ';
        while($row = $result->fetch_assoc()) {
            $var_id = $row['id'];
            $var_name = $row['name'];
            $eventType_int = floor($row['actState']/65536);

            $alarmNumbers = array();
            $eventNumbers = array();
            $alarmNumber = 0;
            $eventNumber = 59;
            while($eventType_int>0){
                $alarmNumber++;
                $eventNumber++;
                if ($eventType_int%2) {
                    $alarmNumbers[]=$alarmNumber;
                    $eventNumbers[]=$eventNumber;
                }
                $eventType_int = $eventType_int/2;
            }
            $eventTypes = implode(',',$eventNumbers);
            $where .= "(eventParameter=$var_id and eventType in ($eventTypes)) or ";

            foreach($alarmNumbers as $alarm_id){
                $new_alarm = array(
                    'dcu_id' => $dcu_id,
                    'var_id' => $var_id,
                    'alarm_id' => $alarm_id,
                    'var_name' => $var_name,
                    'sampleTime' => $row['sampleTime']/1000,
                    'alarm_text'=> T_('Alarm No. %d on process variable %d - %s'),
                    'placeholders'=> array($alarm_id, $var_id, $var_name),
                );
                if (isset($alarms_def[$dcu_id]) &&
                    isset($alarms_def[$dcu_id][$var_name]) &&
                    isset($alarms_def[$dcu_id][$var_name]['alarm_on_text'][$alarm_id-1]) ){
                    $new_alarm['alarm_text'] = $alarms_def[$dcu_id][$var_name]['alarm_on_text'][$alarm_id-1];
                    $new_alarm['placeholders'] = array();
                }
                $dcu_alarms[$var_id.'.'.$alarm_id] = $new_alarm;
            }
        }
        $where = substr($where, 0, -3);

        $query = ' SELECT max(eventUnitTime) AS alarmTime, eventType, eventParameter '
            . ' FROM eventTab '
            . $where
            . ' GROUP BY eventType, eventParameter ORDER by alarmTime DESC';
        $result =  $dbLink->query($query);
        if (($result) and ($result->num_rows)){
            while($row = $result->fetch_assoc()) {
                $var_id = $row['eventParameter'];
                $alarm_id = $row['eventType']-59;

                $dcu_alarms[$var_id.'.'.$alarm_id]['time'] =
                    $row['alarmTime'] / 1000;

            }
        }
    }
    $alarms = $alarms + $dcu_alarms;
}

if (!$alarms) echo "<!-- no alarms - required for IF in ajax -->";

if(!check_allow($GLOBALS['security_lowest_level'], $GLOBALS['security_exception_ids'])){
    ?>

    <tr>
        <td colspan='3' style='text-align:center;'>
            <?= T_('No permission') ?> </td>
    </tr>
    <?php
    die();
}

if ($alarms) {
    if($GLOBALS['sound'] == 'on'){
?>
    <tr>
        <td colspan='3' style='display:none;'>
        <audio autoplay="autoplay">
            <source src="scripts/alarm.mp3" type="audio/mpeg" />
            <embed hidden="true" autostart="true" loop="false" src="scripts/alarm.mp3" />
        </audio>
        </td>
    </tr>
<?php
    }

    foreach ($alarms as $alarm){
        $rowID = $alarm['dcu_id'].'.'.$alarm['var_id'].'.'.$alarm['alarm_id'];
?>
<tr>
    <td class="alarm_id">
        <a href='' title='DCU <?= $alarm['dcu_id'] ?> - <?= $alarm['var_name'] ?>'>
        <?= $rowID ?></a>
    </td>
    <td class="alarm_desc"> <?php vprintf($alarm['alarm_text'],$alarm['placeholders']); ?> </td>
    <td class="alarm_time"> <?php if(isset($alarm['sampleTime']))
            echo date($GLOBALS['date_format']." H:i:s", $alarm['sampleTime']);
        else echo '???';
        ?> </td>
    <td class='alarm_history popup_container' style='text-align:center;'
        data-url='ajax_alarm_history.php'
        data-dcu_id='<?= $alarm['dcu_id'] ?>' data-var_id='<?= $alarm['var_id'] ?>'
        data-alarm_id='<?= $alarm['alarm_id'] ?>'>
        <i class="fa fa-history" aria-hidden="true"></i>
        <!--<span style='font-size:150%;'>&#10226;</span><!--&#11119; -->
        <span class="popup_element"></span>
    </td>
    <?php
    $dcu_id = $alarm['dcu_id'];
    $var_id = $alarm['var_id'];
    $label  = $alarm['var_name'];
    $get_params = 'file=content/dynamic/variable_graph.php';
    $get_params .=  '&amp;multi_id="+this.dataset.onclick_multi_id+"';
    $get_params .=  "&amp;label=DCU $dcu_id<br />Graph for variable $label";
    $get_params .=  '&amp;ylabel=units';
    $get_params .=  '&amp;time_frame=1000';
    ?>

    <td class='alarm_graph popup_container' style='text-align:center;font-size:150%;'
        data-onclick_multi_id ='<?= "[[$dcu_id,$var_id,\"$label\"]]" ?>'
        onclick='window.open("index.php?<?= $get_params ?>",
           "_blank",
           "width=900px,height=600px,left=20,top=20,toolbar=1,resizable=1,location=no,menubar=no")'>
    <a title="graph"><i class="fa fa-signal" aria-hidden="true"></i></a>
    <!--<span style='font-size:150%;'>&#128480;</span>-->
    </td>
</tr>
<?php
    }
}else{
?>

<tr>
    <td colspan='3' style='text-align:center;'>
        <?= T_('There are no alarms, now :)') ?> </td>
</tr>
<?php
}