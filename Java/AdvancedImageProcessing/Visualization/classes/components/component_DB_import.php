<?php
class component_DB_import extends component_default{
    public function __construct($init = NULL, $type = 'static') {
        parent::__construct($init,$type);
    }

    public $host = '127.0.0.1';
    public $user = '';
    public $password = '';
    public $db = '';
    public $category = '';

    public function set_DB_host($host){
        $this->host = $host;
    }
    public function set_DB_user($user){
        $this->user = $user;
    }
    public function set_DB_password($password){
        $this->password = $password;
    }
    public function set_DB_name($db){
        $this->db = $db;
    }
    public function set_category($category){
        $this->category = $category;
    }

    public function display_component(){
        if(!check_allow($this->display_security_lowest_level, $this->display_security_exception_ids))
            return;
        $dir = realpath('./').'/'.$GLOBALS['datafiledir'].'/'.$this->category;
        if(file_exists($dir))
            $scanned_files = array_diff(scandir($dir), array('..', '.'));
        else
            $scanned_files = array();
        if(isset($_POST['import_submit'.$this->object_index])){
            $file_name = get_post_parameter('file_name', 'string');
            if(empty($file_name)){
                echo "<script>swal(\"Failed. No import.\")</script>";
            }else{
                $host = $this->host;
                $pass = $this->password;
                $user = $this->user;
                $db = $this->db;

                $file_name = $dir.'/'.$file_name;
                exec(
                    "mysql --user={$user} --password={$pass} --host={$host} {$db} < $file_name 2>&1",
                    $output);

                if(count($output)>1){
                    echo "<script>swal(\"Problem. ".$output[1]."\")</script>";
                }else{
                    echo "<script>swal(\"Import done.\")</script>";
                }
            }
        }

        $table_attrs = $this->get_table_attrs();
        $table_style = transform_styles($this->table_styles);
        $button_type='submit';
        if((empty($this->user))||(empty($this->password))||(empty($this->db))  ){
            $this->add_global_error(T_('You forgot DB name, user or password.'),'credentials');
            $button_type='button';
        }
        ?>
        <form method='post' action='index.php?<?= $_SERVER["QUERY_STRING"] ?>' name='function_form' >
        <table <?= $table_attrs ?> style = '<?= $table_style ?>'>
            <tr>
                <td colspan='2'>
                <button type='button'> Importuj mily user</button>
                </td>
            </tr>
            <tr>
                <td>
                <div class='select-wrapper'>
                <select name="file_name">
                    <?php
                    foreach($scanned_files as $scanned_file){
                        echo "<option value='$scanned_file'>".substr($scanned_file, 0, -4)."</option>\n";
                    }
                    ?>

                </select>
                </div>
                </td><td>
                <button type=<?= $button_type?> name='import_submit<?= $this->object_index ?>'> Import</button>
                </td>
            </tr>
        </table>
        </form>
        <?php
    }
}