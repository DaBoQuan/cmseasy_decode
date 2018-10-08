<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class logistics extends table {
    public $name='p_shipping';
    static $me;
    public static function getInstance() {
        if (!self::$me) {
            $class=new logistics();
            self::$me=$class;
        }
        return self::$me;
    }
}