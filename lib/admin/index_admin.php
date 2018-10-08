<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');
class index_admin extends admin {
    function init() {
    }
    function index_action() {
		$this->check_pw();
        session::del('mod');
        $tbpre = config::get('database','prefix');
        $user = new user();
        
        $sql = "SELECT count(1) as rec_sum FROM `{$tbpre}archive`";
        $row = $user->rec_query_one($sql);
        $this->view->archivenum = $row['rec_sum'];
        
        $sql = "SELECT value FROM `{$tbpre}settings` WHERE tag='table-hottag'";
        $row = $user->rec_query_one($sql);
        $tmp = unserialize($row['value']);
        $tmp = explode("\n", $tmp['hottag']);
        $this->view->tagnum = count($tmp);
        
        $sql = "SELECT count(1) as rec_sum FROM `{$tbpre}a_comment`";
        $row = $user->rec_query_one($sql);
        $this->view->commentnum = $row['rec_sum'];
        
        $sql = "SELECT count(1) as rec_sum FROM `{$tbpre}archive` WHERE checked = 0";
        $row = $user->rec_query_one($sql);
        $this->view->unchecknum = $row['rec_sum'];
        
        $sql = "SELECT count(1) as rec_sum FROM `{$tbpre}guestbook`";
        $row = $user->rec_query_one($sql);
        $this->view->guestbooknum = $row['rec_sum'];
        
        $sql = "SELECT count(1) as rec_sum FROM `{$tbpre}p_orders`";
        $row = $user->rec_query_one($sql);
        $this->view->ordernum = $row['rec_sum'];
        $this->view->dbversion = $user->verison();
    }
    
    function hotsearch_action() {
    	chkpw('archive_hotsearch');
    }

    function hotdel_action(){
        $key = front::get('key');
        $file = ROOT.'/data/hotsearch/'.urlencode($key).'.txt';
        //var_dump($file);
        $isexists = file_exists($file);
        //var_dump($isexists);
        if($isexists){
            @unlink($file);
        }
        //exit;
        front::redirect(url('index/hotsearch'));
    }

    function logout_action() {
        cookie::del('login_username');
        cookie::del('login_password');
        session::del('username');
        session::del('roles');
        front::redirect(url::create('index'));
    }

    function profile_action(){
        $user = new user();
        if (front::post('submit')) {
            $table_user = new table_user();
            $table_user->filter(false);
            $table_user->edit_before();
            $table_user->save_before();
            if (!Phpox_token::is_token('user_add', front::$post['token'])) {
                exit('令牌错误');
            }
            unset(front::$post['groupid']);
            $user->rec_update(front::$post, $this->cur_user['userid']);
            alertinfo('修改资料成功!',url('index/profile'));
        }
        $this->view->field = $user->getFields();
        $this->view->form = $user->get_form();
        $this->view->data = $user->getrow(array('userid'=>$this->cur_user['userid']));
        $this->view->token = Phpox_token::grante_token('user_add');
    }

    function end() {
        $this->render('index.php');
    }
}