<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class webscan extends table {
    public $name='webscan';
    static $me;
    public static function getInstance() {
        if (!self::$me) {
            $class=new orders();
            self::$me=$class;
        }
        return self::$me;
    }
}