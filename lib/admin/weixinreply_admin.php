<?php 

class weixinreply_admin extends admin{

    public $db_weixinreply = null;

    function init(){
        $this->db_weixinreply = new weixinreply();
    }

    function end() {
        $this->render('index.php');
    }

    function list_action(){
        $wid = intval(front::$get['id']);
        $where = "wid='$wid' AND id!=1 AND id!=2";
        $ordre='id asc';
        $this->view->data = $this->db_weixinreply->getrows($where,'',$ordre,'*');
    }


    function added_action(){
        if (front::post('submit')) {
            $post = front::$post;
            $post['addtime'] = date('Y-m-d H:i:s');
            $post['msgtype'] = 1;
            $post['id'] = 1;
            //var_dump(front::$post);exit;
            $imgtext = "";
            if(is_array(front::$post['twname']) && !empty(front::$post['twname'])){
                $i = 0;
                foreach(front::$post['twname'] as $twname){
                    if(!$twname) continue;
                    $imgtext .= $twname.'*'.front::$post['twurl'][$i].'*'.front::$post['pic'][$i].'|';
                    $i++;
                }
            }
            if($imgtext) $post['imgtext'] = substr($imgtext,0,-1);
            if($this->db_weixinreply->rec_replace($post)) {
                //$id = $this->db_weixinmenu->insert_id();
                front::refresh(url('weixinreply/added/wid/'.$post['wid'], true));
            }else{
                alerterror('添加失败');
            }
        }
        $wid = intval(front::$get['wid']);
        $row = $this->db_weixinreply->getrow(array('wid'=>$wid,'msgtype'=>1));
        $this->view->data = $row;
    }


    function msged_action(){
        if (front::post('submit')) {
            $post = front::$post;
            $post['addtime'] = date('Y-m-d H:i:s');
            $post['msgtype'] = 2;
            $post['id'] = 2;
            //var_dump(front::$post);exit;
            $imgtext = "";
            if(is_array(front::$post['twname']) && !empty(front::$post['twname'])){
                $i = 0;
                foreach(front::$post['twname'] as $twname){
                    if(!$twname) continue;
                    $imgtext .= $twname.'*'.front::$post['twurl'][$i].'*'.front::$post['pic'][$i].'|';
                    $i++;
                }
            }
            if($imgtext) $post['imgtext'] = substr($imgtext,0,-1);
            if($this->db_weixinreply->rec_replace($post)) {
                //$id = $this->db_weixinmenu->insert_id();
                front::refresh(url('weixinreply/msged/wid/'.$post['wid'], true));
            }else{
                alerterror('添加失败');
            }
        }
        $wid = intval(front::$get['wid']);
        $row = $this->db_weixinreply->getrow(array('wid'=>$wid,'msgtype'=>2));
        $this->view->data = $row;
    }

    function addtuwen_action(){
        $num = intval(front::$post['num']);
        include('data/weixinimg.php');
        exit;
    }

    function getsubmenu($pid){
        $where = array('pid'=>$pid);
        $ordre='sort=0,`sort` ASC';
        return $this->db_weixinmenu->getrows($where,'',$ordre,'*');
    }

    function add_action(){
        if (front::post('submit')) {
            $post = front::$post;
            $post['addtime'] = date('Y-m-d H:i:s');
            $post['msgtype'] = 3;
            $imgtext = "";
            if(is_array(front::$post['twname']) && !empty(front::$post['twname'])){
                $i = 0;
                foreach(front::$post['twname'] as $twname){
                    if(!$twname) continue;
                    $imgtext .= $twname.'*'.front::$post['twurl'][$i].'*'.front::$post['pic'][$i].'|';
                    $i++;
                }
            }
            if($imgtext) $post['imgtext'] = substr($imgtext,0,-1);
            if($this->db_weixinreply->rec_insert($post)) {
                //$id = $this->db_weixinmenu->insert_id();
                front::refresh(url('weixinreply/list/id/'.$post['wid'], true));
            }else{
                alerterror('添加失败');
            }
        }
    }

    function edit_action(){
        if (front::post('submit')) {
            $post = front::$post;
            //var_dump(front::$post);exit;
            $imgtext = "";
            if(is_array(front::$post['twname']) && !empty(front::$post['twname'])){
                $i = 0;
                foreach(front::$post['twname'] as $twname){
                    if(!$twname) continue;
                    $imgtext .= $twname.'*'.front::$post['twurl'][$i].'*'.front::$post['pic'][$i].'|';
                    $i++;
                }
            }
            if($imgtext) $post['imgtext'] = substr($imgtext,0,-1);
            if($this->db_weixinreply->rec_update($post,intval($post['id']))) {
                //$id = $this->db_weixinmenu->insert_id();
                front::refresh(url('weixinreply/list/id/'.$post['wid'], true));
            }else{
                alerterror('修改失败');
            }
        }
        $id = intval(front::$get['id']);
        $this->view->data = $this->db_weixinreply->getrow($id);
    }

    function del_action(){
        $id = intval(front::$get['id']);
        $wid = intval(front::$get['wid']);
        if($this->db_weixinreply->rec_delete($id)){
            front::refresh(url('weixinreply/list/id/'.$wid, true));
        }else{
            alerterror('删除失败');
        }
    }

    function hasSub($id){
        $num = $this->db_weixinmenu->rec_count("pid='$id'");
        return $num;
    }

    function getKey($arr){
        return 'V1001_'.$arr['id'];
    }

    function push_action(){
        $wid = intval(front::$get['wid']);
        $row = $this->db_weixinmenu->getrows(array('wid'=>$wid),'','sort=0,sort asc');
        $buttons = array();
        if(is_array($row) && !empty($row)){
            foreach($row as $arr){
                //var_dump($arr);exit;
                if($arr['pid'] == 0){ //如果是一级菜单
                    if($arr['typeid'] == 1 && $this->hasSub($arr['id'])){ //如果类型是菜单并且有子菜单
                        $buttons['button'][$arr['id']]['name'] = $arr['name'];
                        //var_dump($buttons);exit;
                    }else{ //如果没有子菜单

                        $buttons['button'][$arr['id']]['name'] = $arr['name'];
                        if($arr['typeid'] == 2){
                            $buttons['button'][$arr['id']]['url'] = $arr['url'];
                            $buttons['button'][$arr['id']]['type'] = 'view';
                        }else {
                            $buttons['button'][$arr['id']]['key'] = $this->getKey($arr);
                            $buttons['button'][$arr['id']]['type'] = 'click';
                        }
                    }
                }else{
                    $buttons['button'][$arr['pid']]['sub_button'][$arr['id']]['name'] = $arr['name'];
                    if($arr['typeid'] == 2){
                        $buttons['button'][$arr['pid']]['sub_button'][$arr['id']]['url'] = $arr['url'];
                        $buttons['button'][$arr['pid']]['sub_button'][$arr['id']]['type'] = 'view';
                    }else {
                        $buttons['button'][$arr['pid']]['sub_button'][$arr['id']]['key'] = $this->getKey($arr);
                        $buttons['button'][$arr['pid']]['sub_button'][$arr['id']]['type'] = 'click';
                    }
                }
            }
        }
        //var_dump($row);
        //var_dump($buttons);
        $buttons['button'] = array_merge($buttons['button']);
        if(is_array($buttons['button']) && !empty($buttons['button'])){
            foreach($buttons['button'] as $k => $tmp){
                if(is_array($tmp['sub_button']) && !empty($tmp['sub_button'])){
                    $buttons['button'][$k]['sub_button'] = array_merge($tmp['sub_button']);
                }
            }
        }
        $data['body'] = json_encode($buttons);
        $data['body'] = preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $data['body']);
        //var_dump($data['body']);
        $weixin = new weixin();
        $access_token = $weixin->getAccessToken($wid);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
        $res = $weixin->PostJsonData($url,$data['body']);
        //var_dump($res);
        if($res['errcode']){
            alerterror($res['errmsg']);
        }else{
            alertinfo('发布成功',url('weixinmenu/list/id/'.$wid, true));
        }
        exit;
    }

}