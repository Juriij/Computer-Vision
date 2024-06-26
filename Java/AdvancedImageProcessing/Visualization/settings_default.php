<?php
$visualization_version = '1_3_3';

session_start();
session_write_close();

if(isset($_POST['session_clear'])){
    session_start();
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(),'',0,'/');
    //session_regenerate_id(true);
    // todo: sessionStorage.clear()
}

if ((!defined('PHP_VERSION_ID'))||(PHP_VERSION_ID < 50500)) {
    die('Upgrade PHP. See Installation guide.');
}
$errors = array();
$datafiledir = 'data';
$ALL_LANGUAGES = array('en_US'=>'en_US','sk_SK'=>'sk_SK','cz_CZ'=>'cz_CZ');
$user_directory = 'process';

if (isset($_POST['settings_default_submit'])){

    $myfile = fopen("./$user_directory/_settings.php", 'w');

    $success=fwrite($myfile, "<?php \n");
    fwrite($myfile, print_r('$lang_actual = '."'".$_POST['lang_actual_default']."'; \n", true));
    fwrite($myfile, "//DATE AND TIME \n");
    fwrite($myfile, print_r('$timezone_actual = '."'".$_POST['timezone_actual']."'; \n", true));
    fwrite($myfile, print_r('$date_format = '."'".$_POST['date_format_default']."'; \n", true));

    fwrite($myfile, "//PANEL TIME \n");
    fwrite($myfile, print_r('$panel_time_step_unit = '.$_POST['panel_time_step_unit_default']."; \n", true));
    fwrite($myfile, print_r('$panel_time_step = '.$_POST['panel_time_step_default']."; \n", true));
    fwrite($myfile, "//OTHER \n");
    fwrite($myfile, print_r('$default_color_background = '."'".$_POST['default_color_background']."'; \n", true));
    fwrite($myfile, print_r('$default_color_text = '."'".$_POST['default_color_text']."'; \n", true));
    fwrite($myfile, print_r('$variable_refresh_time = '."'".$_POST['variable_refresh_time_default']."'; \n", true));
    fwrite($myfile, print_r('$parameter_refresh_time = '."'".$_POST['parameter_refresh_time_default']."'; \n", true));
    fwrite($myfile, print_r('$dcuresponsewaiting = '."'".$_POST['dcuresponsewaiting_default']."'; \n", true));
    fwrite($myfile, print_r('$sound = '."'".$_POST['sound_default']."'; \n", true));
    fwrite($myfile, print_r('$grid = '."'".$_POST['grid_default']."'; \n", true));

    //fwrite($myfile, print_r('$user_directory = '."'".$_POST['user_directory_default']."'; \n", true));
    fclose($myfile);

    if ($success===FALSE){
        $GLOBALS['errors']['general'][] = "Settings not updated, check permissions";
    }
}

include("./settings_db.php");

// can be overwritten in each file
if(file_exists("./$user_directory/_settings.php"))
    include("./$user_directory/_settings.php");

if (!isset($lang_actual))$lang_actual = 'en_US';
//DATE AND TIME
if (!isset($timezone_actual))$timezone_actual = 'Europe/Bratislava';
if (!isset($date_format))$date_format = 'd.m.Y';
//PANEL TIME
if (!isset($panel_time_step_unit))$panel_time_step_unit = 3600;  // time step in seconds [sec]=1, [min]=60, [hr]=3600, [day]=3600*24
if (!isset($panel_time_step))$panel_time_step = 1;
//OTHER
if (!isset($default_color_background))$default_color_background = '#1f232e';
if (!isset($default_color_text))$default_color_text = '#f2ffff';
if (!isset($variable_refresh_time))$variable_refresh_time =  '5000';
if (!isset($parameter_refresh_time))$parameter_refresh_time = '60000';
if (!isset($dcuresponsewaiting))$dcuresponsewaiting = '30';
if (!isset($sound))$sound = 'on';
if (!isset($grid))$grid = 'off';

// can be overwritten in each file
$security_lowest_level = 0; //allow only users with higher security level
$security_exception_ids = array();
$show_header = true;
$show_panel_errors = false;
$show_panel_time = true;
$show_panel_alarms = true;
$events_LIMIT = 1000;

$msg_text = array(
    'OVERRIDE' => 'OVERRIDE',
    'ALARM' => 'ALARM',
    'MISSED_CONNECTIONS' => 'MISSED CONNECTIONS',
    'OVERWRITE' => 'OVERWRITE',
    'VALUE_BELOW_MIN' => 'VALUE BELOW MIN',
    'VALUE_ABOVE_MAX' => 'VALUE ABOVE MAX',
    'WRONG_DCU' => 'WRONG DCU',
    'WRONG_ID' => 'WRONG ID',
    'WRONG_NAME' => 'WRONG NAME',
    'WRONG_PARAM_ID' => 'WRONG PARAM ID',
    //'NO_DATA' => 'NO DATA',
);

$now_time = time();
$panel_time_timestamp = $now_time;
$panel_time_type = 'actual'; // 'history';
$panel_time_deviation_value = "???";
$panel_time_dispersion_value = "???";
$panel_time_deviation_class = '';
$panel_time_oldest_value = "???";
$panel_time_min_time = "???";
$panel_time_max_time = "???";
?>
