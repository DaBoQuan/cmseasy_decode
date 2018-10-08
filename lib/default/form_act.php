<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class form_act extends act
{
    protected $_table;

    function init()
    {
        if (front::$act == 'message' || front::$act == 'list') return;
        $this->table = front::get('form');
        if (!preg_match('/^my_\w+/', $this->table)){
            throw new HttpErrorException(404,'页面不存在',404);
        }
        $this->_table = new defind($this->table);
        $field = $this->_table->getFields();
        if (!$field){
            throw new HttpErrorException(404,'页面不存在',404);
        }
        $this->view->form = $this->_table->get_form();
        $this->_pagesize = config::get('manage_pagesize');
        $this->view->manage = $this->table;
        $this->view->primary_key = $this->_table->primary_key;
        $fieldlimit = $this->_table->getcols(front::$act == 'list' ? 'user_manage' : 'user_modify');
        //var_dump($fieldlimit);
        //$field = $this->_table->getFields();
        //var_dump($field);

        helper::filterField($field, $fieldlimit);
        //var_dump($field);
        $this->view->field = $field;
        //var_dump($field);
        if (!front::get('page')) front::$get['page'] = 1;
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

    function add_action()
    {
        if (front::$post['submit']) {

            if (config::get('verifycode') == 1) {
                if (!session::get('verify') || front::post('verify') <> session::get('verify')) {
                    alerterror(lang('verification_code'));
                    //$this->render(@setting::$var[$this->table]['myform']['template']);
                    //$this->end=false;
                    return;
                }
            } else if (config::get('verifycode') == 2) {
                if (!verify::checkGee()) {
                    alerterror(lang('verification_code'));
                    //$this->render(@setting::$var[$this->table]['myform']['template']);
                    //$this->end=false;
                    return;
                }
            }

            if (config::get('mobilechk_enable') && config::get('mobilechk_form')) {
                $mobilenum = front::$post['mobilenum'];
                $smsCode = new SmsCode();
                if (!$smsCode->chkcode($mobilenum)) {
                    alerterror(lang('cell_phone_parity_error'));
                    $this->render(@setting::$var[$this->table]['myform']['template']);
                    $this->end = false;
                    return;
                }
            }

            front::$post['checked'] = 0;
            front::$post['userid'] = $this->view->user['userid'];
            front::$post['username'] = $this->view->user['username'];
            front::$post['author'] = $this->view->user['username'];
            front::$post['adddate'] = date('Y-m-d H:i:s');
            front::$post['ip'] = front::ip();
            foreach (front::$post as $k => $p) {
                if (is_array($p)) {
                    front::$post[$k] = implode(',', $p);
                } else {
                    if (preg_match('/^my_/is', $k)) {
                        front::$post[$k] = htmlspecialchars_decode(front::$post[$k]);
                    }
                }
            }
            $data = front::$post;
            $insert = $this->_table->rec_insert($data);
            if ($insert < 1) {
                front::flash(lang('form_submission_failure'));
            } else {
                if (is_array(front::$post) && !empty(front::$post)) {
                    foreach (front::$post as $k => $v) {
                        if (preg_match('/^my_.*?mail$/i', $k) && strstr($v, '@')) {
                            $email = front::$post[$k];
                            break;
                        }
                    }
                    foreach (front::$post as $k => $v) {
                        if (preg_match('/^my_.*?(tel|dianhua|mobile)$/i', $k)) {
                            $tel = front::$post[$k];
                            break;
                        }
                    }
                }
                $code = '';
                foreach ($this->view->field as $k => $v) {
                    $cname = setting::$var[$this->table][$k]['cname'];
                    $val = front::$post[$k];
                    $code .= $cname . ": " . $val . "<br>";
                }
                $smtpemailto = config::get('email');
                $title = setting::$var[$this->table]['myform']['cname'] . lang('result');
                if (config::get('email_form_on') && $email) {
                    $this->sendmail($email, $title, $code);
                }
                if (config::get('email_form_send_admin') && $smtpemailto) {
                    $this->sendmail($smtpemailto, $title, $code);
                }
                if ($tel) {
                    if (config::get('sms_on') && config::get('sms_form_on')) {
                        $smsCode = new SmsCode();
                        $content = $smsCode->getTemplate('form', array(setting::$var[$this->table]['myform']['cname']));
                        sendMsg($tel, $content);
                    }
                    if (config::get('sms_on') && config::get('sms_form_admin_on') && $mobile = config::get('site_mobile')) {
                        sendMsg($mobile, front::$post['username'] . '在' . date('Y-m-d H:i:s') . '提交了表单');
                    }
                }
                if (front::$post['aid']) {
                    echo "<script>alert('" . lang('form_submission_success') . "');window.location.href='" . url::create('/archive/show/aid/' . front::$post['aid']) . "'</script>";
                    //front::redirect();
                } else {
                    front::redirect(url::create('/form/message'));
                }
            }
        }
        $this->render(@setting::$var[$this->table]['myform']['template']);
        $this->end = false;
    }

    function message_action()
    {
    }

    function view($table)
    {
        $this->view->data = $table['data'];
    }

    public static function get_my_tables()
    {
        $tables = array();
        $forms = tdatabase::getInstance()->getTables();
        foreach ($forms as $form) {
            if (preg_match('/^' . config::get('database', 'prefix') . '(my_\w+)/xi', $form['name'], $res))
                $tables[] = $res[1];
        }
        return $tables;
    }

    public static function table_cname($table)
    {
        $tablec = @setting::$var[$table]['cname'];
        if (!$tablec) $tablec = $table;
        return $tablec;
    }
	function show_action(){
        $this->view->data = $this->_table->getrow(front::get('id'));
    }

    function list_action() {
        $row = self::get_my_tables();
        if(is_array($row)){
            foreach ($row as $t){
                $arr[$t] = $this->getData($t);
            }
        }
        $this->view->tables = $arr;
    }

    function getData($t){
        $this->_table = new defind($t);
        if (front::get('page'))
            $page = front::get('page');
        else
            $page = 1;
        $this->view->page = $page;
        $this->pagesize = 1000;
        $limit = (($this->view->page - 1) * $this->pagesize) . ',' . $this->pagesize;
        $row = $this->_table->getrows(array('userid'=>front::$user['userid']),$limit);
        //var_dump($row);
        return $row;
    }

    function end()
    {
        if (isset($this->end) && !$this->end) return;
        if (!isset($this->_view_table['data']) && isset($this->_view_table))
            $this->_view_table['data'] = $this->_view_table;
        if (isset($this->_view_table))
            $this->view($this->_view_table);
        $this->render();
    }

    function search_action()
    {
        if (front::get('keyword') && !front::post('keyword'))
            front::$post['keyword'] = front::get('keyword');
        front::check_type(front::post('keyword'), 'safe');
        if (front::post('keyword')) {
            $this->view->keyword = trim(front::post('keyword'));
            if (inject_check($this->view->keyword)) {
                exit(lang('illegal_request'));
            }
            session::set('keyword', $this->view->keyword);
        } else {
            session::set('keyword', null);
            $this->view->keyword = session::get('keyword');
        }
        if (inject_check($this->view->keyword)) {
            exit(lang('illegal_request'));
        }
        //var_dump($this->view->keyword);

        $type = $this->view->type;
        $condition = "";
        if (front::post('catid')) {
            $condition .= "catid = '" . front::post('catid') . "' AND ";
        }
        $condition .= "(title like '%" . $this->view->keyword . "%'";
        $sets = settings::getInstance()->getrow(array('tag' => 'table-fieldset'));
        $arr = unserialize($sets['value']);
        if (is_array($arr['archive']) && !empty($arr['archive'])) {
            foreach ($arr['archive'] as $v) {
                if ($v['issearch'] == '1') {
                    $condition .= " OR {$v['name']} like '%{$this->view->keyword}%'";
                }
            }
        }
        $condition .= ")";
        $order = "`listorder` desc,1 DESC";
        $limit = (($this->view->page - 1) * $this->pagesize) . ',' . $this->pagesize;
        $articles = $this->archive->getrows($condition, $limit, $order);
        foreach ($articles as $order => $arc) {
            $articles[$order]['url'] = archive::url($arc);
            $articles[$order]['catname'] = category::name($arc['catid']);
            $articles[$order]['caturl'] = category::url($arc['catid']);
            $articles[$order]['adddate'] = sdate($arc['adddate']);
            $articles[$order]['stitle'] = strip_tags($arc['title']);
        }
        $this->view->articles = $articles;
        $this->view->archives = $articles;
        $this->view->record_count = $this->archive->record_count;
    }
}