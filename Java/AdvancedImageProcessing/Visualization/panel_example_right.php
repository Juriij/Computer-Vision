<?php
function panel_example_show(){
    ?>
    <!--PANEL EXAMPLE -->
    <div style='clear:right;height:0px;'> </div>
    <table id='panel_example' class='panel abstract_right' >
    <tr><td>

        <table class='panel_title'>
            <tr>
                <td><div class='title_corner_left1'> <div class='title_corner_left2'> </div></div> </td>
                <td class='panel_title_text1'>
                    <div class='panel_title_text2'>
                    <?= T_('Example panel') ?>

                    </div>
                </td>
            </tr>
        </table>

        <div class='panel_content' >
        </div>

        <table class='panel_bottom'>
            <tr>
                <td class='panel_bottom_inside1'><div class='panel_bottom_inside2'> </div> </td>
                <td><div class='corner_right1'> <div class='corner_right2'> </div></div> </td>
            </tr>
        </table>
    </td></tr>
    </table>

    <div class='panel_show_hide abstract_right' id='panel_show_hide_example' >
        <div class='panel_show_hide_outside'>
            <div class='panel_show_hide_inside panel_orange'>
                <i class="fa fa-times-circle-o" aria-hidden="true"></i>
            </div>
        </div>
    </div>
<?php
}

function panel_example_script(){
?>
    <!--PANEL EXAMPLE SCRIPT-->
    <script>
    $(document).ready(function(){

        var panel_example = sessionStorage.getItem('panel_example');
        if (panel_example=='hide'){
            $('#panel_example').animate({
                    'right' : '-='+$('#panel_example').width()+'px'
                },0);
            $('#panel_show_hide_example').animate({
                    'right' : '-='+$('#panel_example').width()+'px'
                },0);
        }

        $('#panel_show_hide_example').click(function(){
            if (panel_example=='hide'){
                $('#panel_example').animate({
                        'right' : '+='+$('#panel_example').width()+'px'
                    },1000);
                $('#panel_show_hide_example').animate({
                        'right' : '+='+$('#panel_example').width()+'px'
                    },1000);
                panel_example = 'show';
                window.sessionStorage.setItem('panel_example', 'show');
            }else{
                $('#panel_example').animate({
                        'right' : '-='+$('#panel_example').width()+'px'
                    },1000);
                $('#panel_show_hide_example').animate({
                        'right' : '-='+$('#panel_example').width()+'px'
                    },1000);
                panel_example = 'hide';
                window.sessionStorage.setItem('panel_example', 'hide');
            }
        });

    });

    function calculate_new_position(panel_type){
        var panel_status = sessionStorage.getItem('panel_'+panel_type);
        var substract_width = 0;
        if (panel_status==='hide'){
            substract_width = $('#panel_'+panel_type).width();
        }
        var new_position = -$(window).scrollLeft() - substract_width;
        $('#panel_'+panel_type).css({right: new_position});
        $('#panel_show_hide_'+panel_type).css({right: new_position});

    }

    $(window).scroll(function(){
        calculate_new_position('example');
    });
    </script>
<?php
}
?>