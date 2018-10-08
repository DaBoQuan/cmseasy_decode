<?php 

class cache_act extends act
{
    function init()
    {
        $this->check_admin();
    }

    function ctsitemap_action()
    {
        $category = category::getInstance();
        $category->sitemap();
        alertinfo('生成网站地图成功!', front::$from);
        //front::flash(lang('successful_generation'),lang('sitemap'),lang('！'));
        //front::redirect(front::$from);
        /*echo "<script>alert('生成网站地图成功!');window.close();</script>";
        exit;*/
    }

    function check_admin()
    {
        if (cookie::get('login_username') && cookie::get('login_password')) {
            $user = new user();
            $user = $user->getrow(array('username' => cookie::get('login_username')));
            $roles = session::get('roles');
            if ($roles && is_array($user) && cookie::get('login_password') == front::cookie_encode($user['password'])) {
                $this->view->user = $user;
                front::$user = $user;
            } else {
                $user = null;
            }
        }

        if (!isset($user) || !is_array($user)) {
            front::redirect(url::create('admin/login'));
        }
    }

    function index_action()
    {
        $case = 'archive';
        $act = 'list';
        $_GET = array('case' => $case, 'act' => $act);
    }
}