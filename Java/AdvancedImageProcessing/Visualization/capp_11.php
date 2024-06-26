<?php
$tunit_str = 'sec';
$tunit = 1;
$dispvar_init = (object) array("font"=>"Arial", "fontsize"=>"10px", "unitfontsize"=>"9px", "unitfontsizebin"=>"9px", "element_backgroundcolor"=>"rgba(10,10,10,0.6)", "textcolor"=>"white", "canavas"=>"images/capp_11.png");
$GLOBALS['security_lowest_level'] = 15;
$GLOBALS['show_panel_errors'] = false;
$disp_realval_template = array("unitid"=>11, "label"=> "", "id"=>-1, "name"=>"", "disptype"=>1, "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "x"=>100, "y"=>100, "value"=> 0, "state"=>2, "phunit"=>"%", "panel"=>"", "numform"=>"%3.3f", 'sampletime'=> 0);
$disp_binval_template = array("unitid"=>11, "label"=> "", "id"=>-1, "name"=>"", "disptype"=>2, "one"=>"ON", "zero"=>"OFF", "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "x"=>1, "y"=>1, "value"=> 0, "state"=>1, "panel"=>"", 'sampletime'=> 0);
$disp_binvalimg_template = array("unitid"=>11, "label"=> "", "id"=>-1, "name"=>"", "disptype"=>3, "one"=>"images/bval_on.jpg", "zero"=>"images/bval_off.jpg", "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "x"=>1, "y"=>1, "value"=> 0, "state"=>1, "panel"=>"autoswitch", 'sampletime'=> 0);
$disp_binvalimg_autoswitch_template = array("unitid"=>11, "label"=> "", "id"=>-1, "name"=>"", "disptype"=>4, "one"=>"images/on_button.png", "zero"=>"images/off_button.png", "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "x"=>1, "y"=>1, "value"=> 0, "state"=>1, "panel"=>"", 'sampletime'=> 0);
$disp_cfun_template = array("unitid"=>11, "disptype"=>100, "label"=> "", "id"=>-1, "name"=>"", "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "state"=>1);
$disp_siglink_template = array("unitid"=>11, "disptype"=>200, "graphid"=>0, "label"=> "", "id"=>"", "width"=>200, "height"=>200, "graphtype" => 0, "timespan"=>10, "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "state"=>1);
$disp_signal_template = array("unitid"=>11, "disptype"=>201, "graphid"=>0, "label"=> "", "id"=>"", "width"=>200, "height"=>200, "ylabel"=>"", "timespan"=>10, "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "state"=>1);
$disp_txtlink_template = array("unitid"=>11, "label"=>"", "disptype"=>300, "x"=>100, "y"=>100, "panel"=>"", "errimg"=>"images/dval_err.jpg", "okimg"=>"images/dval_ok.jpg", "almimg"=>"images/dval_alarm.jpg", "ovrdimg"=>"images/dval_ovrd.jpg", "state"=>2);
$xoff=0;
$yoff=0;
$xoffdiag=0;
$yoffdiag=0;
$diagsize=10061;
$diagname="images/capp_11.png";
session_start();
$viewname = basename($_SERVER['PHP_SELF']);
if (!empty ($_POST["xoff"])){
$xoff=$_POST["xoff"];
$yoff=$_POST["yoff"];
$xoffdiag=$_POST["xoffdiag"];
$yoffdiag=$_POST["yoffdiag"];
$diagsize=$_POST["diagsize"];
$diagname=$_POST["diagname"];}
else{
if(!empty ($_SESSION["dataviews"])){
$nv = count($_SESSION["dataviews"]);
$allviews = $_SESSION["dataviews"];}
else{$nv=0;}
for($i = 0; $i < $nv; $i++ ){
$ele = $allviews[$i];
if(strcmp($ele->name,$viewname)==0){
$actview = $ele;
break;}
}
if (!empty ($actview)){
$xoff = $actview->xoff;
$yoff = $actview->yoff;
}
}
$inx=0;
$dispvars = array();

$dispvars[$inx] = $disp_cfun_template;
$dispvars[$inx]["id"] = 0;
$dispvars[$inx]["name"] ="cfn:: Bin.Zero";
$dispvars[$inx]["x"] = 50+$xoff;
$dispvars[$inx]["y"] = 50+$yoff;
$dispvars[$inx]["label"] = " Bin.Zero";
$dispvars[$inx]["panel"] = "cfunpanel.php";
$inx=$inx+1;

$dispvars[$inx] = $disp_realval_template;
$dispvars[$inx]["id"] = 0;
$dispvars[$inx]["name"] = "cfn:: Bin.Zero_out0";
$dispvars[$inx]["label"] = "0:";
$dispvars[$inx]["x"] = 130+$xoff;
$dispvars[$inx]["y"] = 63+$yoff;
$dispvars[$inx]["panel"] = "cvarpanel.php";
$dispvars[$inx]["phunit"] = "";
$inx=$inx+1;

$dispvars[$inx] = $disp_cfun_template;
$dispvars[$inx]["id"] = 1;
$dispvars[$inx]["name"] ="cfn:: Real.Zero";
$dispvars[$inx]["x"] = 280+$xoff;
$dispvars[$inx]["y"] = 50+$yoff;
$dispvars[$inx]["label"] = " Real.Zero";
$dispvars[$inx]["panel"] = "cfunpanel.php";
$inx=$inx+1;

$dispvars[$inx] = $disp_realval_template;
$dispvars[$inx]["id"] = 1;
$dispvars[$inx]["name"] = "cfn:: Real.Zero_out0";
$dispvars[$inx]["label"] = "0:";
$dispvars[$inx]["x"] = 360+$xoff;
$dispvars[$inx]["y"] = 63+$yoff;
$dispvars[$inx]["panel"] = "cvarpanel.php";
$dispvars[$inx]["phunit"] = "";
$inx=$inx+1;

$dispvars[$inx] = $disp_cfun_template;
$dispvars[$inx]["id"] = 2;
$dispvars[$inx]["name"] ="cfn:: FROM_Advanced_Image_Proc RTUDP_Input1200";
$dispvars[$inx]["x"] = 730+$xoff;
$dispvars[$inx]["y"] = 160+$yoff;
$dispvars[$inx]["label"] = " FROM_Advanced_Image_Proc RTUDP_Input1200";
$dispvars[$inx]["panel"] = "cfunpanel.php";
$inx=$inx+1;

$dispvars[$inx] = $disp_realval_template;
$dispvars[$inx]["id"] = 2;
$dispvars[$inx]["name"] = "cfn:: FROM_Advanced_Image_Proc RTUDP_Input1200_out0";
$dispvars[$inx]["label"] = "0:";
$dispvars[$inx]["x"] = 810+$xoff;
$dispvars[$inx]["y"] = 173+$yoff;
$dispvars[$inx]["panel"] = "cvarpanel.php";
$dispvars[$inx]["phunit"] = "";
$inx=$inx+1;

$dispvars[$inx] = $disp_realval_template;
$dispvars[$inx]["id"] = 3;
$dispvars[$inx]["name"] = "cfn:: FROM_Advanced_Image_Proc RTUDP_Input1200_out1";
$dispvars[$inx]["label"] = "1:";
$dispvars[$inx]["x"] = 810+$xoff;
$dispvars[$inx]["y"] = 188+$yoff;
$dispvars[$inx]["panel"] = "cvarpanel.php";
$dispvars[$inx]["phunit"] = "";
$inx=$inx+1;

$dispvars[$inx] = $disp_realval_template;
$dispvars[$inx]["id"] = 4;
$dispvars[$inx]["name"] = "cfn:: FROM_Advanced_Image_Proc RTUDP_Input1200_out2";
$dispvars[$inx]["label"] = "2:";
$dispvars[$inx]["x"] = 810+$xoff;
$dispvars[$inx]["y"] = 203+$yoff;
$dispvars[$inx]["panel"] = "cvarpanel.php";
$dispvars[$inx]["phunit"] = "";
$inx=$inx+1;

$dispvars[$inx] = $disp_realval_template;
$dispvars[$inx]["id"] = 5;
$dispvars[$inx]["name"] = "cfn:: FROM_Advanced_Image_Proc RTUDP_Input1200_out3";
$dispvars[$inx]["label"] = "3:";
$dispvars[$inx]["x"] = 810+$xoff;
$dispvars[$inx]["y"] = 218+$yoff;
$dispvars[$inx]["panel"] = "cvarpanel.php";
$dispvars[$inx]["phunit"] = "";
$inx=$inx+1;

$dispvars[$inx] = $disp_realval_template;
$dispvars[$inx]["id"] = 6;
$dispvars[$inx]["name"] = "cfn:: FROM_Advanced_Image_Proc RTUDP_Input1200_out4";
$dispvars[$inx]["label"] = "4:";
$dispvars[$inx]["x"] = 810+$xoff;
$dispvars[$inx]["y"] = 233+$yoff;
$dispvars[$inx]["panel"] = "cvarpanel.php";
$dispvars[$inx]["phunit"] = "";
$inx=$inx+1;

$dispvars[$inx] = $disp_realval_template;
$dispvars[$inx]["id"] = 7;
$dispvars[$inx]["name"] = "cfn:: FROM_Advanced_Image_Proc RTUDP_Input1200_out5";
$dispvars[$inx]["label"] = "5:";
$dispvars[$inx]["x"] = 810+$xoff;
$dispvars[$inx]["y"] = 248+$yoff;
$dispvars[$inx]["panel"] = "cvarpanel.php";
$dispvars[$inx]["phunit"] = "";
$inx=$inx+1;

$dispvars[$inx] = $disp_realval_template;
$dispvars[$inx]["id"] = 8;
$dispvars[$inx]["name"] = "cfn:: FROM_Advanced_Image_Proc RTUDP_Input1200_out6";
$dispvars[$inx]["label"] = "6:";
$dispvars[$inx]["x"] = 810+$xoff;
$dispvars[$inx]["y"] = 263+$yoff;
$dispvars[$inx]["panel"] = "cvarpanel.php";
$dispvars[$inx]["phunit"] = "";
$inx=$inx+1;

$dispvars[$inx] = $disp_cfun_template;
$dispvars[$inx]["id"] = 3;
$dispvars[$inx]["name"] ="cfn:: TO_Advanced_Image_Proc RTUDP_Output2200";
$dispvars[$inx]["x"] = 730+$xoff;
$dispvars[$inx]["y"] = 370+$yoff;
$dispvars[$inx]["label"] = " TO_Advanced_Image_Proc RTUDP_Output2200";
$dispvars[$inx]["panel"] = "cfunpanel.php";
$inx=$inx+1;

$dispvars[$inx] = $disp_cfun_template;
$dispvars[$inx]["id"] = 4;
$dispvars[$inx]["name"] ="cfn:: Real.Zero";
$dispvars[$inx]["x"] = 1170+$xoff;
$dispvars[$inx]["y"] = 70+$yoff;
$dispvars[$inx]["label"] = " Real.Zero";
$dispvars[$inx]["panel"] = "cfunpanel.php";
$inx=$inx+1;

$dispvars[$inx] = $disp_realval_template;
$dispvars[$inx]["id"] = 9;
$dispvars[$inx]["name"] = "cfn:: Real.Zero_out0";
$dispvars[$inx]["label"] = "0:";
$dispvars[$inx]["x"] = 1250+$xoff;
$dispvars[$inx]["y"] = 83+$yoff;
$dispvars[$inx]["panel"] = "cvarpanel.php";
$dispvars[$inx]["phunit"] = "";
$inx=$inx+1;

include("visualization_display_capp.php");
?>
