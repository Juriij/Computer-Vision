<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');

$GLOBALS['security_lowest_level'] = 10; //allow only users with higher security level
$GLOBALS['security_exception_ids'] = array();
if(!check_allow($GLOBALS['security_lowest_level'], $GLOBALS['security_exception_ids']))die();

$dcu_id = my_required_filter_input('dcu_id',FILTER_VALIDATE_INT);
$var_id = my_required_filter_input('var_id',FILTER_VALIDATE_INT);
$alarm_id = my_required_filter_input('alarm_id',FILTER_VALIDATE_INT);

$dbLink_Hist = array();
//open connection
if (empty($GLOBALS['dbLink_Hist'][$dcu_id]))
    $GLOBALS['dbLink_Hist'][$dcu_id] = dcu_opendb('Hist', $dcu_id);
$dbLink = $GLOBALS['dbLink_Hist'][$dcu_id];

$stmt = $dbLink->prepare(
' SELECT * '
. ' FROM eventTab '
. ' WHERE eventParameter =  ?  AND eventType IN (?,?)'
. ' ORDER by eventUnitTime DESC '
. ' LIMIT 10'
    );
if ( FALSE===$stmt ){
    $GLOBALS['errors']['general'][] = T_('Selecting alarm history failed.').'(->prepare) '.$dbLink->error;
    return FALSE;
}
$eventType1 = 60 + $alarm_id -1;
$eventType2=80 + $alarm_id -1;
$success = $stmt->bind_param('ddd', $var_id, $eventType1, $eventType2);
if ( FALSE===$success ){
    $GLOBALS['errors']['general'][] = T_('Selecting alarm history failed.').'(->bind_param) '.$stmt->error;
    $stmt->close();
    return FALSE;
}
$success = $stmt->execute();
if ( FALSE===$success ){
    $GLOBALS['errors']['general'][] = T_('Selecting alarm history failed.').'(->execute) '.$stmt->error;
    $stmt->close();
    return FALSE;
}

$result = $stmt->get_result();
$stmt->free_result();
$stmt->close();

$table_rows = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if($row['eventType']<80) {
            $type = T_('alarm');
            $style  = ' style="color:red" ';
        }else {
            $type = T_('normal');
            $style  = ' style="color:green" ';
        }
        $next_row = "<tr $style >"
            . '<td>'.$type.'</td>'
            . '<td>'.date("$date_format H:i:s", $row['eventUnitTime']/1000).'</td>'
            . '<td>'.$row['eventAcknowledgeUser'].'</td>'
            . '</tr>';
        $table_rows[] = $next_row;
    }
}
foreach($dbLink_Hist as $dbLink) $dbLink->close();
?>

<div class='panel_title'>
    <?= T_('History of this alarm') ?>
</div>
<table style='white-space:nowrap;'>
    <thead>
        <tr>
            <th style='text-align:center;'>Type</th>
            <th style='text-align:center;'>Time</th>
            <th style='text-align:center;'>Ack</th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach ($table_rows as $table_row){
        echo $table_row;
    }
    ?>
    </tbody>
</table>