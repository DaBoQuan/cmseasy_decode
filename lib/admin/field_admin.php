<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class field_admin extends admin {
    function init() {
        $this->table=front::get('table');
        if(preg_match('/^my_/',$this->table)) {
            //form_admin::init();
            $this->_table=new defind($this->table);
        }
        else $this->_table=new $this->table;
        $this->view->form = $this->_table->get_form_field();
        $_fields=array();
        $fields=$this->_table->getFields();
        foreach($fields as $_field) {
            if(preg_match('/^my_/',$_field['name']))
                $_fields[$_field['name']]=$_field;
        }
        $this->_fields=$_fields;
        $this->view->table=$this->table;
        $this->view->primary_key=$this->_table->primary_key;
    }
    function list_action() {
    	if($this->table == 'user'){
    		chkpw('defined_field_user');
    	}
        $this->view->fields=$this->_fields;
    }
    function add_action() {
    	if($this->table == 'user'){
    		chkpw('defined_field_user_add');
    	}
    	if($this->table == 'archive'){
    		chkpw('defined_field_content_add');
    	}
        if(front::post('submit') &&$this->check_myfield()) {
            if(strpos(front::$post['name'],'.') !== FALSE){
                front::flash('字段名称不能含"."！');
                return;
                //var_dump(front::$post);exit;
            }
            if(front::$post['type']=='_image') {
                front::$post['filetype'] = 'image';
                front::$post['type'] = 'varchar';
                front::$post['len']=100;
                front::$post['selecttype']=null;
            }
            elseif(front::$post['type']=='_file') {
                front::$post['filetype'] = 'file';
                front::$post['type'] = 'varchar';
                front::$post['len']=100;
                front::$post['selecttype']=null;
            }
            else {
                if(front::$post['selecttype']) {
                    front::$post['type'] = 'varchar';
                    if(front::$post['selecttype']=='checkbox') front::$post['len']=100;
                    else front::$post['len']=10;
                }
                front::$post['filetype']=null;
            }
            front::$post['len'] = front::$post['type'] == 'varchar'?min(front::$post['len'],255) : (front::$post['type'] == 'int'?min(front::$post['len'],11) : 0);
            if(front::$post['type'] == 'text'||front::$post['type'] == 'mediumtext'||front::$post['type'] == 'datetime'||front::$post['type'] == 'text')
                front::$post['len']=0;
            $option=front::post('type').(front::post('len')>0?'('.front::post('len').')':'');
            $option .= front::post('isnotnull')?' not null':' null';
            $add=$this->_table->query("ALTER TABLE `{$this->_table->name}` ADD COLUMN `".front::post('name')."` $option");
            if(!$add) {
                front::flash('字段添加失败！');
            }else {
                foreach(front::$post as $k=>$n) if(!preg_match('/submit/',$k)) {
                        setting::$_var[$this->table][front::post('name')][$k]=$n;
                    }
                setting::save();
                front::flash('字段添加成功！');
                front::redirect(url::modify('act/list',true));
            }
        }
    }
    function edit_action() {
    	if($this->table == 'user'){
    		chkpw('defined_field_user_edit');
    	}
    	if($this->table == 'archive'){
    		chkpw('defined_field_content_edit');
    	}
        if(front::post('submit')  &&$this->check_myfield()) {
            if(front::$post['type']=='_image') {
                front::$post['filetype'] = 'image';
                front::$post['type'] = 'varchar';
                front::$post['len']=100;
                front::$post['selecttype']=null;
            }
            elseif(front::$post['type']=='_file') {
                front::$post['filetype'] = 'file';
                front::$post['type'] = 'varchar';
                front::$post['len']=100;
                front::$post['selecttype']=null;
            }
            else {
                if(front::$post['selecttype']) {
                    front::$post['type'] = 'varchar';
                    if(front::$post['selecttype']=='checkbox') front::$post['len']=100;
                    else front::$post['len']=10;
                }
                front::$post['filetype']=null;
            }
            if(front::$post['type'] == 'text'||front::$post['type'] == 'mediumtext'||front::$post['type'] == 'datetime'||front::$post['type'] == 'text')
                front::$post['len']=0;
            $option=front::post('type').(front::post('len')>0?'('.front::post('len').')':'');
            $option .= front::post('isnotnull')?' not null':' null';
            $edit=$this->_table->query("ALTER TABLE `{$this->_table->name}` CHANGE `".front::post('name')."` `".front::post('name')."` $option");
            if(!$edit) {
                front::flash('字段修改失败！'."ALTER TABLE `{$this->_table->name}` CHANGE `".front::post('name')."` `".front::post('name')."` $option");
            }else {
                if(!front::$post['issearch']) {
                    front::$post['issearch'] = 0;
                }
                if(!front::$post['isnotnull'])
                    front::$post['isnotnull'] = 0;
                if($this->table=='user') {
                    if(!front::$post['showinreg'])
                        front::$post['showinreg'] = 0;
                }
                foreach(front::$post as $k=>$n) if(!preg_match('/submit/',$k)) {
                        setting::$_var[$this->table][front::post('name')][$k]=$n;
                    }
                setting::save();
                front::flash('字段修改成功！');
                front::redirect(url::modify('act/list',true));
            }
        }
        $this->view->data=setting::$var[$this->table][front::get('name')];
        $this->view->field=$this->_fields[front::get('name')];
    }
    function delete_action() {
    	if($this->table == 'user'){
    		chkpw('defined_field_user_del');
    	}
    	if($this->table == 'archive'){
    		chkpw('defined_field_content_del');
    	}
        if(!preg_match('/^my_.+/',front::get('name'))) {
            front::flash('字段名称不正确！');
        }
        $delete=$this->_table->query("ALTER TABLE `{$this->_table->name}` DROP `".front::get('name')."`");
        if(!$delete) {
            front::flash('字段删除失败！');
        }else {
            unset(setting::$var[$this->table][front::get('name')]);
            setting::save();
            front::flash('字段删除成功！');
            front::redirect(url::modify('act/list',true));
        }
    }
    
    function batch_action(){
    	if(is_array(front::$post['select']) && !empty(front::$post['select'])){
    		foreach(front::$post['select'] as $v){
    			if(!preg_match('/^my_.+/',$v)) {
    				front::flash('字段名称不正确！');
    			}
    			$delete=$this->_table->query("ALTER TABLE `{$this->_table->name}` DROP `".$v."`");
    			if(!$delete) {
    				front::flash('字段删除失败！');
    			}else {
    				unset(setting::$var[$this->table][$v]);
    				setting::save();

    			}
    		}
            front::flash('字段删除成功！');
            front::redirect(url::modify('act/list',true));
    	}
    }
    
    private function check_myfield() {
        if(!preg_match('/^my_.+/',front::post('name'))) {
            front::flash('字段名格式必须是是"my_abc"！');
            return false;
        }
        return true;
    }
    function end() {
        $this->render('index.php');
    }
}