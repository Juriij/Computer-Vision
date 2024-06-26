<?php
function print_menu_link($menu_type, $name, $actual_menu){
    $more_params = '';
    if (isset($actual_menu['dcus']))
        $more_params .= '&dcus='.$actual_menu['dcus'];
    if (isset($actual_menu['event_types']))
        $more_params .= '&event_types='.$actual_menu['event_types'];

    if(isset($actual_menu['file'])){
        echo "<a href='index.php?"
            . "file=".$actual_menu['file']
            . "$more_params' >$name</a>";
    }else
        echo "<a>$name</a>";
}

function get_actual_menu($menu, $file=''){
    $actual_menu_type = -1;
    $actual_menu_item_id = -1;
    $actual_submenu_item = array();

    if (!empty ($file)) {
        foreach ($menu as $menu_type=>$menu_items)
        foreach ($menu_items as $menu_item){
            if( (!empty($menu_item['file'])) && ($menu_item['file']==$file) ){
                $actual_menu_type = $menu_type;
                $actual_menu_item_id = $menu_item['id'];
            }
            foreach ($menu_item['submenu'] as $submenu_item)
                if( (!empty($submenu_item['file'])) && ($submenu_item['file']==$file) ){
                    $actual_menu_type = $menu_type;
                    $actual_menu_item_id = $menu_item['id'];
                    $actual_submenu_item = $submenu_item;
                    return array($actual_menu_type, $actual_menu_item_id, $actual_submenu_item);
                }
        }
    }

    return array($actual_menu_type, $actual_menu_item_id, $actual_submenu_item);
}

function panel_menu_show(){
    global $menu;
    global $actual_menu_type;
    global $actual_menu_item_id;
    global $actual_submenu_item;
?>
    <!--PANEL MENU -->
    <span class='setting_simple haslink' id='panel_show_hide_menu'>
        <i class="panel_icon fa fa-bars fa-2x " aria-hidden="true"></i><!--&#8801;-->
    </span>

    <div id='panel_menu' class='panel abstract_top'>
    <table>
    <tr><td>

        <table class='panel_title'>
            <tr>
                <td class='panel_title_text1'>
                    <div class='panel_title_text2'>
                    <?= T_('Main menu') ?>
                    </div>
                </td>
                <td><div class='title_corner_right1'> <div class='title_corner_right2'> </div></div> </td>
            </tr>
        </table>

        <div class='panel_content' >

            <ul class='menu'>
                <?php
                foreach ($menu as $menu_type=>$menu_items)
                foreach ($menu_items as $menu_item){
                    $id = $menu_item['id'];  //petra: skontrolovat
                    $type_and_id = "$menu_type"."_"."$id";
                    $name = $menu_item['name'];
                    $submenu = $menu_item['submenu'];

                    $active = '';
                    if(($menu_type==$actual_menu_type)&&($id==$actual_menu_item_id))
                        $active = 'active';

                    $expand = '';
                    if (!empty($submenu)) $expand = 'expand';

                    echo "\n<li id='$type_and_id' class='menu_item $active $expand' >";
                    if($menu_type=='welcome'){
                        print_menu_link($menu_type, $name, $menu_item);
                    }else{
                        if ($expand){
//&#9207;⏷
//&#8964;
//&#11167; ⮟
//&#9662; ▾
//&#9663; ▿
                            echo '<div class="menu_plus down"> <i class="fa fa-angle-down" aria-hidden="true"></i></div>';
                            echo '<div class="menu_plus up" hidden> <i class="fa fa-angle-up" aria-hidden="true"></i></div>';
                        }
                        echo "<a>$name</a>";
                    }
                    echo '</li>';

                    if((!empty($submenu))){
                        echo "\n<li id='submenu_$type_and_id' class='submenu' ";
                        if(!$active) echo ' hidden ';
                        echo ">";
                        echo "\n<ul>";
                        if ($menu_type=='service'){
                        ?>

<li class="submenu_selector">
    <form id='dcu_form' method='post' action='index.php?<?= remove_query_offset() ?>' name='dcu_form' >
    <div class='select-wrapper'>
        <select id='dcu_select' name='dcu_select'>
            <?php
                global $dcuConns;
                foreach ($dcuConns as $dcuConn){
                    $selected = '';
                    if (isset($_SESSION['dcu_id_selected']) && ($_SESSION['dcu_id_selected']==$dcuConn['cUnitId'])){
                        $selected = ' selected ';
                    }
                    echo "\n<option value='".$dcuConn['cUnitId']."' $selected >"
                        . $dcuConn['cUnitId'].' - '.$dcuConn['visualName']."</option>";
                }
            ?>

        </select>
    </div>
    </form>
</li>

                        <?php
                        }

                        foreach ($submenu as $submenu_item){
                            $sub_id = $submenu_item['id'];
                            $type_and_id_and_subid = "$menu_type"."_"."$id"."_"."$sub_id";
                            $active2 = '';
                            if(($active)&&($sub_id==$actual_submenu_item['id']))
                                $active2 = 'active';

                            echo "\n<li class='submenu_item $active2' id='submenu_$type_and_id_and_subid'>";
                            print_menu_link($menu_type, $submenu_item['name'], $submenu_item);
                            echo '</li>';
                        }
                        echo "\n</ul>";
                        echo "\n</li>";
                    }
                }
                ?>

            </ul>
        </div>
        <table class='panel_bottom'>
            <tr>
                <td><div class='corner_left1'> <div class='corner_left2'> </div></div> </td>
                <td class='panel_bottom_inside1'><div class='panel_bottom_inside2'> </div> </td>
            </tr>
        </table>

    </td></tr>
    </table>
    </div>
<?php
}

function panel_menu_script(){
    if (isset($_SESSION['userId'])) $logged_user = 1;
    else $logged_user = 0;
?>

    <!--PANEL MENU SCRIPT -->
    <script>
    $(document).ready(function(){

        $("#dcu_select").change(function(){
            $('#dcu_form').submit();
        });

        if(<?= $logged_user ?> == 1){
            var panel_menu = sessionStorage.getItem('panel_menu');
            if(panel_menu===null) panel_menu = 'show';
            if (panel_menu=='hide'){
                window.sessionStorage.setItem('panel_menu', 'hide');
            }else{
                hide_all_panels();
                $('#panel_menu').show();
                window.sessionStorage.setItem('panel_menu', 'show');
            }
        }else{
            panel_menu='hide';
        }

        $('#panel_show_hide_menu').click(function(){
            var panel_menu = sessionStorage.getItem('panel_menu');
            if (panel_menu=='hide'){
                hide_all_panels();
                $('#panel_menu').show(500);
                window.sessionStorage.setItem('panel_menu', 'show');
            }else{
                $('#panel_menu').hide(500);
                window.sessionStorage.setItem('panel_menu', 'hide');
            }
            if(<?= $logged_user ?> == 0){
                sessionStorage.clear();
            }
        });

        $('.expand').click(function(){
            $(this).next('.submenu').toggle(500);
            $(this).find('.down').toggle();
            $(this).find('.up').toggle();
        });
    });

    </script>
<?php
}

function get_menu_hierarchy(){
    $menu = array();
    $menu['welcome'] = array(
        1=> array(
            'id'=>1,
            'name'=>T_('Welcome'),
            'file'=>$GLOBALS['user_directory'].'/welcome.php',
            'static_page'=>0,
            'submenu'=>array(),
        )
    );
    $menu['process'] = select_menu('process');
    foreach ($menu['process'] as &$one_process){
        if (access_granted('process', $one_process['id'])){
            $one_process['submenu'] = select_submenu_process($one_process['id']);
        }

    }
    $menu['event'] = select_menu('event');
    foreach ($menu['event'] as &$one_event){
        if (access_granted('event', $one_event['id'])){
            $one_event['submenu'] = select_submenu_event($one_event['id']);
        }
    }


    if (access_granted('service')){
        $menu['service'] = array(
            1=> array(
                'id'=>1,
                'name'=>T_('Control system'),
                'submenu'=>array(),
            )
        );
        $menu['service'][1]['submenu'] = array(
            1=> array('id'=>1, 'name'=>T_('Parameters'), 'file'=>'content/static/unit.php', 'static_page'=>1),
            2=> array('id'=>2, 'name'=>T_('Control application'),
                'file'=>$GLOBALS['user_directory'].'/application.php', 'static_page'=>0),
            3=> array('id'=>3, 'name'=>T_('Variables'), 'file'=>'content/static/vars.php', 'static_page'=>1),
            4=> array('id'=>4, 'name'=>T_('Functions'), 'file'=>'content/static/fcns.php', 'static_page'=>1),
            5=> array('id'=>5, 'name'=>T_('Events'), 'file'=>'event/system_event_list.php', 'static_page'=>0),
            6=> array('id'=>6, 'name'=>T_('Alarms form'), 'file'=>'content/static/alarms_form.php', 'static_page'=>1),
            7=> array('id'=>7, 'name'=>T_('Advanced supervision'), 'file'=>'content/static/ass.php', 'static_page'=>1),
        );
    }

    if (access_granted('examples')){
        $menu['examples'] = array(
            1=> array(
                'id'=>1,
                'name'=>T_('Examples'),
                'submenu'=>array(),
            )
        );

        $menu['examples'][1]['submenu'] = array(
            1=> array('id'=>1, 'name'=>T_('All'), 'file'=>'content/examples/example.php', 'static_page'=>0),
            2=> array('id'=>2, 'name'=>T_('Variables'), 'file'=>'content/examples/example_variables.php', 'static_page'=>0),
            3=> array('id'=>3, 'name'=>T_('Graph'), 'file'=>'content/examples/example_graph.php', 'static_page'=>0),
            4=> array('id'=>4, 'name'=>T_('DB'), 'file'=>'content/examples/example_db.php', 'static_page'=>0),
            5=> array('id'=>5, 'name'=>T_('Functions'), 'file'=>'content/examples/example_functions.php', 'static_page'=>0),
            6=> array('id'=>6, 'name'=>T_('Servers'), 'file'=>'content/examples/example_servers.php', 'static_page'=>0),
            7=> array('id'=>7, 'name'=>T_('Errors'), 'file'=>'content/examples/example_errors.php', 'static_page'=>0),
            8=> array('id'=>8, 'name'=>T_('Example old'), 'file'=>'content/examples/example_old.php', 'static_page'=>0),
            9=> array('id'=>9, 'name'=>T_('Book of designs'), 'file'=>'content/static/vzorkovnik_prvkov.php', 'static_page'=>1),
        );

    }
    return $menu;
}

function select_menu($type1){
    global $dbLink_Config;
    global $lang_actual;
    if(strcmp($type1, "process")==0){
        $query="SELECT viewId AS id, visualName AS name, visualFile AS file FROM processViews ORDER by viewId";
    }else{
        $query="SELECT viewId AS id, visualName AS name, visualFile AS file FROM eventViews ORDER by viewId";
    }
    $result = $dbLink_Config->query($query);

    $obsahTab = array();
    if (($result) and ($result->num_rows)){
        while ($row = $result->fetch_assoc()){
            $name = $row['name'];
            $lang_start = strpos($name, $lang_actual.':');
            if ($lang_start !== false){
                $lang_end = strpos($name, ',', $lang_start);
                if ($lang_end == false) $lang_end = strlen($name);

                $row['name'] = substr($name, $lang_start+3, $lang_end-$lang_start-3);
            }
            $row['submenu'] = array();
            $obsahTab [$row['id']] = $row;
        }

    }
    return $obsahTab;
}

function select_submenu_process($gid){
    global $dbLink_Config;
    global $lang_actual;

    $query="SELECT visualName AS name,visualFile AS file FROM processViewsPages WHERE groupId=" . $gid . " ORDER by pageId";
    $result = $dbLink_Config->query($query);
    $obsahTab = array();
    if (($result) and ($result->num_rows)){
        $id = 1;
        while ($row = $result->fetch_assoc()){
            $name = $row['name']; //e.g.: en:Alarms,sk:Alarmy,cz:Alarmy
            $lang_start = strpos($name, $lang_actual.':');
            if ($lang_start !== false){
                $lang_end = strpos($name, ',', $lang_start);
                if ($lang_end == false) $lang_end = strlen($name);

                $row['name'] = substr($name, $lang_start+3, $lang_end-$lang_start-3);
            }
            $row['id'] = $id;
            $row['static_page'] = 0;
            $row['submenu'] = array();
            $obsahTab[$id] = $row;
            $id++;
        }
    }
    return $obsahTab;
}

function select_submenu_event($gid){
    global $dbLink_Config;
    global $lang_actual;

    $query = " SELECT eventsAlarms AS event_types, dcuIds AS dcus, visualName AS name, visualFile AS file "
        . " FROM eventViewsPages WHERE groupId=" . $gid . " ORDER by pageId";
    $result = $dbLink_Config->query($query);
    $obsahTab = array();
    if (($result) and ($result->num_rows)){
        $id = 1;
        while ($row = $result->fetch_assoc()){
            $name = $row['name'];
            $lang_start = strpos($name, $lang_actual.':');
            if ($lang_start !== false){
                $lang_end = strpos($name, ',', $lang_start);
                if ($lang_end == false) $lang_end = strlen($name);

                $row['name'] = substr($name, $lang_start+3, $lang_end-$lang_start-3);
            }
            $row['id'] = $id;
            $row['static_page'] = 0;
            $row['submenu'] = array();  //petra: treba?
            if ($row['event_types']==1){$row['event_types'] = 'all';}
            else {$row['event_types'] = 'alarms';}
            $obsahTab[$id] = $row;
            $id++;
        }
    }
    return $obsahTab;
}