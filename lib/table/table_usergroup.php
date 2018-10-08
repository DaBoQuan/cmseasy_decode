<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class table_usergroup extends table_mode {
	
	function save_before() {
		parent::save_before();
		front::$post['powerlist'] = serialize(front::$post['powerlist']);
		if(front::$post['powerlist'] == 'N;') front::$post['powerlist'] = '';
		if(front::$post['fpwlist']){
			front::$post['fpwlist'] = implode(',',front::$post['fpwlist']);
		}else{
			front::$post['fpwlist'] = '';
		}
	}
	
	function view_before(&$data=NULL) {
		if($data['powerlist'] != 'all' && $data['powerlist'] != ''){
			$data['powerlist'] = unserialize($data['powerlist']);
		}
		if($data['fpwlist']) $data['fpwlist'] = explode(',',$data['fpwlist']);
	}
}