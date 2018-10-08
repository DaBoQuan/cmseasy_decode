<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');
class stats_admin extends admin {
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
        $this->tname='访问统计';
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
        $where=null;
        $ordre='`id` DESC';
        $limit=((front::get('page') -1) * $this->_pagesize).','.$this->_pagesize;
        $this->_view_table=$this->_table->getrows($where,$limit,$ordre,$this->_table->getcols('manage'));
        $this->view->record_count=$this->_table->record_count;
        $this->view->data = $this->_view_table;
    }
    function batch_action() {
        if (front::post('batch') &&front::post('select')) {
            $select=implode(',',front::post('select'));
            $select=$this->_table->primary_key.' in ('.$select.')';
            if (front::post('batch') == 'delete') {
                foreach (front::post('select') as $id) {
                    $this->manage->delete_before($id);
                }
                $delete=$this->_table->rec_delete($select);
                if ($delete >0) front::flash("成功删除{$this->tname}！");
                else front::flash("没有{$this->tname}被删除！");
            }
        }
        front::redirect(front::$from);
    }
    
    function clear_action() {
    	if (front::post('batch') == 'clear') {
    		$delete=$this->_table->rec_delete("true");
    		if ($delete >0) front::flash("成功删除{$this->tname}！");
    	}
    	front::redirect(front::$from);
    }
    
    function delete_action() {
        $this->manage->delete_before(front::get('id'));
        $delete=$this->_table->rec_delete(front::get('id'));
        if ($delete) front::flash("删除{$this->tname}成功！");
        front::redirect(url::modify('act/list/table/'.$this->table));
    }
    
    function end() {
        $this->render('index.php');
    }
}