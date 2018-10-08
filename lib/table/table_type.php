<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class table_type extends table_mode
{
    function vaild()
    {
        if (!front::post('typename')) {
            front::flash('请填写类别标题等！');
            return false;
        }
        if (!front::post('htmldir'))
            front::$post['htmldir'] = pinyin::get(front::post('typename'));
        return true;
    }

    function view_before(&$data = null)
    {
        $rank = new rank();
        $rank = $rank->getrow('typeid=' . front::get('id'));
        if (is_array($rank))
            $data['_ranks'] = unserialize($rank['ranks']);
        else $data['_ranks'] = array();
        unset($data['ranks']);
    }

    function save_after($typeid = '')
    {
        if (front::$post['_ranks']) {
            $_ranks = serialize(front::post('_ranks'));
            $rank = new rank();
            if (is_array($rank->getrow(array('typeid' => front::get('id')))))
                $rank->rec_update(array('ranks' => $_ranks), 'typeid=' . $typeid);
            else
                $rank->rec_insert(array('typeid' => front::get('id'), 'ranks' => $_ranks));
        } else {
            $rank = new rank();
            $rank->rec_delete('typeid=' . $typeid);
        }
    }

    function save_before()
    {
        if (front::$post['htmlrule1'] != '') {
            front::$post['htmlrule'] = front::$post['htmlrule1'];
        }
        if (front::$post['listhtmlrule1'] != '') {
            front::$post['listhtmlrule'] = front::$post['listhtmlrule1'];
        }
        front::$post['typecontent'] = stripcslashes(htmlspecialchars_decode(front::$post['typecontent']));
        front::$post['module'] = 'article';
    }

    function delete_before($id = '')
    {
        $typeid = intval(front::$get['id']);
        $where = "typeid = '$typeid'";
        $arc = new archive();
        $arc->rec_delete($where);
    }
}