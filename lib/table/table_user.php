<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class table_user extends table_mode
{
    function save_before()
    {
        if (front::post('passwordnew')) front::$post['password'] = md5(trim(front::post('passwordnew')));
    }

    function delete_before($id = '')
    {
        $user = new user();
        $row = $user->getrow(front::get('id'));
        if ($row['username'] == config::get('install_admin')) {
            front::flash("不能删除安装管理员！");
            front::redirect(front::$from);
        }
        if (front::get('id') == front::$user['userid']) {
            front::flash("不能删除当前用户！");
            front::redirect(front::$from);
        }
        if (is_array(front::post('select')) && in_array(front::$user['userid'], front::post('select'))) {
            front::flash("不能删除当前用户！");
            front::redirect(front::$from);
        }
    }

    function mail_before()
    {
        $user = new user();
        $user_arr = front::post('select');
        if (is_array($user_arr)) {
            $echo = '';
            foreach ($user_arr as $id) {
                $row = $user->getrow($id);
                $echo .= $row['e_mail'] . ',';
            }
            echo substr($echo, 0, -1);
        } else {
            $row = $user->getrow(front::get('id'));
            echo $row['e_mail'];
        }
    }

    function sms_before()
    {
        $user = new user();
        $user_arr = front::post('select');
        if (is_array($user_arr)) {
            $echo = '';
            foreach ($user_arr as $id) {
                $row = $user->getrow($id);
                if($row['tel'])
                    $echo .= $row['tel'] . ',';
            }
            echo substr($echo, 0, -1);
        } elseif($user_arr) {
            $row = $user->getrow(front::get('id'));
            if($row['tel'])
                echo $row['tel'];
        }
    }
}