<?php 

abstract class table
{

    public $connection_id = "";
    public $shutdown_queries = array();
    public $queries = array();
    public $query_id = "";
    public $query_count = 0;
    public $record_row = array();
    public $failed = 0;
    public $halt = "";
    public $sql = '';
    public $db = null;

    public function __construct()
    {
        $config = config::get('database');
        if (!isset($this->prefix))
            $this->prefix = isset($config['prefix']) ? $config['prefix'] : '';
        if (!isset($this->name))
            $this->name = $this->prefix . get_class($this);
        else
            $this->name = $this->prefix . $this->name;
        $this->connect($config["hostname"], $config["user"], $config["password"], $config['database'], $config['type'], $config['encoding']);
    }

    public function connect($host, $user, $pass, $dbname, $type, $charset)
    {

        if ($type == 'mysqli') {
            $this->db = dbmysqli::getInstance($host, $user, $pass, $dbname);
        }elseif($type == 'mysql') {
            $this->db = dbmysql::getInstance($host,$user,$pass,$dbname);
        }
        if (!$this->db->islink) {
            if ($_GET['case'] != 'install' && file_exists(ROOT . '/install/locked'))
                exit('数据库连接失败2!');
        }

        if ($charset && $type!= 'pdosqlite') {
            $this->db->query("SET NAMES '$charset'");
            $this->db->query("SET sql_mode=''");
        }
        return true;
    }

    function query($sql)
    {
        return $this->db->query($sql);
    }

    function query_unbuffered($sql = "")
    {
        return $this->query($sql);
    }

    function fetch_array($sql = "")
    {
        return $this->db->fetch_array($sql);
    }

    function shutdown_query($query_id = "")
    {
        $this->shutdown_queries[] = $query_id;
    }

    function affected_rows()
    {
        return @mysqli_affected_rows($this->connection_id);
    }

    function num_rows($res)
    {
        return $this->db->num_rows($res);
    }

    function get_errno()
    {
        $this->errno = @mysqli_errno($this->connection_id);
        return $this->errno;
    }

    function insert_id()
    {
        return $this->db->insert_id();
        //return @mysqli_insert_id($this->connection_id);
    }

    function query_count()
    {
        return $this->query_count;
    }

    function free_result($res)
    {
        $this->db->free_result($res);
    }

    function close_db()
    {
        if ($this->connection_id) {
            //return @mysql_close($this->connection_id);
        }
    }

    function get_table_names()
    {
        global $db_config;
        $result = mysqli_list_tables($db_config["database"]);
        $num_tables = @mysqli_numrows($result);
        for ($i = 0; $i < $num_tables; $i++) {
            $tables[] = mysqli_tablename($result, $i);
        }
        mysqli_free_result($result);
        return $tables;
    }

    function halt($the_error = "")
    {
        //return;
        if (!config::get('debug'))
            return;
        //$message = 'sql-error: ' . $sql . "<br/>\r\n";
        $message = $the_error . "<br/>\r\n";
        $message .= $this->get_errno() . "<br/>\r\n";
        exit($message);
        @mysqli_query($sql);
    }

    function __destruct()
    {
        $this->shutdown_queries = array();
        $this->close_db();
    }

    function sql_select($tbname, $where = "", $limit = 0, $fields = "*", $order = '')
    {
        $sql = "SELECT " . $fields . " FROM `" . $tbname . "` ";
        if($where){
            $sql .= " WHERE " . $where;
        }
        if($order){
            $sql .= " ORDER BY " . $order;
        }
        if($limit){
            $sql .= " LIMIT " . $limit;
        }
        //$sql = "SELECT " . $fields . " FROM `" . $tbname . "` " . ($where ? " WHERE " . $where : "") . " ORDER BY " . $order . ($limit ? " limit " . $limit : "");
        //echo $sql."<br>";
        return $sql;
    }

    function sql_insert($tbname, $row)
    {
        $sqlfield = '';
        $sqlvalue = '';
        foreach ($row as $key => $value) {
            if (in_array($key, explode(',', $this->getcolslist()))) {
                $sqlfield .= $key . ",";
                $sqlvalue .= "'" . $value . "',";
            }
        }
        return "INSERT INTO `" . $tbname . "`(" . substr($sqlfield, 0, -1) . ") VALUES (" . substr($sqlvalue, 0, -1) . ")";
    }

    function sql_update($tbname, $row, $where)
    {
        //var_dump($row);
        $sqlud = '';
        if (is_string($row))
            $sqlud = $row . ' ';
        else
            foreach ($row as $key => $value) {
                if (in_array($key, explode(',', $this->getcolslist()))) {
                    $value = $value;
                    /*if (preg_match('/^\[(.*)\]$/',$value,$match))
                        $sqlud .= "`$key`"."= '".$match[1]."',";
                    else*/
                    if ($value === "")
                        $sqlud .= "`$key`= NULL, ";
                    else
                        $sqlud .= "`$key`" . "= '" . $value . "',";
                }
            }
        $sqlud = rtrim($sqlud);
        $sqlud = rtrim($sqlud, ',');
        $this->condition($where);
        $sql = "UPDATE `" . $tbname . "` SET " . $sqlud . " WHERE " . $where;
        //echo $sql;
        return $sql;
    }

    function sql_replace($tbname, $row)
    {
        /*$sqlud = '';
        if (is_string($row))
            $sqlud = $row . ' ';
        else
            foreach ($row as $key => $value) {
                if (in_array($key, explode(',', $this->getcolslist()))) {
                    $value = $value;
                    $sqlud .= $key . "= '" . $value . "',";
                }
            }
        return "REPLACE INTO `" . $tbname . "` SET " . substr($sqlud, 0, -1);*/

        $sqlfield = '';
        $sqlvalue = '';
        foreach ($row as $key => $value) {
            if (in_array($key, explode(',', $this->getcolslist()))) {
                $sqlfield .= $key . ",";
                $sqlvalue .= "'" . $value . "',";
            }
        }
        return "REPLACE INTO `" . $tbname . "`(" . substr($sqlfield, 0, -1) . ") VALUES (" . substr($sqlvalue, 0, -1) . ")";

    }

    function sql_delete($tbname, $where)
    {
        $this->condition($where);
        //var_dump($where);
        return "DELETE FROM `" . $tbname . "` WHERE " . $where;
    }

    function rec_insert($row)
    {
        //var_dump($row);
        $tbname = $this->name;
        $sql = $this->sql_insert($tbname, $row);
        //echo $sql;exit;
        return $this->query($sql);
    }

    function rec_update($row, $where)
    {
        $tbname = $this->name;
        //var_dump($tbname);
        $sql = $this->sql_update($tbname, $row, $where);
        //echo $sql."<br>";exit;
        return $this->query($sql);
    }

    function rec_replace($row)
    {
        $tbname = $this->name;
        $sql = $this->sql_replace($tbname, $row);
        //echo $sql."\n";
        return $this->query($sql);
    }

    function rec_delete($where)
    {
        $tbname = $this->name;
        $sql = $this->sql_delete($tbname, $where);
        //echo $sql;exit;
        return $this->query($sql);
    }

    function rec_select($where = "", $limit = 0, $fields = "*", $order = '')
    {
        $tbname = $this->name;
        $sql = $this->sql_select($tbname, $where, $limit, $fields, $order);
        //echo $sql."<br>";
        $res = $this->rec_query($sql);
        //var_dump($res);
        return $res;
    }

    function rec_select_one($where, $fields = "*", $order = "id")
    {
        $tbname = $this->name;
        $sql = $this->sql_select($tbname, $where, 1, $fields, $order);
        //echo $sql."<br>";
        return $this->rec_query_one($sql);
    }

    function rec_query($sql)
    {
        return $this->db->rec_query($sql);
    }

    function rec_query_one($sql)
    {
        $res = $this->db->rec_query_one($sql);
        //var_dump($res);
        return $res;
    }

    function rec_count($where = "")
    {
		$this->condition($where);
        $tbname = $this->name;
        if (preg_match('/_category$/', $tbname))
            $sql = "SELECT count(catid) as rec_sum FROM `" . $tbname . "` " . ($where ? " WHERE " . $where : "");
        else
            $sql = "SELECT count(1) as rec_sum FROM `" . $tbname . "` " . ($where ? " WHERE " . $where : "");
        //echo $sql;//exit;
        $row = $this->rec_query_one($sql);
        return $row["rec_sum"];
    }

    function getrows($condition = '', $limit = 1, $order = '1 desc', $cols = '*')
    {
        $this->condition($condition);
        $this->record_count = $this->rec_count($condition);
        $res = $this->rec_select($condition, $limit, $cols, $order);
        //var_dump($res);
        return $res;
    }

    function getrows1($condition = '', $limit = 1, $order = '1 desc', $cols = '*')
    {
        $this->condition($condition);
        $this->record_count = $this->rec_count($condition);
        return $this->rec_select($condition, $limit, $cols, $order, '');
    }

    function getrow($condition, $order = '1 desc', $cols = '*')
    {
        $this->condition($condition);
        //var_dump($condition);
        return $this->rec_select_one($condition, $cols, $order);
    }

    function condition(&$condition)
    {
        if (isset($condition) && is_array($condition)) {
            $_condition = array();
            foreach ($condition as $key => $value) {
                //$value=str_replace("'","\'",$value);
                $key = htmlspecialchars($key, ENT_QUOTES);
                if (preg_match('/(if|select|ascii|from|sleep)/i', $value)) {
                    //echo $condition;
                    exit('sql inject');
                }
                if (preg_match('/(if|select|ascii|from|sleep)/i', $key)) {
                    //echo $condition;
                    exit('sql inject');
                }
                $_condition[] = "`$key`='$value'";
            }
            $condition = implode(' and ', $_condition);
        } else if (is_numeric($condition)) {
            $this->getFields();
            //var_dump($this->db);
            $condition = "`{$this->db->primary_key}`=$condition";
        } else if (true === $condition) {
            $condition = 'true';
        } else {
            //echo $condition." __ ";
            if (preg_match('/(if|select|ascii|from|sleep)/i', $condition)) {
                //echo $condition;
                exit('sql inject');
            }
        }

        if (get_class($this) == 'archive') {
            if (!front::get('deletestate')) {
                if ($condition)
                    $condition .= ' and (state IS NULL or state<>\'-1\') ';
                else
                    $condition = 'state IS NULL or state<>\'-1\' ';
            } else {
                if ($condition)
                    $condition .= ' and state=\'-1\' ';
                else
                    $condition = ' state=\'-1\' ';
            }
        }
    }

    function getFields()
    {
        $this->db->name = $this->name;
        //var_dump($this->db->name);
        $res = $this->db->getFields();
        //var_dump($res);
        $this->primary_key = $this->db->primary_key;
        return $res;
    }

    function getFiledsList()
    {
        $list = '';
        foreach ($this->getFields() as $field) $list .= $field['name'] . ' ';
        return $list;
    }

    function getcolslist()
    {
        $list = array();
        foreach ($this->getFields() as $field){
            $list[] = $field['name'];
        }
        //echo('===========<br>');
        //var_dump($list);
        //echo('--------------<br>');
        return implode(',', $list);
    }

    function getcols($act)
    {
        return '*';
    }

    function mycols()
    {
        $_cols = array_keys($this->getFields());
        $cols = '';
        foreach ($_cols as $col) {
            if (preg_match('/my_/', $col))
                $cols .= ',' . $col;
        }
        return $cols;
    }

    function get_form()
    {
    }

    final function getname()
    {
        return $this->name;
    }

    /*function rec_query_one1($sql)
    {
        $rs = mysql_query($sql);
        $row = mysql_fetch_array($rs);
        $this->free_result($rs);
        return $row;
    }*/

    function _rec_query1($sql)
    {
        $rs = mysql_query($sql);
        $rs_num = mysql_num_rows($rs);
        $rows = array();
        for ($i = 0; $i < $rs_num; $i++) {
            $rows[] = mysql_fetch_array($rs);
        }
        return $rows;
    }

    function _rec_select1($tbname, $where = "", $limit = 0, $fields = "*", $order = '')
    {
        $sql = $this->sql_select($tbname, $where, $limit, $fields, $orde);
        return $this->_rec_query1($sql);
    }

    function verison()
    {
        //return mysql_get_server_info($this->connection_id);
        return $this->db->verison();
    }
}