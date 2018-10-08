<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class adminlogs_admin extends admin {
    protected $_table;
    function init() {
        $this->_table = new event();
        $this->_table->getFields();
        $this->tname = '管理日志';
        $this->_pagesize = config::get('manage_pagesize');
        if (!front::get('page')) front::$get['page'] = 1;
    }
    
    function batch_action(){
        if(front::post('batch') == 'delete'){
            $sql = "DELETE FROM `".config::get('database', 'prefix')."event`";
            $this->_table->query($sql);
            event::log('日志清除','成功');
            front::refresh(url::modify('act/manage',true));
        }
    }

    function delete_action(){
        $id =  intval($_GET['id']);
        $sql = "DELETE FROM `".config::get('database', 'prefix')."event` WHERE id='$id'";
        $this->_table->query($sql);
        //event::log('删除日志,ID='.$id,'成功');
        front::refresh(url::modify('act/manage',true));
    }
    
    function manage_action() {
    	chkpw('func_data_adminlogs');
        $where = null;
        $ordre = '`id` DESC';
        $limit = ((front::get('page') -1) * $this->_pagesize).','.$this->_pagesize;
        $this->view->data = $this->_table->getrows($where,$limit,$ordre,$this->_table->getcols('manage'));
        $this->view->record_count = $this->_table->record_count;
    }
   
    function end() {
        $this->render('index.php');
    }
}