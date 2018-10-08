<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');
class index_act extends act {
    function index_action() {
    	$this->check_pw();
    }
    function end() {
        if (front::$debug)
            $this->render('style/index.html');
        else
            $this->render();
    }
}