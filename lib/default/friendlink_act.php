<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class friendlink_act extends act {
    function click_action() {
        $friendlink=new friendlink();
        $limit = '';
        $friendlink->rec_update(array('hits'=>'[hits+1]'),front::check_type(front::get('id')));
        $where=" id=".front::check_type(front::get('id'))." ";
        $friendlinks=$friendlink->getrows($where,$limit,'listorder asc,id asc');
        $url=$friendlinks[0][url];
        header("location: $url");
    }
}