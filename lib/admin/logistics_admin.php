<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class logistics_admin extends admin {
    protected $_table;
    function init() {
        $this->table=front::get('table');
        if (preg_match('/^my_/',$this->table)) {
            form_admin::init();
            $this->_table=new defind($this->table);
        }
        else $this->_table=new $this->table;
        $this->_table->getFields();
        $this->view->form=$this->_table->get_form();
        $this->tname=lang($this->table);
        if($this->table=='logistics')
            $this->tname='配货方式';
        $this->_pagesize=config::get('manage_pagesize');
        $this->view->table=$this->table;
        $this->view->primary_key=$this->_table->primary_key;
        if (!front::get('page')) front::$get['page']=1;
        $this->Exc=$this->table == 'templatetag'?true : false;
        $manage='table_'.$this->table;
        if (preg_match('/^my_/',$this->table)) $manage='table_form';
        $this->manage=new $manage;
    }
    function list_action() {
    	chkpw('order_logistics');
        $set1=settings::getInstance();
        $sets1=$set1->getrow(array('tag'=>'table-'.$this->table));
        $setsdata1=unserialize($sets1['value']);
        $this->view->settings=$setsdata1;
        $where=null;
        $ordre='`id` DESC';
        $limit=((front::get('page') -1) * $this->_pagesize).','.$this->_pagesize;
        $this->_view_table=$this->_table->getrows($where,$limit,$ordre,'*');
    }
    function add_action() {
        if (front::post('submit') &&$this->manage->vaild()) {
            $this->manage->filter($this->Exc);
            $this->manage->add_before($this);
            $this->manage->save_before();
            $insert=$this->_table->rec_insert(front::$post);
            $_insertid = $this->_table->insert_id();
            if ($insert <1) {
                front::flash("{$this->tname}添加失败！");
            }
            else {
                $this->manage->save_after($_insertid);
                $info='';
                front::flash("{$this->tname}添加成功！$info");
                front::refresh(url::modify('act/list',true));
            }
        }
        $this->_view_table=array();
        $this->_view_table['data']=array();
    }
    function edit_action() {
        if (front::post('submit') &&$this->manage->vaild()) {
            $this->manage->filter($this->Exc);
            $this->manage->edit_before();
            $this->manage->save_before();
            $update=$this->_table->rec_update(front::$post,front::get('id'));
            if ($update <1) {
                front::flash("{$this->tname}修改失败！");
            }
            else {
                $this->manage->save_after(front::get('id'));
                $info='';
                front::flash("{$this->tname}修改成功！$info");
                $from=session::get('from');
                session::del('from');
                if (!front::post('onlymodify')) front::redirect(url::modify('act/list',true));
            }
        }
        if (!session::get('from')) session::set('from',front::$from);
        if (!front::get('id')) exit("PAGE_NOT FOUND!");
        $this->_view_table=$this->_table->getrow(front::get('id'),'1',$this->_table->getcols('modify'));
        if (!is_array($this->_view_table)) exit("PAGE_NOT FOUND!");
        $this->manage->view_before($this->_view_table);
    }
    function show_action() {
        front::check_type(front::$get['id']);
        $this->_view_table=$this->_table->getrow(front::$get['id'],1,'1 desc',$this->_table->getcols('modify'));
    }
    function batch_action() {
        if (front::post('batch') &&front::post('select')) {
            $select=implode(',',front::post('select'));
            $select=$this->_table->primary_key.' in ('.$select.')';
            if (front::post('batch') == 'check') {
                $check=$this->_table->rec_update(array('checked'=>1),$select);
                if ($check >0) front::flash("{$this->tname}审核完成！");
                else front::flash("没有{$this->tname}被审核！");
            }
            elseif (front::post('batch') == 'move'&&front::post('typeid')) {
                if (in_array(front::post('typeid'),front::post('select'))) front::flash("不能移动到本分类下！");
                else {
                    $check=$this->_table->rec_update(array('parentid'=>front::post('typeid')),$select);
                    if ($check >0) front::flash("分类移动成功！");
                    else front::flash("没有分类被移动！");
                }
            }
            elseif (front::post('batch') == 'move'&&front::post('catid')) {
                if (in_array(front::post('catid'),front::post('select'))) front::flash("不能移动到本栏目下！");
                else {
                    $check=$this->_table->rec_update(array('parentid'=>front::post('catid')),$select);
                    if ($check >0) front::flash("栏目移动成功！");
                    else front::flash("没有栏目被移动！");
                }
            }
            elseif (front::post('batch') == 'movelist'&&front::post('catid')) {
                $check=$this->_table->rec_update(array('catid'=>front::post('catid')),$select);
                if ($check >0) front::flash("移动成功！");
                else front::flash("没有内容被移动！");
            }
            elseif (front::post('batch') == 'recommend'&&front::post('attr1')) {
                $check=$this->_table->rec_update(array('attr1'=>front::post('attr1')),$select);
                if ($check >0) front::flash("设置推荐成功！");
                else front::flash("没有内容被设置！");
            }
            elseif (front::post('batch') == 'deletestate') {
                $deletestate=$this->_table->rec_update(array('state'=>-1),$select);
                if ($deletestate >0) front::flash("{$this->tname}已被移到回收站！");
                else front::flash("没有{$this->tname}被移到回收站！");
            }
            elseif (front::post('batch') == 'restore') {
                $deletestate=$this->_table->rec_update(array('state'=>0),$select);
                if ($deletestate >0) front::flash("{$this->tname}已被还原！");
                else front::flash("没有{$this->tname}被还原！");
            }
            elseif (front::post('batch') == 'delete') {
                foreach (front::post('select') as $id) {
                    $this->manage->delete_before($id);
                }
                $delete=$this->_table->rec_delete($select);
                if ($delete >0) front::flash("成功删除{$this->tname}！");
                else front::flash("没有{$this->tname}被删除！");
            }
            elseif (front::post('batch') == 'addtospecial') {
                $add=$this->_table->rec_update(array('spid'=>front::post('spid')),$select);
            }
            elseif (front::post('batch') == 'removefromspecial') {
                $add=$this->_table->rec_update(array('spid'=>null),$select);
            }
        }
        if (front::post('batch') == 'listorder') {
            $orders=front::post('listorder');
            if (is_array($orders)) foreach ($orders as $id=>$order) {
                    $this->_table->rec_update(array('listorder'=>$order),$id);
                }
        }
        front::redirect(front::$from);
    }
    function delete_action() {
        $this->manage->delete_before(front::get('id'));
        $delete=$this->_table->rec_delete(front::get('id'));
        if ($delete) front::flash("删除{$this->tname}成功！");
        front::redirect(url::modify('act/list/table/'.$this->table.'/bid/'.session::get('bid')));
    }
    function setting_action() {
        $this->_view_table=false;
        $set=settings::getInstance();
        $sets=$set->getrow(array('tag'=>'table-'.$this->table));
        $data=unserialize($sets['value']);
        if (front::post('submit')) {
            $var=front::$post;
            unset($var['submit']);
            $set->rec_replace(array('value'=>serialize($var),'tag'=>'table-'.$this->table,'array'=>var_export($var,true)));
            front::flash("{$this->tname}配置成功！");
        }
        $this->view->settings=$data;
    }
    function view($table) {
        $this->view->data=$table['data'];
        $this->view->field=$table['field'];
    }
    function end() {
        if (!isset($this->_view_table)) return;
        if (!isset($this->_view_table['data'])) $this->_view_table['data']=$this->_view_table;
        $this->_view_table['field']=$this->_table->getFields();
        $this->view->fieldlimit=$this->_table->getcols(front::$act == 'list'?'manage': 'modify');
        $this->view($this->_view_table);
        if (front::post('onlymodify')) $this->render();
        else
        if (front::get('main')) $this->render();
        else $this->render('index.php');
    }
}