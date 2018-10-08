<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');
//error_reporting(0);
class safe_act extends act {

    function getval_action() {
    	$ptime = front::$post['ptime'];
    	$webscan_model = new webscan();
    	$res = $webscan_model->getrow(array('var'=>'key'));
    	if(!empty($res) && !empty($res['value'])){
    		echo md5("webscan360:".$res['value'].":".$ptime);
    	}
    	
    }
}