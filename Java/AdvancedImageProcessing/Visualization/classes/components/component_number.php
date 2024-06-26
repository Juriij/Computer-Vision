<?php
class component_number extends component_default{

    public function __construct($init = NULL, $type = 'variable') {
        parent::__construct($init,$type);
    }

    public function set_display_value(){
        $style_attrs = array();

        if(isset($this->value)){
            switch($this->number_format){
                case 'gpslong':
                    if($this->value<0)
                        $letter = 'W';
                    else
                        $letter = 'E';
                    $value = Real2DMS($this->value).$letter;
                    break;
                case 'gpslat':
                    if($this->value<0)
                        $letter = 'S';
                    else
                        $letter = 'N';
                    $value = Real2DMS($this->value).$letter;
                    break;
                default:
                    $value = sprintf($this->number_format, $this->value);
            }

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
        }else{
            $value = '??.???';
        }

        $this->display_value = get_span($value, $style_attrs);
    }

    public function get_unit(){
        if (!empty($this->unit)) {
            $tag_style = transform_styles($this->unit_styles);
            return "<td\n"
            . "  style = '$tag_style'\n"
            . ">\n"
            . "$this->unit\n"
            . "</td>\n";
        }
    }

    public function get_dataset(){
        $dataset = array(
            'data-template'=>'number',
            'data-number_format'=>$this->number_format,
            'data-min_value'=>$this->min_value,
            'data-max_value'=>$this->max_value,
            );

        $parent_dataset = $this->get_parent_dataset();
        return array_merge($dataset, $parent_dataset);
    }
}

//https://en.wikiversity.org/wiki/Geographic_coordinate_conversion
function Real2DMS($value){
    $degrees = (int) $value;

    $value = ($value - $degrees)*60;
    $minutes = (int) $value;

    $value = ($value - $minutes)*60;
    $seconds = sprintf('%.4f',abs($value));

    return abs($degrees).'°'.abs($minutes).'′'.$seconds.'″';
}