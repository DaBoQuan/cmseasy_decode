<?php 

abstract class act
{
    public $cache_path;

    function __construct()
    {
        $this->filter();

        $this->view = new view($this);
        $this->base_url = config::get('base_url');
        front::$view = $this->view;
        load_lang('system.php');

        if (!front::$admin) {
            $site = config::get('stop_site');
            if ($site == '2') {
                $this->render('system/close.html');
                exit;
            } elseif ($site == '3') {
                $this->render('system/suspend.html');
                exit;
            }
        }

        if (front::$case != 'install') {
            if (!self::installed()) {
                echo '<script>window.location.href="index.php?case=install&admin_dir=' . config::get('admin_dir') . '&site=default";</script>';
            }

            //new stsession(new sessionox());//初始化DB 存储SESSION
            register_shutdown_function('session_write_close');

            $user = new user();
            $row = $user->getrow('userid>0');
            if (!is_array($row)) {
                exit('数据库连接失败！请检查配置文件！<!--a href="index.php?case=install&admin_dir=admin&site=default">重新安装</a-->');
            }
            //var_dump($_COOKIE);

            new setting();

            $this->view->user = null;
            $this->view->userid = 0;
            $this->view->username = '游客';
            $this->view->usergroupid = 1000;
            if (cookie::get('login_username') && cookie::get('login_password')) {
                //$user=new user();
                $user = $user->getrow(array('username' => cookie::get('login_username')));
                if (is_array($user) && cookie::get('login_password') == front::cookie_encode($user['password'])) {
                    $this->view->user = $user;
                    $this->cur_user = $user;
                    $this->view->userid = $user['userid'];
                    $this->view->username = $user['username'];
                    $this->view->usergroupid = $user['groupid'];
                    front::$user = $user;
                }
            }
        }


    }

    static function installed()
    {
        if (file_exists(ROOT . '/install/locked')) return true;
        else return false;
    }

    function init()
    {

    }

    function end()
    {
    }

    function check_pw()
    {

        include(ROOT . '/lib/admin/template_.php');

        $md5_file_check = md5_file(ROOT . '/lib/inc/view.php');
        if (0 && $md5_file_check != $check_code['view_phpcheck']) {  //WWW
            exit(phpox_decode('act'));
        }
    }

    function fetch($tpl = null)
    {
        return $this->view->fetch($tpl);
    }

    function render($tpl = null)
    {

        $content = $this->view->fetch($tpl);
        if(!in_array(get_class($this),array('ballot_act'))) {
            $res = preg_match('/Powered by <a href="https:\/\/www.cmseasy.cn" title="CmsEasy企业网站系统" target="_blank">CmsEasy<\/a>/is', $content);
            //$content=$this->view->show($content,true);
            if (!$res && session::get('ver') != 'corp') {
                $content .= 'Powered by <a href="https://www.cmseasy.cn" title="CmsEasy企业网站系统" target="_blank">CmsEasy</a>';
            }
        }
        echo $content;
        if ($this->cache_path) {
            $path = $this->cache_path;
            tool::mkdir(dirname($path));
            file_put_contents($path, $content);
        }
    }

    function filter()
    {
        if (front::get('page')) front::check_type(front::get('page'));
    }
}
