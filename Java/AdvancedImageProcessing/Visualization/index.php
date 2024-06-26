<?php
//ini_set("session.cookie_secure", 1);

include('settings_default.php');
include('http_params.php');
include('functions.php');
include('functions_db.php');
include('./classes/UCC/ucc_default.php');
include('panel_login.php');
include('panel_settings.php');
include('panel_menu.php');
include('header.php');

// Connect to DB
$dbLink_Config = dcu_opendb('Config');
$dbLink_DB = dcu_opendb('DB');
$dbLink_Hist = array();
$dbLink_ArchHist = array();
$dcuConns = get_dcu_conns();

panel_login_posts();
$GLOBALS['theme_color_background']=$GLOBALS['default_color_background'];
$GLOBALS['theme_color_text']=$GLOBALS['default_color_text'];
$menu = get_menu_hierarchy();

//FILE
$file_name = get_GET_parameter('file');
$component_script = NULL;
if(file_exists($file_name)){
    $directory = dirname($file_name);
    if($directory!='content/static'){
        include('./classes/components/component_default.php');
        include('./classes/class_events.php');
        $component_script = './scripts/component_scripts.php';
        $dispvars = array();
    }
}else{
    $GLOBALS['errors']['general'][] = sprintf(T_('File %s not found.'),$file_name)
        .T_('Check your filename.');
    $file_name = './content/404.php';
}

try{
    include($file_name);
}catch (\Error $e) {
    $GLOBALS['errors']['general'][] = $e;
}

if (isset($GLOBALS['variable_refresh_time_local']))
    $GLOBALS['variable_refresh_time'] = $GLOBALS['variable_refresh_time_local'];
if (isset($GLOBALS['parameter_refresh_time_local']))
    $GLOBALS['parameter_refresh_time'] = $GLOBALS['parameter_refresh_time_local'];

$site_title = $file_name;
session_start();
$_SESSION[$file_name]['security_lowest_level'] = $GLOBALS['security_lowest_level'];
$_SESSION[$file_name]['security_exception_ids'] = $GLOBALS['security_exception_ids'];
if(check_allow(15, array()))
    $_SESSION[$file_name]['show_panel_errors'] = $GLOBALS['show_panel_errors'];
else
    $_SESSION[$file_name]['show_panel_errors'] = false;
session_write_close();

//MENU
list($actual_menu_type, $actual_menu_item_id, $actual_submenu_item) = get_actual_menu($menu, $file_name);
if(($actual_menu_type!=-1)&&($actual_menu_item_id!=-1)){
    $site_title = $menu[$actual_menu_type][$actual_menu_item_id]['name'];

    if(!empty($actual_submenu_item)){
        //$site_title .= ' / '. $actual_submenu_item['name'];
        $site_title = $actual_submenu_item['name'];
        if($actual_menu_type == 'service')
            $site_title .= ' for '.$_SESSION['dcu_id_selected'].' '.$dcuConns[$_SESSION['dcu_id_selected']]['visualName'];
    }
}

if($GLOBALS['show_panel_time']){
    include('./panel_time.php');
    panel_time_posts();  //every file_name has it's own session
}
if($GLOBALS['show_panel_alarms']) include('./panel_alarms.php');
if($GLOBALS['show_panel_errors']) include('./panel_errors.php');

?>
<!DOCTYPE html>
<html lang="en">
<?php include('./head.php');?>
<body>
<noscript>
    <p style='color:red;font-size:30px;'>
        <?= T_('Javascript is disabled in your web browser.')."\n" ?>
    </p>
    <p style='color:green;font-size:30px;'>
        <u>
        <a href='https://www.enable-javascript.com/'>
        <?= T_('How to enable JavaScript in your browser')."\n" ?>
        </a>
        </u>
    </p>
</noscript>
<!-- HEADER -->
    <?php
    if($GLOBALS['show_header']) header_show();
    ?>
<!-- CONTENT -->

<?php
    if(check_allow($GLOBALS['security_lowest_level'], $GLOBALS['security_exception_ids'])){
        if (isset($dispvars)){
            include('./component_transformation.php');
            component_calculation();
        }

        echo "        <div id='content'>";
        display_content();
        echo "        </div>";

    }else{
        echo T_('Low security level for permission');
        $GLOBALS['errors']['general'][] = T_('Low security level for permission');
    }
?>

<script>
    <?php if (($component_script)&&($panel_time_type == 'actual')){?>

    get_variables();
    get_variables2();
    get_params();
    get_rtudpios();
    <?php }?>

//    $(document).ready(function() {
//      $('.dragdrop').each(function () {
//        dragElement($(this)[0]);
//      });
//    } );
</script>

<!-- PANELS -->
    <?php
    if($GLOBALS['show_panel_errors']) panel_errors_show();
    if($GLOBALS['show_panel_time']) panel_time_show();
    if($GLOBALS['show_panel_alarms']) panel_alarms_show();
    ?>

</body>
</html>
<?php
// Disconnect from DB
$dbLink_Config->close();
$dbLink_DB->close();
foreach($dbLink_Hist as $dbLink) if (!empty($dbLink)) $dbLink->close();
foreach($dbLink_ArchHist as $dbLink) if (!empty($dbLink)) $dbLink->close();
?>
