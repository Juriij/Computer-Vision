<?php
include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');

if(!check_security())die();

function do_it(){
    $dcu_id = my_required_filter_input('eventDcu',FILTER_VALIDATE_INT);
    $unitTime = my_required_filter_input('eventUnitTime',FILTER_VALIDATE_INT);
    $eventType = my_required_filter_input('eventType',FILTER_VALIDATE_INT);
    $eventParameter = my_required_filter_input('eventParameter',FILTER_VALIDATE_INT);

    $acknowledge_user = $_SESSION['login_username'].'('.$_SESSION['userId'].')';
    $acknowledgeTime = $GLOBALS['now_time']*1000;  // bind passes parameters by reference

    $output = array();
    //open connection
    if (empty($GLOBALS['dbLink_Hist'][$dcu_id]))
        $GLOBALS['dbLink_Hist'][$dcu_id] = dcu_opendb('Hist', $dcu_id);
    $dbLink = $GLOBALS['dbLink_Hist'][$dcu_id];

    $stmt = $dbLink->prepare(
        'UPDATE eventTab '
        . ' SET eventAcknowledgeUser = ?, eventAcknowledgeTime = ? '
        . ' WHERE (eventUnitTime = ?) AND (eventType = ?) '
        . ' AND ( eventParameter = ?)'
        );
    if ( FALSE===$stmt ){
        $GLOBALS['errors']['general'][] = T_('Acknowledge error').'(->prepare) '.$dbLink->error;
        return FALSE;
    }

    $success = $stmt->bind_param('siiii',
        $acknowledge_user, $acknowledgeTime, $unitTime, $eventType, $eventParameter
        );
    if ( FALSE===$success ){
        $GLOBALS['errors']['general'][] = T_('Acknowledge error').'(->bind_param) '.$stmt->error;
        $stmt->close();
        return FALSE;
    }
    $success = $stmt->execute();
    if ( FALSE===$success ){
        $GLOBALS['errors']['general'][] = T_('Acknowledge error').'(->execute) '.$stmt->error;
        $stmt->close();
        return FALSE;
    }else{
        $output['acknowledged'] = array(
            'user'=>$acknowledge_user,
            'time'=>date("$date_format H:i:s", $GLOBALS['now_time']),
        );
    }
    $stmt->close();

    $dbLink->close();
    return $output;
}

$output = do_it();
$output = array_merge($output, array('errors'=>$GLOBALS['errors']));
//$output = $output + array('errors'=>$GLOBALS['errors']);  nedela: otestovat
print(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));