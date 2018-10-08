<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class manage_act extends act
{
    protected $_table;

    function init()
    {
        $user = array();
        $guest = front::get('guest');
        $guestuser = array();
        $this->_user = new user;
        if ($guest == '1' && config::get('opguestadd')) {
            $guestuser = $user = array(
                'userid' => 0,
                'username' => 'Guest',
                'nickname' => lang('tourist'),
                'groupid' => 0,
                'checked' => 1,
                'intro' => 'Guest',
                'point' => '',
                'introducer' => '',
            );
        } else {
            $username = cookie::get('login_username');
            $password = cookie::get('login_password');
            if ($username != '' && $password != '') {
                $guestuser = $user = $this->_user->getrow(array('username' => $username));
                if (front::cookie_encode($user['password']) != $password) {
                    $guestuser = $user = array();
                }
            }
        }
        $this->view->guestuser = $guestuser;
        if (!$user && front::$act != 'login' && front::$act != 'register') front::redirect(url::create('user/login'));
        $this->view->user = $user;
        $this->table = front::get('manage');
        if ($this->table <> 'archive'
            && $this->table <> 'orders'
            && $this->table <> 'comment'
            && $this->table <> 'invite'
            && $this->table <> 'zanlog'
            && $this->table <> 'guestbook'
        ) {
            throw new HttpErrorException(404,'页面不存在',404);
        }
        $this->_table = new $this->table;
        $this->_table->getFields();
        $this->view->form = $this->_table->get_form();
        $this->_pagesize = config::get('manage_pagesize');
        $this->view->manage = $this->table;
        $this->view->primary_key = $this->_table->primary_key;
        if (!front::get('page')) front::$get['page'] = 1;
        $manage = 'table_' . $this->table;
        $this->manage = new $manage;
    }

    public function guestbooklist_action(){
        $limit = ((front::get('page') - 1) * 20) . ',20';
        $where = "username='" . front::$user['username'] . "'";
        $this->_view_table = $this->_table->getrows($where, $limit, '1 desc', $this->_table->getcols('manage'));
        $this->view->record_count = $this->_table->record_count;
    }


    function commentlist_action()
    {
        $limit = ((front::get('page') - 1) * 20) . ',20';
        $where = "username='" . front::$user['username'] . "'";
        $this->_view_table = $this->_table->getrows($where, $limit, 'adddate desc', $this->_table->getcols('manage'));
        $i = 0;
        $archive = archive::getInstance();
        if (is_array($this->_view_table) && !empty($this->_view_table)) {
            foreach ($this->_view_table as $arr) {
                $news = $archive->getrow($arr['aid']);
                $aurl = $archive->url($news);
                $this->_view_table[$i]['title'] = $news['title'];
                $this->_view_table[$i]['aurl'] = $aurl;
                unset($news);
                $i++;
            }
        }
        //var_dump($this->_view_table);
        $this->view->record_count = $this->_table->record_count;
    }

    function zanlist_action()
    {
        $limit = ((front::get('page') - 1) * 20) . ',20';
        $where = "uid='" . front::$user['userid'] . "'";
        $this->_view_table = $this->_table->getrows($where, $limit, 'addtime desc', $this->_table->getcols('manage'));
        $i = 0;
        $archive = archive::getInstance();
        if (is_array($this->_view_table) && !empty($this->_view_table)) {
            foreach ($this->_view_table as $arr) {
                $news = $archive->getrow($arr['aid']);
                $aurl = $archive->url($news);
                $this->_view_table[$i]['title'] = $news['title'];
                $this->_view_table[$i]['aurl'] = $aurl;
                unset($news);
                $i++;
            }
        }
        //var_dump($this->_view_table);
        $this->view->record_count = $this->_table->record_count;
    }

    function invitelist_action()
    {
        $limit = ((front::get('page') - 1) * 20) . ',20';
        $where = "ctname='" . front::$user['username'] . "'";
        $this->_view_table = $this->_table->getrows($where, $limit, '1 desc', $this->_table->getcols('manage'));
        $this->view->record_count = $this->_table->record_count;
    }

    function list_action()
    {
        $limit = ((front::get('page') - 1) * 20) . ',20';
        $where = "userid={$this->view->user['userid']}";
        $where .= ' and ' . $this->_table->get_where('user_manage');
        //var_dump($where);
        $this->_view_table = $this->_table->getrows($where, $limit, '1 desc', $this->_table->getcols('manage'));
        $this->view->record_count = $this->_table->record_count;
    }
    function guestlist_action()
    {
        echo '<script type="text/javascript">
		alert("' . lang('submit_complete_wait_for_audit') . '");
		window.location.href="' . url::create('/manage/guestadd/manage/archive/guest/1') . '";
		</script>';
    }
    function orderslist_action()
    {
        include_once ROOT . '/lib/plugins/pay/wxscanpay.php';
        $limit = ((front::get('page') - 1) * 20) . ',20';
        $where = "mid={$this->view->user['userid']}";
        $this->_view_table = $this->_table->getrows($where, $limit, 'adddate desc', $this->_table->getcols('manage'));
        /*if(is_array($this->_view_table) && !empty($this->_view_table)){
            foreach ($this->_view_table as $arr){
                //var_dump($arr['oid']);
                $oidout = array();
                preg_match("/-(.*)-(.*)-(.*)/is",$arr['oid'], $oidout);
                //var_dump($oidout);
                $paytype = $oidout[3];
                if($paytype == 'wxscanpay') {
                    $obj = new $paytype();
                    $res = $obj->Queryorder($arr['oid']);
                    var_dump($res);
                }
            }
        }*/
        $this->view->record_count = $this->_table->record_count;
    }

    function add_action()
    {
        if (front::post('submit') && $this->manage->vaild()) {
            $this->manage->filter();
            $this->manage->save_before();
            front::$post['checked'] = 0;
            front::$post['userid'] = $this->view->user['userid'];
            front::$post['username'] = $this->view->user['username'];
            front::$post['author'] = $this->view->user['username'];
            front::$post['adddate'] = date('Y-m-d H:i:s');
            front::$post['ip'] = front::ip();
            $data = array();
            $fieldlimit = $this->_table->getcols(front::$act == 'list' ? 'user_manage' : 'user_modify');
            $fieldlimits = explode(',', $fieldlimit);
            foreach (front::$post as $key => $value) {
                if (preg_match('/(select|union|and|load_file)/i', $value)) {
                    //echo $value;
                    exit(lang('illegal_parameter'));
                }
                if (in_array($key, $fieldlimits))
                    $data[$key] = $value;

            }


            $data = array_merge($data, front::$post);
            unset($data['template']);
            $insert = $this->_table->rec_insert($data);
            if ($insert < 1) {
                front::flash(lang('record_add_failed'));
            } else {
                front::flash(lang('record_add_success'));
                if ($this->table == 'archive')
                    front::redirect(url::create('/manage/list/manage/archive/needcheck/1'));
            }
        }
        chkpwf('add_archive', $this->view->user['groupid']);
        //echo 11;
        $this->_view_table = $this->_table->getrow(null, '1 desc',  $this->_table->getcols('user_modify'));
        $this->_view_table['data'] = array();
    }

    function guestadd_action()
    {
        //var_dump($this->view->guestuser);
        if ($this->view->guestuser['userid']) {
            echo '<script type="text/javascript">
		alert("' . lang('jump_to_member_release_page') . '");
		window.location.href="' . url::create('/manage/add/manage/archive') . '";
		</script>';
        }
        if (front::post('submit') && $this->manage->vaild()) {
            $this->manage->filter();
            $this->manage->save_before();
            //front::$post['title']=addslashes(front::$post['title']);
            front::$post['checked'] = 0;
            front::$post['userid'] = '-999';
            front::$post['username'] = 'guest';
            front::$post['author'] = 'guest';
            front::$post['adddate'] = date('Y-m-d H:i:s');
            front::$post['ip'] = front::ip();
            $data = array();
            $fieldlimit = $this->_table->getcols(front::$act == 'list' ? 'user_manage' : 'user_modify');
            $fieldlimits = explode(',', $fieldlimit);
            foreach (front::$post as $key => $value) {
                if (in_array($key, $fieldlimits))
                    $data[$key] = $value;
            }
            $data = array_merge($data, front::$post);
            $insert = $this->_table->rec_insert($data);
            if ($insert < 1) {
                front::flash(lang('record_add_failed'));
            } else {
                front::flash(lang('record_add_success'));
                if ($this->table == 'archive')
                    front::redirect(url::create('/manage/guestlist/manage/archive/needcheck/1/guest/1'));
            }
        }
        //$this->_view_table = $this->_table->getrow(null);
        $this->_view_table['data'] = array();
    }

    function edit_action()
    {
        $from = front::$from;
        front::check_type(front::get('id'));
        $this->manage->filter();
        $info = $this->_table->getrow(front::get('id'));
        if ($info['userid'] != $this->view->user['userid']) {
            front::flash(lang('record_change_failed_reason_unauthorized'));
            front::refUrl($from);
            //header("Location: " . $from, TRUE, 302);
            exit;
        }
        if ($info['checked']) {
            front::flash(lang('record_change_failed_reason_it_has_passed_the_audit'));
            front::refUrl($from);
            exit;
        }

        if (front::post('submit') && $this->manage->vaild()) {
            $this->manage->save_before();
            $data = array();
            $fieldlimit = $this->_table->getcols(front::$act == 'list' ? 'user_manage' : 'user_modify');
            //var_dump($fieldlimit);
            $fieldlimits = explode(',', $fieldlimit);
            foreach (front::$post as $key => $value) {
                if (preg_match('/(select|union|and|\'|"|\))/i', $value)) {
                    exit(lang('illegal_parameter'));
                }
                if (in_array($key, $fieldlimits))
                    $data[$key] = $value;
            }
            //var_dump($data);exit;
            $update = $this->_table->rec_update($data, front::get('id'));
            if ($update < 1) {
                front::flash(lang('record_add_failed'));
            } else {
                front::flash(lang('record_add_success'));
                $from = session::get('from');
                session::del('from');
                header("Location: " . $from, TRUE, 302);
                exit;
            }
        }
        if (!session::get('from')) session::set('from', front::$from);
        $this->_view_table = $this->_table->getrow(front::get('id'), '1', $this->_table->getcols('modify'));
    }

    function delete_action()
    {
        front::check_type(front::get('id'));
        $row = $this->_table->getrow(array('id' => front::get('id')));
        if ($row['mid'] != $this->view->user['userid']) {
            exit('no_permission');
        }
        $delete = $this->_table->rec_delete(front::get('id'));
        if ($delete) front::flash(lang('delete') . lang('success'));
        front::redirect(url::modify('act/list/manage/' . $this->table));
    }

    function view($table)
    {
        $this->view->data = $table['data'];
        $this->view->field = $table['field'];
    }

    function end()
    {
        if (!isset($this->_view_table)) return;
        if (!isset($this->_view_table['data']))
            $this->_view_table['data'] = $this->_view_table;
        $this->_view_table['field'] = $this->_table->getFields();
        $this->view->fieldlimit = $this->_table->getcols(front::$act == 'list' ? 'user_manage' : 'user_modify');
        $this->view($this->_view_table);
        manage_form::manage($this);
        if (front::$debug)
            $this->render('style/index.html');
        else
            $this->render();
    }
}