<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');
class templatetag_act extends act {
    function init() {
		$this->check_pw();
    }
    function get_action() {
        front::check_type(front::get('id'));
        $tagid=front::get('id');
        echo tool::text_javascript(templatetag::tag($tagid));
    }
    function test_action() {
        front::check_type(front::get('id'));
        $tagid=front::get('id');
        echo templatetag::tag($tagid);
    }
    function visual_action() {
        if ($this->view->usergroupid != '888')
            throw new HttpErrorException(404,'页面不存在',404);
        $id=front::get('id');
        $tpl=str_replace('_d_','/',$id);
        $tpl=str_replace('#','',$tpl);
        $tpl=str_replace('_html','.html',$tpl);
        $content=file_get_contents(TEMPLATE.'/'.config::get('template_dir').'/'.$tpl);
        echo @front::$view->_eval(front::$view->compile($content));
        $this->render('../admin/system/tag_visual.php');
    }
}