<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class linkword extends table
{
    function getcols($act = '')
    {
        return 'id,linkword,linkurl,linkorder,linktimes';
    }

    function get_form()
    {
        return array(
            'linkurl' => array(
                'default' => 'http://',
                //'tips' => '为空则为搜索链接',
            ),
            'linktimes' => array(
                'default' => 1,
            ),
        );
    }
}