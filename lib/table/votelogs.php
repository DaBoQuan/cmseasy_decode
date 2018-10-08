<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class votelogs extends table
{
    public $name = 'vote_logs';
    static $me;

    public static function getInstance()
    {
        if (!self::$me) {
            $class = new votelogs();
            self::$me = $class;
        }
        return self::$me;
    }

    public function save($data){
        $data['addtime'] = time();
        $data['ip'] = front::ip();
        $this->rec_insert($data);
    }
}