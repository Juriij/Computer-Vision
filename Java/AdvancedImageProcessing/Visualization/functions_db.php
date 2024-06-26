<?php
/** Open DB connection
  *
  * @param $dbname string
  * @param $suffix default '', usually equals dcu id
  * @param $die default TRUE
  *
  * @return object or FALSE
  */
function dcu_opendb($dbname, $suffix='', $die = TRUE){
    $dbLink = new mysqli(
            $GLOBALS['host'],
            $GLOBALS['user'],
            $GLOBALS['password']);
    if ($dbLink->connect_errno) die(T_('DB CONNECT ERROR').' '.$dbLink->connect_error);

    $dbLink->select_db($GLOBALS['dbnames'][$dbname].$suffix);

    if ($dbLink->errno) {
        $error = T_('DB CONNECT ERROR').' '.$dbLink->error;
        if ($die) die($error);
        else {
            if($dbname!='ArchHist')
            $GLOBALS['errors']['general'][] = $error;
            return FALSE;
        }
    }

    $dbLink->set_charset('utf8');
    $dbLink->query("SET time_zone = '".$GLOBALS['timezone_actual']."'");

    return $dbLink;
}

/**
  * @return array
  */
function get_dcu_conns(){
    if(isset($_SESSION['dcuConns'])){
        return $_SESSION['dcuConns'];
    }

    global $dbLink_Config;
    $dcuConns = array();
    $query="SELECT * FROM permanentHistRec ORDER BY cUnitId";
    $result =  $dbLink_Config->query($query);
    if ($result->num_rows > 0)
    while($row = $result->fetch_assoc()) {
        $cUnitId = intval($row['cUnitId']);
        $dcuConns[$cUnitId] = $row;
        if (!isset($_SESSION['dcu_id_selected'])){  //save first ID
            session_start();
            $_SESSION['dcu_id_selected'] = $cUnitId;
            session_write_close();
        }
    }

    session_start();
    $_SESSION['dcuConns'] = $dcuConns;
    session_write_close();
    return $dcuConns;
}

/** all variables of control unit with names, ids, physical unit, timespan, notes
  *
  * @param $dcu_id control unit ID
  *
  * @return array array of rows
  */
function get_vars_catalog($dcu_id){
    $output=array();
    //open connection
    if (empty($GLOBALS['dbLink_Hist'][$dcu_id]))
        $GLOBALS['dbLink_Hist'][$dcu_id] = dcu_opendb('Hist', $dcu_id);
    $dbLink = $GLOBALS['dbLink_Hist'][$dcu_id];

    $query = "SELECT * FROM cvarIdTab";
    $result =  $dbLink->query($query);
    if (($result) and ($result->num_rows))
        while ($row = $result->fetch_assoc()){
            $row['id'] = intval($row['id']);
            $output[$row['id']] = $row;
        }

    return $output;
}

/** all variables of control unit with names, ids, physical unit, timespan,  notes
  * check unique names
  *
  * @param $dcu_id control unit ID
  *
  * @return array array of ('ids'=>($id => $name), 'names'=>($name=>$id))
  */
function construct_var_ids_and_names($dcu_id){
    $output=array('ids'=>array(), 'names'=>array());
    $rows = get_vars_catalog($dcu_id);
    foreach($rows as $row){
        $output['ids'][$row['id']] = $row;
        if(isset($output['names'][$row['name']])){
            $old_id = $output['names'][$row['name']]['id'];
            $new_id = $row['id'];
            $output['names'][$row['name']]['error'] =
            sprintf(T_('%s is not unique name.'),$row['name'])
            . sprintf(T_('Check id=%d and id=%d'),$old_id, $new_id);
        }else
            $output['names'][$row['name']] = $row;
    }
    return $output;
}
/** all functions of control unit with names, ids, physical unit, timespan, notes
  *
  * @param $dcu_id control unit ID
  *
  * @return array array of rows
  */
function get_fcns_catalog($dcu_id){
    $output=array();
    //open connection
    if (empty($GLOBALS['dbLink_Hist'][$dcu_id]))
        $GLOBALS['dbLink_Hist'][$dcu_id] = dcu_opendb('Hist', $dcu_id);
    $dbLink = $GLOBALS['dbLink_Hist'][$dcu_id];

    $query = "SELECT * FROM cfunIdTab";
    $result =  $dbLink->query($query);
    if (($result) and ($result->num_rows)){
        while ($row = $result->fetch_assoc()){
            $row['id'] = intval($row['id']);
            $output[$row['id']] = $row;
        }
    }
    return $output;
}
/** all functions of control unit with names, ids, notes
  *
  * @param $dcu_id control unit ID
  *
  * @return array of ('ids'=>($id => $name), 'names'=>($name=>$id))
  */
function construct_fcn_ids_and_names($dcu_id){
    $output=array('ids'=>array(), 'names'=>array());
    $rows = get_fcns_catalog($dcu_id);
    foreach($rows as $row){
        $output['ids'][$row['id']] = $row;
        if(isset($output['names'][$row['name']])){
            $old_id = $output['names'][$row['name']]['id'];
            $new_id = $row['id'];
            $output['names'][$row['name']]['error'] =
            sprintf(T_('%s is not unique name.'),$row['name'])
            . sprintf(T_('Check id=%d and id=%d'),$old_id, $new_id);
        }else
            $output['names'][$row['name']] = $row;
    }
    return $output;
}

function get_one_var_value_selector($db_selector, $dcu_id, $var_id, $time_source, $history_timestamp){
    $output = array();
    //open connection
    if (empty($GLOBALS['dbLink_'.$db_selector][$dcu_id]))
        $GLOBALS['dbLink_'.$db_selector][$dcu_id] = dcu_opendb($db_selector, $dcu_id, FALSE);
    $dbLink = $GLOBALS['dbLink_'.$db_selector][$dcu_id];
    if($dbLink===FALSE) return $output;

    $time_to = $history_timestamp*1000;
    $query = "SELECT * "
        . " FROM cvarValTab_$var_id ";
    if ($time_source=='actual'){
        $query .= " ORDER BY sampleTime DESC LIMIT 1 ";
    }else{
        $query .= " WHERE sampleTime <= $time_to "
            . " ORDER BY sampleTime DESC LIMIT 1 ";
    }

    $result = $dbLink->query($query);
    if ( FALSE===$result ){$GLOBALS['errors']['general'][] = $dbLink->error;}
    if (($result) and ($result->num_rows)){
        $row_value = $result->fetch_assoc();
        $output['id'] = $var_id;
        $output['value'] = $row_value['sampleValue']; //mozu sa zmenit keys
        $output['state'] = $row_value['actState'];
        $output['time'] = $row_value['sampleTime'];
    }
    return $output;
}
function get_one_var_value($dcu_id, $var_id,  $data_source, $time_source, $history_timestamp){

    $variable = array();
    if ($data_source == 'all'){
        $variable = get_one_var_value_selector('Hist', $dcu_id, $var_id,
            $time_source, $history_timestamp);
    }

    if(empty($variable)){
        $variable = get_one_var_value_selector('ArchHist', $dcu_id, $var_id,
            $time_source, $history_timestamp);
    }
    return $variable;
}

/** Selects one variable of control unit with its values and times in microseconds
  *
  * @param $dcu_id dcu id
  * @param $var_id variable id
  *
  * @return array of params
  */
function get_one_var_info($dcu_id, $var_id){
    $output = array();
    $rows = get_vars_catalog($dcu_id);
    foreach($rows as $row){
        if($row['id']==$var_id){

            $variable = get_one_var_value($dcu_id, $var_id,
                'all',
                $GLOBALS['panel_time_type'],
                $GLOBALS['panel_time_timestamp']);

            if (empty($variable)) $variable = array('id'=>$var_id);

            $variable['name'] = $row['name'];
            $variable['note'] = $row['note'];
            $variable['timespan'] = $row['timespan'];
            $variable['physicalUnit'] = $row['physicalUnit'];
            $output = $variable;
            break;

        }
    }

    return $output;
}

function get_var_graph_data_selector($db_selector, $dcu_id, $var_id, $timestamp_from, $timestamp_to, $time_rounding){
    if (empty($GLOBALS['dbLink_'.$db_selector][$dcu_id]))
        $GLOBALS['dbLink_'.$db_selector][$dcu_id] =
        dcu_opendb($db_selector, $dcu_id, FALSE);
    $dbLink = $GLOBALS['dbLink_'.$db_selector][$dcu_id];
    if(empty($dbLink)){
        return array('signals'=>array(), 'times'=>array());
    }

    $time_from = $timestamp_from*1000;
    $time_to = $timestamp_to*1000;

    $query = " SELECT * "
        . " FROM cvarValTab_$var_id "
        . " WHERE sampleTime >= $time_from AND sampleTime <= $time_to "
        . " ORDER BY sampleTime";

    $result = $dbLink->query($query);
    if ( FALSE===$result ){
        $GLOBALS['errors']['general'][] = $dbLink->error;
        return array('signals'=>array(), 'times'=>array());
    }
    $values = array();
    $times = array();
    $output = array();
    if ($result->num_rows > 0){
        $i=0;
        while ($row = $result->fetch_assoc()) {
            $values[$i] = $row['sampleValue'];
            $times[$i] = round($row['sampleTime'], -$time_rounding);

            $i++;
        }
    }

    /*zmazat
    $query = " SELECT AVG(sampleValue) as myaverage"
        . " FROM cvarValTab_$var_id "
        . " WHERE sampleTime >= $time_from AND sampleTime <= $time_to ";
    $result = $dbLink->query($query);
    if ( FALSE===$result ){
        $GLOBALS['errors']['general'][] = $dbLink->error;
    }else {
        $average = $result->fetch_assoc();
    }
    $output = array('signals'=>$values, 'times'=>$times, 'average'=>$average['myaverage']);
     */

    $output = array('signals'=>$values, 'times'=>$times);
    return $output;

}
/** Selects variable signal in defined interval ordered by ID
  *
  * @param $dcu_id control unit ID
  * @param $var_id control variable ID number
  * @param $data_source all/archive
  * @param $time_frame number of seconds
  * @param $time_source actual/history
  * @param $history_timestamp in seconds
  * @param $time_rounding in milliseconds
  *
  * @return array of values plus array of times in microseconds
  */
function get_var_graph_data($dcu_id, $var_id, $data_source, $time_frame, $time_source, $history_timestamp, $time_rounding) {
    $table_data1 = array('signals'=>array(), 'times'=>array());
    $table_data2 = array('signals'=>array(), 'times'=>array());

    $timestamp_from = $history_timestamp;
    $timestamp_to =   $history_timestamp + $time_frame;

    if ($data_source == 'all'){
        //select from history at first
        $table_data1 = get_var_graph_data_selector('Hist', $dcu_id, $var_id,
            $timestamp_from, $timestamp_to, $time_rounding);
        if(!empty($table_data1['times']))
            $timestamp_to = $table_data1['times'][0]/1000 - 1;
    }

    if (($timestamp_from+100)<$timestamp_to){
        //then select from archive
        $table_data2 = get_var_graph_data_selector('ArchHist', $dcu_id, $var_id,
            $timestamp_from, $timestamp_to, $time_rounding);
    }

    $output = array();
    $output['signals'] = array_merge($table_data2['signals'], $table_data1['signals']);
    $output['times'] = array_merge($table_data2['times'], $table_data1['times']);

    if(empty($output['signals'])){

        //ked nic nenajde, tak hladaj 1 najblizsie starsie
        $variable = get_one_var_value($dcu_id, $var_id,
                $data_source,
                'history',
                $timestamp_from);

        if(!empty($variable)){
            $output = array(
                'signals'=>array($variable['value']),
                'times'=>array($variable['time']));
        }else{
            //if($data_source=='ArchHist')
                $GLOBALS['errors']['general'][] = 'No data. Do you record data?';

        /*
            $output = array(
                'signals'=>array(-1),
                'times'=>array($GLOBALS['now_time']));
         *
         */
        }
    }

    return $output;
}
/** Reads data from rtVirtualIOvalue
  *
  * @return array ('iocFunId.iocFunIOIndex' => ('iocFunId','iocVarId','iocFunIOIndex','actualValue','lastUpdate'))
  */
function read_rtudpio(){
    global $dbLink_Config;
    $output = array();
    $query='SELECT * FROM rtVirtualIOvalue';
    $result = $dbLink_Config->query($query);

    if($result){
        while ($row = $result->fetch_assoc()){
            $index = $row['iocFunId'].'.'.$row['iocFunIOIndex'];
            $row['actualValue'] = intval($row['actualValue']);
            $output[$index] = $row;
        }
    }
    return $output;
}
function get_rtudpio_format($fid, $fio){
    global $dbLink_Config;
    $query="SELECT * FROM rtVirtualIO WHERE cFunId=" . $fid;

    if($result = $dbLink_Config->query($query) ){
        $row = $result->fetch_assoc();
        $formats = explode(",", $row['real1bin2int3']);
        return $formats[$fio];
    }
    return 0;
}
function write_rtudpio($fid, $fio, $val){
    global $dbLink_Config;
    $query = "UPDATE rtVirtualIOvalue "
        . "SET actualValue=" . $val . " "
        . "WHERE (iocFunId=" . $fid . ") AND (iocFunIOIndex=" . $fio . ")";
    if($result = $dbLink_Config->query($query)){
        //update initial value
        $query="SELECT * FROM rtVirtualIO WHERE cFunId=" . $fid;
        if($result = $dbLink_Config->query($query) ){
            $row = $result->fetch_assoc();
            $initialValues = explode(",", $row['initialValue']);
            $initialValues[$fio] = $val;
            $initialValue = implode(",", $initialValues);
            $query = "UPDATE rtVirtualIO SET initialValue='" . $initialValue . "' WHERE cFunId=" . $fid;
            $result = $dbLink_Config->query($query);
            if((!$result) || ($fio>=count($initialValues))){
                $GLOBALS['errors']['general'][] = T_('UPDATE rtVirtualIO failed.');
            }
        }
    }else{
        $GLOBALS['errors']['general'][] = T_('UPDATE rtVirtualIOvalue failed.');
    }
}

function delete_archived_request($requestId){
    global $dbLink_Config;
    $stmt = $dbLink_Config->prepare(
        "DELETE  FROM request_archive WHERE requestId=? ");
    if ( FALSE===$stmt ){
        $GLOBALS['errors']['general'][] = T_('Deleting from archive failed.').'(->prepare)'.$dbLink_Config->error;
        return FALSE;
    }
    $success = $stmt->bind_param("d",$requestId);
    if ( FALSE===$success ){
        $GLOBALS['errors']['general'][] = T_('Deleting from archive failed.').'(->bind_param)'.$stmt->error;
        $stmt->close();
        return FALSE;
    }
    $success = $stmt->execute();
    if ( FALSE===$success ){
        $GLOBALS['errors']['general'][] = T_('Deleting from archive failed.').'(->execute)'.$stmt->error;
        $stmt->close();
        return FALSE;
     }
    $stmt->close();
    return TRUE;
}
function get_db_users(){
    global $dbLink_Config;
    $users = array();

    $stmt = $dbLink_Config->prepare('SELECT * FROM visualUsers ');
    if ( FALSE===$stmt ){
        $GLOBALS['errors']['general'][] = T_('Selecting users failed.').'(->prepare) '.$dbLink_Config->error;
        return $users;
    }
    $success = $stmt->execute();
    if ( FALSE===$success ){
        $GLOBALS['errors']['general'][] = T_('Selecting users failed.').'(->execute) '.$stmt->error;
        $stmt->close();
        return $users;
     }
    $result = $stmt->get_result();
    $stmt->free_result();
    $stmt->close();

    if (($result) and ($result->num_rows))
        while($row = $result->fetch_assoc()) {
            $users[$row['userId']] = $row;
        }
    return $users;
}
function get_ass_init(){
    global $dbLink_Config;
    $assinit = '';

    $stmt = $dbLink_Config->prepare('SELECT * FROM visualizationPath WHERE id=300 ');
    if ( FALSE===$stmt ){
        $GLOBALS['errors']['general'][] = T_('Selecting ass.init failed.').'(->prepare) '.$dbLink_Config->error;
        return $assinit;
    }
    $success = $stmt->execute();
    if ( FALSE===$success ){
        $GLOBALS['errors']['general'][] = T_('Selecting ass.init failed.').'(->execute) '.$stmt->error;
        $stmt->close();
        return $assinit;
     }
    $result = $stmt->get_result();
    $stmt->free_result();
    $stmt->close();

    if (($result) and ($result->num_rows)){
        $row = $result->fetch_assoc();
        $assinit = $row['path'];
    }

    return $assinit;
}

function get_ass_result($procedure_id){
    global $dbLink_Config;
    $assresults = array();

    $stmt = $dbLink_Config->prepare("SELECT distinct dcuId, elementId FROM assresults WHERE procedureId=$procedure_id ");
    if ( FALSE===$stmt ){
        $GLOBALS['errors']['general'][] = T_('Selecting ass.results failed.').'(->prepare) '.$dbLink_Config->error;
        return $assresults;
    }
    $success = $stmt->execute();
    if ( FALSE===$success ){
        $GLOBALS['errors']['general'][] = T_('Selecting ass.results failed.').'(->execute) '.$stmt->error;
        $stmt->close();
        return $assresults;
     }
    $result = $stmt->get_result();
    $stmt->free_result();
    $stmt->close();

    if (($result) and ($result->num_rows))
    while($row = $result->fetch_assoc()){
        $assresults[] = $row;
    }

    return $assresults;
}

function get_ass_result2($procedure_id, $dcu_id, $var_id){
    global $dbLink_Config;
    $assresults = array();
    $stmt = $dbLink_Config->prepare("SELECT * FROM assresults "
        . " WHERE procedureId=$procedure_id "
        . " AND dcuId = $dcu_id AND elementId = $var_id"
        . " ORDER BY registerTime DESC");
    if ( FALSE===$stmt ){
        $GLOBALS['errors']['general'][] = T_('Selecting ass.results failed.').'(->prepare) '.$dbLink_Config->error;
        return $assresults;
    }
    $success = $stmt->execute();
    if ( FALSE===$success ){
        $GLOBALS['errors']['general'][] = T_('Selecting ass.results failed.').'(->execute) '.$stmt->error;
        $stmt->close();
        return $assresults;
     }
    $result = $stmt->get_result();
    $stmt->free_result();
    $stmt->close();

    if (($result) and ($result->num_rows))
    while($row = $result->fetch_assoc()){
        $assresults[] = $row;
    }

    return $assresults;
}