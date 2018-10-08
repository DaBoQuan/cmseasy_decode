<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');

class table_admin extends admin
{
    protected $_table;

    function init()
    {
        $this->table = front::get('table');
        if (preg_match('/^my_/', $this->table)) {
            //form_admin::init();
            $this->_table = new defind($this->table);
        } else
            $this->_table = new $this->table;
        $this->_table->getFields();
        $this->view->form = $this->_table->get_form();
        $this->tname = lang($this->table);
        if ($this->table == 'orders')
            $this->tname = '订单';
        if ($this->table == 'archive') {
            $this->tname = '内容';
            session::set('modname', '内容管理');
        }
        if ($this->table == 'user')
            $this->tname = '用户';
        if ($this->table == 'usergroup')
            $this->tname = '用户组';
        if ($this->table == 'announcement')
            $this->tname = '公告';
        if ($this->table == 'guestbook')
            $this->tname = '留言';
        if ($this->table == 'ballot')
            $this->tname = '投票';
        if ($this->table == 'option')
            $this->tname = '投票选项';
        if ($this->table == 'linkword')
            $this->tname = '内链';
        $this->_pagesize = config::get('manage_pagesize');
        $this->view->table = $this->table;
        $this->view->primary_key = $this->_table->primary_key;
        if (!front::get('page'))
            front::$get['page'] = 1;
        $this->Exc = $this->table == 'templatetag'
            || $this->table == 'templatetagwap'
            || $this->table == 'archive'
            || $this->table == 'announcement'
            || $this->table == 'category'
            || $this->table == 'type'
            || $this->table == 'special' ? true : false;
        $manage = 'table_' . $this->table;
        if (preg_match('/^my_/', $this->table))
            $manage = 'table_form';
        $this->manage = new $manage;
    }

    function list_action()
    {
        $set1 = settings::getInstance();
        $sets1 = $set1->getrow(array('tag' => 'table-' . $this->table));
        $setsdata1 = unserialize($sets1['value']);
        $this->view->settings = $setsdata1;
        $where = null;
        $ordre = '`id` DESC';
        if (preg_match('/^special$/', $this->table)) {
            $ordre = "listorder='0',`listorder` asc,`adddate` DESC";
        }
        if (preg_match('/^archive$/', $this->table)) {
            $ordre = "toppost DESC,listorder=0,listorder ASC,adddate DESC";
            $ordre = "adddate DESC";
        }
        if (preg_match('/^type|category$/', $this->table)) {
            $ordre = "listorder=0,listorder asc";
        }
        if (preg_match('/^user$/', $this->table)) {
            $ordre = '`userid` DESC';
        }
        if (preg_match('/^usergroup$/', $this->table)) {
            $ordre = '`groupid` DESC';
        }
        if (preg_match('/^my_/', $this->table)) {
            $ordre = '`fid` DESC';
        }

        if ($this->table == 'archive') {
            session::set('actname', '内容列表');
            $where = $this->_table->get_where('manage');
            if (!front::post('search_catid') && front::get('type') != 'search')
                session::del('search_catid');
            if (get('search_catid')) {
                $catid = get('search_catid');
                session::set('search_catid', $catid);
                $this->category = category::getInstance();
                $categories = $this->category->sons($catid);
                $categories[] = $catid;
                $where .= ' and catid in(' . trim(implode(',', $categories), ',') . ')';
            }
            if (get('catid')) {
                $catid = get('catid');
                $where .= ' and catid=' . $catid;
            }
            if (!front::post('search_typeid') && front::get('type') != 'search')
                session::del('search_typeid');
            if (get('search_typeid')) {
                $typeid = get('search_typeid');
                session::set('search_typeid', $typeid);
                $this->type = type::getInstance();
                $types = $this->type->sons($typeid);
                $types[] = $typeid;
                $where .= ' and typeid in(' . trim(implode(',', $types), ',') . ')';
            }
            if (get('typeid')) {
                $typeid = get('typeid');
                $where .= ' and typeid=' . $typeid;
            }
            if (!front::post('search_title') && front::get('type') != 'search')
                session::del('search_title');
            if (get('search_title')) {
                $title = get('search_title');
                session::set('search_title', $title);
                $where .= " and title like '%$title%' ";
            }
            if (!front::post('search_province_id') && front::get('type') != 'search')
                session::del('search_province_id');
            if (get('search_province_id')) {
                $proid = get('search_province_id');
                session::set('search_province_id', $proid);
                $where .= ' and province_id=' . $proid;
            }
            if (!front::post('search_city_id') && front::get('type') != 'search')
                session::del('search_city_id');
            if (get('search_city_id')) {
                $cityid = get('search_city_id');
                session::set('search_city_id', $cityid);
                $where .= ' and city_id=' . $cityid;
            }
            if (!front::post('search_section_id') && front::get('type') != 'search')
                session::del('search_section_id');
            if (get('search_section_id')) {
                $sectionid = get('search_section_id');
                session::set('search_section_id', $sectionid);
                $where .= ' and section_id=' . $sectionid;
            }
            if (!front::post('search_spid') && front::get('type') != 'search')
                session::del('search_spid');
            if (get('search_spid')) {
                $sectionid = get('search_spid');
                session::set('search_spid', $sectionid);
                $where .= ' and spid=' . $sectionid;
            }
            if (!front::post('search_userid') && front::get('type') != 'search')
                session::del('search_userid');
            if (get('search_userid')) {
                $sectionid = get('search_userid');
                session::set('search_userid', $sectionid);
                $where .= ' and userid=' . $sectionid;
            }
        }

        if ($this->table == 'templatetag') {
            if (front::get('tagfrom')) {
                $where = "tagfrom='" . front::get('tagfrom') . "'";
            } else
                $where = "tagfrom='define'";
            $where .= " and (`tagvar` IS NULL OR `tagvar` = '') ";
        }
        if ($this->table == 'templatetagwap') {
            if (front::get('tagfrom')) {
                $where = "tagfrom='" . front::get('tagfrom') . "'";
            } else
                $where = "tagfrom='define'";
            $where .= " and (`tagvar` IS NULL OR `tagvar` = '') ";
        }
        if ($this->table == 'option') {
            $ballot = new ballot();
            $where = array('bid' => front::$get['bid']);
            session::set('bid', front::$get['bid']);
            $row = $ballot->getrow(array('id' => front::$get['bid']));
            $this->view->ballot = $row;
        }
        if ($this->table == 'tag') {
            $ordre = "tagid DESC";
        }
        if (get('spid')) {
            $sp = new special();
            $sp = $sp->getrow('spid=' . get('spid'));
            $this->view->special = $sp;
        }
        $limit = ((front::get('page') - 1) * $this->_pagesize) . ',' . $this->_pagesize;
        if ($this->table == 'category' || $this->table == 'type') {
            $where .= " `parentid`='0' ";
        }

        if ($this->table == 'user') {
            $where .= "groupid > ".front::$user['groupid'];
            if (front::$get['type'] == 'search' && front::$post['search_title']) {
                $where .= front::$post['field'] . '="' . front::$post['search_title'] . '"';
            }
        }
        if ($this->table == 'comment') {
            if (front::get('uid'))
                $where = "userid='" . front::get('uid') . "'";
        }
        if ($this->table == 'zanlog') {
            if (front::get('uid'))
                $where = "uid='" . front::get('uid') . "'";
            $ordre = 'zlid DESC';
        }
        $this->_view_table = $this->_table->getrows($where, $limit, $ordre, $this->_table->getcols('manage'));
        //echo $this->_table->sql;
        //var_dump($this->_view_table);
        //print_r($this->_view_table);exit();
        $this->view->search_title = front::$post['search_title'];
        $this->view->record_count = $this->_table->record_count;
        $this->view->token = Phpox_token::grante_token('table_del');
    }

    function addballot_action()
    {
        $this->render('dialog/addballot.php');
    }

    function import_action()
    {
        $this->_view_table = '';
        if (front::post('submit')) {
            if ($_FILES['excelFile']['tmp_name']) {
                $upload = new upload();
                $upload->dir = 'attachment';
                if (!$_FILES['excelFile']['name'] || !preg_match('/\.xls$/i', $_FILES['excelFile']['name'])) {
                    alerterror('请选择Excel2003文件');
                }
                $name = $upload->run($_FILES['excelFile']);
                $reader = PHPExcel_IOFactory::createReader('Excel5');
                $PHPExcel = $reader->load($name);
                $sheet = $PHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow();
                $highestColumm = $sheet->getHighestColumn();
                $i = 0;
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($sheet->getCell('A' . $row)->getValue()) {
                        $data['catid'] = $sheet->getCell('A' . $row)->getValue();
                        $data['typeid'] = intval($sheet->getCell('B' . $row)->getValue());
                        $data['spid'] = intval($sheet->getCell('C' . $row)->getValue());
                        $data['title'] = $sheet->getCell('D' . $row)->getValue();
                        $data['content'] = $sheet->getCell('E' . $row)->getValue();
                        $data['introduce'] = $sheet->getCell('F' . $row)->getValue();
                        $data['tag'] = $sheet->getCell('G' . $row)->getValue();
                        $data['adddate'] = $sheet->getCell('H' . $row)->getValue();
                        if ($data['adddate'] == '') {
                            $data['adddate'] = date('Y-m-d H:i:s');
                        }
                        $data['author'] = $sheet->getCell('I' . $row)->getValue();
                        $data['attr3'] = $sheet->getCell('J' . $row)->getValue();
                        $data['checked'] = intval($sheet->getCell('K' . $row)->getValue());
                        $data['attr2'] = $sheet->getCell('L' . $row)->getValue();
                        $data['thumb'] = $sheet->getCell('M' . $row)->getValue();
                        $a = explode('|', $sheet->getCell('N' . $row)->getValue());
                        if (is_array($a) && !empty($a)) {
                            $c = array();
                            $i = 0;
                            foreach ($a as $b) {
                                $c[$i]['url'] = $b;
                                $i++;
                            }
                            $data['pics'] = serialize($c);
                        } else {
                            $data['pics'] = '';
                        }

                        $data['userid'] = $this->view->user['userid'];
                        $this->_table->rec_insert($data);
                        $i++;
                    }
                }
                front::flash("{$this->tname}导入数据成功！");
            } else {
                alerterror('请选择要导入的Excel2003文件');
            }
        }
    }

    function add_action()
    {
        if ($this->table == 'category') {
            chkpw('category_add');
        }
        if ($this->table == 'archive') {
            chkpw('archive_add');
            session::set('actname', '添加内容');
        }
        if ($this->table == 'type') {
            chkpw('type_add');
            front::$post['typecontent'] = htmlspecialchars_decode(front::$post['typecontent']);
        }
        if ($this->table == 'special') {
            chkpw('special_add');
            front::$post['description'] = htmlspecialchars_decode(front::$post['description']);
        }
        if ($this->table == 'user') {
            chkpw('user_add');
        }
        if ($this->table == 'usergroup') {
            chkpw('usergroup_add');
        }
        if ($this->table == 'ballot') {
            chkpw('func_ballot_add');
        }
        if ($this->table == 'announcement') {
            chkpw('func_announc_add');
            front::$post['content'] = htmlspecialchars_decode(front::$post['content']);
        }
        if ($this->table == 'templatetag' && front::get('tagfrom') == 'define') {
            chkpw('templatetag_add_define');
        }
        if ($this->table == 'templatetag' && front::get('tagfrom') == 'category') {
            chkpw('templatetag_add_category');
        }
        if ($this->table == 'templatetag' && front::get('tagfrom') == 'content') {
            chkpw('templatetag_add_content');
        }
        if ($this->table == 'linkword') {
            chkpw('seo_linkword_add');
        }
        if ($this->table == 'friendlink') {
            chkpw('seo_friendlink_add');
        }

        //用户异步提取图库图片
        if (front::$get['ajax']) {
            front::$get['dir'] = front::$get['ajax'];
            $img_arr = image_admin::listimg_action();
            foreach ($img_arr as $v) {
                echo '<img src="upload/images/' . front::$get['dir'] . '/' . $v . '" id="img' . str_replace('.', '', $v) . '" onClick="select_img(\'img' . str_replace('.', '', $v) . '\');" />';
            }
            exit();
        }


        if (front::post('submit') && $this->manage->vaild()) {
            $this->manage->filter($this->Exc);
            $this->manage->add_before($this);
            $this->manage->save_before();
            front::$post['catname'] = str_replace(' ', '&nbsp;', front::$post['catname']);
            front::$post['htmldir'] = str_replace(' ', '_', front::$post['htmldir']);
            if (front::$post['introduce'] == '') {
                front::$post['introduce'] = tool::cn_substr(preg_replace('/&(.*?);/is', '', strip_tags(front::$post['content'])), 200);
            }
            if ($this->table == 'user') {
                //var_dump($_SESSION);
                if (!Phpox_token::is_token('user_add', front::$post['token'])) {
                    exit('令牌错误');
                }
            }
            if ($this->table == 'templatetag') {
                if (front::$post['tagfrom'] != 'define' && !preg_match('/^tag_(.*?)+\.html$/is', front::$post['tagtemplate'])) {
                    exit('参数非法');
                }
            }
            if ($this->table == 'templatetagwap') {
                if (front::$post['tagfrom'] != 'define' && !preg_match('/^tag_(.*?)+\.html$/is', front::$post['tagtemplate'])) {
                    exit('参数非法');
                }
            }
            if ($this->table == 'category') {
                if (front::$post['addtype'] == 'single') {
                    if (!front::$post['htmldir']) {
                        front::$post['htmldir'] = pinyin::get2(front::$post['catname']);
                    }
                    $insert = $this->_table->rec_insert(front::$post);
                    if ($insert < 1) {
                        front::flash("{$this->tname}添加失败！");
                    } else {
                        $_insertid = $this->_table->insert_id();
                        event::log("添加" . $this->tname . ",ID=" . $_insertid, '成功');
                        $this->manage->save_after($_insertid);
                    }
                } else {
                    $catearr = explode("\n", front::$post['batch_add']);
                    foreach ($catearr as $cates) {
                        $catetmp = explode("|", $cates);
                        if ($catetmp[0] != '') {
                            front::$post['catname'] = $catetmp[0];
                            front::$post['htmldir'] = $catetmp[1];
                            if ($catetmp[1] == '') {
                                front::$post['htmldir'] = pinyin::get($catetmp[0]);
                            }
                            $insert = $this->_table->rec_insert(front::$post);
                            if ($insert < 1) {
                                front::flash("{$this->tname}添加失败！");
                            } else {
                                $_insertid = $this->_table->insert_id();
                                event::log("添加" . $this->tname . ",ID=" . $_insertid, '成功');
                                $this->manage->save_after($_insertid);
                            }
                        }
                    }
                }
                front::refresh(url::modify('act/list', true));
            } else {
                $insert = $this->_table->rec_insert(front::$post);
                $_insertid = $this->_table->insert_id();
                $this->manage->save_after($_insertid);
                if ($insert < 1) {
                    front::flash("{$this->tname}添加失败！");
                } else {
                    event::log("添加" . $this->tname . ",ID=" . $_insertid, '成功');
                    $info = '';
                    if ($this->table == 'archive') {
                        $url = url('archive/show/aid/' . $_insertid, false);
                        if (front::get('site') == 'default' || front::get('site') == '') {
                            $info = '<a href="' . $url . '" target="_blank">查看</a>';
                        }
                    }
                    front::flash("{$this->tname}添加成功！$info");
                    if (front::get('type') == 'dialog') {
                        if ($this->table == 'option') {
                            front::flash();
                            exit('添加成功！');
                        }
                    }
                    if ($this->table == 'templatetag') {
                        front::refresh(url::modify('act/list/tagfrom/content', true));
                    }else if($this->table == 'ballot'){
                        //fasong duanxin
                        $user = user::getInstance();
                        $rows = $user->getrows('',0);
                        foreach ($rows as $r){
                            sendMsg($r['tel'],config::get('sitename').'发布了'.front::$post['title'].'，欢迎参与！');
                        }
                        front::refresh(url::modify('act/list', true));
                    } else {
                        front::refresh(url::modify('act/list', true));
                    }
                }
            }
        }
        //$tag_option_info = settings::getInstance()->getrow(array('tag'=>'table-hottag'));
        //$tag_option_arr = unserialize($tag_option_info['value']);
        $this->_view_table = array();
        $this->_view_table['data'] = array();
        $this->view->image_dir = image_admin::listdir();
        $this->view->token = Phpox_token::grante_token('user_add');
        //var_dump($this->view->token);
        //$this->view->tag_opton = explode("\n",$tag_option_arr['hottag']);
    }

    function getfield_action()
    {
        if (get('aid')) {
            $data = $this->_table->getrow(get('aid'), '1 desc', $this->_table->getcols('modify'));
        }
        $field = $this->_table->getFields();
        $set_field = category::getpositionlink(get('catid'));
        //var_dump($set_field);
        $set_fields = array();
        if (is_array($set_field)) {
            foreach ($set_field as $key => $value) {
                $set_fields[] = $value['id'];
            }
        }
        //var_dump($set_fields);
        $code = '<div id="table_field">';
        foreach ($field as $f) {
            $name = $f['name'];
            //var_dump(setting::$var['archive'][$name]);
            if (setting::$var['archive'][$name]['catid'] && !@in_array(setting::$var['archive'][$name]['catid'], $set_fields)) {
                unset($field[$name]);
                continue;
            }
            if (!preg_match('/^my_/', $name)) {
                unset($field[$name]);
                continue;
            }
            if (!isset($data[$name]))
                $data[$name] = '';
            //var_dump($data);
            $code .= '<div class="row">';
            $code .= '<div class="col-xs-4 col-sm-4 col-md-3 col-lg-2 text-right">' . setting::$var['archive'][$name]['cname'] . '</div>';
            $code .= '<div class="col-xs-8 col-sm-7 col-md-7 col-lg-5 text-left" id="con_one_6">';
            $code .= form::getform($name, $form, $field, $data);
            $code .= '</div>';
            $code .= '</div>';
            $code .= '<div class="clearfix blank20"></div>';
        }
        $code .= '</div>';
        echo $code;
    }

    function block_action()
    {
        $id = intval(front::$get['id']);
        $data = $this->_table->getrow($id);
        if ($data['isblock']) {
            $data = array('isblock' => 0);
            $msg = '解冻';
        } else {
            $data = array('isblock' => 1);
            $msg = '冻结';
        }
        $this->_table->rec_update($data, $id);
        alertinfo($msg . '成功', $_SERVER['HTTP_REFERER']);
    }

    function clean_action()
    {
        $id = intval(front::$get['id']);
        $data = $this->_table->getrow($id);
        if ($data['isdelete']) {
            $data = array('isdelete' => 0);
            $msg = '拉回';
        } else {
            $data = array('isdelete' => 1);
            $msg = '清退';
        }
        $this->_table->rec_update($data, $id);
        alertinfo($msg . '成功', $_SERVER['HTTP_REFERER']);
    }

    function edit_action()
    {
        if ($this->table == 'category') {
            chkpw('category_edit');
        }
        if ($this->table == 'archive') {
            chkpw('archive_edit');
        }
        if ($this->table == 'type') {
            chkpw('type_edit');
            front::$post['typecontent'] = htmlspecialchars_decode(front::$post['typecontent']);
        }
        if ($this->table == 'special') {
            chkpw('special_edit');
            front::$post['description'] = htmlspecialchars_decode(front::$post['description']);
        }
        if ($this->table == 'user') {
            chkpw('user_edit');
        }
        if ($this->table == 'usergroup') {
            chkpw('usergroup_edit');
        }
        if ($this->table == 'orders') {
            chkpw('order_edit');
        }
        if ($this->table == 'comment') {
            chkpw('func_comment_edit');
        }
        if ($this->table == 'guestbook') {
            chkpw('func_book_reply');
        }
        if ($this->table == 'announcement') {
            chkpw('func_announc_edit');
            front::$post['content'] = htmlspecialchars_decode(front::$post['content']);
        }
        if ($this->table == 'linkword') {
            chkpw('seo_linkword_edit');
        }
        if ($this->table == 'friendlink') {
            chkpw('seo_friendlink_edit');
        }

        //用户异步提取图库图片
        if (front::$get['ajax']) {
            front::$get['dir'] = front::$get['ajax'];
            $img_arr = image_admin::listimg_action();
            foreach ($img_arr as $v) {
                echo '<img src="upload/images/' . front::$get['dir'] . '/' . $v . '" id="img' . str_replace('.', '', $v) . '" onClick="select_img(\'img' . str_replace('.', '', $v) . '\');" />';
            }
            exit();
        }
        if (front::post('submit') && $this->manage->vaild()) {
            $this->manage->filter($this->Exc);
            $this->manage->edit_before();
            $this->manage->save_before();
            if ($this->table == 'user') {
                //var_dump($_SESSION);
                if (!Phpox_token::is_token('user_add', front::$post['token'])) {
                    exit('令牌错误');
                }
            }

            $update = $this->_table->rec_update(front::$post, front::get('id'));
            if ($this->table == 'category' && front::post('image') != '' && front::post('image_del')) {
                @unlink(front::post('image'));
                $update = $this->_table->rec_update(array('image' => ''), front::get('id'));
            }

            if ($this->table == 'templatetag') {
                if (front::$post['tagfrom'] != 'define' && !preg_match('/^tag_(.*?)+\.html$/is', front::$post['tagtemplate'])) {
                    exit('参数非法');
                }
                front::$post['tagcontent'] = stripslashes(stripslashes(front::$post['tagcontent']));
                if (front::$post['tagfrom'] == 'content') {
                    $path = ROOT . '/config/tag/content_' . intval(front::get('id')) . '.php';
                } else {
                    $path = ROOT . '/config/tag/category_' . intval(front::get('id')) . '.php';
                }
                $tag_config = serialize(front::$post);
                file_put_contents($path, $tag_config);
            }
            if ($update < 1) {
                front::flash("{$this->tname}修改失败！");
            } else {
                event::log("修改" . $this->tname."ID=".front::get('id'), '成功');
                $this->manage->save_after(front::get('id'));
                $info = '';
                if ($this->table == 'archive') {
                    $url = url('archive/show/aid/' . front::get('id'), false);
                    if (front::get('site') == 'default' || front::get('site') == '') {
                        $info = '<a href="' . $url . '" target="_blank">查看</a>';
                    }
                }
                front::flash("{$this->tname}修改成功！$info");
                $from = session::get('from');
                session::del('from');
                if (!front::post('onlymodify'))
                    front::redirect(url::modify('act/list', true));
            }
        }
        $tag_option_info = settings::getInstance()->getrow(array('tag' => 'table-hottag'));
        $tag_option_arr = unserialize($tag_option_info['value']);
        $this->view->tag_opton = explode("\n", $tag_option_arr['hottag']);
        $this->view->image_dir = image_admin::listdir();
        $this->view->token = Phpox_token::grante_token('user_add');
        //var_dump($this->view->token);

        if (!session::get('from'))
            session::set('from', front::$from);
        if (!front::get('id'))
            exit("PAGE_NOT FOUND!");
        $this->_view_table = $this->_table->getrow(front::get('id'), '1 desc', $this->_table->getcols('modify'));
        //var_dump($this->_view_table);exit;
        if (!is_array($this->_view_table))
            exit("PAGE_NOT FOUND!");
        $this->manage->view_before($this->_view_table);
    }

    function htmlrule_action()
    {
        chkpw('category_htmlrule');
        $filename = ROOT . '/data/htmlrule.php';
        $arr = include($filename);
        if (!is_array($arr)) $arr = array();
        if (front::post('submit')) {
            if (front::post('htmlrule')) {
                //file_put_contents($filename, file_get_contents($filename).front::post('htmlrule')."\r\n");
                $tmp['hrname'] = front::post('hrname');
                $tmp['htmlrule'] = front::post('htmlrule');
                $tmp['cate'] = front::post('cate');
                array_push($arr, $tmp);
                file_put_contents($filename, '<?php return ' . var_export($arr, true) . ';');
                front::flash("HTMLrule添加成功!");
            }
        }
        if (front::get('o') == 'del' && front::get('id')) {
            $id = front::get('id') - 1;
            unset($arr[$id]);
            file_put_contents($filename, '<?php return ' . var_export($arr, true) . ';');
            front::flash("HTMLrule删除成功!");
        }
        $this->_view_table = $arr;
    }


    function mail_action()
    {
        chkpw('seo_mail_usersend');
        $where = null;
        $ordre = '1 desc';
        if ($this->table == 'archive') {
            $ordre = "`order`,1 DESC";
            $where = $this->_table->get_where('manage');
            if (!front::post('_typeid'))
                session::del('_typeid');
            if (get('_typeid')) {
                $typeid = get('_typeid');
                session::set('_typeid', $typeid);
                $this->type = type::getInstance();
                $types = $this->type->sons($typeid);
                $types[] = $typeid;
                $where .= ' and typeid in(' . trim(implode(',', $types), ',') . ')';
            }
            if (get('typeid')) {
                $typeid = get('typeid');
                $where .= ' and typeid=' . $typeid;
            }
            if (!front::post('_title'))
                session::del('_title');
            if (get('_title')) {
                $title = get('_title');
                session::set('_title', $title);
                $where .= " and title like '%$title%' ";
            }
        }
        if ($this->table == 'templatetag') {
            if (front::get('tagfrom')) {
                $where = "tagfrom='" . front::get('tagfrom') . "'";
            } else
                $where = "tagfrom='define'";
            $where .= " and (`tagvar` IS NULL OR `tagvar` = '') ";
        }
        if ($this->table == 'option') {
            $ballot = new ballot();
            $where = array('bid' => front::$get['bid']);
            session::set('bid', front::$get['bid']);
            $row = $ballot->getrow(array('id' => front::$get['bid']));
            $this->view->ballot = $row;
        }
        $limit = ((front::get('page') - 1) * $this->_pagesize) . ',' . $this->_pagesize;
        $this->_view_table = $this->_table->getrows($where, $limit, $ordre, $this->_table->getcols('manage'));
        $this->view->record_count = $this->_table->record_count;
    }

    function send_action()
    {
        if (front::get('type') == 'subscription') {
            chkpw('seo_mail_subscription');
        }
        if (front::get('table') == 'user') {
            chkpw('seo_mail_send');
        }
        if (front::post('submit') && $this->manage->vaild()) {
            $_POST['mail_address'] = strtr($_POST['mail_address'], '[', '<');
            $_POST['mail_address'] = strtr($_POST['mail_address'], ']', '>');
            include_once(ROOT . '/lib/plugins/smtp.php');
            $mailsubject = mb_convert_encoding($title, 'GB2312', 'UTF-8');
            $mailtype = "HTML";
            $smtp = new include_smtp(config::get('smtp_mail_host'), config::get('smtp_mail_port'), config::get('smtp_mail_auth'), config::get('smtp_mail_username'), config::get('smtp_mail_password'));
            $smtp->debug = false;
            $smtp->sendmail($_POST['mail_address'], config::get('smtp_user_add'), $_POST['title'], $_POST['content'], $mailtype);
            front::flash('<font color=red>发送邮件成功!</font>');
        }
        if (!session::get('from'))
            session::set('from', front::$from);
        front::get('id') ? $where = "userid in (".front::get('id').")" : $where ='';
        //var_dump($where);
        $this->_view_table = $this->_table->getrow($where, '1', $this->_table->getcols('modify'));
        $this->manage->view_before($this->_view_table);
    }

    function sendsms_action()
    {
        if (front::post('submit') && $this->manage->vaild()) {
            //var_dump(front::$post);
            sendMsg(front::$post['mail_address'],front::$post['content']);
            front::flash('<font color=red>发送短信成功!</font>');
        }
        front::get('id') ? $where = "userid in (".front::get('id').")" : $where ='';
        //var_dump($where);
        $this->_view_table = $this->_table->getrow($where, '1', $this->_table->getcols('modify'));
        $this->manage->view_before($this->_view_table);
    }

    function viewcnzz_action()
    {
        $cnzz = new cnzz();
        $url = $cnzz->autologin(config::get('cnzz_user'), config::get('cnzz_pass'));
        $this->view->url = $url;
        $this->_view_table = $this->_table->getrow(front::get('id'), '1', $this->_table->getcols('modify'));
        $this->manage->view_before($this->_view_table);
    }

    function show_action()
    {
        front::check_type(front::$get['id']);
        $this->_view_table = $this->_table->getrow(front::$get['id'], '1 desc', $this->_table->getcols('modify'));
    }

    function result_action(){
        //var_dump($_GET);
        $bid = intval(front::$get['bid']);
        $votelogs = votelogs::getInstance();
        $rows = $votelogs->getrows(array('bid'=>$bid));
        $voteduser = array_to_hashmap($rows,'uid','username');
        //var_dump($voteduser);
        $user = user::getInstance();
        $rows = $user->getrows('',0);
        $alluser = array_to_hashmap($rows,'userid','username');
        //var_dump($alluser);
        $unvoteuser = array_diff_assoc($alluser,$voteduser);
        $this->_view_table['data']['voteduser'] = $voteduser;
        $this->_view_table['data']['unvoteuser'] = $unvoteuser;
        //$this->render();
        //var_dump($arr);
        //var_dump($rows);
        //var_dump($bid);
    }

    function batch_action()
    {
        if (front::post('batch') && front::post('select')) {
            $str_select = implode(',', front::post('select'));
            //$select = implode(',', front::post('select'));
            $select = $this->_table->primary_key . ' in (' . $str_select . ')';
            if (front::post('batch') == 'check') {
                if ($this->table == 'archive') {
                    chkpw('archive_check');
                }
                $check = $this->_table->rec_update(array('checked' => 1), $select);
                if ($check > 0) {
                    front::flash("{$this->tname}审核完成！");
                    event::log("审核通过" . $this->tname . ",ID=" . $str_select, '成功');
                } else {
                    front::flash("没有{$this->tname}被审核！");
                }
            } elseif (front::post('batch') == 'move' && front::post('typeid')) {
                if (in_array(front::post('typeid'), front::post('select')))
                    front::flash("不能移动到本分类下！");
                else {
                    $check = $this->_table->rec_update(array('parentid' => front::post('typeid')), $select);
                    if ($check > 0) {
                        front::flash("分类移动成功！");
                        event::log("分类移动" . $this->tname . ",ID=" . $str_select, '成功');
                    } else {
                        front::flash("没有分类被移动！");
                    }
                }
            } elseif (front::post('batch') == 'move' && front::post('catid')) {
                if (in_array(front::post('catid'), front::post('select')))
                    front::flash("不能移动到本栏目下！");
                else {
                    $check = $this->_table->rec_update(array('parentid' => front::post('catid')), $select);
                    if ($check > 0) {
                        front::flash("栏目移动成功！");
                        event::log("栏目移动" . $this->tname . ",ID=" . $str_select, '成功');
                    } else {
                        front::flash("没有栏目被移动！");
                    }
                }
            } elseif (front::post('batch') == 'movelist' && front::post('catid')) {
                $check = $this->_table->rec_update(array('catid' => front::post('catid')), $select);
                if ($check > 0) {
                    front::flash("移动成功！");
                    event::log("内容移动" . $this->tname . ",ID=" . $str_select, '成功');
                } else {
                    front::flash("没有内容被移动！");
                }
            } elseif (front::post('batch') == 'recommend' && isset(front::$post['attr1'])) {
                $check = $this->_table->rec_update(array('attr1' => front::post('attr1')), $select);
                if ($check > 0) {
                    front::flash("设置推荐成功！");
                    event::log("设置推荐" . $this->tname . ",ID=" . $str_select, '成功');
                } else {
                    front::flash("没有内容被设置！");
                }
            } elseif (front::post('batch') == 'deletestate') {
                if ($this->table == 'archive') {
                    chkpw('archive_del');
                }
                $deletestate = $this->_table->rec_update(array('state' => -1), $select);
                if ($deletestate > 0) {
                    front::flash("{$this->tname}已被移到回收站！");
                    event::log("移动到回收站" . $this->tname . ",ID=" . $str_select, '成功');
                } else {
                    front::flash("没有{$this->tname}被移到回收站！");
                }
            } elseif (front::post('batch') == 'restore') {
                $deletestate = $this->_table->rec_update(array('state' => 1), $select);
                if ($deletestate > 0) {
                    front::flash("{$this->tname}已被还原！");
                    event::log("还原" . $this->tname . ",ID=" . $str_select, '成功');
                } else {
                    front::flash("没有{$this->tname}被还原！");
                }
            } elseif (front::post('batch') == 'docheck') {
                $deletestate = $this->_table->rec_update(array('checked' => 1), $select);
                $deletestate = $this->_table->rec_update(array('state' => 1), $select);
                if ($deletestate > 0) {
                    front::flash("{$this->tname}已被审核！");
                    event::log("审核通过" . $this->tname . ",ID=" . $str_select, '成功');
                } else {
                    front::flash("没有{$this->tname}被审核！");
                }
            } elseif (front::post('batch') == 'douncheck') {
                $deletestate = $this->_table->rec_update(array('checked' => 0), $select);
                $deletestate = $this->_table->rec_update(array('state' => 0), $select);
                if ($deletestate > 0) {
                    front::flash("{$this->tname}已被取消审核！");
                    event::log("取消审核" . $this->tname . ",ID=" . $str_select, '成功');
                } else {
                    front::flash("没有{$this->tname}被取消审核！");
                }
            } elseif (front::post('batch') == 'top') {
                $deletestate = $this->_table->rec_update(array('toppost' => 3), $select);
                if ($deletestate > 0) {
                    front::flash("{$this->tname}已被置顶！");
                    event::log("置顶" . $this->tname . ",ID=" . $str_select, '成功');
                } else {
                    front::flash("没有{$this->tname}被置顶！");
                }
            } elseif (front::post('batch') == 'delete') {
                if ($this->table == 'archive') {
                    chkpw('archive_del');
                }
                foreach (front::post('select') as $id) {
                    $this->manage->delete_before($id);
                }
                $delete = $this->_table->rec_delete($select);
                if ($delete > 0) {
                    front::flash("成功删除{$this->tname}！");
                    event::log("删除" . $this->tname . "ID=" . $str_select, '成功');
                } else
                    front::flash("没有{$this->tname}被删除！");
            } elseif (front::post('batch') == 'addtospecial') {
                $add = $this->_table->rec_update(array('spid' => front::post('spid')), $select);
                event::log("发布到专题" . $this->tname . "ID=" . $str_select, '成功');
            } elseif (front::post('batch') == 'removefromspecial') {
                $add = $this->_table->rec_update(array('spid' => null), $select);
                event::log("从专题移除" . $this->tname . "ID=" . $str_select, '成功');
            }
        }
        //var_dump($_POST);
        if (front::post('batch') == 'listorder') {
            $orders = front::post('listorder');
            //var_dump($orders);
            if (is_array($orders))
                foreach ($orders as $id => $order) {
                    $this->_table->rec_update(array('listorder' => intval($order)), $id);
                }
        }
        //批量导出
        if (front::post('batch') == 'export') {

            //var_dump(setting::$var[$this->table]);exit;
            $fields = $this->_table->getFields();
            //var_dump($fields);
            //var_dump($this->_table);exit;
            $rows = $this->_table->rec_select($select, 0, '*', '1 asc');
            push($fields, $rows, setting::$var[$this->table]);
            exit;
        }
        front::redirect(front::$from);
    }

    function delete_action()
    {
        if ($this->table == 'category') {
            chkpw('category_del');
        }
        if ($this->table == 'type') {
            chkpw('type_del');
        }
        if ($this->table == 'special') {
            chkpw('special_del');
        }
        if ($this->table == 'user') {
            chkpw('user_del');
        }
        if ($this->table == 'usergroup') {
            chkpw('usergroup_del');
        }
        if ($this->table == 'orders') {
            chkpw('order_del');
        }
        if ($this->table == 'comment') {
            chkpw('func_comment_del');
        }
        if ($this->table == 'guestbook') {
            chkpw('func_book_del');
        }
        if ($this->table == 'announcement') {
            chkpw('func_announc_del');
        }
        if ($this->table == 'linkword') {
            chkpw('seo_linkword_del');
        }
        if ($this->table == 'friendlink') {
            chkpw('seo_friendlink_del');
        }

        if (!Phpox_token::is_token('table_del', front::$get['token'])) {
            exit('令牌错误');
        }
        //var_dump($this->_table);
        $this->manage->delete_before(front::get('id'));
        //var_dump($this->_table);
        $delete = $this->_table->rec_delete(front::get('id'));
        if ($delete) {
            front::flash("删除{$this->tname}成功！");
            event::log("删除{$this->tname},ID=" . front::get('id'), '成功');
        }
        front::redirect(url::modify('act/list/table/' . $this->table . '/bid/' . session::get('bid')));
    }

    function setting_action()
    {
        if ($this->table == 'archive') {
            chkpw('archive_setting');
        }
        if ($this->table == 'friendlink') {
            chkpw('seo_friendlink_setting');
        }
        $this->_view_table = false;
        $set = settings::getInstance();
        $sets = $set->getrow(array('tag' => 'table-' . $this->table));
        $data = unserialize($sets['value']);
        if (front::post('submit')) {
            $var = front::$post;
            unset($var['submit']);
            $set->rec_replace(array('value' => addslashes(serialize($var)), 'tag' => 'table-' . $this->table, 'array' => addslashes(var_export($var, true))));
            event::log("修改{$this->tname}配置", '成功');
            front::flash("配置成功！");
        }
        $this->view->settings = $data;
    }

    function view($table)
    {
        $this->view->data = $table['data'];
        $this->view->field = $table['field'];
    }

    function end()
    {
        if (!isset($this->_view_table))
            return;
        if (!isset($this->_view_table['data']))
            $this->_view_table['data'] = $this->_view_table;
        $this->_view_table['field'] = $this->_table->getFields();
        $this->view->fieldlimit = $this->_table->getcols(front::$act == 'list' ? 'manage' : 'modify');
        $this->view($this->_view_table);
        if (!preg_match('/^my_/', $this->table)) {
            manage_form::table($this);
        }
        if (front::post('onlymodify')) {
            $this->render();
        } else {
            if (front::get('main')) {
                $this->render();
            } else {
                $this->render('index.php');
            }
        }
    }
}