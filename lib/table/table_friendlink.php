<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class table_friendlink extends table_mode {
    function add_before(act $act=null) {
        front::$post['userid']=$act->view->user['userid'];
        front::$post['username']=$act->view->user['username'];
        front::$post['adddate']=date('Y-m-d H:i:s');
        front::$post['ip']=front::ip();
    }
}