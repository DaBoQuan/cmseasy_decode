<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class page_act extends act {
    function init() {
        $this->render('page/'.front::$act.'.html');
        exit;
    }
}