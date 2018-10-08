<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class friendlink extends table
{
    function getcols($act = '')
    {
        switch ($act) {
            case 'manage':
                return 'id,name,logo,adddate,typeid,username,hits,name,listorder' . $this->mycols();
            case 'modify':
                return 'id,name,url,logo,introduce,linktype,listorder,typeid,state,username' . $this->mycols();
            case 'user_modify':
                return 'id,name' . $this->mycols();
            case 'user_manage':
                return 'id,adddate,username,name';
        }
    }

    function get_form()
    {
        return array(
            'linktype' => array(
                'selecttype' => 'radio',
                'select' => form::arraytoselect(array(1 => '文字链接', 2 => 'Logo链接')),
            ),
            'typeid' => array(
                'selecttype' => 'select',
                'select' => form::arraytoselect($this->gettypes()),
            ),
            'state' => array(
                'selecttype' => 'radio',
                'select' => form::arraytoselect(array(1 => '审核', 0 => '禁止')),
            ),
        );
    }

    function gettypes()
    {
        $sets = settings::getInstance()->getrow(array('tag' => 'table-friendlink'));
        if (!is_array($sets))
            return;
        $data = unserialize($sets['value']);
        preg_match_all('%\(([\d\w\/\.-]+)\)(\S+)%m', $data['types'], $result, PREG_SET_ORDER);
        $data = array();
        foreach ($result as $res) $data[$res[1]] = $res[2];
        return $data;
    }
}