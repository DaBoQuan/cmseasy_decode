<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class weixinmenu  extends table {

    public $name='weixinmenu';

    function getsubmenu($pid){
        $where = array('pid'=>$pid);
        $ordre='sort=0,`sort` ASC';
        return $this->getrows($where,'',$ordre,'*');
    }

    static function getTypeName($typeid){
        switch ($typeid){
            case 1:
                return '菜单';
            case 2:
                return '打开网址';
            case 3:
                return '文字回复';
            case 4:
                return '图文回复';
            case 5:
                return '网站内容推送';
            default:
                return '';
        }
    }
} 