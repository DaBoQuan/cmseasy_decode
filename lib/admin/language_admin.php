<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');
class language_admin extends admin {
    function init() {
    }
    function add_action() {
        if (front::post('submit')) {
            $path=ROOT.'/lang/'.config::get('lang_type').'/system.php';
            $tipspath=ROOT.'/lang/cn/system.php';
            $content=file_get_contents($path);
            $tipscontent=file_get_contents($tipspath);
            $replace="'".front::$post['key']."'=>'".front::$post['val']."',";
            $tipsreplace="'".front::$post['key']."'=>'".front::$post['cnnote']."',";
            $content=str_replace(');',"\n".$replace.');',$content);
            file_put_contents($path,$content);
            $pos=strpos($tipscontent,$tipsreplace);
            if (config::get('lang_type') != 'cn'&&$pos === false) {
                $tipscontent=str_replace(');',"\n".$tipsreplace.');',$tipscontent);
                file_put_contents($tipspath,$tipscontent);
            }
            if ($_GET['site'] != 'default') {
                $ftp=new nobftp();
                $ftpconfig=config::get('website');
                $ftp->connect($ftpconfig['ftpip'],$ftpconfig['ftpuser'],$ftpconfig['ftppwd'],$ftpconfig['ftpport']);
                $ftperror=$ftp->returnerror();
                if ($ftperror) {
                    exit($ftperror);
                }
                else {
                    $ftp->nobchdir($ftpconfig['ftppath']);
                    $ftp->nobput($ftpconfig['ftppath'].'/lang/'.config::get('lang_type').'/system.php',$path);
                }
            }
            event::log('添加语言包','成功');
            echo '<script type="text/javascript">alert("操作完成！");window.location.href="'.url('language/edit',true).'";</script>';
            //exit;
            //front::refresh(url('language/edit',true));
        }
    }
    function edit_action() {
        $path=ROOT.'/lang/'.config::get('lang_type').'/system.php';
        $tipspath=ROOT.'/lang/cn/system.php';
        if (front::post('submit')) {
            $content=file_get_contents($path);
            $to_delete_items=front::$post['to_delete_items'];
            unset(front::$post['to_delete_items']);
            foreach (front::$post as $key=>$val) {
                preg_match_all("/'".$key."'=>'(.*?)',/",$content,$out);
                if (is_array($to_delete_items) && in_array($key,$to_delete_items))
                    $content=str_replace($out[0][0],'',$content);
                else
                    $content=str_replace($out[1][0],$val,$content);
            }
            file_put_contents($path,$content);
            if ($_GET['site'] != 'default') {
                $ftp=new nobftp();
                $ftpconfig=config::get('website');
                $ftp->connect($ftpconfig['ftpip'],$ftpconfig['ftpuser'],$ftpconfig['ftppwd'],$ftpconfig['ftpport']);
                $ftperror=$ftp->returnerror();
                if ($ftperror) {
                    exit($ftperror);
                }
                else {
                    $ftp->nobchdir($ftpconfig['ftppath']);
                    $ftp->nobput($ftpconfig['ftppath'].'/lang/'.config::get('lang_type').'/system.php',$path);
                }
            }
            unset($content);
            event::log('修改语言包','成功');
            echo '<script type="text/javascript">alert("操作完成！");window.location.href="'.url('language/edit',true).'";</script>';
        }
        $content=include($path);
        $tips=include($tipspath);
        $this->view->tips=$tips;
        //分页
        $limit = 30;
        if(!front::get('page'))
            $page = 1;
        else
            $page = front::get('page');
        $total = ceil(count($content)/$limit);
        if($page < 1) $page = 1;
        if($page > $total) $page = $total;
        $start = ($page-1) * $limit;
        $end = $start+$limit-1;
        $tmp = range($start,$end);
        $list_content_arr = array();
        $i = 0;
        foreach($content as $k => $v){
        	if(in_array($i++,$tmp))
        	     $list_content_arr[$k] = $v;
        }
        $this->view->sys_lang=$list_content_arr;
        $this->view->link_str = listPage($total,$limit,$page);

    }
    function delete_action() {
        $path=ROOT.'/lang/'.config::get('lang_type').'/system.php';
        $lang=include $path;
        event::log('删除语言包','成功');
        exit;
        front::refresh(url('language/edit',true));
    }
    function end() {
        $this->render('index.php');
    }
}