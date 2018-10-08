<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class user extends table
{
    static $me;

    public static function getInstance()
    {
        if (!self::$me) {
            $class = new user();
            self::$me = $class;
        }
        return self::$me;
    }

    function getcols($act)
    {
        switch ($act) {
            case  'list':
                return 'userid,username,nickname,groupid' . $this->mycols();
            case 'modify':
                return 'userid,password,username,nickname,groupid,qq,e_mail,tel,address,checked' . $this->mycols();
            case 'manage':
                return 'userid,username,nickname,groupid,checked,isblock';
            default:
                return '1';
        }
    }

    function get_form()
    {
        return array(
            'groupid' => array(
                'selecttype' => 'select',
                'select' => form::arraytoselect(usergroup::option()),
            ),
            'checked' => array(
                'selecttype' => 'radio',
                'select' => form::arraytoselect(form::yesornotoarray('审核')),
            ),
        );
    }

    function get_form_field()
    {
        return array(
            'groupid' => array(
                'selecttype' => 'select',
                'select' => form::arraytoselect(usergroup::option()),
            ),
            'checked' => array(
                'selecttype' => 'radio',
                'select' => form::arraytoselect(form::yesornotoarray('审核')),
            ),
        );
    }

    function option()
    {
        $sp = new user();
        $sps = $sp->getrows('groupid!=101 and groupid!=1000', 500);
        $options = array(0 => '请选择...');
        foreach ($sps as $sp) {
            $options[$sp['userid']] = $sp['username'];
        }
        return $options;
    }
}