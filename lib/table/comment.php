<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class comment extends table
{
    public static $_self;

    public $name = 'a_comment';

    public static function getIns(){
        if(!self::$_self){
            self::$_self = new comment();
        }
        return self::$_self;
    }

    function countcomment($aid)
    {
        $com = new comment();
        return $com->rec_count('aid=' . front::get('aid'));
    }

    function getcols($act = '')
    {
        return '*';
    }

}
