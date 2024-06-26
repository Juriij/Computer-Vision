<?php
function panel_time_show(){
?>

    <!--PANEL TIME -->
    <div style='clear:left;height:0px;'> </div>
    <table style="border-collapse: collapse;">
        <tbody>
            <tr>
                <td>
    <table id='panel_time' class='panel abstract_left'>
    <tr><td>

        <table class='panel_title'>
            <tr>
                <td class='panel_title_text1'>
                    <div class='panel_title_text2'>
                    <?= T_('Time panel') ?>
                    </div>
                </td>
                <td><div class='title_corner_right1'> <div class='title_corner_right2'> </div></div> </td>
            </tr>
        </table>

        <div class='panel_content' >
            <!-- show dispersion and deviation -->
            <table id='panel_time_info'>
                <tr>
                    <td>
                        <table id='panel_time_deviation' class='<?= $GLOBALS['panel_time_deviation_class'] ?>'>
                            <tr>
                                <td><?= T_('Time deviation[s]') ?>:</td>
                                <td id='panel_time_deviation_value' style="text-align:right;">
                                    <?php
                                    printf('%.3f', $GLOBALS['panel_time_deviation_value']);
                                    ?>

                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table id='panel_time_dispersion'>
                            <tr>
                                <td><?= T_('Time dispersion[s]') ?>:</td>
                                <td id='panel_time_dispersion_value' style="text-align:right;">
                                    <?php
                                    printf('%.3f', $GLOBALS['panel_time_dispersion_value']);
                                    ?>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <table id='panel_time_oldest'>
                            <tr>
                                <td><?= T_('Oldest DCU.variable') ?>:</td>
                                <td id='panel_time_oldest_value' style="text-align:right;">
                                    <?php
                                    echo $GLOBALS['panel_time_oldest_value'];
                                    ?>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td>
    <form method='post' action='index.php?<?= $_SERVER["QUERY_STRING"] ?>'
          name='panel_time_type_form' id='panel_time_type_form'>
        <label for='panel_time_type'>
            <?= T_('Show') ?>:
        </label><br />
        <div class='select-wrapper'>
        <select name='panel_time_type' id='panel_time_type'>
            <option value='actual' <?php if($GLOBALS['panel_time_type']=='actual') echo 'selected';?>
            > <?= T_('Actual values') ?> </option>
            <option value='history' <?php if($GLOBALS['panel_time_type']=='history') echo 'selected';?>
            > <?= T_('History values') ?> </option>
        </select>
        </div>
    </form>
                    </td>
                </tr>
            </table>

            <?php
            if($GLOBALS['panel_time_type']=='history'){
            ?>

                <form method='post' action='index.php?<?= $_SERVER["QUERY_STRING"] ?>'
                      name='panel_time_datetime_form' id='panel_time_datetime_form'>
                    <table>
                        <tr>
                            <td><input name='panel_time_time'
                                       type='time' step='1'
                                       value='<?= date('H:i:s', $GLOBALS['panel_time_timestamp']) ?>'
                                       required />
                            </td>
                            <td><input name='panel_time_date'
                                       type='date' value='<?= date('Y-m-d', $GLOBALS['panel_time_timestamp']) ?>'
                                       required />
                            </td>
                            <td>
                                <button type='submit' name='panel_time_apply'>
                                    <?= T_('Apply') ?> &#8635;
                                </button>
                            </td>
                        </tr>
                    </table>
                </form>
                <form method='post' action='index.php?<?= $_SERVER["QUERY_STRING"] ?>'
                      name='panel_time_step_forms' id='panel_time_step_form'>
                    <table>
                        <tr>
                            <td>
                                <button type='submit' name='panel_time_back' style='width:100%;'>
                                    &nbsp;&nbsp;<i class="fa fa-angle-double-left" aria-hidden="true"></i>&nbsp;&nbsp;<!--&#10094;&#10094;-->
                                </button>
                            </td>
                            <td style='text-align:center;'>
                                <label for='panel_time_step'>
                                <?php
                                switch ($GLOBALS['panel_time_step_unit']){
                                    case 60: echo T_('min');break;
                                    case 3600: echo T_('hour');break;
                                    case 3600*24: echo T_('day');break;
                                    case 1:
                                    default:
                                        echo T_('sec');break;
                                }
                                ?>
                                </label>
                                <input name='panel_time_step'  id='panel_time_step' type='number'
                                    style='width:80px;'
                                    value='<?= $GLOBALS['panel_time_step'] ?>'
                                    required  />
                            </td>
                            <td>
                                <button type='submit' name='panel_time_forward' style='width:100%;'>
                                    &nbsp;&nbsp;<i class="fa fa-angle-double-right" aria-hidden="true"></i>&nbsp;&nbsp;<!--&#10095;&#10095;-->
                                </button>
                            </td>
                        </tr>
                    </table>
                </form>
                <?php
            }else{
            ?>

                <table class="time_dispersion">
                    <tr>
                        <td>
                            <table>
                                <tr>
                                    <td><?= T_('Values read time[ms]') ?>:</td>
                                    <td id='panel_time_info_refresh' style="text-align:right;">---</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            <?php
            }
            ?>

        </div>

        <table class='panel_bottom'>
            <tr>
                <td><div class='corner_left1'> <div class='corner_left2'> </div></div> </td>
                <td class='panel_bottom_inside1'><div class='panel_bottom_inside2'> </div> </td>
            </tr>
        </table>
    </td></tr>
    </table>

                </td><td style="vertical-align: top;">
    <div class='panel_show_hide abstract_left' id='panel_show_hide_time'>
        <div class='panel_show_hide_outside'>
            <div class='panel_show_hide_inside <?php if ($GLOBALS['panel_time_type']=='history') echo "panel_red"; ?> '>
                <i class="panel_icon fa fa-clock-o " aria-hidden="true"></i><!--&#9716;-->
            </div>
        </div>
    </div>
                </td>
            </tr>
        </tbody>
    </table>
<?php
}

function panel_time_script(){
    if (isset($_SESSION['userId'])) $panel_time_first_show = 'hide';
    else $panel_time_first_show = 'hide';
?>

    <!--PANEL TIME SCRIPT -->
    <script>
    $(document).ready(function(){
        var panel_time = sessionStorage.getItem('panel_time');
        if(panel_time===null)
            panel_time = '<?= $panel_time_first_show ?>';

        if (panel_time=='hide'){
            $('#panel_time').animate({
                    'left' : '-='+$('#panel_time').width()+'px'
                },0);
            $('#panel_show_hide_time').animate({
                    'left' : '-='+$('#panel_time').width()+'px'
                },0);
        }else{
            //$('#panel_time').show(0);
        }

        $('#panel_show_hide_time').click(function(){
            if (panel_time=='hide'){
                //$('#panel_time').show(0);//.animate({width: 'toggle'}, 1000);
                $('#panel_time').animate({
                        'left' : '+='+$('#panel_time').width()+'px'
                    },1000);
                $('#panel_show_hide_time').animate({
                        'left' : '+='+$('#panel_time').width()+'px'
                    },1000);
                panel_time = 'show';
                window.sessionStorage.setItem('panel_time', 'show');
            }else{
                $('#panel_time').animate({
                        'left' : '-='+$('#panel_time').width()+'px'
                    },1000);
                $('#panel_show_hide_time').animate({
                        'left' : '-='+$('#panel_time').width()+'px'
                    },1000);
                //$('#panel_time').hide();//.animate({width: 'toggle'}, 1000);
                panel_time = 'hide';
                window.sessionStorage.setItem('panel_time', 'hide');
            }
        });

        $('#panel_time_type').change(function(){
            $('#panel_time_type_form').submit();
        });
    });
    </script>
<?php
}

function panel_time_posts(){

    if ((isset($_SESSION[$GLOBALS['file_name']]))&&(isset($_SESSION[$GLOBALS['file_name']]['panel_time']))){
        $GLOBALS['panel_time_type'] = $_SESSION[$GLOBALS['file_name']]['panel_time']['panel_time_type'];
        $GLOBALS['panel_time_timestamp'] = $_SESSION[$GLOBALS['file_name']]['panel_time']['panel_time_timestamp'];
        $GLOBALS['panel_time_step'] = $_SESSION[$GLOBALS['file_name']]['panel_time']['panel_time_step'];
    }

    //CHECKT TIME PANEL SUBMITS
    if (isset($_POST['panel_time_type'])){
        $GLOBALS['panel_time_type'] = $_POST['panel_time_type']; // actual/history
        if($_POST['panel_time_type'] == 'actual'){
            $GLOBALS['panel_time_timestamp'] = $GLOBALS['now_time'];
        }
    }
    if (isset($_POST['panel_time_step'])){
        $GLOBALS['panel_time_step'] = $_POST['panel_time_step'];
        if($GLOBALS['panel_time_step']<1){$GLOBALS['panel_time_step']=1;}
    }
    if (isset($_POST['panel_time_forward'])){
        $GLOBALS['panel_time_timestamp'] += $GLOBALS['panel_time_step']*$GLOBALS['panel_time_step_unit'];
        if($GLOBALS['panel_time_timestamp']>$GLOBALS['now_time']){$GLOBALS['panel_time_timestamp']=$GLOBALS['now_time'];}
    }
    if (isset($_POST['panel_time_back'])){
        $GLOBALS['panel_time_timestamp'] -= $GLOBALS['panel_time_step']*$GLOBALS['panel_time_step_unit'];
        if($GLOBALS['panel_time_timestamp']<1){$GLOBALS['panel_time_timestamp']=1;}
    }
    if (isset($_POST['panel_time_apply'])){
        if ((isset($_POST['panel_time_time']))&&(isset($_POST['panel_time_date']))){
            $GLOBALS['panel_time_timestamp'] = strtotime($_POST['panel_time_date'].' '.$_POST['panel_time_time']);
        }
    }

    session_start();
    $_SESSION[$GLOBALS['file_name']]['panel_time'] = array(
        'panel_time_type' => $GLOBALS['panel_time_type'],
        'panel_time_timestamp' => $GLOBALS['panel_time_timestamp'],
        'panel_time_step' => $GLOBALS['panel_time_step'],
    );
    session_write_close();

} ?>
