<?php
class class_events{
    public $type = 'static';
    public $dcus = array(1);
    /** @var string Possible values:
     * - all (default)
     * - alarms
     * - no_modbus
     */
    public $event_types = 'all';
    public $can_acknowledge = 0;
    public $offset = 0;
    public $alarms_def = array();
    public $events_def = array();
    public $vis_alarms_def = array();

    public function __construct() {
        $this->can_acknowledge = (isset($_SESSION['userId']))&&($_SESSION['allowEventAck']==1);

        global $user_directory;
        include("./$user_directory/alarms_definition.php");
        $this->alarms_def = $alarms_def;
        include('./event/events_definition.php');
        $this->events_def = $events_def;


        $vis_alarms_def=simplexml_load_file("./$user_directory/alarms_definition_server.xml")
            or $GLOBALS['errors']['general'][] = 'Error: Cannot create XML object';
        if($vis_alarms_def===FALSE){$GLOBALS['errors']['general'][] = T_('XML parse error'); return;}

        foreach($vis_alarms_def->dcu as $key=>$single_dcu){
            $moje_id = (integer) $single_dcu->attributes()['id'];
            if (!isset($this->alarms_def[$moje_id]))
                $this->alarms_def[$moje_id] = array();
            foreach($single_dcu->variable as $key=>$single_variable){

                $moja_variable = (string) $single_variable->attributes()['name'];
                if (!isset($this->alarms_def[$moje_id][$moja_variable]))
                    $this->alarms_def[$moje_id][$moja_variable] = array(
                    'alarm_on_text' => array(),
                    'alarm_off_text' => array()
                    );
                $i=0;
                while (isset($single_variable->alarm[$i])){
                    $this->alarms_def[$moje_id][$moja_variable]['alarm_on_text'][$i] =
                        trim($single_variable->alarm[$i]->on_text);
                    $this->alarms_def[$moje_id][$moja_variable]['alarm_off_text'][$i] =
                        trim($single_variable->alarm[$i]->off_text);
                $i++;
                }
            }
        }
    }

    /**
      * Decode event
      *
      * @param $t number
      * @param $p number
      * @param $var_name number
      * @param $dcu_id number
      *
      * @return array
      */
    public function decode_event($t, $p, $var_name, $dcu_id){
        global $user_directory;
        $alarms_def = $this->alarms_def;
        $events_def = $this->events_def;

        $event = $this->events_def[0];
        if(isset($this->events_def[$t])) $event = $this->events_def[$t];
        if(($t==53)||($t==54)||($t==55)||($t==56)) $event = $this->events_def[53];

        $event['placeholders'] = array($t);
        $event['var_id'] = NULL;

        switch ($t){
            case 1:
            case 58:
                switch ($p){
                    case 0: $i=T_('<strong>fast</strong> control application execution interrupt'); break;
                    case 1: $i=T_('<strong>medium</strong> control application execution interrupt'); break;
                    case 2: $i=T_('<strong>slow</strong> control application execution interrupt'); break;
                    default: $i=T_('<strong>unknown</strong> control application execution interrupt');
                }
                $event['placeholders'] = array($i);
                break;
            case 4:
                switch ($p & 0xF){
                    case 0: $i=T_('Back to auto operation'); break;
                    case 1: $i=T_('Constant override turned on'); break;
                    default: $i=T_('Dynamic override turned on');
                }
                $var_id = ($p >> 16) & 0xFFFF;
                $event['placeholders'] = array($var_id,$i);
                $event['var_id'] = $var_id;
                break;
            case 9:
            case 10:
            case 16:
            case 33:
            case 35:
            case 51:
            case 108:
                $event['placeholders'] = array($p);
                break;
            case 14:
                $var_id = $p & 0xFFFF;
                $maxconn = $p >> 16;
                $event['placeholders'] = array($maxconn,$var_name);
                $event['var_id'] = $var_id;
                break;
            case 15:
                $sip1 = strval($p & 0xFF) . strval(($p >> 8) & 0xFF) . strval(($p >> 16) & 0xFF) . strval(($p >> 24) & 0xFF);
                $event['placeholders'] = array($sip1);
                break;
            case 19:
                $var_id = $p & 0xFFFF;
                $form1 = $p >> 16;
                $event['placeholders'] = array($var_name);
                $event['var_id'] = $var_id;
                break;
            case 34:
                $sladd = $p & 0xFF;
                $exccode = $p >> 8;
                $event['placeholders'] = array($sladd, $exccode);
                break;
            case 28:
            case 37:
            case 38:
                $var_id = $p & 0xFFFF;
                $event['var_id'] = $var_id;
                $event['placeholders'] = array($var_name);
                break;
            case 39:
            case 40:
                $var_id = $p & 0xFFFF;
                $event['var_id'] = $var_id;
                global $fcn_ids_and_names;
                if(empty($fcn_ids_and_names[$dcu_id]))
                    $fcn_ids_and_names[$dcu_id] = construct_fcn_ids_and_names($dcu_id);
                if (isset($fcn_ids_and_names[$dcu_id]['ids'][$var_id]))
                    $event['placeholders'] = array($fcn_ids_and_names[$dcu_id]['ids'][$var_id]['name']);
                else
                    $event['placeholders'] = array("NOT FOUND, PLEASE SYNCHRONISE DATABASE");
                break;
            case 36:
                $sladd = $p & 0xFF;
                $event['placeholders'] = array($sladd);
                break;
            case 20:
            case 21:
            case 22:
            case 23:
                $deg = construct_temperature($p);
                $event['placeholders'] = array($deg['degC'], $deg['degF']);
                break;
            case 24:
            case 25:
            case 26:
            case 27:
                $volts = construct_volts($p);
                $event['placeholders'] = array($volts);
                break;
            case 41:
                switch ($p){
                    case 1: $i=T_('switch into on-pc control mode failed'); break;
                    case 2: $i=T_('switch into on-chip simulation mode failed'); break;
                    default: $i=T_('modification of on-chip simulation mode parameters');
                }
                $event['placeholders'] = array($i);
                break;
            case 50:
                global $dsa_6bit_time;
                $_6bit_time = $dsa_6bit_time;
                $_6bit_time['value'] = $p;
                translate_to_bites($_6bit_time);
                $event['placeholders'] = array(construct_time_from__6bit_time($_6bit_time));
                break;
            case 52:
                switch ($p & 0xFF){
                    case 1: $i=T_('a user command has blocked communication'); break;
                    case 2: $i=T_('a number of registered TCP segments has reached maximum'); break;
                    default: $i=T_('a number of active tasks has reach maximum');
                }
                $event['placeholders'] = array($p >> 8,$i);
                break;
            case 53:case 54:case 55:case 56:
                $event['placeholders'] = array($t-52,$p);
                break;
            case 101:
            case 109:
                switch ($p & 0x1){
                    case 0: $i=T_('Unsuccessful'); break;
                    case 1: $i=T_('Successful'); break;
                }
                $usr = ($p >> 1) & 0xFF;
                $event['placeholders'] = array($i,$usr);
                break;
            case 102:
            //bit0 indicates if the operation was successful (1) or not(0)
            //bit1-8 visualization user ID (0-255)
            //bit9-18: control variable ID,
            //bit19-22: override status,
            //bit23-26: operation
                switch ($p & 0x1){
                    case 0: $i=T_('unsuccessfully'); break;
                    case 1: $i=T_('successfully'); break;
                }
                $usr = ($p >> 1) & 0xFF;
                $var_id = ($p >> 9) & 0x3FF;
                $ovrd_oper = ($p >> 19) & 0xF;
                $ovrd_type = ($p >> 23) & 0xF;
                switch ($ovrd_type){
                    case 0: $type=T_('Back to auto operation'); break;
                    case 1: $type=T_('Constant override turned on'); break;
                    default: $type=T_('Dynamic override turned on');
                }
                if($ovrd_type>0){
                    switch ($ovrd_oper){ //hynek todo: zjednotit od nuly
                        case 1: $oper=T_('with replace operation'); break;
                        case 2: $oper=T_('with plus operation'); break;
                        default: $oper=T_('with multiply operation');
                    }
                }else $oper='';

                $event['placeholders'] = array($var_id, $i,$usr, $type, $oper);
                $event['var_id'] = $var_id;
                break;
            case 103:
                switch ($p & 0x1){
                    case 0: $i=T_('Unsuccessful'); break;
                    case 1: $i=T_('Successful'); break;
                }
                $usr = ($p >> 1) & 0xFF;
                $var_id = ($p >> 9) & 0x3FF;
                $event['placeholders'] = array($i,$usr,$var_id);
                $event['var_id'] = $var_id;
                break;
            case 104:
                //bit9-31: new control mode states
                switch ($p & 0x1){
                    case 0: $i=T_('Unsuccessful'); break;
                    case 1: $i=T_('Successful'); break;
                }
                $usr = ($p >> 1) & 0xFF;

                global $dsa_dcu_modes;
                $dsa_dcu_modes['value'] = $p >> 9;
                translate_to_bites($dsa_dcu_modes);
                $modes = $dsa_dcu_modes['bites'];

                $event['placeholders'] = array($i,$usr,
                    $modes['unit_param_history_recording']['label'],
                    $modes['unit_param_history_recording']['select_options'][$modes['unit_param_history_recording']['value']],
                    $modes['unit_param_control_mode']['label'],
                    $modes['unit_param_control_mode']['select_options'][$modes['unit_param_control_mode']['value']],
                    $modes['unit_param_rtudp_checksum']['label'],
                    $modes['unit_param_rtudp_checksum']['select_options'][$modes['unit_param_rtudp_checksum']['value']],
                    $modes['unit_param_monitoring']['label'],
                    $modes['unit_param_monitoring']['select_options'][$modes['unit_param_monitoring']['value']],
                    );
                break;
            case 105:
                switch ($p & 0x1){
                    case 0: $i=T_('Unsuccessful'); break;
                    case 1: $i=T_('Successful'); break;
                }
                $usr = ($p >> 1) & 0xFF;
                switch (($p >> 9)& 0x1){
                    case 0: $j=T_('server time used'); break;
                    case 1: $j=T_('user-defined time used'); break;
                }
                $event['placeholders'] = array($i,$usr,$j);
                break;
            case 111:
                switch ($p & 0x1){
                    case 0: $i=T_('Unsuccessful'); break;
                    case 1: $i=T_('Successful'); break;
                }
                $usr = ($p >> 1) & 0xFF;
                $function = ($p >> 9) & 0x7F;
                $parameter = ($p >> 16) & 0x1FFFF;
                $event['placeholders'] = array($i,$usr,$function,$parameter);
                break;
        }

        if(($t>=60)&&($t<=75)) {
            $event = $this->events_def[60];
            $alarmid = $t-60+1;
            $event['placeholders'] = array($alarmid,$p,$var_name);
            $event['var_id'] = $p;
            if (isset($this->vis_alarms_def[$dcu_id]) &&
                isset($this->vis_alarms_def[$dcu_id][$var_name]) &&
                isset($this->vis_alarms_def[$dcu_id][$var_name]['alarm_on_text'][$alarmid-1]) ){
                $event['placeholders'] = array();
                $event['label'] = $this->vis_alarms_def[$dcu_id][$var_name]['alarm_on_text'][$alarmid-1];
            }

            if (isset($this->alarms_def[$dcu_id]) &&
                isset($this->alarms_def[$dcu_id][$var_name]) &&
                isset($this->alarms_def[$dcu_id][$var_name]['alarm_on_text'][$alarmid-1]) ){
                $event['placeholders'] = array();
                $event['label'] = $this->alarms_def[$dcu_id][$var_name]['alarm_on_text'][$alarmid-1];
            }
        }
        if(($t>=80)&&($t<=95)) {
            $event = $this->events_def[80];
            $alarmid = $t-80+1;
            $event['placeholders'] = array($alarmid,$p,$var_name);
            $event['var_id'] = $p;
            if (isset($this->alarms_def[$dcu_id]) &&
                isset($this->alarms_def[$dcu_id][$var_name]) &&
                isset($this->alarms_def[$dcu_id][$var_name]['alarm_off_text'][$alarmid-1]) ){
                $event['placeholders'] = array();
                $event['label'] = $this->alarms_def[$dcu_id][$var_name]['alarm_off_text'][$alarmid-1];
            }
        }
        return $event;
    }

    /** Gets list of all events of control unit from defined time newer
      *
      * @param $dcu_id control unit ID
      *
      * @return object array of values plus array of times
      */
    public function get_event_list($dcu_id){
        $res = array();
        set_time_limit(900);
        //open connection
        if (empty($GLOBALS['dbLink_Hist'][$dcu_id]))
            $GLOBALS['dbLink_Hist'][$dcu_id] = dcu_opendb('Hist', $dcu_id);
        $dbLink = $GLOBALS['dbLink_Hist'][$dcu_id];

        $timestamp_from = $GLOBALS['panel_time_timestamp'];  //timestamp in seconds, value <0 indicates actual time sample
        if($GLOBALS['panel_time_type'] == 'actual'){
            $timestamp_from = -1;
        }

        $time_from = $timestamp_from*1000;
        $where = '1';
        if($this->event_types=='alarms'){
            $where .= ' AND (eventType>=60) AND (eventType<=95) ';
        }elseif($this->event_types=='no_modbus'){
            $where .= ' AND ((eventType<30) OR (eventType>40)) ';
        }


        if($timestamp_from<0){
        }else{
            $where .= " AND (eventUnitTime <= $time_from) ";
        }
        $events_LIMIT = $GLOBALS['events_LIMIT']+1;

        $query = "SELECT ET.eventUnitTime AS unit_time, "
            . " ET.eventType as eventType, "
            . " ET.eventParameter AS eventParameter, "
            . " ET.eventAcknowledgeUser AS ackuser, "
            . " ET.eventAcknowledgeTime AS acktime, "
            . " VT.name AS var_name "
            . " FROM eventTab ET "
            . " LEFT JOIN cvarIdTab VT on VT.id = ET.eventParameter "
            . " WHERE $where ORDER BY eventUnitTime DESC LIMIT $events_LIMIT OFFSET ".$this->offset;

        $result =  $dbLink->query($query);
        if (($result) and ($result->num_rows))
            while($row = $result->fetch_assoc()) {

                if (!empty($row['var_name'])) $var_name = $row['var_name'];
                else $var_name = '-';

                $event = $this->decode_event($row['eventType'], $row['eventParameter'], $var_name, $dcu_id);

                // if event is alarm OFF
                if (($row['eventType']>=80)&&($row['eventType']<=95)){
                    $row['alarm_id'] = $dcu_id.'.'.$event['var_id'].'.'.($row['eventType']-79);
                    $row['alarm_title'] = 'DCU '.$dcu_id.' - '.$var_name;
                }
                // if event is alarm ON
                elseif (($row['eventType']>=60)&&($row['eventType']<=75)){
                    $row['alarm_id'] = $dcu_id.'.'.$event['var_id'].'.'.($row['eventType']-59);
                    $row['alarm_title'] = 'DCU '.$dcu_id.' - '.$var_name;
                }
                // event on DCU
                else {
                    $row['alarm_id'] = $dcu_id;
                    $row['alarm_title'] = 'DCU '.$dcu_id;
                    // event on DCU on variable
                    if (isset($event['var_id'])){
                        $row['alarm_id'] .= '.'.$event['var_id'];
                        $row['alarm_title'] .= ' - '.$var_name;
                    }
                }
                //$row['alarm_id'] = ($row['eventType']>79) ? $row['eventType']-79 : $row['eventType']-59;
                $row['dcu_id'] = $dcu_id;
                $row['var_id'] = $event['var_id'];
                $row['eventlabel'] = $event['label'];
                $row['eventstate'] = $event['state'];
                $row['placeholders'] = $event['placeholders'];
                $res[] = $row;
            }

        return $res;
    }


    public function display_component() {
        $events = array();
        $this->dcus = array_unique($this->dcus);
        $has_more = 0;

        foreach($this->dcus as $dcu ){
            $more_events = $this->get_event_list($dcu);
            if (count($more_events)>$GLOBALS['events_LIMIT']){
                $has_more = 1;
                array_pop($more_events);
            }
            $events = array_merge($events, $more_events);
        }

        $new_offset = $this->offset;
        if($has_more)
            $new_offset = $this->offset +$GLOBALS['events_LIMIT'];
?>
<div style="text-align: center;">
<div style="display: inline-block;">
<form class='event_list_form ' name='user_event_form'
      method='post' action='index.php?file=<?= $_GET['file'] ?>'>
    <label><?= T_('DCUs') ?><br/>
    <input type='text' name='dcus' placeholder='1,2,3' required
        <?php if (isset($_POST['dcus']))echo "value=".$_POST['dcus']; ?> />
    </label>
    <br/>
    <label><?= T_('event types') ?><br/>
    <div class="select-wrapper">
        <select name='event_types'>
        <option value="all"
                <?php if (!isset($_POST['event_types']))  echo "selected";
                if ((isset($_POST['event_types']))&&($_POST['event_types']=="all"))
                echo "selected"; ?> >all</option>
        <option value="alarms"
            <?php if ((isset($_POST['event_types']))&&($_POST['event_types']=="alarms"))
                echo "selected"; ?> >alarms</option>
        <option value="no_modbus"
                <?php if ((isset($_POST['event_types']))&&($_POST['event_types']=="no_modbus"))
                echo "selected"; ?> >no_modbus</option>
        </select>
    </div>
    </label>
    <br/>
    <button type='submit' name='event_list_submit'>
        <?= T_('Filter events') ?><br/>
    </button>
</form>
</div>
</div>

<table width='95%' id="eventstable" class="sorttable content_centered">
    <thead>
        <tr>
            <th><?= T_('DCU.ID.Type') ?></th>
            <th><?= T_('Unit time') ?></th>
            <th><?= T_('State') ?></th>
            <th><?= T_('Event') ?></th>
            <th>
                <?= T_('Ack&nbsp;user&nbsp;ID') ?>
                <?php
                if ($this->can_acknowledge){
                ?>

                <br />
                <button type='button' class='ack_all_button'>
                    <?= T_('ACK ALL') ?>
                </button>
                <?php
                }?>

            </th>
            <th><?= T_('Ack time') ?></th>
        </tr>
    </thead>
    <tbody>
<?php

foreach ($events as $event){
?>
    <tr>
        <td>
            <a title='<?= $event['alarm_title'] ?>'><?= $event['alarm_id'] ?></a>
        </td>
        <td title='<?= date($GLOBALS['date_format'].' H:i:s',$event['unit_time']/1000) ?>'>
            <?= date('Y/m/d H:i:s', $event['unit_time']/1000) ?>
        </td>
        <td>
            <?php
            switch ($event['eventstate']){
                case 1:
                    echo '<i class="fa fa-thumbs-o-up" aria-hidden="true" style="color:green"></i>';
                    //echo '<span style="color:green;font-size:150%;" >&#128402;</span>';
                    break;
                case 2:
                    echo '<i class="fa fa-exclamation-triangle" aria-hidden="true" style="color:red"></i>';
                    //echo '<span style="color:red;font-size:150%;" >&#9888;</span>';
                    break;
                case 3:
                    echo '<i class="fa fa-thumbs-o-down" aria-hidden="true" style="color:red"></i>';
                    //echo '<span style="color:red;font-size:150%;" >&#128403;</span>';
                    break;
                default:
                    echo '<span style="color:orange;" >?</span>';
            }
        ?>

            <span style="display:none;"><?= $event['eventstate'] ?></span>
        </td>
        <td><?php vprintf($event['eventlabel'],$event['placeholders']); ?></td>

        <td style='text-align:center;'>
            <?php
            if ((empty($event['ackuser']))&&$this->can_acknowledge){
                ?>

                <button type='button' class='ack_button'
                       data-eventDcu='<?= $event['dcu_id'] ?>'
                       data-eventUnitTime='<?= $event['unit_time'] ?>'
                       data-eventType='<?= $event['eventType'] ?>'
                       data-eventParameter='<?= $event['eventParameter'] ?>' >
                    <?= T_('ACK') ?>
                </button>
                <?php
            }else{
                echo $event['ackuser']; //&#9745; check vo stvorceku
                //&#10004; check
            }
            ?>

        </td>

        <td>
            <?php
            if (empty($event['ackuser'])){
                echo '-';
            }else{
                echo date($GLOBALS['date_format']." H:i:s", $event['acktime']/1000);
            }
            ?>

        </td>
    </tr>
<?php
}

?>

    </tbody>
</table>

<div style='clear:both;height:0px;'> </div>
<div style='padding: 0.5em 1em; float:right;'>
    <a href='index.php?<?= remove_query_offset() ?>&offset=<?= $new_offset ?>'><?= T_('More') ?></a>
</div>
<!--EVENT LIST SCRIPT -->
<SCRIPT>

    function ack_event(this_button){
            $.ajax({
                url:'ajax_ack_events.php',
                type: 'POST',
                data: {
                    eventDcu: this_button.attr('data-eventDcu'),
                    eventUnitTime: this_button.attr('data-eventUnitTime'),
                    eventType: this_button.attr('data-eventType'),
                    eventParameter: this_button.attr('data-eventParameter'),
                } ,
                context: document,
                dataType: 'json',
                success:function(data){
                    if (data.acknowledged){
                        var ack = data.acknowledged;
                        this_button.parent().next().text(ack.time);
                        this_button.parent().text(ack.user);
                    }

                    append_errors(data.errors);
                },
                error:function(data){
                },
                complete:function(data){
                }
            });
    }

    $(document).ready(function(){
        $('#eventstable').DataTable( {
            searching: false,
            paging: true,
            ordering: true,
            order: [[ 1, 'desc' ]],
            info: false,
            lengthMenu: [[20, 40, 60, -1], [20, 40, 60, 'All selected']],
            pagingType: 'full_numbers',
        } );

        $('.ack_button').click(function(){
            ack_event($(this))
        });
        $('.ack_all_button').click(function(){
            $('.ack_button').each(function(){
                ack_event($(this));
            });
        });
    });

</script>
<?php
    }
}
