<?php 


if (!defined('ROOT'))
    exit('Can\'t Access !');
class invite_admin extends admin{

    public $db_invite = null;

    function init(){
        $this->db_invite = invite::getInstance();
    }

    function generatePassword($length = 8) {
        $possibleChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $password = '';

        for($i = 0; $i < $length; $i++) {
            $rand = rand(0, strlen($possibleChars) - 1);
            $password .= substr($possibleChars, $rand, 1);
        }

        return $password;
    }

    function checkInvite($invite){
        return $this->db_invite->rec_count("invitecode = '$invite'");
    }


    function add_action(){
        if (front::post('num')) {
            $num = intval(front::$post['num']);
            if($num > 0){
                $i = 0;
                while($i < $num){
                    $invite = $this->generatePassword();
                    if($this->checkInvite($invite)){
                        continue;
                    }
                    $data = array(
                        'invitecode' => $invite,
                        'ctuid' => front::$user['userid'],
                        'cttime' => date('Y-m-d H:i:s'),
                        'isuse' => '0',
                    );
                    if(front::$post['ctname']){
                        $data['ctname'] = front::$post['ctname'];
                    }else{
                        $data['ctname'] = front::$user['username'];
                    }
                    $this->db_invite->rec_insert($data);
                    $i++;
                }
            }
        }
        front::refresh(url('invite/list', true));
        exit;
    }

    function add2_action(){
        $id = intval(front::$get['id']);
        $data = $this->db_weixin->getrow($id);
        $this->view->data = $data;
    }

    function add3_action(){
        if (front::post('submit')) {
            $id = intval(front::$post['id']);
            $post['appid'] = front::$post['appid'];
            $post['appsecret'] = front::$post['appsecret'];
            $post['name'] = front::$post['name'];
            $post['oldid'] = front::$post['oldid'];
            $post['weixinid'] = front::$post['weixinid'];
            if($this->db_weixin->rec_update($post,$id)){
                front::refresh(url('weixin/list', true));
            }else{
                alerterror('保存失败');
            }
        }
        $id = intval(front::$get['id']);
        $row = $this->db_weixin->getrow($id);
        $this->view->data = $row;
    }

    function chktest_action(){
        $id = intval(front::$get['id']);
        $row = $this->db_weixin->getrow($id);
        echo $row['checksuc'];
        exit;
    }

    function list_action(){
        $where = '';
        $ordre='inviteid DESC';
        //$this->view->data = $this->db_invite->getrows($where,'',$ordre,'*');
        $prefix = config::get('database', 'prefix');
        $this->view->data = $this->db_invite->rec_query("SELECT t1.*,t3.username as regname FROM {$prefix}invite t1 LEFT JOIN {$prefix}user t3 ON t1.reguid=t3.userid ORDER BY inviteid DESC");

        //var_dump($this->view->data);
        //var_dump($this->view->data);
    }

    function batch_action(){
        if (front::post('batch') && front::post('select')) {
            $select = implode(',', front::post('select'));
            $select = 'inviteid in (' . $select . ')';
            if (front::post('batch') == 'delete') {
                $delete = $this->db_invite->rec_delete($select);
                if ($delete > 0) {
                    front::flash("成功删除！");
                    event::log("删除邀请码", '成功 id=' . implode(',', front::post('select')));
                } else
                    front::flash("没有记录被删除！");
            }
        }
        front::redirect(front::$from);
    }

    function del_action(){
        $id = intval(front::$get['id']);
        if($this->db_invite->rec_delete($id)){
            front::refresh(url('invite/list/', true));
        }else{
            alerterror('删除失败');
        }
    }

    function end() {
        $this->render('index.php');
    }
}