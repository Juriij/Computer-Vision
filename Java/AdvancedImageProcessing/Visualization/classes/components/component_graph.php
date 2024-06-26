<?php
class component_graph extends component_default{
    public function __construct($init = NULL, $type = 'variable_graph') {
        // functions and servers can not by displayed in graph
        parent::__construct($init,'variable_graph');
        $this->onclick[$this->onclick_index]['link'] = 'variable_graph';
        $this->onclick[$this->onclick_index]['tooltip'] = T_('Value evolution in graph');

    }

    public function display_component(){

        $table_style = transform_styles($this->table_styles);
        ?>
        <table id = '<?= $this->object_index ?>'
        data-multi_id = '<?= json_encode($this->multi_id) ?>'
        data-unit = '<?= $this->unit ?>'
        style ='<?= $table_style ?>'
        >
            <tr title='<?= $this->get_title() ?>'>
                <th ><?= $this->label ?> <?= $this->get_messages() ?></th>
            </tr>
            <?= $this->display_graph() ?>

        </table>
        <?= $this->graph_script() ?>
    <?php
    }

    public function display_graph(){
            $history_timestamp = time();
            $graph_time_frame = 'actual';
            $this->onclick[$this->onclick_index]['multi_id'] = $this->multi_id;
        ?>
            <tr class='graph_options'>
                <td>
                    <table>
                        <tr class='general_inputs'>
                            <td>
<i class="fa fa-file-o haslink" aria-hidden="true"
   data-onclick_multi_id = '<?= json_encode($this->onclick[$this->onclick_index]['multi_id']) ?>'
   onclick = '<?= $this->get_onclick_script() ?>'
   title = '<?= T_('Open in window') ?>'
   ></i> <!--&#128462;-->
                            </td>
                            <td>
<i class="fa fa-floppy-o haslink" aria-hidden="true"
   data-onclick_multi_id = '<?= json_encode($this->onclick[$this->onclick_index]['multi_id']) ?>'
   onclick='downloadData(<?= $this->object_index ?>, response_type = "file")'
   title = '<?= T_('Download') ?>'
   ></i> <!--&#128427;-->
                            </td>
                            <td>
<div class='select-wrapper'>
<select class='time_source' data-id='<?=$this->object_index?>'>
    <option value='actual'> <?= T_('Actual') ?> </option>
    <option value='history' <?php if($graph_time_frame=='history') echo 'selected';
    ?> > <?= T_('History') ?> </option>
</select>
</div>
                            </td>
                            <td>
<div class='select-wrapper'>
<select class='time_frame'
    data-id='<?=$this->object_index?>' >
    <option value=<?= $this->time_frame ?> selected>
        &harr;
        <?= $this->time_frame ?> <?= T_('s') ?></option>
    <option value='60'> <?= sprintf(T_ngettext("%d s", "%d s",60),60); ?> </option>
    <option value='600'> <?= sprintf(T_ngettext("%d min", "%d min",10),10); ?> </option>
    <option value='3600'> <?= sprintf(T_ngettext("%d hour", "%d hours",1),1); ?> </option>
    <option value='10800'> <?= sprintf(T_ngettext("%d hour", "%d hours",3),3); ?> </option>
    <option value='21600'> <?= sprintf(T_ngettext("%d hour", "%d hours",6),6); ?> </option>
    <option value='43200'> <?= sprintf(T_ngettext("%d hour", "%d hours",12),12); ?></option>
    <option value='86400'> <?= sprintf(T_ngettext("%d day", "%d days",1),1); ?> </option>
    <option value='259200'> <?= sprintf(T_ngettext("%d day", "%d days",3),3); ?> </option>
    <option value='604800'> <?= sprintf(T_ngettext("%d week", "%d weeks",1),1); ?> </option>
    <option value='1209600'> <?= sprintf(T_ngettext("%d week", "%d weeks",2),2); ?> </option>
    <option value='2419200'> <?= sprintf(T_ngettext("%d month", "%d months",1),1); ?> </option>
    <option value='7257600'> <?= sprintf(T_ngettext("%d month", "%d months",3),3); ?> </option>
    <option value='14515200'> <?= sprintf(T_ngettext("%d month", "%d months",6),6); ?> </option>
    <option value='31536000'> <?= sprintf(T_ngettext("%d year", "%d years",1),1); ?></option>
</select>
</div>
                            </td>
                            <td>
<div class='select-wrapper'>
<select class='data_source' >
    <option value='all'
    > <?= T_('All data') ?> </option>
    <option value='archive' <?php if($this->archiveonly=='yes') echo 'selected';
    ?> > <?= T_('Archive') ?> </option>
</select>
</div>
                            </td>
                            <td>
<div class='select-wrapper'>
    <select class='refresh_interval' data-id='<?=$this->object_index?>'>
        <option value=3000> 3 <?= T_('s') ?> &#8635;</option>
        <option value=10000> 10 <?= T_('s') ?> &#8635;</option>
        <option value=60000> <?= sprintf(T_ngettext("%d min", "%d min",1),1); ?> &#8635;</option>
        <option value=0> <?= T_('Pause') ?> </option>
    </select>
</div>
                            </td>
                            <td style='position: relative;'>
<div class="horizontal_center waiting" style='display:none;'>
    <div class="vertical_center"><i class="fa fa-spinner fa-spin"></i> </div>
</div>
                            </td>
                        </tr>
                        <tr class='history_inputs' style='display:none;'>
<td colspan='2'><?= T_('from') ?></td>
<td colspan='5' >
    <input class='history_date' style="width:auto;"
        type='date'
        value = <?= date('Y-m-d', $history_timestamp) ?> />

    <input class='history_time' style="width:auto;"
        type='time'
        step='1'
        value = <?= date('H:i:s', $history_timestamp) ?> />

    <button type='submit' name='panel_time_apply'
            class='history_submit' data-id='<?=$this->object_index?>'>
        &#8635;
    </button>
</td>
                        </tr>
                    </table>

                    <div id='gfile_<?= $this->object_index ?>'> <!-- exported file --> </div>
                </td>
            </tr>
            <tr class='show_hide_button'>
                <td>
<i class="fa fa-info-circle" aria-hidden="true" ></i>
                </td>
            </tr>
            <tr style='display:none;'>

                <td>
                    <?= T_('Graph read time[ms]') ?>: <span class ='info_refresh'> --- </span></br>
                    <span class ='info_statistics' style='white-space: pre-wrap;'> --- </span>
                </td>
            </tr>
            <tr>
                <td> <div id='graph<?= $this->object_index ?>'
                          style='width:<?= $this->width ?>px; height:<?= $this->height ?>px;'>
                        <?= T_('graph area') ?>
                    </div> </td>
            </tr>
            <tr>
                <td> <div id='leg<?= $this->object_index ?>'
                          style='width:<?= $this->width ?>px;'>
                        Legend
                    </div> </td>
            </tr>
            <tr>
                <td>
                    <button class='show_hide_button'>
                    Add variable
                    </button>
                    <div style="display:none; border: 1px solid #c5c5c5;">
                        <input id='new_var<?= $this->object_index ?>' type="text" list="var_list<?= $this->object_index ?>" />
                        <datalist id="var_list<?= $this->object_index ?>">
                        <?php
                        global $dcuConns;
                        global $var_ids_and_names;
                        foreach ($dcuConns as $dcuConn){
                            if(empty($var_ids_and_names[$dcuConn['cUnitId']]))
                                $var_ids_and_names[$dcuConn['cUnitId']] = construct_var_ids_and_names($dcuConn['cUnitId']);
                        }
                        foreach ($var_ids_and_names as $dcu_id=>$var_dcu){
                            foreach($var_dcu['ids'] as $var_ids){
                                $var_id = $var_ids['id'];
                                $var_name = '"'.$var_ids['name'].'"';
                                echo "\n<option value='[$dcu_id,$var_id,$var_name]'>"
                                . $dcu_id.' - '.$var_ids['name']."</option>";
                            }
                        }
                        ?>
                        </datalist>
                        <button class='go_add' data-id='<?= $this->object_index ?>'>
                            <i class="fa fa-plus" aria-hidden="true"></i>
                        </button>
                        <p id='show_new_graph<?= $this->object_index ?>'
                            data-onclick_multi_id = '<?= json_encode($this->onclick[$this->onclick_index]['multi_id']) ?>'
                            onclick = '<?= $this->get_onclick_script() ?>'
                            style='margin-left:5px;'>
                            <br />
                            <button type='button'>
                            Show new graph
                            </button>
                        </p>

                    </div>
                </td>
            </tr>
        <?php
        }

    public function graph_script(){
    ?>
    <script type='text/javascript'>
        //petra: naplnenie grafu na zaciatku tromi bodkami, skontrolovat
        //petra: mozno radsej zobrat data z component_calculation
        //http://dygraphs.com/options.html#Data

        var count_multi_ids=<?= count($this->multi_id) ?>;
        graph_data = [Date.now()];
        for(item=1;item<=count_multi_ids;item++){
            graph_data.push(-1);
        }

        multi_id = <?= json_encode($this->multi_id) ?>;
        var labels=[];
        var colors=[];
        labels[0] = 'Date Time';
        for(i=1;i<=count_multi_ids;i++){
            labels[i] = multi_id[i-1][0] + ' . ' + multi_id[i-1][1]+ ' . ' + multi_id[i-1][2];
            switch((i-1)%6){
                case 0: colors[i-1] = 'rgb('+(255-((i-1)/6)*50).toString()+',0,0)';
                    break;
                case 1: colors[i-1] = 'rgb(0,'+(255-((i-1)/6)*50).toString()+',0)';
                    break;
                case 2: colors[i-1] = 'rgb(0,0,'+(255-((i-1)/6)*50).toString()+')';
                    break;
                case 3: colors[i-1] = 'rgb('+(255-((i-1)/6)*50).toString()+','+(255-((i-1)/6)*50).toString()+',0)';
                    break;
                case 4: colors[i-1] = 'rgb('+(255-((i-1)/6)*50).toString()+',0,'+(255-((i-1)/6)*50).toString()+')';
                    break;
                case 5: colors[i-1] = 'rgb(0,'+(255-((i-1)/6)*50).toString()+','+(255-((i-1)/6)*50).toString()+')';
                    break;
            }
        }

        onlineGraphs[<?= $this->object_index ?>] = new Dygraph(
            document.getElementById('graph<?= $this->object_index ?>'),
            [graph_data],
            {
                titleHeight: 20,
                labels: labels,
                legend: 'always',
                drawPoints: true,
                connectSeparatedPoints: true,
                animatedZooms: true,
                drawAxesAtZero: true,
                axisLineColor: 'rgb(55,55,55)',
                gridLineColor: 'rgb(55,55,55)',
                colors: colors,
                labelsDiv: 'leg<?= $this->object_index ?>',
                ylabel: '<div style="-webkit-transform: rotate(90deg);"><?= T_('Y axis') ?>: <?= $this->unit ?></div>',
                yLabelWidth: 14,
                xlabel: '<div><?= T_('X axis unit time') ?></div>',
                digitsAfterDecimal: <?= $this->digitsAfterDecimal ?>,
            }
        );/*'data/temp.csv'*/

        getSignalGraph(<?= $this->object_index ?>, response_type = 'data');

    </script>
    <?php
    }

}