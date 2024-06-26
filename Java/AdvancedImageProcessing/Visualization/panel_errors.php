<?php
function panel_errors_show(){
    filter_errors();
    $display = '';
    if(empty($GLOBALS['errors'])) $display = 'display:none;';
?>

    <!--PANEL ERRORS -->
    <div style='clear:right;height:0px;'> </div>
    <table style="border-collapse: collapse;">
        <tbody>
            <tr>
                <td>
    <table id='panel_errors' class='panel abstract_left' style='<?= $display ?>' >
    <tr><td>

        <table class='panel_title'>
            <tr>
                <td class='panel_title_text1'>
                    <div class='panel_title_text2'>
                    <?= T_('Error panel - Object no. - coordinates [left,top]').' ⇒ '.T_('error message') ?>

                    </div>
                </td>
                <td><div class='title_corner_right1'> <div class='title_corner_right2'> </div></div> </td>
            </tr>
        </table>

        <div class='panel_content' >
    <?php
    foreach ($GLOBALS['errors'] as $error_key=>$error_value){
        $id = 'error_'.$error_key;
        echo "<div id='$id'>";
        foreach($error_value as $error_value_type=>$error_value_msg){
            $id = 'error_'.$error_key.'_'.$error_value_type;
            echo "<div id='$id'>";
            echo $error_value_msg;
            echo "</div>";
        }
        echo "</div>";
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
    <div class='panel_show_hide abstract_left' id='panel_show_hide_errors' style='<?= $display ?>' >
        <div class='panel_show_hide_outside'>
            <div class='panel_show_hide_inside panel_orange'>
                <i class="fa fa-times-circle-o" aria-hidden="true"></i><!--&#8855;-->
            </div>
        </div>
    </div>
                </td>
            </tr>
        </tbody>
    </table>
<?php
}

function panel_errors_script(){
?>
    <!--PANEL ERRORS SCRIPT petra: spravit vsetky panely cez 1 funkciu-->
    <script>
    $(document).ready(function(){

        var panel_errors = sessionStorage.getItem('panel_errors');
        if (panel_errors=='hide'){
            $('#panel_errors').animate({
                    'left' : '-='+$('#panel_errors').width()+'px'
                },0);
            $('#panel_show_hide_errors').animate({
                    'left' : '-='+$('#panel_errors').width()+'px'
                },0);
        }

        $('#panel_show_hide_errors').click(function(){
            if (panel_errors=='hide'){
                $('#panel_errors').animate({
                        'left' : '+='+$('#panel_errors').width()+'px'
                    },1000);
                $('#panel_show_hide_errors').animate({
                        'left' : '+='+$('#panel_errors').width()+'px'
                    },1000);
                panel_errors = 'show';
                window.sessionStorage.setItem('panel_errors', 'show');
            }else{
                $('#panel_errors').animate({
                        'left' : '-='+$('#panel_errors').width()+'px'
                    },1000);
                $('#panel_show_hide_errors').animate({
                        'left' : '-='+$('#panel_errors').width()+'px'
                    },1000);
                panel_errors = 'hide';
                window.sessionStorage.setItem('panel_errors', 'hide');
            }
        });

    });
    </script>
<?php
}
?>