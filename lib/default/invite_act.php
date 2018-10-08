<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class invite_act  extends act {

    public $db_invite = null;

    function init(){
        //var_dump(front::$user);
        if(!front::$user){
            front::redirect(url::create('user/login'));
        }
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
            $num = 1;
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
                    /*if(front::$post['ctname']){
                        $data['ctname'] = front::$post['ctname'];
                    }else{
                        $data['ctname'] = front::$user['username'];
                    }*/
                    $data['ctname'] = front::$user['username'];
                    $this->db_invite->rec_insert($data);
                    $i++;
                }
            }
        }
        front::refresh(url('manage/invitelist/manage/invite'));
        exit;
    }

    function del_action(){
        $id = intval(front::$get['id']);
        $row = $this->db_invite->getrow($id);
        if($row['ctname'] == front::$user['username'] && $this->db_invite->rec_delete($id)){
            front::refresh(url('manage/invitelist/manage/invite'));
        }else{
            alerterror('删除失败');
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
?>