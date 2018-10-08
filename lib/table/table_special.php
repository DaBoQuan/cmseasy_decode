<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class table_special extends table_mode {
	function save_before() {
		front::$post['description'] = stripcslashes(htmlspecialchars_decode(front::$post['description']));
		front::$post['adddate'] = time();
		front::$post['listorder'] = 0;
		front::$post['disabled'] = 0;
	}
}