<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class user_admin extends admin {
    function init() {
    }
    function index_action() {
        $this->render();
    }
}