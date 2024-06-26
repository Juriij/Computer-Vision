<?php
$tunit_str = 'sec';
$tunit = 1;
$dispvar_init = (object) array("font"=>"Arial", "fontsize"=>"8px", "unitfontsize"=>"9px", "unitfontsizebin"=>"9px", "textcolor"=>"white", "canavas"=>"images/dcufront.png");
$disp_realval_template = array("unitid"=>11, "label"=> "", "id"=>-1, "name"=>"", "disptype"=>1, "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "x"=>100, "y"=>100, "value"=> 0, "state"=>2, "phunit"=>"%", "panel"=>"", "numform"=>"%3.3g", 'sampletime'=> 0);
$disp_binval_template = array("unitid"=>11, "label"=> "", "id"=>-1, "name"=>"", "disptype"=>2, "one"=>"ON", "zero"=>"OFF", "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "x"=>1, "y"=>1, "value"=> 0, "state"=>1, "panel"=>"", 'sampletime'=> 0);
$disp_binvalimg_template = array("unitid"=>11, "label"=> "", "id"=>-1, "name"=>"", "disptype"=>3, "one"=>"images/bval_on.jpg", "zero"=>"images/bval_off.jpg", "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "x"=>1, "y"=>1, "value"=> 0, "state"=>1, "panel"=>"autoswitch", 'sampletime'=> 0);
$disp_binvalimg_autoswitch_template = array("unitid"=>11, "label"=> "", "id"=>-1, "name"=>"", "disptype"=>4, "one"=>"images/on_button.png", "zero"=>"images/off_button.png", "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "x"=>1, "y"=>1, "value"=> 0, "state"=>1, "panel"=>"", 'sampletime'=> 0);
$disp_cfun_template = array("unitid"=>11, "disptype"=>100, "label"=> "", "id"=>-1, "name"=>"", "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "state"=>1);
$disp_siglink_template = array("unitid"=>11, "disptype"=>200, "graphid"=>0, "label"=> "", "id"=>"", "width"=>200, "height"=>200, "graphtype" => 0, "timespan"=>10, "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "state"=>1);
$disp_signal_template = array("unitid"=>11, "disptype"=>201, "graphid"=>0, "label"=> "", "id"=>"", "width"=>200, "height"=>200, "ylabel"=>"", "timespan"=>10, "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "state"=>1);
$disp_txtlink_template = array("unitid"=>11, "label"=>"", "disptype"=>300, "x"=>100, "y"=>100, "panel"=>"", "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "state"=>2);
$inx=0;
$dispvars = array();

include("visualization_display_nouser.php");
?>
