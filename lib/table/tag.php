<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class tag extends table {
    public  $name='b_tag';
    
    function getcols($act) {
    	switch ($act) {
    		case 'list':
    			return 'tagid,tagname';
    		case 'modify':
    			return 'tagid,tagname';
    		case 'manage':
    			return 'tagid,tagname';
    		default: return '1';
    	}
    }
    
    function url($tag,$page=1) {
    	if(front::$get['t'] == 'wap'){
    		
    		if(config::get('tag_html')){
    			$otag = new tag();
    			$row = $otag->getrow("tagname='$tag'");
    			$tagid= $row['tagid'];
    			$pinyin = pinyin::get($tag);
    			return config::get('base_url').'/tags-wap/'.$pinyin.'-'.$tagid.'-'.$page.'.html';
    		}
    		if(front::$rewrite){
    			return config::get('base_url').'/tags-wap-'.urlencode($tag).'-'.$page.'.htm';
    		}
    		return url::create('tag/show/t/wap/tag/'.urlencode($tag).($page>1?'/page/'.$page:''),false);

    	}
    	if(config::get('tag_html')){
    		$otag = new tag();
    		$row = $otag->getrow("tagname='$tag'");
    		$tagid= $row['tagid'];
    		$pinyin = pinyin::get($tag);
    		return config::get('base_url').'/tags/'.$pinyin.'-'.$tagid.'-'.$page.'.html';
    	}
    	if(front::$rewrite){
    		return config::get('base_url').'/tags-'.urlencode($tag).'-'.$page.'.htm';
    	}
    	return url::create('tag/show/tag/'.urlencode($tag).($page>1?'/page/'.$page:''),false);
    }
    
    static function getTags() {
    	$data=array();
    	$data[0] = '请选择';
    	$otag = new tag();
    	$row = $otag->getrows('',0,'tagid ASC');
    	if(is_array($row)){
    		foreach ($row as $arr){
    			$data[$arr['tagname']] = $arr['tagname'];
    		}
    	}
    	//return $data;
    	$sets=settings::getInstance()->getrow(array('tag'=>'table-hottag'));
    	if (!is_array($sets)){
    		return $data;
    	}
    	$data1=unserialize($sets['value']);
    	preg_match_all('%\(([\d\w\/\.-]+)\)(\S+)%m',$data1['hottag'],$result,PREG_SET_ORDER);
    	foreach ($result as $res)
    		$data[$res[2]]=$res[2];
    	
    	return $data;
    }
    
    function urls($tagstring) {
        $tags=explode(',',$tagstring);
        $urls=array();
        foreach($tags as $tag) {
            if($tag)
                $urls[$tag]=$this->url($tag);
        }
        return $urls;
    }
    function pagination() {
        return template('system/tag_pagination.html');
    }
}