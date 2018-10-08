<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class form_admin  extends admin {
    function init() {
        $tables=self::get_my_tables();
        $this->tables=$tables;
    }
    function addform_action() {
    	chkpw('defined_form_add');
        if(front::post('submit')  &&$this->check_mytable()) {
            $delete=archive::getInstance()->query("
                CREATE TABLE `".config::get('database','prefix').front::post('name')."` (
                  `fid`  int(11) NOT NULL auto_increment,
                  `aid` int(11) ,
                  `username` varchar(50) NOT NULL,
                  `userid` int(11) NOT NULL,
                  `adddate` datetime default NULL,
                  `view` int(11) default '0',
                  `state` tinyint(2) default '1',
                  `checked` tinyint(2) default '0',
                  `ip` varchar(20) default NULL,
                  PRIMARY KEY  (`fid`),
                  UNIQUE KEY `fid` (`fid`)
                ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
            ");
            $post=front::$post;
            unset($post['submit']);
            if(!preg_match('/^myform\/(.*?)+\.html$/is', $post['template'])){
            	exit('参数非法');
            }
            setting::$_var[front::post('name')]['myform']=$post;
            setting::save();
            front::flash('添加成功！');
            front::redirect(url::modify('act/listform',true));
        }
    }
    function editform_action() {
    	chkpw('defined_form_edit');
        $this->view->table=front::get('table');
        if(front::post('submit')  &&$this->check_mytable()) {
            $post=front::$post;
			 if(!preg_match('/^(myform)\/(\w)+\.html$/i', $post['template'])){
            	exit('非法参数');
            }
            unset($post['submit']);
            setting::$_var[front::post('name')]['myform']=$post;
            setting::save();
            front::flash('修改成功！');
            front::redirect(url::modify('act/listform',true));
        }
    }
    function delete_action() {
    	chkpw('defined_form_del');
        $table=new defind(front::get('table'));
        $delete=archive::getInstance()->query("DROP TABLE {$table->name}");
        if($delete)  front::flash("删除表单成功！");
        front::redirect(url::modify('act/listform/table/'.$table->name));
    }
    function listform_action() {
    	chkpw('defined_form_list');
        $this->view->tables=$this->tables;
    }

	
    function export_action(){
        $obj = new defind(front::$get['table']);
        $fields = $obj->getFields();
        //var_dump($fields);
        $row = $obj->getrows(false,false);
        $titles =  array_keys($row[0]);
        //var_dump($titles);
        $name = setting::$var[front::$get['table']]['myform']['cname'];
        push($fields,$row,setting::$var[front::$get['table']],$name);
        //var_dump($row);
        exit;
    }


    private function check_mytable() {
        if(!preg_match('/^my_.+/',front::post('name'))) {
            front::flash('表名格式必须是是"my_abc"！');
            return false;
        }
        return true;
    }
    private function get_my_tables() {
        $tables=array();
        $forms=tdatabase::getInstance()->getTables();
        foreach($forms as $form) {
            if(preg_match('/^'.config::get('database','prefix').'(my_\w+)/xi',$form['name'],$res))
                $tables[]=$res[1];
        }
        return $tables;
    }
    function end() {
        $this->render('index.php');
    }
}