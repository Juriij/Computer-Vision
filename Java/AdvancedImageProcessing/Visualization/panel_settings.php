<?php

function panel_settings_show(){
?>

<!--PANEL SETTINGS -->
<span class='setting_simple haslink' id='panel_show_hide_settings'>
    <i class='fa fa-cog  fa-2x' aria-hidden='true'></i><!--&#9881;-->
</span>

<div id='panel_settings' class='panel abstract_top' >
    <table class='panel_title'>
        <tr>
            <td class='panel_title_text1'>
                <div class='panel_title_text2'>
                <?= T_('Settings') ?>
                </div>
            </td>
            <td><div class='title_corner_right1'> <div class='title_corner_right2'> </div></div> </td>
        </tr>
    </table>

    <form id='panel_settings_form' class='panel_content' name='panel_settings_form'
          method='post' action='index.php?<?= $_SERVER["QUERY_STRING"] ?>'>
        <table>
            <tr>
                <th colspan='2'>
                    <?= T_('version') ?> <?= $GLOBALS['visualization_version'] ?>
                </th>
            </tr>
            <tr>
                <td><a href='./help.html' target='_blank'><?= T_('Help') ?></a></td>
                <td></td>
            </tr>
            <?php
            if( (isset($_SESSION['showControlSystem'])) && ($_SESSION['showControlSystem']) ){
            ?>
            <tr>
                <td id="upgrade" class="haslink"><?= T_('Download & Upgrade') ?></td>
                <td></td>
            </tr>
            <tr>
                <td><?= T_('Default language') ?>:</td>
                <td>
                    <div class='select-wrapper'>
                        <select name='lang_actual_default'>
                            <?php
                            foreach($GLOBALS['ALL_LANGUAGES'] as $key=>$value){
                                echo "\n<option value=".$value;
                                if ($GLOBALS['lang_actual'] == $key){
                                    echo ' selected ';
                                }
                                echo ">$value</option>";
                            }
                            ?>

                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?= T_('Actual time zone') ?>:</td>
                <td>
                    <div class='select-wrapper'>
                        <select name='timezone_actual' >
                        <?php
                        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
                        foreach($tzlist as $timezone){
                            echo "\n<option value='$timezone' ";
                            if ($GLOBALS['timezone_actual'] == $timezone){
                                echo 'selected';
                            }
                            echo ">$timezone</option>";
                        }
                        ?>

                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td><a href='https://www.php.net/manual/en/function.date.php'
                       title='PHP <?= T_('Date format') ?>'><?= T_('Date format') ?> *:</a></td>
                <td><input type='text' name='date_format_default' value='<?= $GLOBALS['date_format'] ?>' /></td>
            </tr>
            <tr>
                <td><?= T_('Time panel') ?> <?= T_('default for') ?> <?= T_('Step') ?>:</td>
                <td>
                    <div class='select-wrapper'>
                        <select name='panel_time_step_unit_default'>
                            <?php
                            $options = array(
                                array('value'=>1, 'label'=>T_('sec')),
                                array('value'=>60, 'label'=>T_('min')),
                                array('value'=>3600, 'label'=>T_('hour')),
                                array('value'=>3600*24, 'label'=>T_('day')),
                            );
                            foreach($options as $option){
                                echo "\n<option value=".$option['value'];
                                if ($GLOBALS['panel_time_step_unit'] == $option['value']){
                                    echo ' selected ';
                                }
                                echo ">".T_($option['label'])."</option>";
                            }
                            ?>

                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?= T_('Time panel') ?> <?= T_('default for') ?> <?= T_('Step size') ?>:</td>
                <td><input type='number' name='panel_time_step_default'
                           min='1'
                           value='<?= $GLOBALS['panel_time_step'] ?>' /></td>
            </tr>
            <tr>
                <td><?= T_('Default background color') ?>:</td>
                <td><input type='color' name='default_color_background'
                           value='<?= $GLOBALS['default_color_background'] ?>' /></td>
            </tr>
            <tr>
                <td><?= T_('Default text color') ?>:</td>
                <td><input type='color' name='default_color_text'
                           value='<?= $GLOBALS['default_color_text'] ?>' /></td>
            </tr>
            <tr>
                <td><?= T_('Refresh time for variables and alarms (in msec)') ?>:
                <?php if (isset($GLOBALS['variable_refresh_time_local']))
                    echo "<br /><span style='color:red;'>This refresh is set in local php file!</span>"; ?>
                </td>
                <td><input type='number' name='variable_refresh_time_default'
                           min='100'
                           value=<?= $GLOBALS['variable_refresh_time'] ?> /></td>
            </tr>
            <tr>
                <td><?= T_('Refresh time for functions (in msec)') ?>:
                <?php if (isset($GLOBALS['parameter_refresh_time_local']))
                    echo "<br /><span style='color:red;'>This refresh is set in local php file!</span>"; ?>
                </td>
                <td><input type='number' name='parameter_refresh_time_default'
                           min='100'
                           value=<?= $GLOBALS['parameter_refresh_time'] ?> /></td>
            </tr>
            <tr>
                <td><?= T_('Maximum waiting time in 100msec for UCC-DCU call (recommended 30)') ?>:</td>
                <td><input type='number' name='dcuresponsewaiting_default'
                           value=<?= $GLOBALS['dcuresponsewaiting'] ?> /></td>
            </tr>
            <tr>
                <td><?= T_('Sound') ?>:</td>
                <td>
                    <div class='select-wrapper'>
                        <select name='sound_default'>
                            <option value='off' <?php if ($GLOBALS['sound'] == 'off'){
                                    echo ' selected ';
                                } ?>><?= T_('Off')?></option>
                            <option value='on' <?php if ($GLOBALS['sound'] == 'on'){
                                    echo ' selected ';
                                } ?>><?= T_('On')?></option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?= T_('Grid') ?>:</td>
                <td>
                    <div class='select-wrapper'>
                        <select name='grid_default'>
                            <option value='off' <?php if ($GLOBALS['grid'] == 'off'){
                                    echo ' selected ';
                                } ?>><?= T_('Off')?></option>
                            <option value='on' <?php if ($GLOBALS['grid'] == 'on'){
                                    echo ' selected ';
                                } ?>><?= T_('On')?></option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan='2' style="white-space:nowrap;">
                    <button type='submit' name='session_clear'>
                        <?= T_('Clear session') ?>
                    </button>
                    <button type='button' name='cancel'>
                        <?= T_('Cancel') ?>
                    </button>
                    <button type='submit' name='settings_default_submit'>
                        <?= T_('Save') ?>
                    </button>
                </td>
            </tr>
            <tr>
                <td>memory_get_usage</td>
                <td><?= memory_get_usage() ?></td>
            </tr>
        <?php
        }else{
        ?>

            <tr>
                <td><?= T_('More settings after login.') ?></td>
                <td> </td>
            </tr>
        <?php
        }
        ?>

        </table>
    </form>

    <table class='panel_bottom'>
        <tr>
            <td><div class='corner_left1'> <div class='corner_left2'> </div></div> </td>
            <td class='panel_bottom_inside1'><div class='panel_bottom_inside2'> </div> </td>
        </tr>
    </table>
</div>
<?php
}

function panel_settings_script(){
?>
    <!--PANEL SETTINGS SCRIPT -->
    <script>
        $(document).ready(function(){

            $('#panel_show_hide_settings').click(function(){
                if($('#panel_settings').is(':hidden')) {
                    hide_all_panels();
                    $('#panel_settings').show(500);
                }else {
                    $('#panel_settings').hide(500);
                }
            });

            $('#panel_settings').find('[name=cancel]').click(function(){
                $('#panel_settings').hide();
            });

            $('#upgrade').click(function(){
                swal({
                    title: "Download, unzip, backup and replace files? ",
                    text: "For manual upgrade go to Help",
                    buttons: ["No", "Yes"],
                    icon: "info",
                })
                .then((answer) => {
                    if (answer) {window.location.href='./upgrade.php';}
                    else {}
                });
            });
        });

    </script>
<?php
}
?>