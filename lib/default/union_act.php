<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class union_act extends act {
    function init() {
        if(!union::getconfig('enabled')) {
            echo '<script type="text/javascript">alert("'.lang('promotion_alliance_is_not_open_into_the_member_center').'")</script>';
            front::refresh(url::create('user/index'));
        }
        $user='';
        if(cookie::get('login_username') &&cookie::get('login_password')) {
            $user=new user();
            $user=$user->getrow(array('username'=>cookie::get('login_username')));
        }
        if(!is_array($user) &&front::$act != 'into'&&front::$act != 'login'&&front::$act != 'register'&&front::$act != 'login_js'&&front::$act != 'login_success'&&front::$act != 'getpass'&&front::$act != 'edit'){
        	front::redirect(url::create('user/login'));
        }else{
        	if (is_array($user) && cookie::get('login_password') == front::cookie_encode($user['password'])) {
        		$this->view->user = $user;
        		$this->view->usergroupid = $user['groupid'];
        		$obj = new usergroup();
        		$this->roles = $obj->getrow(array('groupid'=>$this->view->usergroupid));
        	}
        }
        $this->_user=new user;
        $this->view->form = $this->_user->get_form();
        $this->view->field = $this->_user->getFields();
        $this->view->primary_key=$this->_user->primary_key;
        $this->view->data = $this->view->user;
        $this->_union = new union();
        $this->view->uniondata = $this->_union->getrow(array('userid'=>$this->view->data['userid']));
        if(!$this->view->uniondata &&front::$act != 'register'&&front::$act != 'into') {
            echo '<script type="text/javascript">alert("'.lang('did_not_apply_for_the_account_to_the_union_application_page').'");window.location.href="'.url::create('union/register').'";</script>';
            //front::refresh(url::create('union/register'));
        }
        $this->_pagesize=config::get('manage_pagesize');
    }
    function into_action() {
        preg_match_all("/case=union&act=into&(.*)/isu",$_SERVER['QUERY_STRING'],$queryout);
        if(!empty($queryout[1][0])) {
            $userid = intval($queryout[1][0]);
            //var_dump($userid);
            $r = $this->_union->getrow(array('userid'=>$userid));
            var_dump($r);
            if($r) {
                $time = time() -3600*24;
                $unionvisit = new union_visit();
                $r_visit = $unionvisit->rec_select("userid=$userid AND visitip='".front::ip()."' AND visittime>$time",0,'*',' 1');
                var_dump($r_visit);
                if(!$r_visit) {
                    $rewardtype = union::getconfig('rewardtype');
                    $rewardnumber = union::getconfig('rewardnumber');
                    $user = $this->_user->getrow(array('userid'=>$userid));
                    $user['username'];
                    switch($rewardtype) {
                        case 'point':
                            union::pointadd($user['username'],$rewardnumber,'union');
                            break;
                    }
                    $useridarr = array();
                    $useridarr['userid'] = $userid;
                    $updatevisit = $this->_union->rec_update(array('visits'=>'[visits+1]'),$useridarr);
                    if($this->_union->affected_rows()) {
                        $useridarr['userid'] = $userid;
                        $useridarr['visittime'] = time();
                        $useridarr['visitip'] = front::ip();
                        $useridarr['referer'] = $_SERVER['HTTP_REFERER'];
                        if(preg_match('/select/i',$useridarr['referer']) || preg_match('/union/i',$useridarr['referer']) || preg_match('/"/i',$useridarr['referer']) ||preg_match('/\'/i',$useridarr['referer'])){
                        	exit(lang('illegal_parameter'));
                        }
                        $unionvisit->rec_insert($useridarr);
                        $union_visitid = $unionvisit->insert_id();
                        $cookietime = time() +union::getconfig('keeptime');
                        cookie::set('union_visitid',$union_visitid,$cookietime);
                        cookie::set('union_userid',$userid,$cookietime);
                    }
                }
            }
        }
        $url = union::getconfig('forward') ?union::getconfig('forward') : config::get('site_url');
        //header('location:'.$url);
        exit;
    }
    function stats_action() {
        $r = $this->_union->getrow(array('userid'=>$this->view->data['userid']));
        $r['allexpendamount'] = $r['totalexpendamount'] +$r['settleexpendamount'];
        $r['settleamount'] = round($r['settleexpendamount']*$r['profitmargin']/100,2);
        $r['lastpaydate'] = $r['lastpaytime'] ?date('Y-m-d',$r['lastpaytime']) : '';
        $this->view->uniondata = $r;
    }
    function getcode_action() {
    }
    function pay_action() {
        $union_pay = new union_pay();
        if(!front::get('page')) front::$get['page']=1;
        $limit=((front::$get['page']-1)*20).',20';
        $where="userid={$this->view->user['userid']}";
        $this->view->paylist = $union_pay->getrows($where,$limit,'1 desc');
        $this->view->record_count=$union_pay->record_count;
    }
    function visit_action() {
        $unionvisit = new union_visit();
        if(!front::get('page')) front::$get['page']=1;
        $limit=((front::$get['page']-1)*20).',20';
        $where="userid={$this->view->user['userid']}";
        $this->view->visitlist = $unionvisit->getrows($where,$limit,'1 desc');
        $this->view->record_count=$unionvisit->record_count;
    }
    function reguser_action() {
        $unionreguser = new user();
        if(!front::get('page')) front::$get['page']=1;
        $limit=((front::$get['page']-1)*20).',20';
        $where="introducer={$this->view->user['userid']}";
        $this->view->visitlist = $unionreguser->getrows($where,$limit,'1 desc');
        $this->view->record_count=$unionreguser->record_count;
    }
    function index_action() {
        $this->view->data=$this->view->user;
    }
    function edit_action() {
    	if(preg_match('/(select|and|\))/i',front::$post['payaccount']) || preg_match('/union/i',front::$post['payaccount']) || preg_match('/"/i',front::$post['payaccount']) ||preg_match('/\'/i',front::$post['payaccount'])){
    		exit(lang('illegal_parameter'));
    	}
        if(front::post('submit')) {
        	unset(front::$post['email']);
        	unset(front::$post['password']);
            $this->_union->rec_update(front::$post,'userid='.$this->view->user['userid']);
            front::flash(lang('modify_data_successfully'));
            front::redirect(url::create('union/edit'));
        }
        $this->view->data=$this->view->user;
        $this->view->data['payaccount'] = $this->view->uniondata['payaccount'];
        $this->view->data['website'] = $this->view->uniondata['website'];
    }
    function register_action() {
        $r = $this->_union->getrow(array('userid'=>$this->view->data['userid']));
        if($r) {
            echo '<script type="text/javascript">alert("'.lang('you_have_applied_for_and_turn_to_the_alliance_page').'")</script>';
            front::refresh(url::create('union/stats'));
        }
        if(front::post('submit')) {
            if(!config::get('reg_on')) {
                front::flash(lang('site_has_been_closed_to_register'));
                return;
            }
            if(config::get('verifycode')) {
                if(!session::get('verify') ||front::post('verify')<>session::get('verify')) {
                    front::flash(lang('verification_code'));
                    return;
                }
            }
            if(front::post('nickname') != strip_tags(front::post('nickname'))
                    ||front::post('nickname') != htmlspecialchars(front::post('nickname'))
            ) {
                front::flash(lang('name_is_not_standardized'));
                return;
            }
            if(strlen(front::post('nickname'))<4) {
                front::flash(lang('please_fill_out_the_real_name_seriously'));
                return;
            }
            if(strlen(front::post('payaccount'))<1) {
                front::flash(lang('please_fill_in_the_payment_account'));
                return;
            }
            if(strlen(front::post('tel'))<1) {
                front::flash(lang('please_fill_in_the_contact_phone'));
                return;
            }
            if(strlen(front::post('address'))<1) {
                front::flash(lang('please_fill_in_the_contact_address'));
                return;
            }
            if(strlen(front::post('website'))<1) {
                front::flash(lang('please_fill_in_the_website_address'));
                return;
            }
            /*if(strlen(front::post('e_mail'))<1) {
                front::flash(lang('请填写邮箱！'));
                return;
            }*/
            if(is_array($_POST)){
            	foreach ($_POST as $v){
            		if(preg_match('/(select|load_file|\[|password)/i', $v)){
            			exit('not access');
            		}
            	}
            }
            $userarr = array();
            $userarr['nickname'] = front::$post['nickname'];
            $userarr['tel'] = front::$post['tel'];
            $userarr['address'] = front::$post['address'];
            //$userarr['e_mail'] = front::$post['e_mail'];
            $unionarr = array();
            $unionarr['userid'] = $this->view->data['userid'];
            $unionarr['username'] = $this->view->data['username'];
            $unionarr['payaccount'] = front::$post['payaccount'];
            $unionarr['website'] = front::$post['website'];
            $unionarr['profitmargin'] = union::getconfig('profitmargin');
            $unionarr['regtime'] = time();
            $unionarr['regip'] = front::ip();
            $unionarr['passed'] = 1;
            if(front::post('nickname') &&$this->view->data['userid']) {
                $insert=$this->_user->rec_update($userarr,'userid='.$this->view->user['userid']);
                $insert1 = $this->_union->rec_insert($unionarr);
                if($insert &&$insert1) front::flash(lang('successful_application'));
                else {
                    front::flash(lang('application_failure'));
                    return;
                }
                front::redirect(url::create('union/stats'));
                exit;
            }
            else {
                front::flash(lang('application_failure'));
                return;
            }
        }
    }
    function end() {
        if(isset($this->end) &&!$this->end) return;
        if(front::$debug)
            $this->render('style/index.html');
        else
            $this->render();
    }
}