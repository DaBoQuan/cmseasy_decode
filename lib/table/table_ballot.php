<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class table_ballot extends table_mode {
    function add_before(act $act=null) {
    }

    function save_before() {
        front::$post['viewgroupid'] = front::$post['viewgroupid'] ? implode(',',front::$post['viewgroupid']) : null;
        front::$post['votegroupid'] = front::$post['votegroupid'] ?implode(',',front::$post['votegroupid']) : null;
        front::$post['resgroupid'] =  front::$post['resgroupid'] ?implode(',',front::$post['resgroupid']) : null;
    }

    function view_before(&$data = null)
    {
        $data['viewgroupid'] = explode(',',$data['viewgroupid']);
        $data['votegroupid'] = explode(',',$data['votegroupid']);
        $data['resgroupid'] = explode(',',$data['resgroupid']);
    }
}