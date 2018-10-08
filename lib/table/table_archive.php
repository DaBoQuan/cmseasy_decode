<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class table_archive extends table_mode
{
    function view_before(&$data = null)
    {
        $data['pics'] = unserialize($data['pics']);
        //var_dump($data['pics']);
        /*if(is_array($pics) &&!empty($pics)) {
            foreach ($pics as $k => $v) {
                $data['pics' . $k] = $v;
            }
        }*/
        //$archive['pics'] = $v;
        //unset($data['pics']);
        $rank = new rank();
        $rank = $rank->getrow('aid=' . intval(front::get('id')));
        if (is_array($rank))
            $data['_ranks'] = unserialize($rank['ranks']);
        else $data['_ranks'] = array();
        unset($data['ranks']);
        if ($data['isecoding'] == '2' || ($data['isecoding'] == '0' && config::get('isecoding') == '0')) {
            $data['ecoding'] = '';
        }

        $linkword = new linkword();
        $linkwords = $linkword->getrows(null, 1000, 'linkorder desc');
        foreach ($linkwords as $linkword) {
            $linkword['linktimes'] = (int)$linkword['linktimes'];
            if (trim($linkword['linkurl']) && !preg_match('%^http://$%', trim($linkword['linkurl']))) {
                $link = "<a href='$linkword[linkurl]' target='_blank'>$linkword[linkword]</a>";
            } else {
                $link = "<a href='" . url('archive/search/keyword/' . urlencode($linkword['linkword'])) . "' target='_blank'>$linkword[linkword]</a>";
            }
            $data['content'] = _keylinks($data['content'], $linkword['linkword'], $link, $linkword['linktimes']);
        }


    }

    function vaild()
    {
        if (!front::post('title')) {
            front::flash('请填写标题！');
            return false;
        }
        if (!front::post('catid')) {
            front::flash('请选择分类！');
            return false;
        }
        return true;
    }

    function add_before(act $act = null)
    {
        front::$post['userid'] = $act->view->user['userid'];
        front::$post['username'] = $act->view->user['username'];
        if (empty(front::$post['author'])) {
            front::$post['author'] = $act->view->user['username'];
        }
        front::$post['checked'] = intval(front::$post['checked']);
        if (empty(front::$post['adddate'])) {
            front::$post['adddate'] = date('Y-m-d H:i:s');
        }
    }

    function save_before()
    {
        parent::save_before();

        //自定义字段允许HTML
        if (is_array(front::$post) && !empty(front::$post)) {
            foreach (front::$post as $k => $v) {
                if (preg_match('/^my_/is', $k)) {
                    front::$post[$k] = htmlspecialchars_decode(front::$post[$k]);
                }
            }
        }
        //var_dump(front::$post['content']);
        front::$post['content'] = stripslashes(front::$post['content']);
        //var_dump(front::$post['content']);
        front::$post['content'] = htmlspecialchars_decode(front::$post['content']);
        //var_dump(front::$post['content']);
        //exit;
        //var_dump(front::$post['content']);
        //exit;
        if (front::$post['htmlrule1'] != '') {
            front::$post['htmlrule'] = front::$post['htmlrule1'];
        }

        front::$post['strong'] = intval(front::$post['strong']);
        //var_dump(front::$post['pics']);exit;
        /*$pics = array();
        foreach(front::$post as $k =>$v) {
            if(preg_match('/pics(\d+)/i',$k,$out)) {
                if($v != ''){
                    $pics[$out[1]] = $v;
                }
                unset(front::$post[$k]);
            }
        }
        */

        front::$post['pics'] = serialize(front::$post['pics']);
        if (!front::post('attr1')) {
            front::$post['attr1'] = '';
        }
        front::$post['introduce_len'] = intval(front::$post['introduce_len']);
        if(!isset(front::$post['type'])){
            front::$post['type'] = '';
        }
        front::$post['province_id'] = 0;
        front::$post['city_id'] = 0;
        front::$post['section_id'] = 0;
        if (!front::$post['introduce']) {
            front::$post['introduce'] = cut(strip_tags(front::$post['content']), front::$post['introduce_len'] * 2);
        }

        if (front::$post['savehttppic']) {
            //front::$post['content'] = stripslashes(front::$post['content']);
			front::$post['content'] = stripcslashes(htmlspecialchars_decode(front::$post['content']));
            front::$post['content'] = preg_replace_callback('%(<img\s[^>|/>]*?src\s*=\s*["|\']?)([^"|\'|\s>]*)%is', 'savepic', front::$post['content']);
            //front::$post['content'] = addslashes(front::$post['content']);
			front::$post['content'] = stripcslashes(htmlspecialchars_decode(front::$post['content']));
        }


        //处理防伪码 只在新加时生成
        if (front::$get['act'] == 'add') {
            if (front::$post['isecoding'] == '1') {
                front::$post['ecoding'] = config::get('ecoding') . randomkeys(18);
            } else if (front::$post['isecoding'] == '0' && config::get('isecoding')) {
                front::$post['ecoding'] = config::get('ecoding') . randomkeys(18);
            }
        }

        //var_dump(front::$post);exit;

        //var_dump(front::$post['content']);exit;

        if (front::$post['autothumb']) {
            //front::$post['content'] = stripslashes(front::$post['content']);
			front::$post['content'] = stripcslashes(htmlspecialchars_decode(front::$post['content']));
            preg_match('%(<img\s[^>|/>]*?src\s*=\s*["|\']?)([^"|\'|\s>]*)%is', front::$post['content'], $out);
            $out[1] = '';
            //$out[2] = savepic1($out);
            if (!$out[2]) return;
            //front::$post['thumb'] = str_ireplace(config::get('site_url'),'',$out[2]);
            $len = 1;
            if (config::get('base_url') != '/') {
                $len = strlen(config::get('base_url')) + 1;
            }
            if (substr($out[2], 0, 4) == 'http') {
                front::$post['thumb'] = str_ireplace(config::get('site_url'), '', $out[2]);
            } else {
                front::$post['thumb'] = substr($out[2], $len);
            }
            $catid = front::get('catid');
            $thumb = new thumb();
            $thumb->set(front::$post['thumb'], 'file');
            front::$post['thumb'] = str_ireplace('.jpg', '_s.jpg', front::$post['thumb']);
            if ($catid)
                $thumb->create(front::$post['thumb'], category::getwidthofthumb($catid), category::getheightofthumb($catid));
            else
                $thumb->create(front::$post['thumb'], config::get('thumb_width'), config::get('thumb_height'));
            $sp = $len > 1 ? '/' : '';
            front::$post['thumb'] = config::get('base_url') . $sp . front::$post['thumb'];
            if (substr(front::$post['thumb'], 0, 1) != '/') {
                front::$post['thumb'] = '/' . front::$post['thumb'];
            }
            //front::$post['content'] = addslashes(front::$post['content']);
			front::$post['content'] = stripcslashes(htmlspecialchars_decode(front::$post['content']));
        }

        if(!front::$post['state']) front::$post['state']= 1;

        front::$post['listorder'] = intval(front::$post['listorder']);
    }

    function save_after($aid = '')
    {
        //$tag=preg_replace('/\s+/',' ',trim(front::$post['tag']));
        $tags = explode(',', trim(front::$post['tag']));
        //var_dump($tags);
        $tag_table = new tag();
        $arctag_table = new arctag();
        //var_dump($tags);
        foreach ($tags as $tag) {
            if ($tag && !$tag_table->getrow('tagname="' . $tag . '"')) {
                $tag_table->rec_insert(array('tagname' => $tag));
            }
            $tag = $tag_table->getrow('tagname="' . $tag . '"');
            $arctag_table->rec_replace(array('aid' => $aid, 'tagid' => $tag['tagid']));
        }
        //exit;
        $doit = false;
        if (session::get('attachment_id') || front::post('attachment_id')) {
            //var_dump($_SESSION);
            //var_dump($_POST);
            //var_dump($aid);
            //exit;
            $attachment_id = session::get('attachment_id') ? session::get('attachment_id') : front::post('attachment_id');
            $attachment = new attachment();
            $attachment->rec_update(array('aid' => $aid, 'intro' => front::post('attachment_intro')), array('id'=>$attachment_id));
            $doit = true;
            if (session::get('attachment_id')) session::del('attachment_id');
        }
        if (front::post('attachment_path') != '' && !$doit) {
            $attachment = new attachment();
            $attachment->rec_insert(array('aid' => $aid, 'path' => front::post('attachment_path'), 'intro' => front::post('attachment_intro'), 'adddate' => date('Y-m-d H:i:s')));
        }
        //exit;
        if (front::post('_ranks')) {
            $_ranks = serialize(front::post('_ranks'));
            $rank = new rank();
            if (is_array($rank->getrow(array('aid' => $aid))))
                $rank->rec_update(array('ranks' => $_ranks), 'aid=' . $aid);
            else
                $rank->rec_insert(array('aid' => $aid, 'ranks' => $_ranks));
        } else {
            $rank = new rank();
            $rank->rec_delete('aid=' . $aid);
        }
        if (front::post('vote')) {
            $votes = front::$post['vote'];
            $images = front::$post['vote_image'];
            $vote = new vote();
            $_vote = $vote->getrow('aid=' . $aid);
            if (!$_vote) $_vote = array('aid' => $aid);
            $_vote['titles'] = serialize($votes);
            $_vote['images'] = serialize($images);
            $vote->rec_replace($_vote, $aid);
            //var_dump($_vote);exit;
        }
    }

    function delete_before($id = '')
    {
        $arc = new archive();
        $info = $arc->getrow($id);
        $attachment = new attachment();
        $res = $attachment->getrows(array("aid" => $id));
        if (is_array($res) && !empty($res)) {
            foreach ($res as $v) {
                @unlink($v['path']);
            }
        }

        if (category::getarcishtml($info)) {
            $path = ROOT . preg_replace("%" . THIS_URL . "[\\/]%", '', archive::url($info));
            if (file_exists($path)) unlink($path);
        }

    }
}