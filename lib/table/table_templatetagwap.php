<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class table_templatetagwap extends table_mode {
    function vaild() {
        if(!front::post('name')) {
            front::flash('请填写名称！');
            return false;
        }
        if(!front::post('tagcontent')) {
            front::flash('请填写内容！');
            return false;
        }
        return true;
    }
    function save_before() {
        if(!front::post('tagfrom')) front::$post['tagfrom']='define';
        if(!front::post('attr1')) front::$post['attr1']='0';
        if(front::$post['tagcontent']) front::$post['tagcontent'] = htmlspecialchars_decode(front::$post['tagcontent']);
        front::$post['tagcontent'] = str_replace(array('<?','?>'),'',front::$post['tagcontent']);
    }
}