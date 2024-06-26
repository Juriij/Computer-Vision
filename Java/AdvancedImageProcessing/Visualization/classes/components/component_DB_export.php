<?php
class component_DB_export extends component_default{
    public function __construct($init = NULL, $type = 'static') {
        parent::__construct($init,$type);
    }

    public $host = '127.0.0.1';
    public $user = '';
    public $password = '';
    public $db = '';
    public $tables = array();
    public $category = '';
    public $limit = '';

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
    public function set_DB_tables($tables){
        $this->tables = $tables;
    }
    public function set_category($category){
        $this->category = $category;
    }

    public function display_component(){
        if(!check_allow($this->display_security_lowest_level, $this->display_security_exception_ids))
            return;
        $button_type='submit';
        if((empty($this->user))||(empty($this->password))||(empty($this->db))||(empty($this->tables))  ){
            $this->add_global_error(T_('You forgot DB name, user, password or tables.'),'credentials');
            $button_type='button';
        }
        if(isset($_POST['export_submit'.$this->object_index])){
            $file_name = get_post_parameter('file_name', 'string');
            if(empty($file_name)){
                echo "<script>swal(\"Failed. No export.\")</script>";
            }else{
                $host = $this->host;
                $pass = $this->password;
                $user = $this->user;
                $db = $this->db;
                $tables = implode(' ', $this->tables);
                $dir = realpath('./').'/'.$GLOBALS['datafiledir'].'/'.$this->category;
                $limit = $this->limit;

                mkdir($dir, 0777);
                $file_name = $dir.'/'.$file_name.'.sql';
                $output = array();
                exec(
                    "mysqldump --user={$user} --password={$pass} --host={$host} {$db} $tables --result-file={$file_name}  2>&1",
                    $output);
                if(count($output)>1){
                    echo "<script>swal(\"Problem. ".$output[1]."\")</script>";
                }else{
                    echo "<script>swal(\"Export done.\")</script>";
                }
            }
        }

        $table_attrs = $this->get_table_attrs();
        $table_style = transform_styles($this->table_styles);
        ?>
        <form method='post' action='index.php?<?= $_SERVER["QUERY_STRING"] ?>' name='function_form' >
        <table <?= $table_attrs ?> style = '<?= $table_style ?>'>
            <tr>
                <td colspan='2'>
                <button type='button'> Exportuj mily user</button>
                </td>
            </tr>
            <tr>
                <td>
                <input type="text" value="nazov suboru" name="file_name"/>
                </td><td>
                <button type=<?= $button_type?> name='export_submit<?= $this->object_index ?>'> Export</button>
                </td>
            </tr>
        </table>
        </form>
        <?php
    }
}