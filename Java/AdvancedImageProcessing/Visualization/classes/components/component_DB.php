<?php
class component_DB extends component_default{
    public function __construct($init = NULL, $type = 'static') {
        parent::__construct($init,$type);
    }

    public $host = '127.0.0.1';
    public $user = '';
    public $password = '';
    public $db = '';
    public $table = '';
    public $select = ' * ';
    public $column_labels = array();
    public $orderby = '';
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
    public function set_DB_table($table){
        $this->table = $table;
    }
    public function set_column_label($column,$label){
        $this->column_labels[$column] = $label;
        $this->select .= ", $column as '$label' ";
    }
    public function set_orderby($orderby, $order = 'ASC'){
        if(!empty($this->orderby)) $this->orderby.=', ';
        $this->orderby .= " $orderby ";
        if(strtoupper($order)=='DESC') $this->orderby .= $order;
    }
    public function set_limit($value){
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'table_limit')){
            $this->limit = " LIMIT $value ";
        }
    }
    public function set_height($value){
        $value = my_intval($value);
        if ($this->validate($value, 'VALUE_POSITIVE_INTEGER', 'set_height')){
            $this->table_styles['display'] = 'block';
            $this->table_styles['max-height'] = $value.'px';
            $this->table_styles['overflow-y'] = 'scroll';
        }
    }
    public function display_component(){
        if(!check_allow($this->display_security_lowest_level, $this->display_security_exception_ids))
            return;

        try{
            $dbLink= new mysqli(
                $this->host,
                $this->user,
                $this->password,
                $this->db);
        } catch (Exception $ex) {

        }


        if (mysqli_connect_error()) {
            $text = T_('DB CONNECT ERROR').' (' . mysqli_connect_errno() . ') '. mysqli_connect_error();
            $GLOBALS['errors']['general'][]=
                sprintf(
                T_('Object %d - [%d, %d]'),
                $this->object_index,
                $this->table_styles['left'],
                $this->table_styles['top']
                ).' ⇒ '.$text;
            return;
        }
        $dbLink->set_charset('utf8');
        $dbLink->query("SET time_zone = '".$GLOBALS['timezone_actual']."'");
        if(!empty($this->orderby))
            $this->orderby = ' ORDER BY '.$this->orderby;

        $stmt = $dbLink->prepare(
            'SELECT '.$this->select.' FROM '.$this->table.
            $this->orderby.$this->limit
            );
        if ( FALSE===$stmt ){
            $GLOBALS['errors']['general'][] = T_('Displaying component failed.').'(->prepare) '.$dbLink->error;
            return FALSE;
        }

        $success = $stmt->execute();
        if ( FALSE===$success ){
            $GLOBALS['errors']['general'][] = T_('Displaying component failed.').'(->execute) '.$stmt->error;
            $stmt->close();
            return FALSE;
        }
        $result = $stmt->get_result();
        $stmt->close();

        $table_attrs = $this->get_table_attrs();
        $this->table_styles['border'] = '4px solid gray';
        $table_style = transform_styles($this->table_styles);
        echo "<table $table_attrs \n";
        echo "  style = '$table_style' \n";
        echo "> \n";
        echo "<thead> \n";
        if (isset($this->label))
            echo "<tr><th colspan='3'>".$this->label."</th></tr>\n";
        echo "<tr>\n";

        foreach($this->column_labels as $label){
            echo "<th>$label\n";
            echo "</th>\n";
        }

        echo "</tr> \n";
        echo "</thead> \n";
        echo "<tbody> \n";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>\n";
            foreach($this->column_labels as $key=>$label){
                echo "<td>\n";
                echo $row[$key];
                echo "</td>\n";
            }
            echo "</tr> \n";
        }

        echo "</tbody> \n";
        echo "</table> \n\n";
        $dbLink->close();
    }
}