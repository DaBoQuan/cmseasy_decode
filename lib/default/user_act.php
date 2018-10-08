<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class user_act extends act
{
    function init()
    {
        $user = null;
        $this->_user = new user();
        if (cookie::get('login_username') && cookie::get('login_password')) {
            $user = $this->_user->getrow(array('username' => cookie::get('login_username')));
            if (cookie::get('login_password') != front::cookie_encode($user['password'])) {
                unset($user);
            }
        }
        $nologin_arr = array('login', 'ologin', 'respond', 'dialog_login',
            'space', 'register', 'login_js', 'login_success', 'getpass');
        if (!is_array($user) && !in_array(front::$act, $nologin_arr)) {
            front::redirect(url::create('user/login'));
        } else {
            $this->view->user = $user;
        }

        $this->view->form = $this->_user->get_form();
        $this->view->field = $this->_user->getFields();
        $this->view->primary_key = $this->_user->primary_key;
        if (is_array($_POST)) {
            foreach ($_POST as $v) {
                if (inject_check($v)) {
                    exit(lang('do_not_submit_illegal_content'));
                }
            }
        }

        $this->view->union_conf = include ROOT.'/config/union.php';
    }

    function index_action()
    {
        $this->view->data = $this->view->user;
    }

    function space_action()
    {
        //$space=new user();
        //$space=$space->getrow(array('userid'=>front::get('mid')));
        //$this->view->user=$space;
        //var_dump($this->view->user);
        if (!$this->view->user['userid']) {
            alertinfo(lang('not_logged'), url::create('user/login'));
        }
        $this->_table = new archive;
        if (!front::get('page')) front::$get['page'] = 1;
        $limit = ((front::get('page') - 1) * 20) . ',20';
        $where = "userid={$this->view->user['userid']}";
        $where .= ' and ' . $this->_table->get_where('user_manage');
        $this->_view_table = $this->_table->getrows($where, $limit, '1 desc', $this->_table->getcols('manage'));
        $this->view->data = $this->_view_table;
        $this->view->record_count = $this->_table->record_count;
    }

    function edit_action()
    {
        if (front::post('submit')) {
            unset(front::$post['username']);
            unset(front::$post['groupid']);
            unset(front::$post['powerlist']);
            unset(front::$post['password']);
            if (!is_email(front::$post['e_mail'])) {
                alerterror(lang('mailbox_format_is_not'));
            }
            foreach (front::$post as $k => $v) {
                if (is_array($v) && !empty($v)) {
                    front::$post[$k] = implode(',', $v);
                }
                front::check_type(front::post($k), 'safe');
            }
            $this->_user->rec_update(front::$post, "username='".session::get('username')."'");
            front::flash(lang('modify_data_successfully'));
            front::redirect(url::create('user/index'));
        }
        $this->view->data = $this->view->user;
        //var_dump($this->view->data);
    }

    //第三方平台登录
    function ologin_action()
    {
		$logintypes = array('alipaylogin','qqlogin','wechatlogin');
        $logintype = $_GET['logtype'];
        if(!in_array($logintype,$logintypes)){
            exit(lang('the_wrong_name'));
        }
        $logintype = $_GET['logtype'];
        $where = array('ologin_code' => $logintype);
        $ologins = ologin::getInstance()->getrows($where);
        include_once ROOT . '/lib/plugins/ologin/' . $logintype . '.php';
        $loginobj = new $logintype();
        $url = $loginobj->get_code(unserialize_config($ologins[0]['ologin_config']));
        @header("Location: $url");
        exit;
    }

    function respond_action()
    {
        $classname = front::$get['ologin_code'];
        if (!in_array($classname, array('alipaylogin', 'qqlogin','wechatlogin'))) {
            front::flash(lang('the_wrong_name'));
            return;
        }
        if (front::post('regsubmit')) {
            if (!config::get('reg_on')) {
                front::flash(lang('site_has_been_closed_to_register'));
                return;
            }
            if (front::post('username') != strip_tags(front::post('username'))
                || front::post('username') != htmlspecialchars(front::post('username'))
            ) {
                front::flash(lang('ame_is_not_standardized'));
                return;
            }
            if (strlen(front::post('username')) < 4) {
                front::flash(lang('user_name_is_too_short'));
                return;
            }
            if (front::post('username') && front::post('password')) {
                $username = front::post('username');
                $username = str_replace('\\', '', $username);
                $password = md5(front::post('password'));
                $data = array(
                    'username' => $username,
                    'password' => $password,
                    'groupid' => 101,
                    'userip' => front::ip(),
                    $classname => session::get('openid'),
                );
                if ($this->_user->getrow(array('username' => $username))) {
                    front::flash(lang('user_name_already_registered'));
                    return;
                }
                $insert = $this->_user->rec_insert($data);
                $_userid = $this->_user->insert_id();
                if ($insert) {
                    front::flash(lang('registered_success'));
                } else {
                    front::flash(lang('registration_failure'));
                    return;
                }
                $user = $data;
                cookie::set('login_username', $user['username']);
                cookie::set('login_password', front::cookie_encode($user['password']));
                session::set('username', $user['username']);
                front::redirect(url::create('user'));
                exit;
            }
        }

        if (front::post('submit')) {
            if (front::post('username') && front::post('password')) {
                $username = front::post('username');
                $password = md5(front::post('password'));
                $data = array(
                    'username' => $username,
                    'password' => $password,
                );
                $user = new user();
                $row = $user->getrow(array('username' => $data['username'], 'password' => $data['password']));
                if (!is_array($row)) {
                    $this->login_false();
                    return;
                }
                $post[$classname] = session::get('openid');
                $this->_user->rec_update($post, 'userid=' . $row['userid']);
                cookie::set('login_username', $row['username']);
                cookie::set('login_password', front::cookie_encode($row['password']));
                session::set('username', $row['username']);
                front::redirect(url::create('user'));
                return;
            } else {
                $this->login_false();
                return;
            }

        }

        include_once ROOT . '/lib/plugins/ologin/' . $classname . '.php';
        $ologinobj = new $classname();
        $status = $ologinobj->respond();
        //var_dump(session::get('openid'));exit;
        $where[$classname] = session::get('openid');
        if (!$where[$classname]) front::redirect(url::create('user'));
        $user = new user();
        $data = $user->getrow($where);
        if (!$data) {
            $this->view->data = $status;
        } else {
            cookie::set('login_username', $data['username']);
            cookie::set('login_password', front::cookie_encode($data['password']));
            session::set('username', $data['username']);
            front::redirect(url::create('user'));
        }

    }

    //////////////////////

    function login_action()
    {
        if (!$this->loginfalsemaxtimes())
            if (front::post('submit')) {
                if(config::get('verifycode') == 1) {
                    if (!session::get('verify') || front::post('verify') <> session::get('verify')) {
                        alerterror(lang('verification_code'));
                        return;
                    }
                }else if(config::get('verifycode') == 2){
                    if (!verify::checkGee()) {
                        alerterror(lang('verification_code'));
                        return;
                    }
                }
                if (config::get('mobilechk_enable') && config::get('mobilechk_login')) {
                    $mobilenum = front::$post['mobilenum'];
                    $smsCode = new SmsCode();
                    if (!$smsCode->chkcode($mobilenum)) {
                        alerterror(lang('cell_phone_parity_error') . "<a href=''>" . lang('backuppage') . "</a>");
                        return;
                    }
                }
                if (front::post('username') && front::post('password')) {
                    $username = front::post('username');
                    $password = md5(front::post('password'));
                    $data = array(
                        'username' => $username,
                        'password' => $password,
                    );
                    $user = new user();
                    $user = $user->getrow($data);
                    if (!is_array($user)) {
                        $this->login_false();
                        return;
                    }
                    //var_dump($user);exit;
                    if($user['isblock']){
                        alerterror('您的账户已被冻结！');
                        return;
                    }
                    //$user = $data;
                    cookie::set('login_username', $user['username']);
                    cookie::set('login_password', front::cookie_encode($user['password']));
                    session::set('username', $user['username']);
                    $this->view->from = front::post('from') ? front::post('from') : front::$from;
                    front::flash($this->fetch('login_success.html'));
                    return;
                } else {
                    $this->login_false();
                    return;
                }
            }
        $this->view->ologinlist = ologin::getInstance()->getrows('', 50);
    }

    function dialog_login_action()
    {
        if (!$this->loginfalsemaxtimes())
            if (front::post('submit')) {
                if (config::get('verifycode')) {
                    if (!session::get('verify') || front::post('verify') <> session::get('verify')) {
                        front::flash(lang('verification_code') . "<a href=''>" . lang('backuppage') . "</a>");
                        return;
                    }
                }
                if (front::post('username') && front::post('password')) {
                    $username = front::post('username');
                    $password = md5(front::post('password'));
                    $data = array(
                        'username' => $username,
                        'password' => $password,
                    );
                    $user = new user();
                    $user = $user->getrow($data);
                    if (!is_array($user)) {
                        $this->login_false();
                        return;
                    }
                    //$user = $data;
                    //var_dump($user);exit;
                    if($user['isblock']){
                        front::flash('您的账户已被冻结！');
                        return;
                    }
                    cookie::set('login_username', $user['username']);
                    cookie::set('login_password', front::cookie_encode($user['password']));
                    session::set('username', $user['username']);
                    session::set('userid', $user['uid']);
                    $this->view->from = front::post('from') ? front::post('from') : front::$from;
                    $this->view->message = $this->fetch('user/login_success.html');
                    return;
                } else {
                    $this->login_false();
                    return;
                }
            }
    }

    function login_false()
    {
        cookie::set('loginfalse', (int)cookie::get('loginfalse') + 1, time() + 3600);
        event::log('loginfalse', lang('failure') . ' user=' . front::post('username'));
        //front::flash(lang('login_failure') . "<a href=''>" . lang('backuppage') . "</a>");
        alerterror(lang('login_failure'));
    }

    function loginfalsemaxtimes()
    {
        if (cookie::get('loginfalse') > 5 || event::loginfalsemaxtimes()) {
            front::flash('wrong_too_many_times');
            return true;
        }
    }

    function login_js_action()
    {
        if (cookie::get('login_username') && cookie::get('login_password')) {
            $user = $this->_user->getrow(array('username' => cookie::get('login_username')));
            if (is_array($user) && cookie::get('login_password') == front::cookie_encode($user['password'])) {
                $this->view->user = $user;
                session::set('username', $user['username']);
            }
        }
        echo tool::text_javascript($this->fetch());
        exit;
    }

    function logout_action()
    {
        cookie::del('login_username');
        cookie::del('login_password');
        session::del('username');
        front::redirect(url::create('user/login'));
        exit;
    }

    private function sendmail($smtpemailto, $title, $mailbody)
    {
        include_once(ROOT . '/lib/plugins/smtp.php');
        $mailsubject = mb_convert_encoding($title, 'GB2312', 'UTF-8');
        $mailtype = "HTML";
        $smtp = new include_smtp(config::get('smtp_mail_host'), config::get('smtp_mail_port'), config::get('smtp_mail_auth'), config::get('smtp_mail_username'), config::get('smtp_mail_password'));
        $smtp->debug = false;
        $smtp->sendmail($smtpemailto, config::get('smtp_user_add'), $mailsubject, $mailbody, $mailtype);
    }

    function register_action()
    {
        //echo session::get('verify');
        //var_dump($_SESSION);
        if (front::post('submit')) {
            if (!config::get('reg_on')) {
                alerterror(lang('site_has_been_closed_to_register'));
                return;
            }

            if(config::get('verifycode') == 1) {
                if (!session::get('verify') || front::post('verify') <> session::get('verify')) {
                    alerterror(lang('verification_code'));
                    exit;
                }
            }else if(config::get('verifycode') == 2){
                if (!verify::checkGee()) {
                    alerterror(lang('verification_code'));
                    exit;
                }
            }

          if (config::get('invitation_registration')) {

              $invite = front::$post['invite'];
              $db_invite = invite::getInstance();
              if(!$db_invite->checkInvite($invite)){
                  alerterror('邀请码错误');
                  return;
              }
          }

            if (config::get('mobilechk_enable') && config::get('mobilechk_reg')) {
                $mobilenum = front::$post['mobilenum'];
                $smsCode = new SmsCode();
                if (!$smsCode->chkcode($mobilenum)) {
                    alerterror(lang('cell_phone_parity_error'));
                    return;
                }
            }
            if (front::post('username') != strip_tags(front::post('username'))
                || front::post('username') != htmlspecialchars(front::post('username'))
            ) {
                alerterror(lang('name_is_not_standardized'));
                return;
            }
            if (strlen(front::post('username')) < 4) {
                alerterror(lang('user_name_is_too_short'));
                return;
            }
            if (strlen(front::post('e_mail')) < 1 && !is_email(front::post('e_mail'))) {
                alerterror(lang('please_fill_in_the_mailbox'));
                return;
            }
            if (!is_email(front::post('e_mail'))) {
                alerterror(lang('please_fill_in_the_correct_mailbox_format'));
                return;
            }
            if (strlen(front::post('tel')) < 1) {
                alerterror(lang('please_fill_in_your_mobile_phone_number'));
                return;
            }


            if (front::post('username') && front::post('password')) {
                $username = front::post('username');
                $username = str_replace('\\', '', $username);
                $password = md5(front::post('password'));
                $e_mail = front::post('e_mail');
                $tel = front::post('tel');
                $data = array(
                    'username' => $username,
                    'password' => $password,
                    'e_mail' => $e_mail,
                    'tel' => $tel,
                    'groupid' => 101,
                    'userip' => front::ip()
                );
  
                foreach ($this->view->field as $f) {
                    $name = $f['name'];
                    if (!preg_match('/^my_/', $name)) {
                        unset($field[$name]);
                        continue;
                    }
                    if (!setting::$var['user'][$name]['showinreg']) {
                        continue;
                    }
                    $data[$name] = front::post($name);
                }
                if ($this->_user->getrow(array('username' => $username))) {
                    front::flash(lang('user_name_already_registered'));
                    return;
                }
                $insert = $this->_user->rec_insert($data);
                $_userid = $this->_user->insert_id();
                if ($insert) {
                    if (config::get('sms_on') && config::get('sms_reg_on')) {
                        $smsCode = new SmsCode();
                        $content = $smsCode->getTemplate('reg', array($username, front::post('password')));
                        sendMsg($tel, $content);
                    }
                    $cmsname = config::get('sitename');
                    if (config::get('email_reg_on')) {
                        $this->sendmail($e_mail, lang('welcome_to_register')."$cmsname !", lang('respect') . $username . ', ' . lang('hello_welcome_you_to_register' . $cmsname . '!'));
                    }
                    alerterror(lang('registered_success'));
                } else {
                    alerterror(lang('registration_failure'));
                    return;
                }
                if (union::getconfig('enabled')) {
                    $union_visitid = intval(cookie::get('union_visitid'));
                    $union_userid = intval(cookie::get('union_userid'));
                    if ($union_visitid && $union_userid) {
                        $union_reg = new union();
                        $r = $union_reg->getrow(array('userid' => $union_userid));
                        if ($r) {
                            $union_reg->rec_update(array('registers' => '[registers+1]'), array('userid' => $union_userid));
                            if ($union_reg->affected_rows()) {
                                $union_visit_reg = new union_visit();
                                $union_visit_reg->rec_update(array('regusername' => front::post('username'), 'regtime' => time()), array('visitid' => $union_visitid));
                                $this->_user->rec_update(array('introducer' => $union_userid), array('userid' => $_userid));
                                $regrewardtype = union::getconfig('regrewardtype');
                                $regrewardnumber = union::getconfig('regrewardnumber');
                                switch ($regrewardtype) {
                                    case 'point':
                                        union::pointadd($r['username'], $regrewardnumber, 'union');
                                        break;
                                }
                            }
                        }
                    }
                }
                $user = $data;
                cookie::set('login_username', $user['username']);
                cookie::set('login_password', front::cookie_encode($user['password']));
                session::set('username', $user['username']);
                front::redirect(url::create('user'));
                exit;
            } else {
                alerterror(lang('registration_failure'));
                return;
            }
        }
        /*if (front::get('t') == 'wap') {
            $tpl = 'wap/register.html';
            $this->render($tpl);
            exit;
        }*/
    }

    function changepassword_action()
    {
        if (front::post('dosubmit') && front::post('password')) {
            if (!front::post('oldpassword') || !is_array($this->_user->getrow(array('password' => md5(front::post('oldpassword'))), 'userid=' . $this->view->user['userid']))) {
                front::flash(lang('the_original_password_is_not_correct!_Password_change_failed'));
                return;
            }
            $this->_user->rec_update(array('password' => md5(front::post('password'))), 'userid=' . $this->view->user['userid']);
            front::flash(lang('password_modification_success'));
        }

        $this->view->data = $this->view->user;

    }

    function getpass_action()
    {
        if (front::post('step') == '') {
            echo template('getpass.html');
        } else if (front::post('step') == '1') {
            if(config::get('verifycode') == 1) {
                if (!session::get('verify') || front::post('verify') <> session::get('verify')) {
                    alerterror(lang('verification_code'));
                    return;
                }
            }else if(config::get('verifycode') == 2){
                if (!verify::checkGee()) {
                    alerterror(lang('verification_code'));
                    return;
                }
            }
            if (strlen(front::post('username')) < 4) {
                alerterror(lang('user_name_is_too_short'));
                return;
            }
            $user = new user();
            $user = $user->getrow(array('username' => front::post('username')));
            $this->view->user = $user;
            session::set('answer', $user['answer']);
            session::set('username1', $user['username']);
            session::set('e_mail', $user['e_mail']);
            if (!empty($user['answer'])) {
                echo template('getpass_1.html');
            } else {
                session::set('ischk', 'true');
                echo template('getpass_2.html');
            }
        } else if (front::post('step') == '2') {
            if (strlen(front::post('answer')) < 1) {
                echo '<script>alert("' . lang('please_enter_the_answer') . '");</script>';
                return;
            }
            if (front::post('answer') != session::get('answer')) {
                echo '<script>alert("' . lang('your_answer_is_wrong') . '");</script>';
                return;
            }
            session::set('ischk', 'true');
            echo template('getpass_2.html');
        } else if (front::post('step') == '3') {
            if (strlen(front::post('e_mail')) < 1) {
                echo '<script>alert("' . lang('please_enter_the_registration_time_to_fill_in_the_mailbox') . '");</script>';
                return;
            }
            if (front::post('e_mail') != session::get('e_mail')) {
                echo '<script>alert("' . lang('mailbox_and_user_does_not_match') . '");</script>';
                return;
            }
            if (session::get('ischk') == 'true') {
                function randomstr($length)
                {
                    $str = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
                    for ($i = 0; $i < $length; $i++) {
                        $str1 .= $str{mt_rand(0, 35)};
                    }
                    return $str1;
                }

                $password1 = randomstr(6);
                $password = md5($password1);
                $user = new user();
                $user->rec_update(array('password' => $password), 'username="' . session::get('username1') . '"');
                /*config::setPath(ROOT.'/config/config.php');
                function sendmail($email_to,$email_subject,$email_message,$email_from = '') {
                    extract($GLOBALS,EXTR_SKIP);
                    require ROOT.'/lib/tool/sendmail_inc.php';
                }
                $mail[email]=config::get('email');*/
                $this->sendmail(session::get('e_mail'), lang('member_retrieve_password'), ' ' . lang('respect') . session::get('username1') . ', ' . lang('hello_Your_new_password_is') . ':' . $password1 . ' ' . lang(您可以登录后到会员中心进行修改) . '!');
                echo '<script>alert("' .lang('system_to_generate_the_password_has_been_sent_to_your_mailbox,_jump_to_the_login_page') . '");window.location="index.php?case=user&act=login"</script>';
            } else {
                echo '<script>alert("' . lang('illegal_parameter') . '");</script>';
                return;
            }
        }
        exit;
    }

    function fckupload_action()
    {
        /*$uploads=array();
        if(is_array($_FILES)) {
            $upload=new upload();
            foreach($_FILES as $name=>$file) {
                $uploads[$name]=$upload->run($file);
            }
            $this->view->uploads=$uploads;
        }
        $this->render('../admin/system/fckupload.php');*/
        exit;
    }

    function fckuploadcheck_action()
    {
        if (empty($this->view->user) || !$this->view->user['userid'])
            throw new HttpErrorException(404,'页面不存在',404);
        fckuser::$user = $this->view->user;
        $this->end = false;
    }

    function end()
    {
        if (isset($this->end) && !$this->end) return;
        if (front::$debug)
            $this->render('style/index.html');
        else
            $this->render();
    }
}