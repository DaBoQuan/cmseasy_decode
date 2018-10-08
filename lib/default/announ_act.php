<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class announ_act extends act {
    function init() {
    }
    function show_action() {
        front::check_type(front::get('id'));
        $announcement=new announcement();
        $this->view->announ=$announcement->getrow(front::get('id'));
    }
    function end() {
        if(front::$debug)
            $this->render('style/index.html');
        else
            $this->render();
    }
}