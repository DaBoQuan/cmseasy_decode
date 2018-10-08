<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class tdatabase extends table
{
    static $instance;

    public static function getInstance()
    {
        if (self::$instance == null) {
            $class = get_class();
            self::$instance = new $class();
        }
        return self::$instance;
    }

    function getTables()
    {
        //var_dump($this);
        $config = config::get('database');
        $prefix = $config['prefix'];

            $rows = $this->rec_query("show table status from `" . $config['database'] . "`");
            $tables = array();
            foreach($rows as $row){
                if (!preg_match("/^$prefix/", $row['Name']))
                    continue;
                $table = array();
                $table['name'] = $row['Name'];
                $table['count'] = $row['Rows'];
                $table['size'] = $row['Data_length'];
                $tables[] = $table;
            }
            return $tables;
        //var_dump($rows);exit;

        /*while ($row = mysqli_fetch_row($rows)) {
            $prefix = config::get('database', 'prefix');
            if (!preg_match("/^$prefix/", $row[0]))
                continue;
            $table = array();
            $table['name'] = $row[0];
            $table['count'] = $row[4];
            $table['size'] = $row[6];
            $tables[] = $table;
        }
        return $tables;*/
    }

    function autoBakTablesBags()
    {
        set_time_limit(0);
        $tables = $this->getTables();
        $tables = array_col_values($tables, 'name');
        $bagsize = 1;
        $file = date('Y-m-d-H-i-') . substr(base64_encode(md5(time() . rand(0, 1000000))), 0, 6);
        $_path = ROOT . "/data/$file/sql-" . $file;
        tool::mkdir(dirname($_path . '.ext'));
        $tabledump = '';
        if (is_array($tables))
            foreach ($tables as $table) {
                $tabledump .= "DROP TABLE IF EXISTS `$table`;-- \n";
                $create = mysqli_fetch_row($this->query("SHOW CREATE TABLE `$table` "));
                $create_str = $create[1];
                if (front::post('mysql4'))
                    $create_str = preg_replace('/ENGINE=.+?$/', '', $create_str);
                $tabledump .= $create_str . ";-- \n";
            }
        $bag = 1;
        if (is_array($tables))
            foreach ($tables as $table) {
                $rows = $this->query("SELECT * FROM `$table` ");
                $numfields = mysqli_num_fields($rows);
                $numrows = mysqli_num_rows($rows);
                while ($row = mysqli_fetch_row($rows)) {
                    $comma = "";
                    $tabledump .= "INSERT INTO `$table` VALUES(";
                    for ($i = 0; $i < $numfields; $i++) {
                        //var_dump($row[$i]);
                        $tabledump .= $comma . "'" . addslashes($row[$i]) . "'";
                        //var_dump(addslashes($row[$i]));
                        $comma = ",";
                    }
                    $tabledump .= ");-- \n";
                    if (strlen($tabledump) > $bagsize * 1024 * 1024) {
                        file_put_contents($_path . '-' . $bag . '.sql', $tabledump);
                        $bag++;
                        $tabledump = '';
                    }
                }
                $tabledump .= "\n";
            }
        if ($tabledump)
            file_put_contents($_path . '-' . $bag . '.sql', $tabledump);
    }

    function restoreTables($file)
    {
        set_time_limit(0);
        $database = new tdatabase();
        $sqlquery = file_get_contents($file);
        if (!$sqlquery)
            return;
        $sqlquery = str_replace("\r", "", $sqlquery);
        $sqls = preg_split ("/;--[ \t]{0,}\n/is", $sqlquery);
        //var_dump($sqls);exit;
        $nerrCode = "";
        $i = 0;
        foreach ($sqls as $q) {
            $q = trim($q);
            if ($q == "") {
                continue;
            }
            if ($database->query($q))
                $i++;
            else
                $nerrCode .= "执行： <font color='blue'>$q</font> 出错!</font><br>";
        }
    }

    function bakTablesBags()
    {
        set_time_limit(0);
        $tables = front::post('select');
        $bagsize = front::post('bagsize');
        if ($bagsize < 1)
            $bagsize = 1;
        $file = date('Y-m-d-H-i-') . substr(base64_encode(md5(time() . rand(0, 10000000))), 0, 6);
        $_path = ROOT . "/data/$file/sql-" . $file;
        tool::mkdir(dirname($_path . '.ext'));
        $tabledump = '';
        if (is_array($tables))
            foreach ($tables as $table) {
                $tabledump .= "DROP TABLE IF EXISTS `$table`;-- \n";
                $create = mysqli_fetch_row($this->query("SHOW CREATE TABLE `$table` "));
                $create_str = $create[1];
                if (front::post('mysql4'))
                    $create_str = preg_replace('/ENGINE=.+?$/', '', $create_str);
                $tabledump .= $create_str . ";-- \n";
            }
        $bag = 1;
        if (is_array($tables)) {
            foreach ($tables as $table) {
                $rows = $this->query("SELECT * FROM `$table` ");
                $numfields = mysqli_num_fields($rows);
                $numrows = mysqli_num_rows($rows);
                while ($row = mysqli_fetch_row($rows)) {
                    //var_dump($row);
                    $comma = "";
                    $tabledump .= "INSERT INTO `$table` VALUES(";
                    for ($i = 0; $i < $numfields; $i++) {
                        $tabledump .= $comma . "'" . addslashes($row[$i]) . "'";
                        $comma = ",";
                    }
                    $tabledump .= ");-- \n";
                    if (strlen($tabledump) > $bagsize * 1024 * 1024) {
                        file_put_contents($_path . '-' . $bag . '.sql', $tabledump);
                        $bag++;
                        $tabledump = '';
                    }
                }
                $tabledump .= "\n";
            }
        }
        //var_dump($tabledump);
        if ($tabledump)
            file_put_contents($_path . '-' . $bag . '.sql',$tabledump);
    }
}
