<?php
class component_interval extends component_default{

    public function __construct($init = NULL, $type = 'variable') {
        parent::__construct($init,$type);
        $this->unit = array();
    }



    public function set_display_value(){
        $alias = '';
        if(isset($this->value)){
            foreach ($this->interval as $interval){

                if ($this->value<$interval['to']){
                    $alias = $interval;
                    break;
                }
            }
            if (empty($alias)){
                $last_interval = $this->interval[count($this->interval)-1];
                $alias = $last_interval;
            }
        }else{
            $alias = $this->interval[0];
            $alias['styles']['filter'] = 'grayscale(100%)';
        }

        $this->display_value = transform_alias($alias);
    }

    public function get_dataset(){
        $dataset = array(
            'data-template'=>'interval',
            'data-interval'=>json_encode($this->interval),
            'data-user_directory'=>$GLOBALS['user_directory'],
            'data-min_value'=>$this->min_value,
            'data-max_value'=>$this->max_value,
            );

        $parent_dataset = $this->get_parent_dataset();
        return array_merge($dataset, $parent_dataset);
    }
    public function set_max_value($value){
        //interval constraints are calculated automaticaly via add_interval
        $this->add_msg(' set_max_value() '. T_(' method is ignored.'));
    }
    public function set_unit($value){
        // unit is calculated in add_interval_text
        $this->add_msg(' set_unit() '. T_(' method is ignored.'));
    }
    public function set_interval_font_family($value){
        //petra: drzat si v parametroch radsej pointer na posledny interval
        $last_interval = &$this->interval[count($this->interval)-1];
        $last_interval['styles']['font-family'] = $value;
    }
    public function set_interval_font_style($value){
        $last_interval = &$this->interval[count($this->interval)-1];
        $last_interval['styles']['font-style'] = $value;
    }
    public function set_interval_font_color($value){
        $last_interval = &$this->interval[count($this->interval)-1];
        $last_interval['styles']['color'] = $value;
    }
    public function set_interval_font_size($value){
        $last_interval = &$this->interval[count($this->interval)-1];
        $last_interval['styles']['font-size'] = $value.'px';
    }
    public function set_interval_image_width($value){
        $last_interval = &$this->interval[count($this->interval)-1];
        $last_interval['styles']['width'] = $value.'px';
    }
    public function set_interval_image_height($value){
        $last_interval = &$this->interval[count($this->interval)-1];
        $last_interval['styles']['height'] = $value.'px';
    }
    public function add_interval_text($to=1000, $text='???'){
        $new_interval = array(
            'text' => $text,
            'type' => 'text',
            'to' => $to,
            'styles' => array(),
        );
        $this->interval[] = $new_interval;
        $this->max_value = $to;
        $this->unit[] = "<$to ⇒ $text";
    }
    public function add_interval_image($to=1000, $value='???'){
        $new_interval = array(
            'to' => $to,
            'styles' => array(),
        );
        if ($value==='???'){
            $new_interval['text'] = '???';
            $new_interval['type'] = 'text';
        }else{
            $new_interval['image'] = $value;
            $new_interval['type'] = 'image';
        }

        $this->interval[] = $new_interval;
        $this->max_value = $to;
    }
}