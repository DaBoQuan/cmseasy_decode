<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class vote extends table
{
    public $name = 'a_vote';
    static $me;

    function url($id)
    {
        return ballot::url($id);
    }

    public static function getInstance()
    {
        if (!self::$me) {
            $class = new vote();
            self::$me = $class;
        }
        return self::$me;
    }

    static function aid($aid)
    {
        static $vote;
        $aid = intval($aid);
        if (!isset($vote[$aid])) {
            $vote = array();
            $vote[$aid] = self::getInstance()->getrow('aid=' . $aid);
        }
        return $vote[$aid];
    }

    static function get($aid, $vid)
    {
        $_vote = self::aid($aid);
        $_vote = @$_vote['votes'];
        if (!$_vote)
            $_votes = array();
        else
            $_votes = unserialize($_vote);
        if (isset($_votes[$vid]))
            return $_votes[$vid];
        else
            return 0;
    }

    static function title($aid, $vid)
    {
        $_vote = self::aid($aid);
        $_vote = @$_vote['titles'];
        if (!$_vote)
            $_votes = array();
        else
            $_votes = unserialize($_vote);
        if (isset($_votes[$vid]))
            return $_votes[$vid];
        else
            return '';
    }

    static function img($aid, $vid)
    {
        $_vote = self::aid($aid);
        //var_dump($_vote);
        $_vote = @$_vote['images'];
        if (!$_vote)
            $_votes = array();
        else
            $_votes = unserialize($_vote);
        if (isset($_votes[$vid]))
            return $_votes[$vid];
        else
            return '';
    }

    static function voted($aid, $username)
    {
        $_vote = self::aid($aid);
        if ($username) {
            if (preg_match("/$username,/i",$_vote['users']))
                return true;
            else
                return false;
        } else {
            return false;
        }
    }
}