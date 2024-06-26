<?php
class component_image extends component_default{

    public function __construct($init = NULL, $type = 'static') {
        parent::__construct($init,$type);
    }

    public function get_display_value(){
        $style_attrs = array();
        if (!empty($this->height)){
            $style_attrs['height'] = $this->height.'px';
        }
        if (!empty($this->width)){
            $style_attrs['width'] = $this->width.'px';
        }

        if(!isset($this->image))
        $this->add_msg(T_('To set image, use set_image() method'), 'VALIDATE');

        if(isset($this->scale_y)){
            $delta = (-1) * $this->scale_y / ($this->max_value - $this->min_value);
            $position = ($this->value - $this->min_value) * $delta;
            $style_attrs['position'] = 'absolute';
            $style_attrs['top'] = $position.'px';
            $style_attrs['left'] = '0px';
        }
        if(isset($this->scale_x)){
            $delta = $this->scale_x / ($this->max_value - $this->min_value);
            $position = ($this->value - $this->min_value) * $delta;
            $style_attrs['position'] = 'absolute';
            $style_attrs['left'] = $position.'px';
            $style_attrs['top'] = '0px';
        }
        if ($this->rotation == True){
            $style_attrs['transform'] = 'rotate('.$this->value.'deg )';
        }
        $this->display_value = get_image($this->image, $style_attrs);
        return $this->display_value;
    }
/*
    public function set_value($new_value, $states=array()){
        return;
    }
*/
    public function get_dataset(){
        $dataset = array(
            'data-template'=>'image',
            'data-image'=>$this->image,
            'data-width'=>$this->width,
            'data-height'=>$this->height,
            'data-user_directory'=>$GLOBALS['user_directory'],
            );

        $parent_dataset = $this->get_parent_dataset();
        return array_merge($dataset, $parent_dataset);
    }
}