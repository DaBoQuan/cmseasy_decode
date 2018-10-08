<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class announcement extends table
{
    function getcols($act = '')
    {
        switch ($act) {
            case 'list':
                return 'id,title,adddate,content' . $this->mycols();
            case 'modify':
                return 'id,title,content' . $this->mycols();
            case 'manage':
                return 'id,title,adddate,content';
            default:
                return '1';
        }
    }

    function get_form() {
        return array(
            'content'=>array(
                'type'=>'mediumtext',
            ),
        );
    }

    static function url($id)
    {
        return url::create('announ/show/id/' . $id);
    }
}