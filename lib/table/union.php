<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class union extends table {
    public static function getconfig($key) {
        $path = ROOT.'/config/union.php';
        $config = include $path;
        return $config[$key];
    }
    public static function pointadd($username,$num,$note) {
        $user = new user();
        $num = intval($num);
        $note = $note;
        $userarr = array();
        $userarr['username'] = $username;
        $user->rec_update(array('point'=>'[point+'.$num.']'),$userarr);
        $pay_exchange = new pay_exchange();
        $userarr['username'] = $username;
        $userarr['type'] = 'point';
        $userarr['value'] = $num;
        $userarr['note'] = $note;
        $userarr['addtime'] = time();
        $userarr['ip'] = front::ip();
        $pay_exchange->rec_insert($userarr);
    }
}