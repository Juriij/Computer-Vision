<?php
class component_batchserver extends component_default{

    public function __construct($init = NULL, $type = 'batchserver') {
        parent::__construct($init,'batchserver');
        $this->value = 1; //petra: docasne
    }

    public function set_display_value(){
        $this->display_value = '<div style="background-color:white; color: black;'
            . 'width:'.$this->width.'px'.';'
            . 'height:'.$this->height.'px'.';">Batch server</div>';
    }
}