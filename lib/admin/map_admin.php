<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class map_admin extends admin {
    function init() {
    }
    function index_action() {
        session::del('mod');
    }
    function end() {
        $this->render('map/index.php');
    }
}