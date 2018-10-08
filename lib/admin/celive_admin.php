<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');
class celive_admin extends admin {
    public $archive;
    function init() {
        header('Cache-control: private, must-revalidate');
        front::$admin=false;
        front::$html=true;
    }
    function system_action() {
    }
    function chat_action() {
    }
    function user_action() {
    }
    function admin_action() {
        header('Location: celive/admin/index.php');
    }
    function end() {
        front::$html=false;
        front::$admin=true;
        $this->render('index.php');
    }
}