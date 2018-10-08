<?php 

class ballot_act extends act
{
    function init()
    {
        $this->table = 'ballot';
        $this->_table = new $this->table;
    }

    function index_action()
    {
        if (front::post('submit')) {
            if (!front::post('ballot')) {
                front::alert(lang('Please_select_vote'));
                return false;
            }
            /*if (config::get('checkip')) {
                $time = cookie::get('vttime');
                if (time() - $time < config::get('timer') * 60) {
                    front::alert(lang('You_have_voted'));
                    return false;
                }
            }*/
            $bid = intval(front::$post['bid']);
            $ballot = new ballot();
            $row = $ballot->getrow($bid);

            if($row['enddate'] && date('Y-m-d') >= $row['enddate']){
                alerterror('投票已经截止！');
            }

            if($row['votegroupid']){
                $votegroup = explode(',',$row['votegroupid']);
                if(!in_array($this->view->usergroupid,$votegroup)){
                    alerterror('你无权进行投票！');
                }
            }

            $votelogs = votelogs::getInstance();

            if(config::get('vote_onlyone')){
                $count = $votelogs->rec_count(array('uid'=>$this->view->userid,'bid'=>$bid));
                if($count > 0){
                    alerterror('每个会员每个投票只能表决一次');
                }
            }
            if (is_array(front::$post['ballot'])) {
                $ids = implode(',', front::$post['ballot']);
            } else {
                $ids = front::$post['ballot'];
            }
            if (preg_match('/(select|union|and|\'|"|\))/i', $ids)) {
                exit(lang('illegal_parameter'));
            }
            $where = "id in($ids)";
            $data = 'num=num+1';
            $option = new option();
            $option->rec_update($data, $where);
            $this->_table->rec_update($data, $bid);

            $votelogs->save(array(
                'uid' => $this->view->userid,
                'username' => $this->view->username,
                'bid' => $bid,
                'oid' => $ids,
            ));
            cookie::set('vttime', time(), time() + 3600 * 24);
            front::alert(lang('Successful_vote'));
        }
    }

    function show_action()
    {
        $id = intval(front::get('id'));
        $ballot = new ballot();
        $option = new option();
        $where = array('id' => $id);
        $arr = $ballot->getrow($where);
        if($arr['viewgroupid']){
            $votegroup = explode(',',$arr['viewgroupid']);
            if(!in_array($this->view->usergroupid,$votegroup)){
                alertinfo('你无权查看该投票！',url('vote/list'));
            }
        }
        $res = true;
        if($arr['resgroupid']){
            $votegroup = explode(',',$arr['resgroupid']);
            if(!in_array($this->view->usergroupid,$votegroup)){
                $res = false;
            }
        }
        $row = $option->getrows(array('bid' => $id), null, 'num desc');
        if(is_array($row) && !empty($row)){
            $i = 0;
            foreach ($row as $r){
                if(!$res) unset($row[$i]['num']);
                $i++;
            }
        }
        $this->view->arr = $arr;
        $this->view->row = $row;
    }

    function getjs_action()
    {
        $lang = include ROOT . '/lang/' . config::get('lang_type') . '/system.php';
        $id = front::get('id');
        if (preg_match('/select/i', $id)) {
            exit(lang('illegal_parameter'));
        }
        $ballot = new ballot();
        $option = new option();
        $where = array('id' => $id);
        $arr = $ballot->getrow($where);
        $row = $option->getrows(array('bid' => $id), null, 'num desc');
        $this->view->arr = $arr;
        $this->view->row = $row;
        $this->view->lang = $lang;
        /*$html='document.write(\'<form name="form1" method="post" action="'.url("ballot").'">\');';
        $html .= 'document.write(\'<input type="hidden" name="bid" id="bid" value="'.$arr['id'].'" />\');';
		$html .= 'document.write(\'<h5>\');';
        $html .= 'document.write(\''.$arr['title']."</h5>');";
        foreach ($row as $option) {
            if ($arr['type'] == 'radio') {
                $html .= 'document.write(\'<input type="radio" name="ballot" id="ballot" value="'.$option['id'].'" />\');';
            }
            else {
                $html .= 'document.write(\'<input type="checkbox" name="ballot[]" id="ballot" value="'.$option['id'].'" />\');';
            }
            $html .= 'document.write(\' '.$option['name'].' ('.$option['num'].')<br>\');';
        }
        $html .= 'document.write(\'<input type="submit" name="submit" id="button" value=" '.$lang['vote'].'" /></form>\');';
        echo $html;*/
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