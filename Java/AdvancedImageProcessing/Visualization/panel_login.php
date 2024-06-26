<?php
function access_granted($menu_type, $viewinx=0){
    if(isset($_SESSION['userId'])){
        if($menu_type == 'event'){
            return TRUE;
        }
        if (($menu_type == 'service')||($menu_type == 'examples')){
            if(isset($_SESSION['showControlSystem'])){
                return $_SESSION['showControlSystem'];
            }
        }else{ //process
            if(isset($_SESSION['showView'])){
                $swen = $_SESSION['showView'];
                if($viewinx<= count($swen)){
                    return $swen[$viewinx];
                }
            }
        }
    }
    return FALSE;
}

function panel_login_show(){
    if (!isset($_SESSION['login_username'])){
        ?>

        <div class="logged_none setting"><?= T_('Login') ?></div>
        <!--LOGIN FORM -->
        <div id='login_div' class = 'panel_login panel abstract_top' style='display:inherit;'>
        <?php login_form_show(); ?>

        </div>
        <?php
    }else{
        ?>
        <div class="logged_user setting">
        <i class="fa fa-user" aria-hidden="true"></i><!--&#128100;-->
        <?= $_SESSION['login_username'] ?>

        </div>
        <!--Logout Form -->
        <div id='logout_div' class = 'panel_login panel abstract_top'>
        <?php logout_form_show(); ?>

        </div>
        <?php
    }
}

function login_form_show(){
?>

        <form class='login_form panel_content' name='login_form'
              method='post' action='index.php?<?= $_SERVER["QUERY_STRING"] ?>'>
            <label><?= T_('Username') ?><br/>
            <input type='text' name='login_username' placeholder='root' required />
            </label>
            <br/>
            <label><?= T_('Password') ?><br/>
            <input type='password' name='login_password' placeholder='************' required/>
            </label>
            <br/>
            <label><?= T_('Automatic LogOff period in minutes') ?><br/>
            <input type='text' name='login_period' value='10' required/>
            </label>
            <br/>
            <div class='checkbox'>
                <label> <?= T_('Never log off') ?><br/>
                <input name='login_remember' type='checkbox' />
                </label>
            </div>

            <button type='submit' name='cancel'>
                <?= T_('Cancel') ?>
            </button>
            <button type='submit' name='login'>
                <?= T_('Login') ?>
            </button>
        </form>
<?php
}

function logout_form_show(){
?>

        <form id='logout_form' class='panel_content' name='logout_form'
              method='post' action='index.php?<?= $_SERVER["QUERY_STRING"] ?>'>
            <div class='login_title'><?= T_('Do you really want to log out?') ?></div>
            <button type='submit' name='cancel'>
                <?= T_('Cancel') ?>
            </button>
            <button type='submit' name='logout'>
                <?= T_('Logout') ?>
            </button>
        </form>
<?php
}

function panel_login_script(){
?>
    <!--PANEL LOGIN SCRIPT -->
    <script>
        $(document).ready(function(){

            $('.logged_none').click(function(){
                if($('#login_div').is(':hidden')) {
                    hide_all_panels();
                    $('#login_div').show(500);
                }else {
                    $('#login_div').hide(500);
                }
            });
            $('.logged_user').click(function(){
                if($('#logout_div').is(':hidden')) {
                    hide_all_panels();
                    $('#logout_div').show(500);
                }else {
                    $('#logout_div').hide(500);
                }
            });

            $('.panel_login').find('[name=cancel]').click(function(){
                $('#logout_div').hide();
                $('#login_div').hide();
            });

        });

    </script>
<?php
}

function check_credentials($login_username, $login_password){
    global $dbLink_Config;
    $obsahTab = array();

    if((empty($login_username))||(empty($login_password)))
        return $obsahTab;
    $stmt = $dbLink_Config->prepare(
        'SELECT * FROM visualUsers '
        . ' WHERE visualLogin = ? AND visualPassword = ?'
        );
    $stmt->bind_param('ss', $login_username,$login_password);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->free_result();
    $stmt->close();

    if (($result) and ($result->num_rows)){
        $obsahTab = $result->fetch_assoc();
        $showView = array();
        foreach ($obsahTab as $key=>$value){
            if (strpos($key, 'showView') !== false) {
                $viewID = substr($key, 8);
                $showView[$viewID] = $value;
            }
        }
        $obsahTab['showView'] = $showView;
    }

    return $obsahTab;
}

function panel_login_posts(){
    // check if logged too long
    if (isset($_SESSION['login_username'])){
        $dT = $GLOBALS['now_time'] - $_SESSION['SessionLastUse'];

        if  (($dT >  $_SESSION['SessionPeriod']) && // if too long since last use
            ($_SESSION['SessionNeverLogOff']<1)) {
                logout();
        }else{
            session_start();
            $_SESSION['SessionLastUse'] = $GLOBALS['now_time'];
            session_write_close();
        }
    }

    // login user
    if (isset($_POST['login'])){
        $login_username = get_post_parameter('login_username', 'string');
        $login_password = get_post_parameter('login_password', 'string');
        $logdat = check_credentials($login_username, $login_password);

        if(isset($logdat['userId'])) { // succesful login
            session_start();
            $_SESSION['SessionLastUse'] = $GLOBALS['now_time'];
            $_SESSION['SessionPeriod']  = 3;
            $login_period = get_post_parameter('login_period', 'integer');
            if(!empty($login_period)) $_SESSION['SessionPeriod']  = $login_period*60;

            if(isset($_POST['login_remember'])){
                $_SESSION['SessionNeverLogOff'] = 1;
            }else{
                $_SESSION['SessionNeverLogOff'] = 0;
            }
            $_SESSION['userId'] = $logdat['userId'];
            $_SESSION['login_username'] = $login_username;
            $_SESSION['dcuUserId'] = $logdat['dcuUserId'];
            $_SESSION['dcuUserSecurity'] = $logdat['dcuUserSecurity'];
            $_SESSION['dcuUserPassword'] = $logdat['dcuUserPassword'];
            $_SESSION['showControlSystem'] = $logdat['showControlSystem'];
            $_SESSION['allowEventAck'] = $logdat['allowEventAck'];
            $_SESSION['showView'] = $logdat['showView'];
            session_write_close();
        }else{
            $GLOBALS['errors']['general'][] = T_('Login unsuccessful');
        }
    }

    // logout user
    if (isset($_POST['logout'])){
        logout();
    }
}

function logout(){
    session_start();
    unset($_SESSION['login_username']);
    unset($_SESSION['SessionLastUse']);
    unset($_SESSION['SessionPeriod']);
    unset($_SESSION['SessionNeverLogOff']);
    unset($_SESSION['dcuUserId']);
    unset($_SESSION['dcuUserSecurity']);
    unset($_SESSION['dcuUserPassword']);
    unset($_SESSION['showControlSystem']);
    unset($_SESSION['allowEventAck']);
    unset($_SESSION['showView']);
    unset($_SESSION['userId']);
    session_write_close();
}