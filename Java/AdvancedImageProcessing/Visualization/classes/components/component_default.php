<?php
class component_default {
    protected $positioning = ''; //petra: ??? "minx"=>-1, "miny"=>-1, "maxx"=>-1,"maxy"=>-1,
    protected $sampletime = ''; //petra: ???


    public static $instance_counter = 0;
    public $object_index = 0;
    /** @var string Possible values:
     *  - variable (default), variable_graph, variable_param,
     *  - function, function_param,
     *  - rtudpio_server,
     *  - batchserver,
     *  - static */
    public $type = 'variable';
    /** @var boolean default FALSE*/
    public $additional_refresh = NULL;
    public $can_set_value = FALSE;
    /** @var string */
    public $arithmetic = '';

    /* TABLE PROPERTIES */
    public $table_styles = array (
        'position' => 'absolute',
        'white-space' => 'nowrap',
        'left' => 0,
        'top' => 0,
        'font-family' => NULL,
        'color' => NULL,
        'background-color' => NULL,
        'font-size' => NULL,
    );
    public $scale_x = NULL;
    public $scale_y = NULL;

    public $onclick = array(array(
        'link' => NULL,  // string
        'tooltip' => NULL,  // string
        'get_params' => array (),
        'target' => "_blank",
        'popup_params' => array (),
        'onclick_params' => array (
            'multi_id' => array(array()),
            'dcu_id' => NULL,
            'var_id' => NULL,
            'param_id' => NULL,
            'security_lowest_level' => 0,
            'security_exception_ids' => array(),
            'set_value_front' => array (), //first time is allways 0
        ),
        'multi_id' => array(array()),
        'dcu_id' => NULL,
        'var_id' => NULL,
        'param_id' => NULL,
        'security_lowest_level' => 0,
        'security_exception_ids' => array(),
    ));
    protected $onclick_index=0;

    /* LABEL PROPERTIES */
    /** @var string|NULL */
    protected $label = NULL;
    protected $label_styles = array ();

    /* UNIT PROPERTIES */
    public $unit = NULL;
    protected $unit_styles = array ();

    /* VALUE PROPERTIES */
    protected $value = NULL;
    protected $value_styles = array ();
    protected $value_overwrite = NULL;
    protected $display_value = NULL;
    /** @var integer Required 1-65535 */
    public $dcu_id = NULL;
    /** @var integer Required just one var_id|var_name|multi_id 1-65535; is func_id and rtudpio_server_id, too */
    public $var_id = NULL;
    /** @var integer Required just one var_id|var_name|multi_id 1-65535; is func_name and rtudpio_server_name, too */
    public $var_name = NULL;
    /** @var integer Required for func_paramid and rtudpio_paramid 1-65535*/
    public $param_id = NULL;
    /** @var array(array()) Required just one var_id|var_name|multi_id */
    public $multi_id = array(array());
    public $display_security_lowest_level = 0;
    public $display_security_exception_ids = array();
    public $min_value = -0x7FFFFFFFFFFFFFFF;
    public $max_value = 0x7FFFFFFFFFFFFFFF;
    public $random_decimal_precision = NULL;

    /** @var string */
    protected $message = '';
    protected $msg_styles = array ();
    /** @var array of HTMLs*/
    protected $msg_icon = array (
        'OVERRIDE' => '<i class="fa fa-hand-paper-o" aria-hidden="true"></i>', //&#128401;
        'ALARM' => '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>',//&#9888;
        'MISSED_CONNECTIONS' => '<i class="fa fa-chain-broken" aria-hidden="true"></i>',
        'OVERWRITE' => '<i class="fa fa-info-circle" aria-hidden="true"></i>',
        'VALUE_BELOW_MIN' => '<i class="fa fa-info-circle" aria-hidden="true"></i>',
        'VALUE_ABOVE_MAX' => '<i class="fa fa-info-circle" aria-hidden="true"></i>',
        'WRONG_DCU' => '<i class="fa fa-times-circle-o" aria-hidden="true"></i>',//&#8855;
        'WRONG_ID' => '<i class="fa fa-times-circle-o" aria-hidden="true"></i>',//&#8855;
        'WRONG_NAME' => '<i class="fa fa-times-circle-o" aria-hidden="true"></i>',//&#8855;
        'WRONG_PARAM_ID' => '<i class="fa fa-times-circle-o" aria-hidden="true"></i>',//&#8855;
        'NO_DATA' => '<i class="fa fa-times-circle-o" aria-hidden="true"></i>',//&#8855;
        'default' => '<i class="fa fa-question-circle" aria-hidden="true"></i>',
        'data' => array(),
    );
    //parameters for class component_number
    public $number_format = '%5.2f';
    //parameters for class component_image
    protected $image = NULL;
    protected $width = NULL; // +animation +graph
    protected $height = NULL;
    //parameters for class component_interval, component_binary
    public $interval = array();
    //parameters for class component_graph and value_graph link
    /** @var integer in seconds */
    protected $time_frame = 1000;
    /** @var integer in decimal places, max is 15 */
    protected $digitsAfterDecimal = 2;
    /** @var string Possible values:
     * - "no"  => (default) show graph from all history recording data
     * - "yes" => show graph only from archive
     */
    protected $archiveonly = 'no';
    /** @var boolean */
    public $rotation = False;

    public function __construct($init = NULL, $type = 'variable') {
        if ($init){ //petra: neda sa nejako inac?
            $init->message = '';  //we do not inherit errors
            $init_array = get_object_vars($init);
            foreach ($init_array as $key=>$value){
                if (is_array($value)){
                    $this->$key = array_merge($this->$key, $value);
                }else{
                    $this->$key = $value;
                }
                $this->$key = $value;
            }
        }
        if($type=='rtudpio_server'){
            $this->dcu_id = 1;
        }
        $this->type = $type;
        $this->object_index = self::$instance_counter;
        self::$instance_counter++;
    }

    public function display_component(){
        if(!check_allow($this->display_security_lowest_level, $this->display_security_exception_ids))
            return;
        $table_attrs = $this->get_table_attrs();
        $table_style = transform_styles($this->table_styles);
        echo "<table ";
        echo "  $table_attrs \n";
        echo "  style = '$table_style' \n";
        echo "> \n";

        echo "<tr>\n";
        echo $this->get_label();
        $value_attrs = $this->get_value_attrs();
        $value_style = transform_styles($this->value_styles);
        echo "<td ";
        echo "$value_attrs \n";
        echo "  style = '$value_style ' \n";
        echo "> \n";
        echo $this->get_display_value()."\n";
        echo $this->get_messages()."\n";
        echo "</td>\n";

        echo $this->get_unit();

        echo "</tr> \n";

        if($this->onclick_index>0){
            echo "<tr>\n";
            echo "<td> </td><td> </td>\n";
            echo "<td class='component-dropdown'>";
            $size=1+$this->onclick_index;
            echo "<select size='$size'> ";
            $options="";
            do{
                $value = $this->get_onclick_script();
                $title = $this->get_onclick_title();
                $options = "<option value='' onclick='$value'>$title</option>\n".$options;
                $this->onclick_index--;
            }while ($this->onclick_index>=0);
            echo "$options</select> ";
            echo "</td>\n";
            echo "</tr> \n";
        }
        echo "</table> \n\n";
    }

    public function validate($value, $type, $function){
        if ($type=='VALUE_INTEGER'){
            if((!is_int($value)) || ($value==0)){
                $this->add_msg($function.' '.T_('value must be non zero integer'), 'VALIDATE');
                return FALSE;
            }
        }
        if ($type=='VALUE_POSITIVE_INTEGER'){
            if((!is_int($value)) || ($value<=0)){
                $this->add_msg($function.' '.T_('value must be positive integer'), 'VALIDATE');
                return FALSE;
            }
        }
        if ($type=='VALUE_POSITIVE_INTEGER_ZERO'){
            if((!is_int($value)) || ($value<0)){

                $this->add_msg($function.' '.T_('value must be positive integer + 0'), 'VALIDATE');
                return FALSE;
            }
        }
        if ($type=='VALUE_NUMERIC'){
            if(!is_numeric($value)) {
                $this->add_msg($function.' '.T_('value is not a real number'), 'VALIDATE');
                return FALSE;
            }
        }

        return TRUE;
    }

    // <editor-fold desc="------TABLE PROPERTIES-----" defaultstate="expanded">
    public function set_left($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER_ZERO', 'set_left')){
            $this->table_styles['left'] = $value.'px';
        }
    }
    public function set_top($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER_ZERO', 'set_top')){
            $this->table_styles['top'] = $value.'px';
        }
    }
    public function set_additional_refresh($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER_ZERO', 'set_additional_refresh')){
            $this->additional_refresh = $value;
        }
    }

    public function set_scale_x($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_INTEGER', 'set_scale_x')){
            $this->scale_x = $value;
            $this->value_styles['position'] = 'relative';
        }
    }
    public function set_scale_y($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_INTEGER', 'set_scale_y')){
            $this->scale_y = $value;
            $this->value_styles['position'] = 'relative';
        }
    }
    public function set_font_family($value){
        $this->table_styles['font-family'] = $value;
    }
    public function set_font_style($value){
        $this->table_styles['font-style'] = $value;
    }
    public function set_font_color($value){
        $this->table_styles['color'] = $value;
    }
    public function set_font_background($value){
        $this->table_styles['background-color'] = $value;
    }
    public function set_font_size($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_font_size')){
            $this->table_styles['font-size'] = $value.'px';
        }
    }

    public function get_table_attrs(){
        $table_attrs = array();
        //$table_attrs['id'] = $this->object_index;
        //$table_attrs['name'] = "element_".$this->object_index; //petra: zatial netreba
        $table_attrs['title'] = $this->get_title();
        $table_attrs['class'] = ' dragdrop ';

        if($this->onclick_index>0)
            $table_attrs['class'] .= ' multi_onclick ';
        else
        if(!empty($this->onclick[0]['link'])){
            $table_attrs['class'] .= ' haslink ';
            $table_attrs['onclick'] = $this->get_onclick_script();
            if(!empty($this->onclick[0]['multi_id'][0])){
                $table_attrs['data-onclick_multi_id'] = json_encode($this->onclick[0]['multi_id']);
            }
        }
        $tag_attrs = transform_attributes($table_attrs);
        return $tag_attrs;
    }
    public function get_onclick_title(){
        $title = "";
        if (!empty($this->onclick[$this->onclick_index]['link'])){
            if ($this->onclick[$this->onclick_index]['link'] == 'autoswitch'){
                $title = T_('autoswitch actual value');
            }else{
                $title = $this->onclick[$this->onclick_index]['link'];
            }
        }
        if (!empty($this->onclick[$this->onclick_index]['tooltip'])){
            $title = $this->onclick[$this->onclick_index]['tooltip'];
        }
        return $title;
    }

    public function get_title(){
        $title = $this->var_name;
        if($this->onclick_index>0) return $title;
        //else
        $title .= ' -> '.$this->get_onclick_title();
        return $title;
    }
    // </editor-fold>

    // <editor-fold desc="-----ONCLICK PROPERTIES----" defaultstate="expanded">
    public function set_onclick($value, $front_value=NULL, $front_time=NULL){
        $i = $this->onclick_index;
        $this->onclick[$i]['link'] = $value;
        if($value=='panel_var_ovrd'){
            $this->set_faceplate_width(600);
            $this->set_faceplate_height(110);
        }
        if($value=='set_binary_value'){
            $this->onclick[$i]['onclick_params']['set_value_front'][] = array($front_value,0);
        }
        if($value=='set_real_value'){
            $this->onclick[$i]['onclick_params']['set_value_front'][] = array($front_value,0);
        }
        if($value=='push_button'){
            $this->onclick[$i]['link'] = 'set_binary_value';
            $this->onclick[$i]['onclick_params']['set_value_front'][] = array($front_value,0);
            $this->onclick[$i]['onclick_params']['set_value_front'][] = array(1-$front_value,$front_time);
        }
    }
    public function add_onclick(){
        $this->onclick_index++;
        $this->onclick[$this->onclick_index] = array(
            'link' => NULL,  // string
            'tooltip' => NULL,  // string
            'get_params' => array (),
            'target' => "_blank",
            'popup_params' => array (),
            'onclick_params' => array (
                'multi_id' => array(array()),
                'dcu_id' => NULL,
                'var_id' => NULL,
                'param_id' => NULL,
                'security_lowest_level' => 0,
                'security_exception_ids' => array(),
                'set_value_front' => array (), //first time is allways 0
            ),
            'multi_id' => array(array()),
            'dcu_id' => NULL,
            'var_id' => NULL,
            'param_id' => NULL,
            'security_lowest_level' => 0,
            'security_exception_ids' => array(),
        );
    }
    public function add_next_binary_value($front_value, $front_time){
        $this->onclick[$this->onclick_index]['onclick_params']['set_value_front'][]
            = array($front_value,$front_time);
    }
    public function add_next_real_value($front_value, $front_time){
        $this->onclick[$this->onclick_index]['onclick_params']['set_value_front'][]
            = array($front_value,$front_time);
    }
    public function set_onclick_tooltip($value){
        $this->onclick[$this->onclick_index]['tooltip'] = $value;
    }
    public function set_onclick_target($value){
        $this->onclick[$this->onclick_index]['target'] = $value;
    }
    public function set_onclick_faceplate_width($value){
        $this->add_msg(' set_onclick_faceplate_width() '. T_(' method is renamed to set_faceplate_width.'));
    }
    public function set_faceplate_width($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_faceplate_width')){
            $this->onclick[$this->onclick_index]['popup_params']['width'] = $value.'px';
        }
    }
    public function set_onclick_faceplate_height($value){
        $this->add_msg(' set_onclick_faceplate_height() '. T_(' method is renamed to set_faceplate_height.'));
    }
    public function set_faceplate_height($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_faceplate_height')){
            $this->onclick[$this->onclick_index]['popup_params']['height'] = $value.'px';
        }
    }
    public function set_onclick_security_lowest_level($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER_ZERO', 'set_onclick_security_lowest_level')){
            $this->onclick[$this->onclick_index]['onclick_security_lowest_level'] = $value;
        }
    }
    /**
      * @param $value array of IDs
      */
    public function set_onclick_security_exception_ids($value){
        if (is_array($value)){
            foreach($value as $exception_id){
                if (!$this->validate($exception_id, 'VALUE_POSITIVE_INTEGER', 'array of set_onclick_security_exception_ids'))
                    return;
            }
            $this->onclick[$this->onclick_index]['onclick_security_exception_ids'] = $value;
        }
    }
    public function set_display_security_lowest_level($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER_ZERO', 'set_onclick_security_lowest_level')){
            $this->display_security_lowest_level = $value;
        }
    }

    /**
      * @param $value array of IDs
      */
    public function set_display_security_exception_ids($value){
        if (is_array($value)){
            foreach($value as $exception_id){
                if (!$this->validate($exception_id, 'VALUE_POSITIVE_INTEGER', 'array of set_display_security_exception_ids'))
                    return;
            }
            $this->display_security_exception_ids = $value;
        }
    }
    public function add_GET_parameter($key, $value){
        $this->onclick[$this->onclick_index]['get_params'][$key] = $value;
    }
    public function add_faceplate_parameter($key, $value){
        $this->add_GET_parameter($key, $value);
    }
    public function set_arithmetic_transformation($formula){
        $this->arithmetic = $formula;
    }
    public function get_onclick_script(){
        $i = $this->onclick_index;
        $onclick = '';
        if((!empty($this->onclick[$i]['link']))&&
            (check_allow($this->onclick[$i]['security_lowest_level'], $this->onclick[$i]['security_exception_ids']))){
            $width = 0;
            $height = 0;
            $file = '';

            $link = $this->onclick[$i]['link'];
            switch ($link) {
                //hocijaka externa stranka
                case (preg_match('/http(s?):\/\/.*/', $link) ? true : false) :
                    $index = $link;
                    $file = '';
                    break;
                //hocijaka interna stranka nedela: treba??
                case (preg_match('/index.php*/', $link) ? true : false) :
                    $index = $link;
                    $file = '';
                    break;
                //hocijaka stranka z adresara process alebo examples
                case (preg_match('/process*/', $link) ? true : false) :
                case (preg_match('/content\/examples*/', $link) ? true : false) :
                    $index = 'index.php?';
                    $file = $link;
                    break;
                //hocijaky interny PHP subor v koreni
                case (preg_match('/[a-zA-Z0-9_]+.php/', $link) ? true : false) :
                    $index = 'index.php?';
                    break;
                //hocijaky interny HTML subor v koreni
                case (preg_match('/[a-zA-Z0-9_#]+.html/', $link) ? true : false) :
                    $index = $link;
                    break;
                case 'panel_function': //byvale cfunpanel.php
                case 'panel_function_schedule': //byvale cfunpanel_time_program.php
                case 'panel_var_complete':
                case 'panel_var_simple':
                case 'panel_var_ovrd':
                case 'panel_var_alarms':
                    $index = 'index.php?'; //note: len ak je can_set_value!
                    $file = "content/static/$link.php";
                    $this->onclick[$i]['get_params']['dcu_id'] = $this->dcu_id;
                    $this->onclick[$i]['get_params']['var_id'] = $this->var_id;
                    $this->onclick[$i]['get_params']['var_name'] = $this->var_name;
                    break;

                case 'variable_graph':
                    $index = 'index.php?';
                    $file = 'content/dynamic/variable_graph.php';

                    //MULTI ID

                    if(empty($this->onclick[$i]['multi_id'][0])){
                        if(!empty($this->multi_id[0])){
                            $this->onclick[$i]['multi_id'] = $this->multi_id;
                        }else{
                            $first_param = $this->dcu_id;
                            $second_param = (isset($this->var_id))? $this->var_id:-1;
                            $third_param = (isset($this->var_name))? $this->var_name:'';
                            $this->onclick[$i]['multi_id'] = array(array($first_param,$second_param,$third_param));
                        }
                    }
                    $this->onclick[$i]['get_params']['multi_id'] = '"+this.dataset.onclick_multi_id+"';

                    //GRAPH LABEL
                    if(empty($this->onclick[$i]['get_params']['label']))
                        if(empty($this->label))$this->onclick[$i]['get_params']['label'] = T_('Graph for ').$this->var_name;
                        else $this->onclick[$i]['get_params']['label'] = $this->label;

                    //GRAPH  Y LABEL
                    if(empty($this->onclick[$i]['get_params']['ylabel']))
                        $this->onclick[$i]['get_params']['ylabel'] = $this->unit;

                    $this->onclick[$i]['get_params']['time_frame'] = $this->time_frame;
                    $this->onclick[$i]['get_params']['archiveonly'] = $this->archiveonly;
                    break;
                case 'autoswitch':
                    $index = 'noindex';

                    if($GLOBALS['panel_time_type']=='actual') // we cannot edit historical values
                        $onclick = "autoswitch(this, \"$this->type\")";
                    break;

                case 'set_binary_value':
                    $index = 'noindex';
                    $front = json_encode($this->onclick[$i]['onclick_params']['set_value_front']);
                    if($GLOBALS['panel_time_type']=='actual') // we cannot edit historical values
                        $onclick = "set_binary_value(this, \"$this->type\",$front)";
                    break;

                case 'set_real_value':
                    $index = 'noindex';
                    $front = json_encode($this->onclick[$i]['onclick_params']['set_value_front']);
                    if($GLOBALS['panel_time_type']=='actual') // we cannot edit historical values
                        $onclick = "set_real_value(this, \"$this->type\",$front)";
                    break;

                case 'editvalue':
                    $index = 'noindex';
                    if($GLOBALS['panel_time_type']=='actual') // we cannot edit historical values
                        $onclick = "editvalue(this, \"$this->type\")";
                    break;
                default:
                    $index = 'noindex';
            }
            if ($index != 'noindex'){
                if ($file) {$file = "file=$file";}

                $get_result = '';
                foreach ($this->onclick[$i]['get_params'] as $key=>$value){
                    $get_result .=  "&$key=$value";
                }

                $popup_result = '';
                foreach ($this->onclick[$i]['popup_params'] as $key=>$value){
                    $popup_result .=  "$key=$value,";
                }

                $href = "$index$file$get_result";
                $target = $this->onclick[$i]['target'];
                $onclick = "open_new_window(\"$href\", \"$target\", \"$popup_result\")";
            }

        }
        return $onclick;
    }
    // </editor-fold>

    // <editor-fold desc="-----LABEL PROPERTIES------" defaultstate="expanded">
    public function set_label($value){
        $this->label = $value;
    }
    public function get_label(){
        if (!empty($this->label)) {
            $tag_style = transform_styles($this->label_styles);
            return "<td\n"
            . "  style = '$tag_style'\n"
            . ">\n"
            . "$this->label\n"
            . "</td>\n";
        }
    }
    public function set_label_font_family($value){
        $this->label_styles['font-family'] = $value;
    }
    public function set_label_font_color($value){
        $this->label_styles['color'] = $value;
    }
    public function set_label_font_size($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_label_font_size')){
            $this->label_styles['font-size'] = $value.'px';
        }
    }
    // </editor-fold>

    // <editor-fold desc="------UNIT PROPERTIES------" defaultstate="expanded">
    public function set_unit($value){
        $this->unit = $value;
    }
    public function get_unit(){}
    public function set_unit_font_family($value){
        $this->unit_styles['font-family'] = $value;
    }
    public function set_unit_font_color($value){
        $this->unit_styles['color'] = $value;
    }
    public function set_unit_font_size($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_unit_font_size')){
            $this->unit_styles['font-size'] = $value.'px';
        }
    }
    // </editor-fold>

    // <editor-fold desc="------ERROR PROPERTIES-----" defaultstate="expanded">
    public function set_msg_font_family($value){
        $this->msg_styles['font-family'] = $value;
    }
    public function set_msg_font_color($value){
        $this->msg_styles['color'] = $value;
    }
    public function set_msg_font_size($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_msg_font_size')){
            $this->msg_styles['font-size'] = $value.'px';
        }
    }
    public function set_msg_icon($key, $html){
        $this->msg_icon[$key] = $html;
        $this->msg_icon['data'][$key] = $html;
    }
    public function add_global_error($text, $type){
        $object = '(DCU'.$this->dcu_id;
        if (isset($this->var_name)) $object .= ' name: '.$this->var_name.') ';
        else if (isset($this->var_id)) $object .= ' id: '.$this->var_id.') ';

        $GLOBALS['errors'][$this->object_index][$type]=
            sprintf(
            T_('Object %d %s - [%d, %d]'),
            $this->object_index, $object,
            $this->table_styles['left'],
            $this->table_styles['top']
            ).' ⇒ '.$text;
    }
    public function add_component_msg($new_msg = 'Some error', $appendix = ''){
        global $msg_text;
        switch ($new_msg){
            case 'OVERRIDE':
            case 'ALARM':
            case 'MISSED_CONNECTIONS':
                $this->message .= '<span title="'.T_($msg_text[$new_msg]).' '.$appendix.'">'.$this->msg_icon[$new_msg].'</span>';
                break;
            case 'VALUE_BELOW_MIN':
                $title = " $this->value ";
                $title .= T_('is below acceptable minimum');
                $this->message .= '<span title="'.T_($msg_text[$new_msg]).'">'.$this->msg_icon[$new_msg].'</span>';
                //$this->add_global_error(T_($msg_text[$new_msg]), $new_msg);
                break;
            case 'VALUE_ABOVE_MAX':
                $title = " $this->value ";
                $title .= T_('is above acceptable maximum');
                $this->message .= '<span title="'.T_($msg_text[$new_msg]).'">'.$this->msg_icon[$new_msg].'</span>';
                //$this->add_global_error(T_($msg_text[$new_msg]), $new_msg);
                break;
            default:

        }
    }
    public function add_msg($new_msg = 'Some error', $type = 'dev'){
        global $msg_text;
        switch ($new_msg){
            case 'OVERWRITE':
            case 'WRONG_DCU':
            case 'WRONG_ID':
            case 'WRONG_NAME':
            case 'WRONG_PARAM_ID':
                if($GLOBALS['show_panel_errors']){
                    if($this->msg_icon[$new_msg]){
                        $this->message .= '<span title="'.T_($msg_text[$new_msg]).'">'.$this->msg_icon[$new_msg].'</span>';
                        $this->message .= $this->object_index;
                    }
                    $this->add_global_error(T_($msg_text[$new_msg]), $new_msg);
                }
                break;

            case 'NO_DATA':
                global $panel_time_type;
                switch ($panel_time_type){
                    case 'actual':
                        $title = T_('NO DATA (Not connected, wrong identificators or wrong actual_values.txt)');
                        break;
                    case 'history':
                        $title = T_('NO DATA (Not connected, wrong identificators or no value in chosen time)');
                        break;
                }
                if($GLOBALS['show_panel_errors']){
                    if(!empty($this->msg_icon[$new_msg])){
                        $this->message .= '<span title="'.$title.'">'.$this->msg_icon[$new_msg].'</span>';
                        $this->message .= $this->object_index;
                    }
                    $this->add_global_error($title, $new_msg);
                }
                break;
            default:
                if($GLOBALS['show_panel_errors']){
                    if($this->msg_icon['default']){
                        $this->message .= '<span title="'.$new_msg.'">'.$this->msg_icon['default'].'</span>';
                        $this->message .= $this->object_index;
                    }
                    $this->add_global_error($new_msg, $type);
                }
        }
    }
    public function get_messages(){
        if (!empty($this->message)) {

            $tag_style = transform_styles($this->msg_styles);
            return "<span class='blink'\n"
            . "  style = '$tag_style'\n"
            . ">\n"
            . "$this->message\n"
            . "</span>";
        }
    }
    // </editor-fold>

    // <editor-fold desc="------VALUE PROPERTIES-----" defaultstate="expanded">
    public function set_value_font_family($value){
        $this->value_styles['font-family'] = $value;
    }
    public function set_value_font_color($value){
        $this->value_styles['color'] = $value;
    }
    public function set_value_font_size($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_value_font_size')){
            $this->value_styles['font-size'] = $value.'px';
        }
    }

    /**
      * Validates and sets dcu ID
      *
      * @param $value number starting from 0
      */
    public function set_dcu_id($value){
        if($this->type=='rtudpio_server') {
            $this->dcu_id = 1;
            return;
        }
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER_ZERO', 'set_dcu_id')){
            global $dcuConns;
            if(!empty($dcuConns[$value])){
                $this->dcu_id = $value;
            }else{
                $this->add_msg('WRONG_DCU');
                $this->dcu_id = -1;
            }
        }else
            $this->dcu_id = -1;
    }

    /**
      * Validates and sets variable ID / function ID / rtudp iocFunId
      *
      * @param $value number starting from 0
      */
    public function set_id($value){
        if (isset($this->var_name)){
            //set_name was set prior to set_id
            $this->add_msg(' set_id() '. T_(' method is ignored. Name is defined.'));
            return;
        }
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER_ZERO', 'set_id')){
            $this->var_id = $value;
        }else
            $this->var_id = -1;
    }
    /**
      * Sets variable name / function name
      *
      * @param $value string
      */
    public function set_name($value){

        if (isset($this->var_id)){
            //set_id was set prior to set_name
            $this->add_msg(' set_name() '. T_(' method is ignored. ID is defined.'));
            return;
        }
        $this->var_name = $value;
    }

    public function set_multi_id($value){
        $new_multi_id = array();
        foreach($value as $one_multi_id){
            if(!empty($one_multi_id)){
                $first_parameter = my_intval($one_multi_id[0]);
                $second_parameter = $one_multi_id[1];
                $third_parameter = $one_multi_id[2];

                if (!$this->validate($first_parameter, 'VALUE_POSITIVE_INTEGER_ZERO', 'set_multi_id')){
                    continue;
                }
                global $dcuConns;
                if(empty($dcuConns[$first_parameter])){
                    $this->add_msg('WRONG_DCU');
                    continue;
                }
                $new_multi_id[] = array($first_parameter, $second_parameter, $third_parameter);
            }
        }
        $this->multi_id = $new_multi_id;
    }

    /**
      * Validates and sets function parameter ID / rtudp iocVarId
      *
      * @param $value number starting from 0
      */
    public function set_param_id($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER_ZERO', 'set_param_id')){
            $this->param_id = $value;
        }else
            $this->param_id = -1;
    }

    public function set_max_value($value){
        $value = my_floatval($value);
        if ($this->validate($value, 'VALUE_NUMERIC', 'set_max_value')){
            $this->max_value = $value;
        }
    }
    public function set_min_value($value){
        $value = my_floatval($value);
        if ($this->validate($value, 'VALUE_NUMERIC', 'set_min_value')){
            $this->min_value = $value;
        }
    }
    public function set_random_decimal_precision($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_random_decimal_precision')){
            $this->random_decimal_precision = $value;
        }
    }

    /**
      * Sets the value from controller
      * Checks overwrite and validates
      *
      * @param $new_value number
      * @param $new_value number
      */
    public function set_value($new_value, $new_state=0){
        if (!isset($this->value_overwrite)) {
            $this->value = $new_value;
            $state_array = translate_state_RAM($new_state);
            foreach($state_array as $key=>$value){
                if ($value['value']===1){
                    $this->add_component_msg($key, $value['text']);
                }
            }
        }

        if ($this->arithmetic != ''){
            $x = $this->value;
            $op = str_replace("x", $this->value, $this->arithmetic);
            eval( '$result = ('.$op.');' );
            $this->value = $result;
        }
    }

    public function set_overwrite($value){
        $value = my_floatval($value);
        if ($this->validate($value, 'VALUE_NUMERIC', 'set_overwrite')){
            $this->value_overwrite = $value;
            $this->value = $this->value_overwrite;
            $this->add_component_msg('OVERWRITE');
        }
    }

    /**
      * In case everything is OK, calculate display_value from value
      *
      * @param
      */
    public function set_display_value(){
        $this->display_value = $this->value; //not used
    }

    public function get_display_value(){
        //pokus: if($this->can_set_value)
        if (!isset($this->value)){ //in case DB value is NULL
            $this->add_msg('NO_DATA');
        } else if ($this->validate($this->value, 'VALUE_NUMERIC', '')){
            if ($this->value<$this->min_value){
                $this->add_component_msg('VALUE_BELOW_MIN');
            }else if ($this->value>$this->max_value){
                $this->add_component_msg('VALUE_ABOVE_MAX');
            }
        }
        $this->set_display_value();
        return "<span style='display:none;' class='element_value' data-value='".$this->value."'></span>"
        .$this->display_value;
    }

    public function get_dataset(){
        return array();
    }

    public function get_parent_dataset(){
        $dataset = array();
        switch ($this->type){
            case 'variable':
                $dataset['class'] = 'valid_variable';
                if(isset($this->additional_refresh)) {
                    $dataset['class'] = 'additional_refresh';
                    $dataset['data-additional_refresh'] = $this->additional_refresh;
                }
                break;
            case 'variable_param':
                $dataset['class'] = 'valid_variable_param';
                $dataset['data-param_id'] = $this->param_id;
                break;
            case 'function_param':
                $dataset['class'] = 'valid_function_param';
                $dataset['data-param_id'] = $this->param_id;
                break;
            case 'rtudpio_server':
                $dataset['class'] = 'valid_rtudpio';
                $dataset['data-param_id'] = $this->param_id;
                break;
            default:
        }

        $dataset['data-dcu_id'] = $this->dcu_id;
        $dataset['data-var_id'] = $this->var_id;
        if($this->rotation) $dataset['data-rotation'] = $this->rotation;
        if(isset($this->scale_x)) $dataset['data-scale_x'] = $this->scale_x;
        if(isset($this->scale_y)) $dataset['data-scale_y'] = $this->scale_y;
        if(isset($this->random_decimal_precision)) $dataset['data-random_decimal_precision'] = $this->random_decimal_precision;
        if(isset($this->arithmetic)) $dataset['data-arithmetic'] = $this->arithmetic;

        if(isset($this->onclick[$this->onclick_index]['dcu_id'])) $dataset['data-onclick_dcu_id']
            = $this->onclick[$this->onclick_index]['dcu_id'];
        if(isset($this->onclick[$this->onclick_index]['var_id'])) $dataset['data-onclick_dcu_id']
            = $this->onclick[$this->onclick_index]['var_id'];
        if(isset($this->onclick[$this->onclick_index]['param_id'])) $dataset['data-onclick_dcu_id']
            = $this->onclick[$this->onclick_index]['param_id'];
        foreach($this->msg_icon['data'] as $msg_key=>$msg_icon){
            $dataset["data-$msg_key"] = $msg_icon;
        }

        return $dataset;
    }

    /**
      * For javascript purposes only
      *
      * @param
      *
      * @return string
      */
    public function get_value_attrs(){
        $value_attrs = array();

        if ((!isset($this->value_overwrite))&&
            ($this->can_set_value===TRUE)){
            $value_attrs = $this->get_dataset();
        }
        $value_attrs['id'] = $this->object_index;

        return transform_attributes($value_attrs);
    }
    // </editor-fold>

    /* SPECIFIC FOR CHILD CLASSES - in case of general initialization */
    // <editor-fold desc="----SPECIFIC PROPERTIES----" defaultstate="expanded">
    public function set_number_format($value){
        // todo: validate value
        $this->number_format = $value;
    }

    public function set_image($value){
        //todo: ak obrazok neexistuje, pridat error do zoznamu errorov
        $this->image = $value;
    }

    public function set_width($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_width')){
            $this->width = $value;  // nepridavat PX, lebo sa to pouziva v matematickych vypoctoch!
        }
    }
    public function set_height($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_height')){
            $this->height = $value;  // nepridavat PX, lebo sa to pouziva v matematickych vypoctoch!
        }
    }

    public function set_nrows($value){}
    public function set_ncolumns($value){}
    /* SPECIFIC INTERVAL PROPERTIES*/
    public function set_interval_font_family($value){}
    public function set_interval_font_color($value){}
    public function set_interval_font_size($value){}
    public function set_interval_image_width($value){}
    public function set_interval_image_height($value){}
    public function add_interval_text($to=1000, $text='???'){}
    public function add_interval_image($to=1000, $image='???'){}
    public function set_zero_image($value){}
    public function set_one_image($value){}
    public function set_zero_text($value){}
    public function set_one_text($value){}
    public function set_zero_font_family($value){}
    public function set_zero_font_color($value){}
    public function set_zero_font_size($value){ }
    public function set_zero_image_width($value){}
    public function set_zero_image_height($value){}
    public function set_one_font_family($value){ }
    public function set_one_font_color($value){}
    public function set_one_font_size($value){}
    public function set_one_image_width($value){ }
    public function set_one_image_height($value){}
    /* SPECIFIC GRAPH PROPERTIES*/
    /**
      * @param $value positive integer in seconds
      */
    public function set_time_frame($value){
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_time_frame')){
            $this->time_frame = $value;
        }
    }

    /**
      * @param $value yes/no
      */
    public function set_archiveonly($value){
        if ($value == 'no') $this->archiveonly = 'no';
        if ($value == 'yes') $this->archiveonly = 'yes';
    }

    /**
      * @param $value positive integer in decimal places, max is 15
      */
    public function set_digitsAfterDecimal($value){
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_digitsAfterDecimal')){
            $this->digitsAfterDecimal = $value;
        }
    }
    public function rotate_according_to_my_own_value(){
        $this->rotation = True;
    }
    // </editor-fold>

}

include('./classes/components/component_text.php');
include('./classes/components/component_number.php');
include('./classes/components/component_image.php');
include('./classes/components/component_animation.php');
include('./classes/components/component_binary.php');
include('./classes/components/component_interval.php');
include('./classes/components/component_graph.php');
include('./classes/components/component_batchserver.php');
include('./classes/components/component_DB.php');
include('./classes/components/component_DB_export.php');
include('./classes/components/component_DB_import.php');

include('./classes/components/component_calculation.php');


//petra: template to display batch (IMPLEMENTED ONLY IN ANDROID PANEL/SERVER)
$disp_batchserversetup_template = array(
    "disptype"=>50, "fcnparamid"=>-1,
    "minx"=>-1,"miny"=>-1, "maxx"=>-1,"maxy"=>-1, "minvalue"=>0, "maxvalue"=>100,
       );

//petra: template for action button different functions may be activated using this button
$disp_actionbutton_template = array(
    "disptype"=>401, //petra_otazky: pouziva sa?
    "img"=>"images/example_combo.png",
    "link"=>"",
    );

//petra: template for server/operator panel Real-Time Virtual UDP I/O value display with text string
$disp_realvaltext_rtudpio_template = array(
    "disptype"=>505,
       );

function transform_styles($params = array()){
    $tag_style = '';
    foreach ($params as $key=>$value){
        if(isset($value)){
            $tag_style .= " $key:$value; ";
        }
    }
    return $tag_style;
}

function transform_attributes($params = array()){
    $tag_attrs = '';
    foreach ($params as $key=>$value){
        if(isset($value)){
            $tag_attrs .= " $key='$value' \n";
        }
    }
    return $tag_attrs;
}

function transform_alias($alias){
    $styles = array();
    if(isset($alias['styles'])) $styles = $alias['styles'];

    switch($alias['type']){
        case 'text':
            return get_span($alias['text'], $styles);
        case 'image':
            return get_image($alias['image'], $styles);
        default:
            return '???';
    }
}

function get_image($file, $style_attrs = array()){
    $user_directory = $GLOBALS['user_directory'];
    $tag_style = transform_styles($style_attrs);

    return "<img  src='$user_directory/$file' "
        . " alt='$user_directory/$file' "
        . " style= '$tag_style' />";
}
function get_span($inner_text, $style_attrs = array()){
    $tag_style = transform_styles($style_attrs);
    return "<div style='$tag_style'>$inner_text</div>";
}

function create_component_from_dataset($key, $value){
    switch ($value['template']){
        case 'animation':
            $component = new component_animation();
            $component->object_index = $key;
            $GLOBALS['errors'][$component->object_index] = array();

            if(isset($value['nrows'])){
                $nrows = filter_var($value['nrows'],FILTER_VALIDATE_INT);
                if ($nrows!==FALSE) $component->set_nrows($nrows);
            }
            if(isset($value['ncolumns'])){
                $ncolumns = filter_var($value['ncolumns'],FILTER_VALIDATE_INT);
                if ($ncolumns!==FALSE) $component->set_ncolumns($ncolumns);
            }
            if(isset($value['width'])){
                $width = filter_var($value['width'],FILTER_VALIDATE_INT);
                if ($width!==FALSE) $component->set_width($width);
            }
            if(isset($value['height'])){
                $height = filter_var($value['height'],FILTER_VALIDATE_INT);
                if ($height!==FALSE) $component->set_height($height);
            }
            if(isset($value['image'])){
                $image = filter_var(
                    $value['image'],
                    FILTER_VALIDATE_REGEXP,
                    array('options'=>array('regexp'=>"/^(a-z)*/")) //petra: php bug v regexp
                    );
                if ($image !== FALSE) $component->set_image($image);
            }

            break;
        case 'binary':
            $component = new component_binary();
            $component->object_index = $key;
            $GLOBALS['errors'][$component->object_index] = array();

            if(isset($value['interval'])){
                $component->interval = $value['interval']; //petra: ovalidovat array
            }
            break;
        case 'interval':
            $component = new component_interval();
            $component->object_index = $key;
            $GLOBALS['errors'][$component->object_index] = array();

            if(isset($value['interval'])){
                $component->interval = $value['interval']; //petra: ovalidovat array
            }
            break;
        case 'number':
            $component = new component_number();
            $component->object_index = $key;
            $GLOBALS['errors'][$component->object_index] = array();

            if(isset($value['number_format'])){
                $number_format = filter_var(
                    $value['number_format'],
                    FILTER_VALIDATE_REGEXP,
                    array('options'=>array('regexp'=>"/^(0-9a-z\%\.)*/")) //petra: php bug v regexp
                    );

                if ($number_format !== FALSE) $component->set_number_format($number_format);
            }

            break;
        case 'image':
            $component = new component_image();
            $component->object_index = $key;
            $GLOBALS['errors'][$component->object_index] = array();
            if(isset($value['image'])){
                $component->set_image($value['image']);
            }
            if(isset($value['width'])){
                $width = filter_var($value['width'],FILTER_VALIDATE_INT);
                if ($width!==FALSE) $component->set_width($width);
            }
            if(isset($value['height'])){
                $height = filter_var($value['height'],FILTER_VALIDATE_INT);
                if ($height!==FALSE) $component->set_height($height);
            }
            break;
        default: break;
    }

    if(isset($value['user_directory'])){
        $user_directory = filter_var(
            $value['user_directory'],
            FILTER_VALIDATE_REGEXP,
            array('options'=>array('regexp'=>"/^(a-z_)*/")) //petra: php bug v regexp
            );
        if($user_directory!== FALSE)
            $GLOBALS['user_directory'] = $user_directory;
    }

    if(isset($value['rotation'])){
        $component->rotate_according_to_my_own_value();
    }

    if(isset($value['dcu_id'])){
        $dcu_id = filter_var($value['dcu_id'],FILTER_VALIDATE_INT);
        if($dcu_id=== FALSE){
            $component->add_msg('WRONG_DCU');
        }else{
            $component->set_dcu_id($dcu_id);
        }
    }else $component->add_msg(T_('Post not set').' dcu_id');

    if(isset($value['var_id'])){
        $var_id = filter_var($value['var_id'],FILTER_VALIDATE_INT);
        if($var_id=== FALSE){
            $component->add_msg('WRONG_ID');
        }else{
            $component->set_id($var_id);
        }
    }else $component->add_msg(T_('Post not set').' var_id');

    if(isset($value['scale_x'])){
        $component->set_scale_x($value['scale_x']);
    }
    if(isset($value['scale_y'])){
        $component->set_scale_y($value['scale_y']);
    }
    if(isset($value['arithmetic'])){
        $component->set_arithmetic_transformation($value['arithmetic']);
    }
    if(isset($value['min_value'])){
        $min_value = filter_var($value['min_value'],FILTER_VALIDATE_INT);
        if ($min_value!==FALSE) $component->set_min_value($min_value);
    }
    if(isset($value['max_value'])){
        $max_value = filter_var($value['max_value'],FILTER_VALIDATE_INT);
        if ($max_value!==FALSE) $component->set_max_value($max_value);
    }
    if(isset($value['override'])){
        $component->set_msg_icon('OVERRIDE', $value['override']);
    }
    if(isset($value['alarm'])){
        $component->set_msg_icon('ALARM', $value['alarm']);
    }
    if(isset($value['missed_connections'])){
        $component->set_msg_icon('MISSED_CONNECTIONS', $value['missed_connections']);
    }
    if(isset($value['overwrite'])){
        $component->set_msg_icon('OVERWRITE', $value['overwrite']);
    }
    if(isset($value['value_below_min'])){
        $component->set_msg_icon('VALUE_BELOW_MIN', $value['value_below_min']);
    }
    if(isset($value['value_above_max'])){
        $component->set_msg_icon('VALUE_ABOVE_MAX', $value['value_above_max']);
    }
    if(isset($value['wrong_dcu'])){
        $component->set_msg_icon('WRONG_DCU', $value['wrong_dcu']);
    }
    if(isset($value['wrong_id'])){
        $component->set_msg_icon('WRONG_ID', $value['wrong_id']);
    }
    if(isset($value['wrong_name'])){
        $component->set_msg_icon('WRONG_NAME', $value['wrong_name']);
    }
    if(isset($value['wrong_param_id'])){
        $component->set_msg_icon('WRONG_PARAM_ID', $value['wrong_param_id']);
    }
    return $component;
}
