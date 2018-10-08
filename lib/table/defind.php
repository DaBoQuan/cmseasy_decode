<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class defind extends table
{
    function __construct($name)
    {
        $this->name = $name;
        parent::__construct();
    }

    function getcols($act = '')
    {
        switch ($act) {
            case 'manage':
                return 'fid,adddate,username,ip';
            case 'modify':
                return 'fid' . $this->mycols();
            case 'user_modify':
                return $this->mycols();
            case 'user_manage':
                return 'fid,adddate' . $this->mycols();
        }
    }

    function get_form_field()
    {
        $arr = array(0 => '全站使用');
        return array(
            'catid' => array(
                'selecttype' => 'select',
                'select' => form::arraytoselect(category::option(0, 'tolast', $arr)),
                'default' => get('catid'),
                'regex' => '/\d+/',
                'filter' => 'is_numeric',
            ),
            'ishtml' => array(
                'selecttype' => 'radio',
                'select' => form::arraytoselect(array(0 => '继承', 1 => '生成', 2 => '不生成')),
            ),
            'checked' => array(
                'selecttype' => 'radio',
                'select' => form::arraytoselect(form::yesornotoarray('审核')),
            ),
            'image' => array(
                'filetype' => 'image',
            ),
            'displaypos' => array(
                'selecttype' => 'checkbox',
                'select' => form::arraytoselect(array(1 => '首页推荐', 2 => '首页焦点', 3 => '首页头条', 4 => '列表页推荐', 5 => '内容页推荐')),
            ),
            'htmlrule' => array(
                'tips' => " 默认：{?category::gethtmlrule(get('id'),'showhtmlrule')}",
            ),
            'template' => array(
                'selecttype' => 'select',
                'select' => form::arraytoselect(front::$view->archive_tpl_list()),
                'tips' => " 默认：{?category::gettemplate(get('id'),'showtemplate')}",
            ),
            'introduce_len' => array(
                'default' => config::get('archive_introducelen'),
            ),
            'attr1' => array(
                'selecttype' => 'checkbox',
            ),
        );
    }
}
