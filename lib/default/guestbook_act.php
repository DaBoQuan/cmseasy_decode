<?php 

class guestbook_act extends act
{
    function init()
    {
        if (!config::get('guestbook_enable')) {
            alerterror(lang('message_this_feature_has_been_closed'));
        }
        $this->table = 'guestbook';
        $this->_table = new $this->table;
        if (!$this->_table->getFields()){
            throw new HttpErrorException(404,'页面不存在',404);
        }
        $this->view->form = $this->_table->get_form();
        $this->_pagesize = config::get('manage_pagesize');
        $this->view->manage = $this->table;
        $this->view->primary_key = $this->_table->primary_key;
        $fieldlimit = $this->_table->getcols(front::$act == 'list' ? 'user_list' : 'user_modify');
        $field = $this->_table->getFields();
        helper::filterField($field, $fieldlimit);
        $this->view->field = $field;
        if (!front::get('page')) front::$get['page'] = 1;
    }

    function index_action()
    {
        if(!config::get('guestbook_enable')){
            exit('留言功能已关闭');
        }
        $this->list_action();
        if (front::post('submit')) {
            $guest = new guestbook();
            $ip = front::ip();
            $username = $this->cur_user['username'];
            $row = $guest->getrow("username='$username' OR ip='$ip'", "adddate DESC");

            if ($row['adddate'] && time() - strtotime($row['adddate']) <= intval(config::get('guestbook_time'))) {
                alerterror(lang('frequent_operation_please_wait'));
                return;
            }

            if (config::get('verifycode') == 1) {
                if (!session::get('verify') || front::post('verify') <> session::get('verify')) {
                    alerterror(lang('verification_code'));
                    return;
                }
            } else if (config::get('verifycode') == 2) {
                if (!verify::checkGee()) {
                    alerterror(lang('verification_code'));
                    return;
                }
            }

            if (config::get('mobilechk_enable') && config::get('mobilechk_guestbook')) {
                $mobilenum = front::$post['mobilenum'];
                $smsCode = new SmsCode();
                if (!$smsCode->chkcode($mobilenum)) {
                    alerterror(lang('cell_phone_parity_error'));
                    return false;
                }
            }

            if (!front::post('guesttel')) {
                alerterror(lang('please_fill_in_the_phone'));
                return false;
            }
            if (!front::post('title')) {
                alerterror(lang('please_fill_in_the_title'));
                return false;
            }
            if (!front::post('content')) {
                alerterror(lang('please_fill_in_the_content'));
                return false;
            }


            front::$post['checked'] = 0;
            if (empty($this->view->user)) {
                front::$post['userid'] = 0;
                front::$post['username'] = lang('tourist') . '：' . front::$post['nickname'];
            } else {
                front::$post['userid'] = $this->view->user['userid'];
                front::$post['username'] = $this->view->user['username'];
            }
            front::$post['adddate'] = date('Y-m-d H:i:s');
            front::$post['addtime'] = time();
            front::$post['ip'] = front::ip();
            front::$post['title'] = strip_tags(front::$post['title']);
            $data = front::$post;
            $insert = $this->_table->rec_insert($data);
            if ($insert < 1) {
                front::flash(lang('message_failed'));
            } else {
                $body = '标题:'.front::$post['title'].'<br>名字:'.front::$post['username'].'<br>电话:'.front::$post['guesttel'].'<br>邮箱:'.front::$post['guestemail'].'<br>QQ:'.front::$post['guestqq'].'<br>内容:'.front::$post['content'];
                if (config::get('email_gust_send_cust') && front::$post['guestemail']) {
                    $title = lang('you_in') . config::get('sitename') . lang('the_message_has_been_submitted');

                    $this->sendmail(front::$post['guestemail'], $title, $body);
                }
                if (config::get('email_guest_send_admin') && config::get('email')) {
                    $title = lang('web_ site') . date('Y-m-d H:i:s') . lang('new_message');

                    $this->sendmail(config::get('email'), front::$post['title'], $body);
                }
                if (config::get('sms_on') && config::get('sms_guestbook_on')) {
                    $smsCode = new SmsCode();
                    $content = $smsCode->getTemplate('guestbook');
                    sendMsg(front::$post['guesttel'], $content);
                }
                if (config::get('sms_on') && config::get('sms_guestbook_admin_on') && $mobile = config::get('site_mobile')) {
                    sendMsg($mobile, front::$post['username'] . lang('in') . date('Y-m-d H:i:s') . lang('message_success'));
                }
                alertinfo(lang('message_success'),$_SERVER['HTTP_REFERER']);
            }
        }
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

    function del_action(){
        $id = intval(front::$get['id']);
        $guestbook = guestbook::getInstance();
        $row = $guestbook->getrow($id);
        if($row['username'] == front::$user['username']){
            $guestbook->rec_delete($id);
            front::refresh(url('manage/guestbooklist/manage/guestbook'));
        }else{
            alerterror('删除失败');
        }
    }

    function list_action()
    {
        $limit = ((front::get('page') - 1) * config::get('list_pagesize')) . ',' . config::get('list_pagesize');
        $where = null;
        //var_dump($this->_table->getcols('user_list'));
        $this->_view_table = $this->_table->getrows($where, $limit, '1 desc', $this->_table->getcols('user_list'));
        //var_dump($this->_view_table);
        $this->view->record_count = $this->_table->record_count;
    }

    function view($table)
    {
        $this->view->data = $table['data'];
    }

    function end()
    {
        if (!isset($this->_view_table['data']) && isset($this->_view_table))
            $this->_view_table['data'] = $this->_view_table;
        if (isset($this->_view_table))
            $this->view($this->_view_table);
        $this->render();
    }
}