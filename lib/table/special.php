<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class special extends table {
	
    public $name='b_special';
    public static $me;
    
    public static function getInstance() {
    	if (!self::$me) {
    		$class=new special();
    		self::$me=$class;
    	}
    	return self::$me;
    }
    
    function getcols($act) {
        switch ($act) {
            case 'manage':
                return 'spid,title,banner';
            default: return '*';
        }
    }
    function get_form() {
        return array(
                'banner'=>array(
                        'filetype'=>'thumb',
                ),
        		'ishtml'=>array(
        				'selecttype'=>'radio',
        				'select'=>form::arraytoselect(array(0=>'不生成',1=>'生成')),
        				'default'=>0,
        		),
				'template'=>array(
                    'selecttype'=>'select',
                    'select'=>form::arraytoselect(front::$view->special_tpl_list()),
                    //'tips'=>" 默认：{?category::gettemplate(get('id'),'showtemplate')}",
                ),
        );
    }
    static function url($spid,$ishtml=false,$page=1) {
    	$ishtml = special::getishtml($spid);
    	if(front::$get['t'] == 'wap'){
    		if($ishtml){
    			return config::get('site_url').'special_wap/'.$spid.'/list-'.$page.'.html';
    		}else{
    			return url::create('special/show/t/wap/spid/'.$spid.($page >1 ?'/page/'.$page : ''),false);
    		}
    	}
    	if(!$ishtml && !front::$rewrite){
        	return url::create('special/show/spid/'.$spid.($page >1 ?'/page/'.$page : ''),false);
    	}else if(front::$rewrite){
    		return config::get('site_url').'speciallist-'.$spid.'-'.$page.'.htm';
    	}else{
    		return config::get('site_url').'special/'.$spid.'/list-'.$page.'.html';
    	}
    }
    function pagination() {
        return template('system/special_pagination.html');
    }
    function option() {
        $sp=new special();
        $sps=$sp->getrows('',500);
        $options=array(0=>'请选择...');
        foreach ($sps as $sp) {
            $options[$sp['spid']]=$sp['title'];
        }
        return $options;
    }
    function gettitle($spid) {
        if (empty($spid)) return;
        $sp=new special();
        $sp=$sp->getrow('spid='.$spid);
        return $sp['title'];
    }
    function getishtml($spid) {
    	if (empty($spid)) return;
    	$sp=new special();
    	$sp=$sp->getrow('spid='.$spid);
    	return $sp['ishtml'];
    }
    function listdata($limit=10) {
        $special=new special();
        $specials=$special->getrows('',$limit);
        foreach ($specials as $order=>$sp) {
            $specials[$order]['url']=special::url($sp['spid'],$sp['ishtml']);
        }
        return $specials;
    }
}