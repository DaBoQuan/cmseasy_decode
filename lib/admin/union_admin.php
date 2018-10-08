<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class union_admin extends admin {
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
    	chkpw('union_list');
        $set1=settings::getInstance();
        $sets1=$set1->getrow(array('tag'=>'table-'.$this->table));
        $setsdata1=unserialize($sets1['value']);
        $this->view->settings=$setsdata1;
        $where=null;
        $ordre='`userid` DESC';
        $limit=((front::get('page') -1) * $this->_pagesize).','.$this->_pagesize;
        $this->_view_table=$this->_table->getrows($where,$limit,$ordre,$this->_table->getcols('manage'));
        $this->view->record_count=$this->_table->record_count;
        $this->_view_user = new user;
        foreach($this->_view_table as $key=>$val) {
            $userunion = $this->_view_user->getrow(array('userid'=>$val['userid']));
            $val['point'] = $userunion['point'];
            $this->_view_table[$key] = $val;
        }
    }
    function settle_action() {
        $this->_view_user = new user;
        if (front::post('submit') &&$this->manage->vaild()) {
            $this->manage->filter($this->Exc);
            $this->manage->edit_before();
            $this->manage->save_before();
            $union_payarr = array();
            $union_payarr['expendamount'] = front::$post['settleexpendamount'] = round(floatval(front::$post['settleexpendamount']),2);
            $union_payarr['amount'] = front::$post['settleamount'] = round(front::$post['settleexpendamount']*front::$post['profitmargin']/100,2);
            $union_payarr['userid'] = front::$post['userid']=front::get('id');
            $union_payarr['inputer'] = front::$post['inputer']=$this->view->user['username'];
            $union_payarr['addtime'] = front::$post['addtime']=time();
            $union_payarr['ip'] = front::$post['ip']=front::ip();
            $union_payarr['payaccount'] = front::$post['payaccount'];
            $union_payarr['profitmargin'] = front::$post['profitmargin'];
            $union_pay = new union_pay();
            $insert = $union_pay->rec_insert($union_payarr);
            $unionarr = array();
            $unionarr['totalexpendamount']='[totalexpendamount+'.front::$post['settleexpendamount'].']';
            $unionarr['totalpayamount']='[totalpayamount+'.front::$post['settleamount'].']';
            $unionarr['lastpayamount']=front::$post['settleamount'];
            $unionarr['lastpaytime']=time();
            $unionarr['settleexpendamount']='[settleexpendamount-'.front::$post['settleexpendamount'].']';
            $this->_table->rec_update($unionarr,front::get('id'));
            $this->_view_user->rec_update(array('point'=>'[point-'.front::$post['settleexpendamount'].']'),front::get('id'));
            front::flash("{$this->tname}完成操作！");
            front::redirect(url::modify('act/list/table/'.$this->table));
        }
        $userunion = $this->_view_user->getrow(array('userid'=>front::get('id')));
        $this->_table1 = new union();
        $this->_view_table=$this->_table1->getrow(array('userid'=>front::get('id')));
        $this->_view_table=$this->_view_table+$userunion;
    }
    function pay_action() {
    	chkpw('union_pay');
        $this->_view_user = new user;
        $this->_union_pay = new union_pay();
        $where=null;
        $ordre='`payid` DESC';
        $limit=((front::get('page') -1) * $this->_pagesize).','.$this->_pagesize;
        $this->_view_table=$this->_union_pay->getrows($where,$limit,$ordre,$this->_union_pay->getcols('manage'));
        foreach($this->_view_table as $key=>$val) {
            $userunion = $this->_view_user->getrow(array('userid'=>$val['userid']));
            $val['username'] = $userunion['username'];
            $this->_view_table[$key] = $val;
        }
        $this->view->record_count=$this->_union_pay->record_count;
    }
    function visit_action() {
    	chkpw('union_visit');
        $this->_union_visit = new union_visit();
        $where=null;
        $ordre='`visitid` DESC';
        $limit=((front::get('page') -1) * $this->_pagesize).','.$this->_pagesize;
        $this->_view_table=$this->_union_visit->getrows($where,$limit,$ordre,$this->_union_visit->getcols('manage'));
        $this->view->record_count=$this->_union_visit->record_count;
    }
    function reguser_action() {
    	chkpw('union_reguser');
        $this->_view_user = new user;
        $where='introducer>0';
        $ordre='`userid` DESC';
        $limit=((front::get('page') -1) * $this->_pagesize).','.$this->_pagesize;
        $this->_view_table=$this->_view_user->getrows($where,$limit,$ordre,$this->_view_user->getcols('manage'));
        foreach($this->_view_table as $key=>$val) {
            $userunion = $this->_view_user->getrow(array('userid'=>$val['introducer']));
            $val['introducerusername'] = $userunion['username'];
            $this->_view_table[$key] = $val;
        }
        $this->view->record_count=$this->_view_user->record_count;
    }
    function config_action() {
    	chkpw('union_config');
        /*function str_replace_once($needle,$replace,$haystack) {
            $pos = @strpos($haystack,$needle);
            if ($pos === false) {
                return $haystack;
            }
            return substr_replace($haystack,$replace,$pos,strlen($needle));
        }*/
        if (front::post('submit') &&$this->manage->vaild()) {
            $this->manage->filter($this->Exc);
            $this->manage->add_before($this);
            $this->manage->save_before();
            $path = ROOT.'/config/union.php';
            $content = file_get_contents($path);
            foreach(front::$post['setting'] as $key=>$val) {
            	$content = preg_replace("/'$key'=>'(.*?)',/is","'$key'=>'$val',", $content);
                //preg_match_all("/'".$key."'=>'(.*?)',/isu",$content,$out);
                //var_dump($out);
                //$content = str_replace_once($out[1][0],$val,$content);
            }
            //exit;
            file_put_contents(ROOT.'/config/union.tmp.php',$content);
            if($_GET['site']!='default') {
                set_time_limit(0);
                $ftp = new nobftp();
                $ftpconfig = config::get('website');
                $ftp->connect($ftpconfig['ftpip'],$ftpconfig['ftpuser'],$ftpconfig['ftppwd'],$ftpconfig['ftpport']);
                $ftperror = $ftp->returnerror();
                if($ftperror) {
                    exit($ftperror);
                }else {
                    $ftp->nobchdir($ftpconfig['ftppath']);
                    $ftp->nobput($ftpconfig['ftppath'].'/config/union.php',ROOT.'/config/union.tmp.php');
                }
            }else {
                file_put_contents($path,$content);
            }
            unset($content);
            front::flash("{$this->tname}完成操作！");
            front::redirect(url::modify('act/config/table/'.$this->table));
        }
        $path = ROOT.'/config/union.php';
        $config = include $path;
        $this->_view_table=$config;
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
    function delete_action() {
        $this->manage->delete_before(front::get('id'));
        $delete=$this->_table->rec_delete(front::get('id'));
        if ($delete) front::flash("删除{$this->tname}成功！");
        front::redirect(url::modify('act/list/table/'.$this->table.'/bid/'.session::get('bid')));
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