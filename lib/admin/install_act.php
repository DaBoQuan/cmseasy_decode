<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');

class install_act extends act
{

    function init()
    {
        header('Cache-control: private, must-revalidate');
        if (self::installed())
            exit('系统已安装！若要再安装，请删除文件 /install/locked ! ');
        //set_time_limit(0);
    }

    function index_action()
    {

        if (front::get('step') == 2 && isset(front::$post['dosubmit'])) {
            $this->view->mysqli = extension_loaded('mysqli');
            $this->view->mysql_pass = false;
            front::$post['prefix'] = strtolower(front::$post['prefix']);
            config::modify(array('type' => 'mysqli'));
            $connect = @mysqli_connect(front::post('hostname'), front::post('user'), front::post('password'));
            if (front::post('createdb') && !@mysqli_select_db($connect, front::post('database'))) {
                @mysqli_query($connect, "CREATE DATABASE " . front::post('database'));
                @mysqli_query($connect, "ALTER DATABASE `$_POST[database]` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
            }
            if (!mysqli_connect_errno()) {
                $db = @mysqli_select_db($connect, front::post('database'));
                if ($db) {
                    $this->view->mysql_pass = true;
					session::set('testdata',front::$post['testdata']);
                    config::modify(front::$post);
                    front::redirect(url('install/index/step/3',true));
                }else{
                    $this->view->dberror = true;
                }
            }else{
                $this->view->connerror = '数据库连接失败'.mysqli_connect_errno().':'.mysqli_connect_error();
            }

        }
        if (front::get('step') == 3 && isset(front::$post['dosubmit'])) {
            config::modify(array('cookie_password' => sha1(get_hash())));
            config::modify(array('install_admin' => front::post('admin_username')));
            if (!front::post('admin_password') || !front::post('admin_username') || front::post('admin_password') <> front::post('admin_password2')) {
                $this->view->adminerror = true;
            }else {
                 $this->instalsqltype = session::get('testdata');
                $this->smodarr = front::post('smod');
                $this->prepare();
                return;
            }
        }
        $this->render();
    }

    private function prepare()
    {
        set_time_limit(0);
        if ($this->instalsqltype) {
            $sqlquery = file_get_contents(ROOT . '/install/data/install_testdb.sql');
        } else {
            $sqlquery = file_get_contents(ROOT . '/install/data/install.sql');
        }
        if (!$sqlquery) {
            exit('数据库文件不存在！');
        }
        $sqlquery = str_replace('cmseasy_', config::get('database', 'prefix'), $sqlquery);
        $sqlquery = str_replace('\'admin\'', '\'' . front::post('admin_username') . '\'', $sqlquery);
        $sqlquery = str_replace('\'21232f297a57a5a743894a0e4a801fc3\'', '\'' . md5(front::post('admin_password')) . '\'', $sqlquery);

        file_put_contents(ROOT . '/install/install.data', $sqlquery);

        front::redirect(url::create('install/view'));
    }

    function database_action()
    {
        set_time_limit(0);
        $data_file = ROOT . '/install/install.data';


        if (file_exists($data_file) == false)
            exit('找不到数据文件。');

        $sqlquery = file_get_contents($data_file);

        $mysql = new user();
        $sqlquery = str_replace("\r", "", $sqlquery);
        $sqls = preg_split("/;[ \t]{0,}\n/", $sqlquery);
        $nerrCode = "";

        $sqls2 = array();
        foreach ($sqls as $i => $q) {
            $q = trim($q);
            if ($q != '') {
                $sqls2[] = $q;
            }
        }

        echo '<script type="text/javascript">setInterval(function(){window.scrollTo(0,document.body.scrollHeight);},300);</script>';
        echo '<style>*{line-height:180%;font-size:12px;color:#888;}</style>';
        foreach ($sqls2 as $i => $q) {

            echo str_pad(' ', 1024, ' ');

            if (preg_match('/CREATE TABLE (.*?) \(/i', $q, $match) > 0) {

                echo '正在安装数据表	' . $match[1] . '...<br>';
            }

            if (!$mysql->query($q)) {
                $nerrCode .= "执行： <font color='blue'>$q</font> 出错!</font><br>";
            }
        }

        @unlink($data_file);

        echo '数据表安装完成 ！';
        echo '<script type="text/javascript">setTimeout(function(){window.top.location="' . url::create('install/success') . '";},1000);</script>';
    }

    function view_action()
    {
        $this->render();
    }

    function success_action()
    {
        $this->render();
        file_put_contents(ROOT . '/install/locked', 'install-locked !');
        @unlink(ROOT . '/install/index.php');
    }


}