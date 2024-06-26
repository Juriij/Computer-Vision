<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="viewport" content="initial-scale = 1.0,maximum-scale = 1.0" />
    <title><?= $site_title ?> - Control System Visualization</title>
    <link rel="icon" href="favicon.ico">
    <link href="styles/style_default.css" rel="stylesheet" type="text/css">
<?php
    if (isset($dispvar_init))  // backward compatibility
    if (isset($dispvar_init->backgroundcolor))
        $GLOBALS['theme_color_background']=$dispvar_init->backgroundcolor;

    //unset($_COOKIE['theme_color_background']);
    setcookie('theme_color_background', $GLOBALS['theme_color_background'],
        0, //expires
        '/', //path
        null, //domain
        false//, //secure
        //false //httponly
        //'Lax' //samesite, only since PHP8
    );
    setcookie('theme_color_text', $GLOBALS['theme_color_text'],
        0, //expires
        '/', //path
        null, //domain
        false//true, //secure
        //false //httponly
        //'Lax' //samesite, only since PHP8
    );

    if ($GLOBALS['grid'] == 'on'){
        ?>
    <style>
        body {
            background-image:url(styles/images/grid.png);
            background-repeat:repeat;
            background-position: 0px 47px;
        }
    </style>
        <?php
    }
    ?>

    <link href="styles/style_background.php" rel="stylesheet" type="text/css">
    <link href="styles/style.php" rel="stylesheet" type="text/css">
    <link href="styles/style_android.css" rel="stylesheet" type="text/css">
    <!--http://jkorpela.fi/html/characters.html-->
    <link href="font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="scripts/jquery-3.1.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!--https://dygraphs.com/options.html#-->
    <script src='scripts/dygraph.min.js'></script>
    <script src='scripts/general_scripts.js'></script>
    <!-- https://datatables.net/examples/basic_init/zero_configuration.html -->
    <script src="scripts/DataTables-1.10.18/js/jquery.dataTables.js"></script>
    <!-- https://sweetalert.js.org/guides/ -->
    <script src='scripts/sweetalert.min.js'></script>

<?php
    panel_menu_script();
    panel_settings_script();
    panel_login_script();
    if($GLOBALS['show_header']) header_script();
    if($GLOBALS['show_panel_errors']) panel_errors_script();
    if($GLOBALS['show_panel_time']) panel_time_script();
    if($GLOBALS['show_panel_alarms']) panel_alarms_script();

    //<!-- COMPONENT SCRIPTS -->
    if ($GLOBALS['component_script']){
        include($GLOBALS['component_script']);
    }
?>

</head>