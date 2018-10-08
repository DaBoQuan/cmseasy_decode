<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class table_form  extends table_mode {
    function add_before(act $act=null) {
        front::$post['userid']=$act->view->user['userid'];
        front::$post['username']=$act->view->user['username'];
        front::$post['checked']=1;
        front::$post['adddate']=date('Y-m-d H:i:s');
        front::$post['ip']=front::ip();

        //自定义字段允许HTML
        if(is_array(front::$post) && !empty(front::$post)){
            foreach(front::$post as $k => $v){
                if(preg_match('/^my_/is',$k)){
                    front::$post[$k] = htmlspecialchars_decode(front::$post[$k]);
                }
            }
        }
    }
}