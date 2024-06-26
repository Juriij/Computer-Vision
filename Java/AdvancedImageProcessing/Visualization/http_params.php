<?php
function get_faceplate_parameter($param){
    return get_GET_parameter($param);
}
/**
  * Gets GET parameter
  *
  * @param $param parameter name
  *
  * @return array
  */
function get_GET_parameter($param){
    //petra: prerobit ako filter_input(INPUT_GET,$get_name,FILTER_VALIDATE_INT);
    $output = NULL;
    switch ($param){
        //+SETTINGS
        case 'file':  //petra: osetrit ze aky file moze byt
            if (isset($_GET['file'])){$output = $_GET['file'];}
            else {
                $output = $GLOBALS['menu']['welcome'][1]['file'];
                header("Location: index.php?file=$output");
                die();
            }
            break;

        //GRAPH
        case 'multi_id':
            if (isset($_GET['multi_id'])){
                $output = json_decode($_GET['multi_id']);
            }
            break;
        case 'time_frame':
            if (isset($_GET['time_frame'])){
                $output = $_GET['time_frame'];
                $output = intval($output);
            }else $output=1000;
            break;

        case 'dcu_id':
        case 'var_id':
        case 'var_name':
        case 'var_pretty_name':
        case 'label':
        case 'ylabel':
        case 'event_types':
        case 'dcus':
        default:
            if (isset($_GET[$param])){$output = $_GET[$param];}

    }

    return $output;
}

/**
  * Gets POST parameter
  *
  * @param $post_name parameter name
  * @param $type integer or binary or string or language or timezone or DCU
  * @return array
  */
function get_post_parameter($post_name, $type='integer'){
    $output=NULL;
    switch ($post_name){
        //LANGUAGE
        case 'language_select':
            $output = filter_input(
                INPUT_POST,
                'language_select',
                FILTER_VALIDATE_REGEXP,
                array(
                    'options'=>array('regexp'=>"/[a-zA-Z_]+/"),
                    //'flags' => FILTER_NULL_ON_FAILURE,
                    )
                );
            if (!empty($output)) $_SESSION['language'] = $output;
            break;
        //HEADER
        case 'timezone_actual':
            $output = filter_input(
                INPUT_POST,
                'timezone_actual',
                FILTER_VALIDATE_REGEXP,
                array(
                    'options'=>array(
                        'regexp'=>"/[a-zA-Z_\/]+/",
                        //'default'=>'Europe/London',
                        ),
                    //'flags' => FILTER_NULL_ON_FAILURE,
                    )
                );
            if (!empty($output)) $_SESSION['timezone_actual'] = $output;
            break;
        //MENU
        case 'dcu_select':
            if (isset($_POST['dcu_select'])){
                $output = filter_input(INPUT_POST,'dcu_select',FILTER_VALIDATE_INT);
                session_start();
                if (!empty($output)) $_SESSION['dcu_id_selected'] = $output;
                session_write_close();
            }
            break;

        default:
            switch($type){
                case 'string':
                    $output = filter_input(
                        INPUT_POST,
                        $post_name,
                        FILTER_VALIDATE_REGEXP,
                        array(
                            'options'=>array('regexp'=>"/[a-zA-Z_0-9,]+/"),
                            //'flags' => FILTER_NULL_ON_FAILURE,
                        )
                    );
                    break;
                case 'real':
                case 1:
                    $output = filter_input(INPUT_POST,$post_name,FILTER_VALIDATE_FLOAT);
                    break;
                case 'binary':
                case 2:
                case 'integer':
                case 3:
                    $output = filter_input(INPUT_POST,$post_name,FILTER_VALIDATE_INT);
                    break;
                case 'datetime':
                case 'integer_array':
                    $output = $_POST[$post_name]; //petra: dokoncit filter_input
                    break;
                default:
                    $output=NULL;
            }
            if($output===FALSE)$output=NULL;
    }

    return $output;
}

function my_required_filter_input($name, $validation_type){
    $post = filter_input(INPUT_POST,$name,$validation_type);
    if($post===NULL) {
        $GLOBALS['errors']['general'][] = T_('Post not set').' '.$name;
        $output = array('errors'=>$GLOBALS['errors']);
        print(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));
        die();}
    if($post===FALSE) {
        $GLOBALS['errors']['general'][] = T_('Wrong post').' '.$name;
        $output = array('errors'=>$GLOBALS['errors']);
        print(json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR));
        die();}
    return $post;
}
// Select timezone
get_post_parameter('timezone_actual');
if(isset($_SESSION['timezone_actual'])){
    $GLOBALS['timezone_actual'] = $_SESSION['timezone_actual'];
}
date_default_timezone_set($GLOBALS['timezone_actual']);

// Select DCU
get_post_parameter('dcu_select');

// Select language
get_post_parameter('language_select');
if(isset($_SESSION['language'])){
    $GLOBALS['lang_actual'] = $_SESSION['language'];
}
define('LOCALE_DIR', realpath('./') .'/locale');
require_once(LOCALE_DIR.'/gettext.inc');
$encoding = 'UTF-8';
T_setlocale(LC_MESSAGES, $GLOBALS['lang_actual']);

T_bindtextdomain('process', LOCALE_DIR);
T_bind_textdomain_codeset('process', $encoding);

T_bindtextdomain('events', LOCALE_DIR);
T_bind_textdomain_codeset('events', $encoding);

$domain = 'messages';
T_bindtextdomain($domain, LOCALE_DIR);
T_bind_textdomain_codeset($domain, $encoding);
T_textdomain($domain);