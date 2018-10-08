<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class event extends table
{
    function getcols($act = '')
    {
        return '*';
    }

    static function loginfalsemaxtimes()
    {
        $ip = front::ip();
        $ftime = time() - 3600;
        $event = new event;
        return $event->rec_count("event='loginfalse' and ip='$ip' and addtime>$ftime ") > 5;
    }

    static function log($action, $remark)
    {
        $user = new user();
        $username = cookie::get('login_username');
        $row = $user->getrow(array('username' => $username));
        $uid = $row['userid'];
        $action = lang($action);
        $remark = lang($remark);
        $ip = front::ip();
        $addtime = time();
        $sql = "INSERT INTO `" . config::get('database', 'prefix') . "event`  VALUES (null,'$uid','$username','$ip','$addtime','$action','$remark')";
        $user->query($sql);
    }
}