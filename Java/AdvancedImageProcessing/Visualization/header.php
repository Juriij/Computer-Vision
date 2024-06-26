<?php
function header_show(){
    global $site_title;
?>
<header>
<table id='site_header'>
    <tr>
    <td>
    <div id='branding_outside'>
        <div id='branding_middle'>
            <div id='branding_inside'>
                <a href='index.php'>
                    <img src='logo.png' alt='ProSystemy' style='padding:5px 40px 5px 5px;'>
                </a>
            </div>
        </div>
    </div>
    </td>

    <td style="overflow:hidden;white-space:nowrap;max-width: 300px;">
        <div id='site_title'> <?= $site_title ?></div>
    </td>
    <td>
    <nav id='settings_outside'>
        <div id='settings_middle'>
            <div id='settings_inside'>
                <table style='margin-right: 0px;margin-left: auto; border-spacing: 0px;'>
                    <tr>
                <td class='mobile_hide'> <?php panel_language_show(); ?></td>
                <td style='position: relative;'
                    class='mobile_hide'> <?php panel_login_show(); ?></td>
                <td style="position: relative;"
                    class='mobile_show'> <?php panel_settings2_show(); ?></td>
                <td style="position: relative;"> <?php panel_settings_show(); ?></td>
                <td style="position: relative;"> <?php panel_menu_show(); ?></td>
                </tr></table>
            </div>
        </div>
    </nav>
    </td>
    </tr>
</table>
</header>
<?php
}
function panel_settings2_show(){
?>

    <!--PANEL SETTINGS2 -->
    <span class='setting_simple' id='panel_show_hide_settings2'>
        <i class='fa fa-cogs  fa-2x' aria-hidden='true'></i> <!--&#9881;-->
    </span>

    <div id='panel_settings2' class='panel abstract_top' >
        <div class='panel_content'>
        <div class='setting'><?php panel_language_show(); ?></div>
        <br />
        <?php
            if (!isset($_SESSION['login_username'])){
                login_form_show();
            }else{
                echo '<i class="fa fa-user" aria-hidden="true"></i>';//&#128100;
                echo $_SESSION['login_username'];
                logout_form_show();
            }

        ?>

        </div>
    </div>
<?php
}
function panel_language_show(){
?>

    <!--PANEL LANGUAGE -->
    <form method="post" action='index.php?<?= $_SERVER["QUERY_STRING"] ?>'
          name="language_form" class="language_form setting_simple" >
    <select name="language_select" class="language_select">
        <?php
        foreach($GLOBALS['ALL_LANGUAGES'] as $key=>$value){
            echo "\n<option value='$key' ";
            if ($GLOBALS['lang_actual'] == $key){
                echo 'selected';
            }
            echo ">$value</option>";
        }
        ?>

    </select>
    <input name='language' type='submit' value='' hidden />
    </form>
<?php
}

function header_script(){
    if (isset($_SESSION['userId'])) $panel_settings2_first_show = 'hide';
    else $panel_settings2_first_show = 'show';
    ?>

    <!--header SCRIPT -->
    <script>

    $(document).ready(function(){

        $(".language_select").change(function(){
                $(this).parent().submit();
            });
        //timezone script removed

        var panel_settings2 = '<?= $panel_settings2_first_show ?>';
        if (panel_settings2=='hide'){
            $('#panel_settings2').hide();
        }else{
            $('#panel_settings2').show();
        }
        $('#panel_show_hide_settings2').click(function(){
            if($('#panel_settings2').is(':hidden')) {
                hide_all_panels();
                $('#panel_settings2').show(500);
            }else {
                $('#panel_settings2').hide(500);
            }
        });
    });
    </script>
<?php
}
