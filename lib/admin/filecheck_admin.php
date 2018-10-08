<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

//WWW
class filecheck_admin extends admin {
    function filecheck_action() {
        session::set('actname','文件防护');
        $this->render('index.php');
    }
}
