<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class comment_act extends act {
    function init() {
        $this->manage=new table_comment;
    }
    function add_action() {
        //var_dump(front::$post);exit;
        if(front::post('submit') &&front::post('aid') && config::get('comment')) {

            //var_dump(front::$post);exit;
        	/*if(config::get('verifycode')) {
	            //if(front::post('verify')<>session::get('verify')) {
                if(!verify::checkGee()){
	                 alertinfo(lang('verification_code'), front::$from);
	                //front::redirect(front::$from);
	            }
        	}*/

            $ip = front::ip();
            $username = $this->cur_user['username'];

            $comment = new comment();
            $row = $comment->getrow("username='$username' OR ip='$ip'", "adddate DESC");
            //var_dump(time());
            if ($row['adddate'] && time() - strtotime($row['adddate']) <= intval(config::get('comment_time'))) {
                alerterror(lang('frequent_operation_please_wait'));
                return;
            }

            if(config::get('mobilechk_enable') && config::get('mobilechk_comment')){
                $mobilenum = front::$post['mobilenum'];
                $smsCode = new SmsCode();
                if(!$smsCode->chkcode($mobilenum)){
                    alertinfo(lang('cell_phone_parity_error'), front::$from);
                }
            }
            if(!front::post('username')) {
                /*front::flash(lang('请留下你的名字！'));
                front::redirect(front::$from);*/
            	alertinfo(lang('please_leave_your_name'), front::$from);
            }
            if(!front::post('content')) {
                /*front::flash(lang('请填写评论内容！'));
                front::redirect(front::$from);*/
                alertinfo(lang('please_fill_in_the_comments'), front::$from);
            }
            $this->manage->filter();
            $comment=new comment();
            $archive=new archive();
            front::$post['state'] = intval(config::get('comment_ischeck'));
            front::$post['adddate']=date('Y-m-d H:i:s');
            $comment->rec_insert(front::$post);
            $archive->rec_update('comment_num=comment_num+1',front::post('aid'));
            //front::flash(lang('提交成功！'));
            alertinfo(lang('comments_submitted_successfully'), front::$from);
            //front::redirect(front::$from);
        }else {
            front::flash(lang('comment_submission_failed'));
            front::redirect(front::$from);
        }
    }
    function list_action() {
        front::check_type(front::get('aid'));
        $this->view->article=archive::getInstance()->getrow(front::get('aid'));
        $this->view->page=front::get('page')?front::get('page'):1;
        $this->pagesize=config::get('list_pagesize');
        $limit=(($this->view->page-1)*$this->pagesize).','.$this->pagesize;
        $comment=new comment();
        $this->view->comments=$comment->getrows('state=1 and aid='.front::get('aid'),$limit);
        $this->view->record_count=$comment->rec_count('state=1 and aid='.front::get('aid'));
        front::$record_count=$this->view->record_count;
        $this->view->pages = ceil(front::$record_count / $this->pagesize);

        $this->view->aid=front::get('aid');
    }
    function comment_js_action() {
        front::check_type(front::get('aid'));
        $comment=new comment();
        $this->view->comments=$comment->getrows('state=1 and aid='.front::get('aid'),20,'1');
        $this->view->aid=front::get('aid');
        echo  tool::text_javascript($this->fetch());
        exit;
    }
function digui(&$str,$id){
        $comment = comment::getIns();
        $row = $comment->getrow($id);
        //var_dump($row);//exit;
        $str .= "<div class='bor'><div class='h'><span class='name'>{$row['username']}</span><span class='date'>{$row['adddate']}</span></div><div class='p'>";
        if($row['rid']){
            $this->digui($str,$row['rid']);
        }
        $str .= nl2br($row['content'])."</div><div class='f'><span class='zan_btn' data-aid='{$row['aid']}' data-cid='{$row['id']}'>赞[<i id='zan_{$row['id']}'>{$row['zannum']}</i>]</span><span class='reply_btn' data-raid='{$row['aid']}' data-rcid='{$row['id']}'>回复</span></div><div class='clear'></div><div style='display:none;' id='rcid_{$row['id']}'><textarea id='trid_{$row['id']}' name='content' rows='6' cols='50'></textarea><br /><input name='submit' value='发表回复' type='button' data-baid='{$row['aid']}' data-brid='{$row['id']}' class='re_btn' /></div><div class='clear'></div></div>";
        return $str;
    }

    function ajax_action() {
        front::check_type(front::get('aid'));
        $where = 'state=1 and aid='.front::get('aid');
        $comment = comment::getIns();
        $p = intval(front::get('p'));
        if(!$p) $p = 1;
        $pagesize = config::get('list_pagesize');
        $count = $comment->rec_count($where);
        $limit = (($p - 1) * $pagesize) . ',' . $pagesize;
        $pages = ceil($count / $pagesize);
        $row = $comment->getrows($where,$limit,'zannum desc,adddate desc');
        $i = 0;
        if(is_array($row) && !empty($row)){
            foreach($row as $arr){
                //if($arr['rid']){
                    //echo 11;
                //}
                //var_dump($arr['rid']);exit;
                if($arr['rid']){
                    $str = '';
                    $this->digui($str,$arr['rid']);
                    $row[$i]['content'] = $str . $arr['content'];
                }else{
                    $row[$i]['content'] = nl2br($arr['content']);
                }
                $i++;
            }
        }
        //var_dump($row);exit;
        $json = json_encode($row);
        echo $json;
        //$this->view->comments=$comment->getrows('state=1 and aid='.front::get('aid'),20,'1');
        //$this->view->aid=front::get('aid');
        //echo  tool::text_javascript($this->fetch());
        exit;
    }

    function zan_action(){
        if(!$this->cur_user['userid']){
            exit('unsign');
        }
        $id = intval(front::get('id'));
        $aid = intval(front::get('aid'));
        $comment = comment::getIns();
        $row = $comment->getrow($id);
        $zannum = intval($row['zannum'])+1;
        $comment->rec_update(array('zannum'=>$zannum),$id);
        $zanlog = zanlog::getInstance();
        $zanlog->addlog($aid,$id,$this->cur_user['userid']);
        echo $zannum;
        exit;
    }

    function reply_action(){
        if(!$this->cur_user['username']) {
            alerterror('请先登录');
        }
        $aid = intval(front::post('aid'));
        $rid = intval(front::post('rid'));
        $content = front::$post['content'];
        $comment = comment::getIns();
        $comment->rec_insert(array(
            'aid' => $aid,
            'content' => $content,
            'rid' => $rid,
            'userid' => $this->cur_user['userid'],
            'username' => $this->cur_user['username'],
            'adddate' => date('Y-m-d H:i:s'),
            'state' => intval(config::get('comment_ischeck')),
        ));

        $archive = new archive();
        $archive->rec_update('comment=comment+1',$aid);
        front::redirect($_SERVER['HTTP_REFERER']);
    }

    function del_action(){
        $id = intval(front::$get['id']);
        $comment = comment::getIns();
        $row = $comment->getrow($id);
        if($row['username'] == front::$user['username'] && $comment->rec_delete($id)){
            front::refresh(url('manage/commentlist/manage/comment'));
        }else{
            alerterror('删除失败');
        }
    }

    function delzan_action(){
        $id = intval(front::$get['id']);
        $zanlog = zanlog::getInstance();
        $row = $zanlog->getrow($id);
        if($row['uid'] == front::$user['userid'] && $zanlog->rec_delete($id)){

            $comment = comment::getIns();
            $arr = $comment->getrow($row['cid']);
            $zannum = abs(intval($arr['zannum'])-1);
            $comment->rec_update(array('zannum'=>$zannum),$row['cid']);

            front::refresh(url('manage/zanlist/manage/zanlog'));
        }else{
            alerterror('删除失败');
        }
    }
    function end() {
        if(front::$debug)
            $this->render('style/index.html');
        else
            $this->render();
    }
}