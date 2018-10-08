<?php 

class weixinmenu_admin extends admin{

    public $db_weixinmenu = null;

    function init(){
        $this->db_weixinmenu = new weixinmenu();
    }

    function end() {
        $this->render('index.php');
    }

    function list_action(){
        if (front::post('submit')) {
            if(is_array(front::$post['sort']) && !empty(front::$post['sort'])){
                foreach (front::$post['sort'] as $id => $val) {
                    $this->db_weixinmenu->rec_update(array('sort'=>$val),$id);
                }

            }
            if(is_array(front::$post['name']) && !empty(front::$post['name'])){
                foreach (front::$post['name'] as $id => $val) {
                    $this->db_weixinmenu->rec_update(array('name'=>$val),$id);
                }

            }
            alertinfo('保存成功',url('weixinmenu/list/id/'.front::$post['wid'], true));
        }
        $wid = intval(front::$get['id']);
        $where = array('wid'=>$wid,'pid'=>0);
        $ordre='sort=0,`sort` ASC';
        $this->view->data = $this->db_weixinmenu->getrows($where,'',$ordre,'*');
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
            if($this->db_weixinmenu->rec_insert($post)) {
                //$id = $this->db_weixinmenu->insert_id();
                front::refresh(url('weixinmenu/list/id/'.$post['wid'], true));
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
            if($this->db_weixinmenu->rec_update($post,intval($post['id']))) {
                //$id = $this->db_weixinmenu->insert_id();
                front::refresh(url('weixinmenu/list/id/'.$post['wid'], true));
            }else{
                alerterror('修改失败');
            }
        }
        $id = intval(front::$get['id']);
        $this->view->data = $this->db_weixinmenu->getrow($id);
    }

    function del_action(){
        $id = intval(front::$get['id']);
        $wid = intval(front::$get['wid']);
        $this->db_weixinmenu->rec_delete(array('pid'=>$id));
        if($this->db_weixinmenu->rec_delete($id)){
            front::refresh(url('weixinmenu/list/id/'.$wid, true));
        }else{
            alerterror('删除失败');
        }
    }

    function hasSub($id){
        $num = $this->db_weixinmenu->rec_count("pid='$id'");
        return $num;
    }

    function getKey($arr){
        return 'KEY'.$arr['id'];
    }

    function push_action(){
        $wid = intval(front::$get['wid']);
        $row = $this->db_weixinmenu->getrows(array('wid'=>$wid,'pid'=>0),'','sort=0,sort asc');
        $buttons = array();
        if(is_array($row) && !empty($row)){
            foreach($row as $arr){
                //var_dump($arr);exit;
                if($arr['pid'] == 0){ //如果是一级菜单
                    if($arr['typeid'] == 1 && $this->hasSub($arr['id'])){ //如果类型是菜单并且有子菜单
                        $buttons['button'][$arr['id']]['name'] = $arr['name'];
                        $row2 = $this->db_weixinmenu->getrows(array('wid'=>$wid,'pid'=>$arr['id']),'','sort=0,sort asc');
                        if(is_array($row2) && !empty($row2)){
                            foreach($row2 as $arr2){
                                $buttons['button'][$arr['id']]['sub_button'][$arr2['id']]['name'] = $arr2['name'];
                                if($arr2['typeid'] == 2){
                                    $buttons['button'][$arr['id']]['sub_button'][$arr2['id']]['url'] = $arr2['url'];
                                    $buttons['button'][$arr['id']]['sub_button'][$arr2['id']]['type'] = 'view';
                                }else {
                                    $buttons['button'][$arr['id']]['sub_button'][$arr2['id']]['key'] = $this->getKey($arr2);
                                    $buttons['button'][$arr['id']]['sub_button'][$arr2['id']]['type'] = 'click';
                                }
                            }
                        }
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
                    /*$row2 = $this->db_weixinmenu->getrows(array('wid'=>$wid,'pid'=>$arr['pid']),'','sort=0,sort asc');
                    $buttons['button'][$arr['pid']]['sub_button'][$arr['id']]['name'] = $arr['name'];
                    if($arr['typeid'] == 2){
                        $buttons['button'][$arr['pid']]['sub_button'][$arr['id']]['url'] = $arr['url'];
                        $buttons['button'][$arr['pid']]['sub_button'][$arr['id']]['type'] = 'view';
                    }else {
                        $buttons['button'][$arr['pid']]['sub_button'][$arr['id']]['key'] = $this->getKey($arr);
                        $buttons['button'][$arr['pid']]['sub_button'][$arr['id']]['type'] = 'click';
                    }*/
                }
            }
        }
        //var_dump($row);
        //var_dump($buttons);exit();
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
        //var_dump($data['body']);exit;
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