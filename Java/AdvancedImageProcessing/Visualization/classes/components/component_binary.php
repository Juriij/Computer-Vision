<?php
class component_binary extends component_default{

    public function __construct($init = NULL, $type = 'variable') {
        parent::__construct($init,$type);
        $this->max_value = 1;
        $this->min_value = 0;
        $this->interval = array(
            0=>array(
                'image' => 'images/LIBRARY_NEW/buttons/btn_circle-dwn-OFF-cut.png',
                'text' => 'OFF',
                'type' => 'image',
                'styles' => array(),
            ),
            1=>array(
                'image' => 'images/LIBRARY_NEW/buttons/btn_circle-up-ON-cut.png',
                'text' => 'ON',
                'type' => 'image',
                'styles' => array(),
            ),
            2=>array(
                'text' => 'Not binary',
                'type' => 'text',
                'styles' => array( // can not be empty, because POST dont get it
                    'color' => 'red',
                ),
            ),
        );
        $this->unit = array();
    }

    public function set_display_value(){
        $alias = '';
        if(isset($this->value)){
            switch ($this->value){
                case 0: $alias = $this->interval[0];break;
                case 1: $alias = $this->interval[1];break;
                default: $alias = $this->interval[2];
            }
        }else{
            $alias = $this->interval[0];
            $alias['styles']['filter'] = 'grayscale(100%)';
        }
        $this->display_value = transform_alias($alias);
    }

    public function get_dataset(){
        $dataset = array(
            'data-template'=>'binary',
            'data-interval'=>json_encode($this->interval),
            'data-user_directory'=>$GLOBALS['user_directory'],
            );

        $parent_dataset = $this->get_parent_dataset();
        return array_merge($dataset, $parent_dataset);
    }

    public function set_max_value($value){
        //binary constraints are added in constructor
        $this->add_msg(' set_max_value() '. T_(' method is ignored.'));
    }
    public function set_min_value($value){
        //binary constraints are added in constructor
        $this->add_msg(' set_min_value() '. T_(' method is ignored.'));
    }
    public function set_unit($value){
        // unit is calculated in set_zero_text, set_one_text
        $this->add_msg(' set_unit() '. T_(' method is ignored.'));
    }
    public function set_zero_image($value='images/LIBRARY_NEW/buttons/btn_circle-dwn-OFF-cut.png', $ylabel=''){
        $this->interval[0]['image'] = $value;
        $this->interval[0]['type'] = 'image';
        // negative offsets since 7.1.0
        //$pos1 = strpos($value, '/', -1);
        //$pos2 = strpos($value, '.', -1);
        //if($pos1===FALSE) $pos1=0;
        //$this->unit[0] = '0 ⇒ '.substr($value, $pos1, $pos2-$pos1);

        if(!empty($ylabel)) $this->unit[0] = "0 ⇒ $ylabel";
        else{
            $value = basename($value, '.png');
            $this->unit[0] = "0 ⇒ $value";
        }
    }
    public function set_one_image($value='images/LIBRARY_NEW/buttons/btn_circle-up-ON-cut.png', $ylabel=''){
        $this->interval[1]['image'] = $value;
        $this->interval[1]['type'] = 'image';
        // negative offsets since 7.1.0
        //$pos1 = strpos($value, '/', -1);
        //$pos2 = strpos($value, '.', -1);
        //if($pos1===FALSE) $pos1=0;
        //$this->unit[1] = '1 ⇒ '.substr($value, $pos1, $pos2-$pos1);

        if(!empty($ylabel)) $this->unit[1] = "1 ⇒ $ylabel";
        else{
            $value = basename($value, '.png');
            $this->unit[1] = "1 ⇒ $value";
        }
    }
    public function set_zero_text($value='Stop'){
        $this->interval[0]['text'] = $value;
        $this->interval[0]['type'] = 'text';
        $this->unit[0] = "0 ⇒ $value";
    }
    public function set_one_text($value='Start'){
        $this->interval[1]['text'] = $value;
        $this->interval[1]['type'] = 'text';
        $this->unit[1] = "1 ⇒ $value";
    }
    public function set_zero_font_family($value){
        $this->interval[0]['styles']['font-family'] = $value;
    }
    public function set_zero_font_color($value){
        $this->interval[0]['styles']['color'] = $value;
    }
    public function set_zero_font_size($value){
        $this->interval[0]['styles']['font-size'] = $value.'px';
    }
    public function set_zero_image_width($value){
        $this->interval[0]['styles']['width'] = $value.'px';
    }
    public function set_zero_image_height($value){
        $this->interval[0]['styles']['height'] = $value.'px';
    }
    public function set_one_font_family($value){
        $this->interval[1]['styles']['font-family'] = $value;
    }
    public function set_one_font_color($value){
        $this->interval[1]['styles']['color'] = $value;
    }
    public function set_one_font_size($value){
        $this->interval[1]['styles']['font-size'] = $value.'px';
    }
    public function set_one_image_width($value){
        $this->interval[1]['styles']['width'] = $value.'px';
    }
    public function set_one_image_height($value){
        $this->interval[1]['styles']['height'] = $value.'px';
    }
}