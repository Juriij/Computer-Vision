<?php
function panel_alarms_show(){
    ?>

    <!--PANEL ALARM -->
    <div style='clear:left;height:0px;'> </div>

    <table style="border-collapse: collapse;">
        <tbody>
            <tr>
                <td>
    <table id='panel_alarms' class='panel abstract_left'>
    <tr><td>
        <table class='panel_title'>
            <tr>
                <td class='panel_title_text1'>
                    <div class='panel_title_text2'>
                    <?= T_('Alarm panel') ?>
                    </div>
                </td>
                <td><div class='title_corner_right1'> <div class='title_corner_right2'> </div></div> </td>
            </tr>
        </table>

        <div class='panel_content' >

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Time</th>
                            <th> </th>
                            <th> </th>
                        </tr>
                    </thead>
                    <tbody id='tbody_alarms'>
                    </tbody>
                </table>
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
    <div  id='panel_show_hide_alarms' class='panel_show_hide abstract_left '>
        <div class='panel_show_hide_outside'>
            <div class='panel_show_hide_inside'>
                <i class="panel_icon fa fa-exclamation-triangle " aria-hidden="true"></i><!--&#9888;-->
            </div>
        </div>
    </div>
                </td>
            </tr>
        </tbody>
    </table>
<?php
}

function panel_alarms_script(){
    if (isset($_SESSION['userId'])) $panel_alarms_first_show = 'first_hide';
    else $panel_alarms_first_show = 'hide';
    ?>

    <!--PANEL ALARM SCRIPT -->
    <script>
    $(document).ready(function(){

        var panel_alarms = sessionStorage.getItem('panel_alarms');
        if(panel_alarms===null)
            panel_alarms = '<?= $panel_alarms_first_show ?>';

        if((panel_alarms==='hide')||(panel_alarms==='first_hide')){
            panel_alarms_animate_hide(0);
        }

        $('#panel_show_hide_alarms').click(function(){
            if((panel_alarms==='hide')||(panel_alarms==='first_hide')){
                panel_alarms_animate_show(1000);
                panel_alarms = 'show';
                window.sessionStorage.setItem('panel_alarms', 'show');
            }else{
                panel_alarms_animate_hide(1000);
                panel_alarms = 'hide';
                window.sessionStorage.setItem('panel_alarms', 'hide');
            }
        });

        function panel_alarms_animate_hide(delay=0){
            $('#panel_alarms').animate({
                    'left' : '-='+$('#panel_alarms').width()+'px'
                },delay);
            $('#panel_show_hide_alarms').animate({
                    'left' : '-='+$('#panel_alarms').width()+'px'
                },delay);
        }
        function panel_alarms_animate_show(delay=0){
            $('#panel_alarms').animate({
                    'left' : '+='+$('#panel_alarms').width()+'px'
                },delay);
            $('#panel_show_hide_alarms').animate({
                    'left' : '+='+$('#panel_alarms').width()+'px'
                },delay);
        }
        function get_alarms(){
            start_time = new Date().getTime();
            var refresh_time = <?= $GLOBALS['variable_refresh_time'] ?>;
            $.ajax({
                url:'ajax_get_alarms.php',
                type: 'POST',
                success:function(data){
                    $('#tbody_alarms').html(data);

                    if(data.substring(0, 14)==='<!-- no alarms'){
                        $('#panel_show_hide_alarms .panel_show_hide_inside').removeClass('panel_red');
                        if(panel_alarms==='first_show'){
                            panel_alarms_animate_show(1000);
                            panel_alarms = 'first_hide';
                        }
                    }else{
                        $('#panel_show_hide_alarms .panel_show_hide_inside').addClass('panel_red');

                        if(panel_alarms==='first_hide'){
                            panel_alarms_animate_show(1000);
                            panel_alarms = 'first_show';
                        }
                    }

                    $('.alarm_history').hover(function(){
                        var popup_element = $(this).children('.popup_element');
                        var dataurl = $(this).attr('data-url');
                        var dcu_id = $(this).attr('data-dcu_id');
                        var var_id = $(this).attr('data-var_id');
                        var alarm_id = $(this).attr('data-alarm_id');
                        $.ajax({
                            url: dataurl,
                            type: 'POST',
                            data: {
                                dcu_id : dcu_id,
                                var_id : var_id,
                                alarm_id : alarm_id,
                            } ,
                            success: function (output) {
                                popup_element.html(output);
                                popup_element.show(0);
                            },
                            error: function(){
                                popup_element.html(_T('Some error'));
                            }
                        });

                    }, function(){
                        $(this).children('.popup_element').hide(500);
                    });

                    var end_time = new Date().getTime();
                    var diff_time = end_time-start_time;
                    if(diff_time<refresh_time){
                        setTimeout(function(){get_alarms();}, refresh_time-diff_time);
                    }else{
                        get_alarms();
                    }
                },
                error:function(jqXHR, textStatus, errorThrown ){
                    console.log('error'+textStatus);
                    $('#tbody_alarms').html('ERROR '+textStatus);
                    setTimeout(function(){get_alarms();}, refresh_time);
                },
                complete:function(jqXHR, textStatus ){
                }
            });
        }
        get_alarms();
    });
    </script>
<?php
}
?>
