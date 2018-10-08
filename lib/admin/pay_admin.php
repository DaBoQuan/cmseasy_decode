<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class pay_admin extends admin {
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
        if($this->table=='pay')
            $this->tname='支付模块';
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
    	chkpw('order_pay');
        $set1=settings::getInstance();
        $sets1=$set1->getrow(array('tag'=>'table-'.$this->table));
        $setsdata1=unserialize($sets1['value']);
        $this->view->settings=$setsdata1;
        $where = $pay_list = array();
        $where['enabled'] = 1;
        $ordre='`pay_id` DESC';
        $limit=((front::get('page') -1) * $this->_pagesize).','.$this->_pagesize;
        $this->_view_table=$this->_table->getrows($where,$limit,$ordre);
        $pay_list = $this->_view_table;
        foreach($pay_list as $key=>$value) {
            $pay_list[$value['pay_code']] = $value;
            unset($pay_list[$key]);
        }
        $modules = read_modules(ROOT.'/lib/plugins/pay/');
        //var_dump($modules);
        global $_LANG;
        for ($i = 0;$i <count($modules);$i++) {
            $code = $modules[$i]['code'];
            $modules[$i]['pay_code'] = $modules[$i]['code'];
            if (isset($pay_list[$code])) {
                $modules[$i]['id'] = $pay_list[$code]['pay_id'];
                $modules[$i]['name'] = $pay_list[$code]['pay_name'];
                $modules[$i]['pay_fee'] =  $pay_list[$code]['pay_fee'];
                $modules[$i]['is_cod'] = $pay_list[$code]['is_cod'];
                $modules[$i]['desc'] = $pay_list[$code]['pay_desc'];
                $modules[$i]['pay_order'] = $pay_list[$code]['pay_order'];
                $modules[$i]['install'] = '1';
            }
            else {
                $modules[$i]['name'] = $_LANG[$modules[$i]['code']];
                if (!isset($modules[$i]['pay_fee'])) {
                    $modules[$i]['pay_fee'] = 0;
                }
                $modules[$i]['desc'] = $_LANG[$modules[$i]['desc']];
                $modules[$i]['install'] = '0';
            }
            if ($modules[$i]['pay_code'] == 'tenpayc2c') {
                $tenpayc2c = $modules[$i];
            }
        }
        $this->_view_table = $modules;
    }
    function install_action() {
        if (front::post('submit') &&$this->manage->vaild()) {
            $this->manage->filter($this->Exc);
            $this->manage->add_before($this);
            $this->manage->save_before();
            front::$post['pay_config'] = array();
            if (isset(front::$post['cfg_value']) &&is_array(front::$post['cfg_value'])) {
                for ($i = 0;$i <count(front::$post['cfg_value']);$i++) {
                    $pay_config[] = array('name'=>trim(front::$post['cfg_name'][$i]),
                            'type'=>trim(front::$post['cfg_type'][$i]),
                            'value'=>trim(front::$post['cfg_value'][$i])
                    );
                }
            }
            front::$post['pay_config'] = serialize($pay_config);
            front::$post['pay_fee'] = $pay_fee  = empty(front::$post['pay_fee'])?0:front::$post['pay_fee'];
            front::$post['enabled'] = 1;
            //var_dump(front::$post);
            unset(front::$post['pay_id']);
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
        $set_modules = true;
        global $_LANG;
        include_once(ROOT.'/lib/plugins/pay/'.front::get('name').'.php');
        $data = $modules[0];
        if (isset($data['pay_fee'])) {
            $data['pay_fee'] = trim($data['pay_fee']);
        }else {
            $data['pay_fee']     = 0;
        }
        $pay['pay_code']    = $data['code'];
        $pay['pay_name']    = $_LANG[$data['code']];
        $pay['pay_desc']    = $_LANG[$data['desc']];
        $pay['is_cod']      = $data['is_cod'];
        $pay['pay_fee']     = $data['pay_fee'];
        $pay['is_online']   = $data['is_online'];
        $pay['pay_config']  = array();
        foreach ($data['config'] as $key =>$value) {
            $config_desc = (isset($_LANG[$value['name'] .'_desc'])) ?$_LANG[$value['name'] .'_desc'] : '';
            $pay['pay_config'][$key] = $value +
                    array('label'=>$_LANG[$value['name']],'value'=>$value['value'],'desc'=>$config_desc);
            if ($pay['pay_config'][$key]['type'] == 'select'||
                    $pay['pay_config'][$key]['type'] == 'radiobox') {
                $pay['pay_config'][$key]['range'] = $_LANG[$pay['pay_config'][$key]['name'] .'_range'];
            }
        }
        $this->_view_table['pay'] = $pay;
    }
    function edit_action() {
        if (front::post('submit') &&$this->manage->vaild()) {
            $this->manage->filter($this->Exc);
            $this->manage->edit_before();
            $this->manage->save_before();
            front::$post['pay_config'] = array();
            if (isset(front::$post['cfg_value']) &&is_array(front::$post['cfg_value'])) {
                for ($i = 0;$i <count(front::$post['cfg_value']);$i++) {
                    $pay_config[] = array('name'=>trim(front::$post['cfg_name'][$i]),
                            'type'=>trim(front::$post['cfg_type'][$i]),
                            'value'=>trim(front::$post['cfg_value'][$i])
                    );
                }
            }
            front::$post['pay_config'] = serialize($pay_config);
            front::$post['pay_fee'] = $pay_fee  = empty(front::$post['pay_fee'])?0:front::$post['pay_fee'];
            front::$post['enabled'] = 1;
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
        //var_dump($this->_table->getcols('modify'));
        //var_dump($this->_table);
        if (!front::get('id')) exit("PAGE_NOT FOUND!");
        $this->_view_table=$this->_table->getrow(front::get('id'),'1');
        //var_dump($this->_view_table);
        $set_modules = true;
        global $_LANG;
        include_once(ROOT.'/lib/plugins/pay/'.$this->_view_table['pay_code'].'.php');
        $data = $modules[0];
        $pay = $this->_view_table;
        if (is_string($pay['pay_config'])) {
            $store = unserialize($pay['pay_config']);
            $code_list = array();
            foreach ($store as $key=>$value) {
                $code_list[$value['name']] = $value['value'];
            }
            $pay['pay_config'] = array();
            foreach ($data['config'] as $key =>$value) {
                $pay['pay_config'][$key]['desc'] = (isset($_LANG[$value['name'] .'_desc'])) ?$_LANG[$value['name'] .'_desc'] : '';
                $pay['pay_config'][$key]['label'] = $_LANG[$value['name']];
                $pay['pay_config'][$key]['name'] = $value['name'];
                $pay['pay_config'][$key]['type'] = $value['type'];
                if (isset($code_list[$value['name']])) {
                    $pay['pay_config'][$key]['value'] = $code_list[$value['name']];
                }
                else {
                    $pay['pay_config'][$key]['value'] = $value['value'];
                }
                if ($pay['pay_config'][$key]['type'] == 'select'||
                        $pay['pay_config'][$key]['type'] == 'radiobox') {
                    $pay['pay_config'][$key]['range'] = $_LANG[$pay['pay_config'][$key]['name'] .'_range'];
                }
            }
        }
        if (!isset($pay['pay_fee'])) {
            if (isset($data['pay_fee'])) {
                $pay['pay_fee'] = $data['pay_fee'];
            }
            else {
                $pay['pay_fee'] = 0;
            }
        }
        if (!is_array($this->_view_table)) exit("PAGE_NOT FOUND!");
        $this->_view_table['paycfg'] = unserialize($this->_view_table['paycfg']);
        $this->_view_table['pay'] = $pay;
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