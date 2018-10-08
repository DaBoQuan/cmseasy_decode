<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class vote_act extends act
{
    function init()
    {
        if (cookie::get('login_username') && cookie::get('login_password')) {
            $user = new user();
            $user = $user->getrow(array('username' => cookie::get('login_username')));
            if (is_array($user) && cookie::get('login_password') == front::cookie_encode($user['password'])) {
                $this->view->user = $user;
                $this->view->usergroupid = $user['groupid'];
            }
        } else $this->view->usergroupid = 0;
    }

    public function list_action()
    {
        $ballot = new ballot();
        $rows = $ballot->getrows(null,0,'id desc');
        if(is_array($rows) && !empty($rows)){
            $i = 0;
            foreach ($rows as $r){
                $res = true;
                if($r['resgroupid']){
                    $votegroup = explode(',',$r['resgroupid']);
                    if(!in_array($this->view->usergroupid,$votegroup)){
                        $res = false;
                    }
                }
                if(!$res) unset($rows[$i]['num']);

                if($r['viewgroupid']){
                    $votegroup = explode(',',$r['viewgroupid']);
                    if(!in_array($this->view->usergroupid,$votegroup)){
                        unset($rows[$i]);
                    }
                }

                $i++;
            }
        }
        $this->view(array('data'=>$rows));
        $this->render();
    }

    function do_action()
    {
        if (front::post('submit') && front::post('vote') && front::post('aid')) {
            front::check_type(front::post('aid'));
            if (!isset($this->view->user)) front::flash(lang('not_logged'));
            $vote = new vote();
            $_vote = $vote->getrow('aid=' . front::post('aid'));
            if (preg_match('/' . $this->view->user['username'] . ',/i', $_vote['users'])) {
                front::flash(lang('no_repeat_the_vote'));
                front::redirect(front::$from);
            }
            $_votes = $_vote['votes'];
            if (!$_votes) $_votes = array();
            else $_votes = unserialize($_votes);
            $_votes[front::post('vote')] = $_votes[front::post('vote')] + 1;
            $votes = serialize($_votes);
            $vote_data = array_merge($_vote, array('votes' => $votes, 'aid' => front::post('aid'), 'users' => $_vote['users'] . $this->view->user['username'] . ','));
            $vote->rec_replace($vote_data, front::post('aid'));
            front::flash(lang('successful_vote'));
        } else {
            front::flash(lang('vote_failed'));
        }
        front::redirect(front::$from);
    }

    function view_action()
    {
        //exit;
        //$class = new ReflectionClass('vote');
        //var_dump($class);exit;
        $this->view->aid = front::get('aid');
        //echo $this->fetch();
        echo tool::text_javascript($this->fetch());
    }

    function view($table)
    {
        $this->view->data = $table['data'];
    }

    function show_action()
    {
        $this->render();
    }
}