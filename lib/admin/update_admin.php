<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class update_admin extends admin
{
    function init()
    {
    }

    function index_action()
    {
        $curl = new curl();
        $curl->set('file', 'upserv/frontend/web/index.php?r=version/check&code=' . _VERCODE . '&ver=' . session::get('ver'));
        $str = $curl->curl_post(null, 10);
        //var_dump($str);
        $arr = json_decode($str, 1);
        if ($arr['err'] == 0) {
            $this->view->isnew = 1;
        }
        //var_dump($arr);exit;
        $this->view->row = $arr;
        $user = new user();
        $this->view->dbversion = $user->verison();
    }

    function getfile_action()
    {
        $curl = new curl();
        $curl->set('file', 'upserv/frontend/web/index.php?r=version/getfile&code=' . front::get('code') . '&domain=' . front::$domain . '&oldver=' . _VERCODE . '&newver=' . front::get('code') . '&cmsver=' . session::get('ver'));
        $str = $curl->curl_post(null, 10);
        //session::set('downurl', $str);
        //var_dump('upserv/frontend/web/index.php?r=version/getfile&code=' . front::get('code').'&domain='.front::$domain.'&oldver='._VERCODE.'&newver='.front::get('code').'&cmsver='.session::get('ver'));exit;
        echo $str;
        exit;
    }

    function get_file($url, $folder = "./")
    {
        set_time_limit(24 * 60 * 60); // 设置超时时间
        $destination_folder = $folder . '/'; // 文件下载保存目录，默认为当前文件目录
        if (!is_dir($destination_folder)) { // 判断目录是否存在
            $this->mkdirs($destination_folder); // 如果没有就建立目录
        }
        $newfname = $destination_folder . 'patch.zip'; // 取得文件的名称
        //var_dump($url);exit;
        $file = fopen($url, "rb"); // 远程下载文件，二进制模式
        if ($file) { // 如果下载成功
            $newf = fopen($newfname, "wb"); // 远在文件文件
            if ($newf) // 如果文件保存成功
                while (!feof($file)) { // 判断附件写入是否完整
                    fwrite($newf, fread($file, 1024), 1024); // 没有写完就继续
                    //usleep(2000);
                    //clearstatcache();
                }
        } else {
            return false;
        }
        if ($file) {
            fclose($file); // 关闭远程文件
        } else {
            return false;
        }
        if ($newf) {
            fclose($newf); // 关闭本地文件
        } else {
            return false;
        }
        return true;
    }

    function mkdirs($path, $mode = "0777")
    {
        if (!is_dir($path)) { // 判断目录是否存在
            $this->mkdirs(dirname($path), $mode); // 循环建立目录
            mkdir($path, $mode); // 建立目录
        }
        return true;

    }


    function downfile_action()
    {
        $url = front::get('url');

        $res = $this->get_file($url, 'cache');
        if (!$res) {
            $res = array(
                'err' => 1,
                'data' => '更新包下载失败！',
            );
        } else {
            @unlink('upgrade/config.php');
            @unlink('upgrade/config.tmp.php');
            @unlink('upgrade/upgrade.sql');
            $archive = new PclZip('cache/patch.zip');
            $archive->extract(PCLZIP_OPT_PATH, ROOT, PCLZIP_OPT_REPLACE_NEWER);


            if(file_exists('upgrade/config.php')) {
                $configtmp = include(ROOT.'/config/config.php'); //获取原config的配置数组
                //var_dump($configtmp);exit;
                $configtmpfile = file_get_contents('config/config.php'); //获取原config的文件内容
                $newconfig = file_get_contents('upgrade/config.php'); //获取新config数组结构内容
                file_put_contents('upgrade/config.tmp.php', $configtmpfile); //写入原config的文件内容

                file_put_contents('config/config.php', $newconfig); //把原config文件结构更新为新的config文件结构

                config::modify($configtmp);
                config::modify(array('user' => $configtmp['database']['user']));
                config::modify(array('hostname' => $configtmp['database']['hostname']));
                config::modify(array('database' => $configtmp['database']['database']));
                config::modify(array('password' => $configtmp['database']['password']));
                config::modify(array('prefix' => $configtmp['database']['prefix']));
            }

            if(file_exists('upgrade/upgrade.sql')) {
                $sqlquery = file_get_contents('upgrade/upgrade.sql');
                $sqlquery = str_replace('`cmseasy_', '`' . config::get('database', 'prefix'), $sqlquery);

                $mysql = new user();
                $sqlquery = str_replace("\r", "", $sqlquery);
                $sqls = preg_split("/;[ \t]{0,}\n/is", $sqlquery);

                foreach ($sqls as $q) {
                    $q = trim($q);
                    if ($q == "") {
                        continue;
                    }
                    $mysql->query($q);
                }
            }

            if(file_exists('upgrade/command.php')){
                include ROOT . '/upgrade/command.php';
            }
            $res = array(
                'err' => 0,
                'data' => '升级成功！',
            );
        }

        echo json_encode($res);
        exit;
    }

    function getsize_action()
    {
        echo filesize('cache/patch.zip');
        exit;
    }

    function end()
    {
        $this->render('index.php');
    }
}