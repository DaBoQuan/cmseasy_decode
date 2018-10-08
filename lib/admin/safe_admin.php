<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');
//error_reporting(0);
class safe_admin extends admin {
	
    function init() {
    }
    function webshell_action() {
		chkpw('func_data_safe');
		require_once ROOT.'/webscan360/webscan360.class.php';
		$webscan_model = new webscan360();
	    $url = $webscan_model->getWebshellUrl ();
		$this->view->iframe_url = $url;

    	
    }
    function protect_action() {
		chkpw('func_data_safe');
		require_once ROOT.'/webscan360/webscan360.class.php';
		$webscan_model = new webscan360();
	    $url = $webscan_model->getProtectUrl();
		$this->view->iframe_url = $url;    	
    }
	function end() {
        $this->render('index.php');
    }

}