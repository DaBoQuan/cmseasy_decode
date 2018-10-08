<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class ballot extends table {
    function getcols($act) {
        switch($act) {
            case  'list':
                return '*';
            case 'modify':
                return '*';
            case 'manage':
                return '*';
            default: return '1';
        }
    }
    function get_form() {
        $checkbox = front::$act == 'add' ? 'checkbox2' : 'checkbox';
        return array(
                'type'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(array('radio'=>'单选','checkbox'=>'多选')),
                        'default'=>get('catid'),
                        'regex'=>'/\d+/',
                        'filter'=>'is_numeric',
                ),
            'viewgroupid'=>array(
                'selecttype'=>$checkbox,
                'select'=>form::arraytoselect($this->getgourplist()),
            ),
            'votegroupid'=>array(
                'selecttype'=>$checkbox,
                'select'=>form::arraytoselect($this->getgourplist()),
            ),
            'resgroupid'=>array(
                'selecttype'=>$checkbox,
                'select'=>form::arraytoselect($this->getgourplist()),
            ),
        );
    }
    static function url($id) {
        return url::create('vote/show/id/'.$id);
    }

    function getgourplist(){
        $usergroup = usergroup::getInstance();
        $rows = $usergroup->getrows(null,0,'groupid desc');
        foreach ($rows as $row){
            $arr[$row['groupid']] = $row['name'];
        }
        return $arr;
    }
}