<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class guestbook extends table
{
    static $me;

    public static function getInstance()
    {
        if (!self::$me) {
            $class = new guestbook();
            self::$me = $class;
        }
        return self::$me;
    }

    function getcols($act = '')
    {
        return '*';
    }
}