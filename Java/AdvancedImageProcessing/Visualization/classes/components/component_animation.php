<?php
class component_animation extends component_default{

    public function get_image_styles(){
        $style_attrs = array();
        if (!empty($this->height)){  //div height
            $animation_height = $this->height * $this->nrows;
            $style_attrs['height'] = $animation_height.'px';
        }
        if (!empty($this->width)){  //div width
            $animation_width = $this->width * $this->ncolumns;
            $style_attrs['width'] = $animation_width.'px';
        }

        if(isset($this->value)){
            $corrected_value = $this->value;
            if($this->value<$this->min_value){
                $corrected_value = $this->min_value;
                $style_attrs['filter'] = 'grayscale(100%)';
            }
            if($this->value>$this->max_value){
                $corrected_value = $this->max_value;
                $style_attrs['filter'] = 'grayscale(100%)';
            }

            $nframes = $this->nrows * $this->ncolumns;
            $delta_ratio = $nframes / ($this->max_value - $this->min_value + 1 );
            $corrected_value = ($corrected_value - $this->min_value) * $delta_ratio;
        }else{
            $corrected_value = 0;
            $style_attrs['filter'] = 'grayscale(100%)';
        }
        if ((!empty($this->height))&&(!empty($this->width)) ){
            $offset_height_index=floor($corrected_value/$this->ncolumns); //height offset index
            $offset_height=$offset_height_index * $this->height;
            $offset_width_index=floor($corrected_value - ($offset_height_index*$this->ncolumns));
            $offset_width=$offset_width_index * $this->width;

            $style_attrs['margin-top'] = -$offset_height.'px';
            $style_attrs['margin-left'] = -$offset_width.'px';
        }

        return $style_attrs;
    }

    public function set_display_value(){
        // REQUIRED PARAMETERS
        if($this->min_value==-0x7FFFFFFFFFFFFFFF)
            $this->add_msg(' set_min_value() '. T_(' is required.'));
        if($this->max_value==0x7FFFFFFFFFFFFFFF)
            $this->add_msg(' set_max_value() '. T_(' is required.'));

        $style_attrs = array('overflow'=> 'hidden', 'display'=>'inline-block');
        if (!empty($this->height)){
            $style_attrs['height'] = $this->height.'px';
        }
        if (!empty($this->width)){
            $style_attrs['width'] = $this->width.'px';
        }
        $div_style = transform_styles($style_attrs);

        $output = "<div style='$div_style'>\n";
        $output .= get_image($this->image, $this->get_image_styles());
        $output .= "</div>\n";

        $this->display_value = $output;
    }

    public function get_dataset(){
        $dataset = array(
            'data-template'=>'animation',
            'data-nrows'=>$this->nrows,
            'data-ncolumns'=>$this->ncolumns,
            'data-width'=>$this->width,
            'data-height'=>$this->height,
            'data-image'=>$this->image,
            'data-user_directory'=>$GLOBALS['user_directory'],
            'data-min_value'=>$this->min_value,
            'data-max_value'=>$this->max_value,
            );

        $parent_dataset = $this->get_parent_dataset();
        return array_merge($dataset, $parent_dataset);
    }

    protected $nrows = 1;
    public function set_nrows($value){
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_nrows')){
            $this->nrows = $value;
        }
    }

    protected $ncolumns = 1;
    public function set_ncolumns($value){
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_ncolumns')){
            $this->ncolumns = $value;
        }
    }
}