<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class rank extends table
{
    public $name = 'a_rank';
    static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new rank();
        }
        return self::$instance;
    }

    static function arcget($aid, $gid, $act = 'view')
    {
        $aid = intval($aid); $_rank = self::getInstance()->getrow('aid=' . $aid);
        $_rank = @$_rank['ranks'];
        if (!$_rank)
            $_ranks = array();
        else
            $_ranks = unserialize($_rank);
        if (isset($_ranks[$gid][$act]) && $_ranks[$gid][$act] == -1)
            return false;
        else
            return true;
    }

    static function catget($catid, $gid, $act = 'view')
    {
        $catid = intval($catid);
        $_rank = self::getInstance()->getrow('catid=' . $catid);
        $_rank = @$_rank['ranks'];
        if (!$_rank)
            $_ranks = array();
        else
            $_ranks = unserialize($_rank);
        if (isset($_ranks[$gid][$act]) && $_ranks[$gid][$act] == -1)
            return false;
        else
            return true;
    }
}