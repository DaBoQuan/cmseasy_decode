<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');
class website_admin extends admin {
    function init() {
    }
    function addwebsite_action() {
    	chkpw('website');
        if (front::post('submit')) {
            $var = front::$post;
            front::$post['path'] = str_replace('.php','',front::$post['path']);
            $path = ROOT.'/config/website/'.front::$post['path'].'.php';
            $contenttmp = file_get_contents(ROOT.'/config/config.example.php');
            if (is_array($var))
                foreach ($var as $key=>$value) {
                    $value=str_replace("'","\'",$value);
                    $contenttmp=preg_replace("%(\'$key\'=>)\'.*?\'(,\s*//)%i","$1'$value'$2",$contenttmp);
                }
            @file_put_contents($path,$contenttmp);
            @file_put_contents(ROOT.'/config/help_'.front::$post['path'].'.php',ROOT.'/config/help.php');
            //echo '<script type="text/javascript">alert("操作完成！")</script>';
            front::refresh(url('website/listwebsite',true));
        }
    }
    function editwebsite_action() {
    	chkpw('website');
        if (front::post('submit')) {
            $var = front::$post;
            front::$post['path'] = str_replace('.php','',front::$post['path']);
            $path = ROOT.'/config/website/'.front::$post['path'].'.php';
            $contenttmp = file_get_contents(ROOT.'/config/config.example.php');
            if (is_array($var)) {
                foreach ($var as $key => $value) {
                    $value = str_replace("'", "\'", $value);
                    $contenttmp = preg_replace("%(\'$key\'=>)\'.*?\'(,\s*//)%i", "$1'$value'$2", $contenttmp);
                }
            }
            //var_dump($path);
            @file_put_contents($path,$contenttmp);
            //echo '<script type="text/javascript">alert("操作完成！")</script>';
            front::refresh(url('website/listwebsite',true));
        }
        front::$get['id'] = str_replace('.php','',front::$get['id']);
        $path = ROOT.'/config/website/'.front::$get['id'].'.php';
        $datatmp = include $path;
        $this->view->data = $datatmp;
    }
    function deletewebsite_action() {
    	chkpw('website');
        front::$get['id'] = str_replace('.php','',front::$get['id']);
        $path = ROOT.'/config/website/'.front::$get['id'].'.php';
        @unlink($path);
        $path = ROOT.'/config/help_'.front::$get['id'].'.php';
        @unlink($path);
        echo '<script type="text/javascript">alert("操作完成！")</script>';
        front::refresh(url('website/listwebsite',true));
    }
    function listwebsite_action() {
    	chkpw('website');
        $path = ROOT.'/config/website';
        $dir = opendir($path);
        $website_num = 0;
        $website = array();
        while($file = readdir($dir)) {
            if(!($file == '..')) {
                if(!($file == '.')) {
                    if(!is_dir($path.'/'.$file)) {
                        $tmparr = include $path.'/'.$file;
                        $website_num++;
                        $tmparr['website']['id'] = $website_num;
                        $tmparr['website']['url'] = $tmparr['site_url'];
                        $args = array('username'=>$tmparr['site_username'],'password'=>md5($tmparr['site_password']));
                        $tmparr['website']['admindir'] =  $tmparr['site_admindir'];
                        $tmparr['website']['args'] = urlencode(base64_encode(xxtea_encrypt(serialize($args),$tmparr['cookie_password'])));
                        $tmparr['website']['path'] = str_replace('.php','',$file);
                        $tmparr['website']['hostname'] = $tmparr['database']['hostname'];
                        $tmparr['website']['user'] = $tmparr['database']['user'];
                        $tmparr['website']['password'] = $tmparr['database']['password'];
                        $website[] = $tmparr['website'];
                    }
                }
            }
        }
        $this->view->data = $website;
    }
    function checkmysql_action() {
        set_time_limit(0);
        $mysqlconn=@mysql_connect($_GET['host'],$_GET['user'],$_GET['pwd']);
        if($mysqlconn) {
            echo '<font color="green">连接数据库服务器成功！</font>';
        }else {
            echo '<font color="red">连接数据库服务器失败！</font>';
        }
        exit;
    }
    function checkftp_action() {
        set_time_limit(0);
        $ftp = new nobftp();
        $ftp->connect($_GET['ftpip'],$_GET['ftpuser'],$_GET['ftppwd']);
        $ftperror = $ftp->returnerror();
        if(!$ftperror) {
            echo '<font color="green">连接FTP服务器成功！</font>';
        }else {
            echo '<font color="red">'.$ftperror.'</font>';
        }
        exit;
    }
    function end() {
        $this->render('index.php');
    }
}