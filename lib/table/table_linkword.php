<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class table_linkword extends table_mode {
    function save_before() {
        $linkurl=trim(front::$post['linkurl']);
        if(preg_match('@^http://$@',$linkurl)) {
            front::$post['linkurl']='';
        }
    }
}