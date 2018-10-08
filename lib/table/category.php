<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class category extends table
{
    public $name = 'b_category';
    static $me;

    function getcols($act)
    {
        return '*';
    }

    function get_form()
    {
        return array(
                'parentid'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(category::option(0,'isnotlast')),
                        'default'=>get('parentid'),
                ),
                'ishtml'=>array(
                        'selecttype'=>'radio',
                        'select'=>form::arraytoselect(array(0=>'继承',1=>'生成',2=>'不生成')),
                        'default'=>0,
                ),
                'isshow'=>array(
                        'selecttype'=>'radio',
                        'select'=>form::arraytoselect(array(1=>'正常显示',0=>'禁用')),
                    'default' => '1',
                ),
                'ispages'=>array(
                        'selecttype'=>'radio',
                        'select'=>form::arraytoselect(array(1=>'分页',0=>'单页')),
                        'default'=>1,
                ),
                'includecatarchives'=>array(
                        'selecttype'=>'radio',
                        'select'=>form::arraytoselect(array(1=>'包含',0=>'不包含')),
                        'default'=>1,
                ),
                'isecoding'=>array(
                    'selecttype'=>'radio',
                    'select'=>form::arraytoselect(array(0=>'继承',1=>'开启',2=>'不开启')),
                    'default'=>0,
                ),
                'scategory'=>array(
                        //'tips'=>"&nbsp;被调用的格式 categories(\$catid,'标记')",
                ),
                'image'=>array(
                        'filetype'=>'thumb',
                ),
                'template'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(front::$view->archive_tpl_list('archive/list')),
                        'default'=>"{?category::gettemplate(get('id'),'listtemplate',false)}",
                        //'tips'=>" 默认：{?category::gettemplate(get('id'))}",
                ),
                'listtemplate'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(front::$view->archive_tpl_list('archive/list')),
                        'default'=>"{?category::gettemplate(get('id'),'listtemplate',false)}",
                        //'tips'=>" 默认：{?category::gettemplate(get('id'),'listtemplate')}",
                ),
                'showtemplate'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(front::$view->archive_tpl_list('archive/show')),
                        'default'=>"{?category::gettemplate(get('id'),'showtemplate',false)}",
                        //'tips'=>" 默认：{?category::gettemplate(get('id'),'showtemplate')}",
                ),
        		'templatewap'=>array(
        				'selecttype'=>'select',
        				'select'=>form::arraytoselect(front::$view->mobile_tpl_list('archive/list')),
        				'default'=>"{?category::gettemplate(get('id'),'listtemplatewap',false)}",
        				//'tips'=>" 默认：{?category::gettemplate(get('id'))}",
        		),
        		'listtemplatewap'=>array(
        				'selecttype'=>'select',
        				'select'=>form::arraytoselect(front::$view->mobile_tpl_list('archive/list')),
        				'default'=>"{?category::gettemplate(get('id'),'listtemplatewap',false)}",
        				//'tips'=>" 默认：{?category::gettemplate(get('id'),'listtemplate')}",
        		),
        		'showtemplatewap'=>array(
        				'selecttype'=>'select',
        				'select'=>form::arraytoselect(front::$view->mobile_tpl_list('archive/show')),
        				'default'=>"{?category::gettemplate(get('id'),'showtemplatewap',false)}",
        				//'tips'=>" 默认：{?category::gettemplate(get('id'),'showtemplate')}",
        		),
                'showform'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(get_my_tables_list()),
                        'default'=>"0",
                ),
                'isnav'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(array(1=>'显示',0=>'不显示')),
                    'default' => 1,
                ),
        		'ismobilenav'=>array(
        				'selecttype'=>'select',
        				'select'=>form::arraytoselect(array(1=>'显示',0=>'不显示')),
                    'default' => 1,
        		),
                'htmlrule'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(getHtmlRule('category')),
                        'default'=>'',
                ),
                'listhtmlrule'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(getHtmlRule('category')),
                        'default'=>'',
                ),
                'showhtmlrule'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(getHtmlRule('archive')),
                        'default'=>'',
                ),
                'categorycontent' => array(
                'type' => 'mediumtext',
                ),
        );
    }
    public static function getInstance() {
        if (!self::$me) {
            $class=new category();
            $class->init();
            self::$me=$class;
        }
        return self::$me;
    }
    function init() {
        $_category=$this->getrows(null,1000,'listorder=0,listorder asc');
        $category=array();
        foreach ($_category as $one) {
            if (!front::$admin &&!$one['isshow']) continue;
            $category[$one['catid']]=$one;
        }
        $this->category=$category;
        $parent=array();
        foreach ($category as $one) {
            $parent[$one['catid']]=$one['parentid'];
        }
        $this->parent=$parent;
        $this->tree=new tree($parent);
    }
    function son($id) {
        if (!isset($this->tree)) $this->init();
        return $this->tree->get_son($id);
    }
    function sons($id) {
        if (!isset($this->tree)) $this->init();
        $sons=array();
        $this->tree->get_sons($id,$sons);
        return $sons;
    }
    function hasson($id) {
        return self::getInstance()->tree->has_son($id);
    }
    function getparents($id,$up=true) {
        if (!isset($this->tree)) $this->init();
        return $this->tree->get_parents($id);
    }
    static function getparentsid($id,$up=true) {
        $category=self::getInstance();
        if (!isset($category->tree)) $category->init();
        return $category->tree->get_parents($id);
    }
    function getparent($id) {
        if (isset($this->tree->parent[$id])) return $this->tree->parent[$id];
        else return false;
    }
    function getposition($id) {
        if (!isset($this->tree)) $this->init();
        $position=$this->tree->get_parents($id);
        return $position;
    }
    function getposition1($id) {
        if (!isset($this->tree)) $this->init();
        $position=$this->tree->get_parents1($id);
        return $position;
    }
    static function gettopparent($id) {
        $position=self::getInstance()->getposition($id);
        return $position[count($position) -1];
    }
    function htmlpath($id) {
        if (!isset($this->tree)) $this->init();
        $positions=$this->tree->get_parents($id);
        $path=array();
        foreach ($positions as $_id) {
            if ($_id &&isset($this->category[$_id])) $path[]=$this->category[$_id]['htmldir'];
        }
        return implode('/',$path);
    }
    static function option($catid=0,$tag='all',&$option=array(0=>'请选择...'),&$level=0) {
        $category=self::getInstance();
        if (is_array($category->son($catid))) foreach ($category->son($catid) as $_catid) {
                if (!self::check($_catid,$tag)) continue;
                $strpre=$level >0 ?str_pad('',$level * 12,'&nbsp;').'└&nbsp;': '';
                $option[$_catid]=$strpre.$category->category[$_catid]['catname'];
                if (is_array($category->son($_catid))) {
                    $level++;
                    self::option($_catid,$tag,$option,$level);
                    $level--;
                }
            }
        return $option;
    }
    static function name($catid) {
        $category=self::getInstance();
        if (isset($category->category[$catid]['catname'])) return $category->category[$catid]['catname'];
        else return '';
    }
    static function categorypages($catid) {
        $category=self::getInstance();
        if (isset($category->category[$catid]['attr3'])) return $category->category[$catid]['attr3'];
        else return '';
    }
    static function image($catid) {
        $category=self::getInstance();
        if (isset($category->category[$catid]['image'])) return config::get('base_url').'/'.$category->category[$catid]['image'];
        else return '';
    }
    static function url($catid,$page=null,$relative=false) {
        //var_dump(front::$get);
        $category=self::getInstance();
        if (@$category->category[$catid]['linkto']) return $category->category[$catid]['linkto'];
        
        if(front::$ismobile == true){
        	if (config::get('wap_html_prefix')){
        		$wap_html_prefix='/'.trim(config::get('wap_html_prefix'),'/');
        	}
        	if(front::$rewrite){
        		if (!$page){
        			return config::get('site_url').'list_wap_'.$catid.'.htm';
        		}else{
        			return config::get('site_url').'list_wap_'.$catid.'_'.$page.'.htm';
        		}
        	}
        	if (!category::getiswaphtml($catid)) {
        		if (!$page){
        			return url::create('archive/list/t/wap/catid/'.$catid);
        		}else{
        			return url::create('archive/list/t/wap/catid/'.$catid.'/page/'.$page);
        		}
        	}else{
        		$rule=category::gethtmlrule($catid,'listhtmlrule');
        		$rule=str_replace('{$caturl}',$category->htmlpath($catid),$rule);
        		$rule=str_replace('{$dir}',$category->category[$catid]['htmldir'],$rule);
        		$rule=str_replace('{$catid}',$catid,$rule);
        		if ($category->category[$catid]['ispages'] &&!$page) $page=1;
        		if ($page) $rule=str_replace('{$page}',$page,$rule);
        		else $rule=preg_replace('/\(.*?\)/','',$rule);
        		$rule=preg_replace('%/\.html$%','/index.html',$rule);
        		$rule=preg_replace('/[\(\)]/','',$rule);
        		$rule=preg_replace('%[\\/]index\.htm(l)?%','',$rule);
        		$rule=rtrim($rule,'/');
        		$rule=trim($rule,'\\');
        		if ($relative) return $wap_html_prefix.'/'.$rule;
        		$rule=str_replace('/1.html','',$rule);
        		$path = config::get('base_url').$wap_html_prefix.'/'.$rule;
        		//echo $path;
        		return $path;
        	}
        }
        
        if (config::get('html_prefix')) $html_prefix='/'.trim(config::get('html_prefix'),'/');
        if (!category::getishtml($catid) ||front::$rewrite) {
            if (!$page) return url::create('archive/list/catid/'.$catid);
            else return url::create('archive/list/catid/'.$catid.'/page/'.$page);
        }
        else {
            $rule=category::gethtmlrule($catid,'listhtmlrule');
            $rule=str_replace('{$caturl}',$category->htmlpath($catid),$rule);
            $rule=str_replace('{$dir}',$category->category[$catid]['htmldir'],$rule);
            $rule=str_replace('{$catid}',$catid,$rule);
            if ($category->category[$catid]['ispages'] &&!$page){
                $page=1;
            }
            if ($page){
                $rule=str_replace('{$page}',$page,$rule);
            }else{
                $rule=preg_replace('/\(.*?\)/','',$rule);
            }
            $rule=preg_replace('%/\.html$%','/index.html',$rule);
            $rule=preg_replace('/[\(\)]/','',$rule);
            $rule=preg_replace('%[\\/]index\.htm(l)?%','',$rule);
            $rule=rtrim($rule,'/');
            $rule=trim($rule,'\\');
            if ($relative) return $html_prefix.'/'.$rule;
            $rule=str_replace('/1.html','',$rule);
            return config::get('base_url').$html_prefix.'/'.$rule;
        }
    }
    static function getpositionlink($catid) {
        $category=self::getInstance();
        if (!isset($category->category[$catid])) return;
        $position=$category->getposition($catid);
        $links=array();
        if (!$catid) return $links;
        foreach ($position as $order=>$id) {
            $links[$order]['id']=$id;
            $links[$order]['name']=@$category->category[$id]['catname'];
            $links[$order]['url']=self::url($id);
        }
        return $links;
    }
    static function getpositionlink1($catid) {
        $category=self::getInstance();
        if (!isset($category->category[$catid])) return;
        $position=$category->getposition($catid);
        $links=array();
        if (!$catid) return $links;
        foreach ($position as $order=>$id) {
            $links['id']=$id;
            $links['name']=@$category->category[$id]['catname'];
            $links['url']=self::url($id);
            break;
        }
        return $links;
    }
    static function getpositionlink2($catid) {
        $category=self::getInstance();
        if (!isset($category->category[$catid])) return;
        $position=$category->getposition1($catid);
        $links=array();
        if (!$catid) return $links;
        foreach ($position as $order=>$id) {
            $links[$order]['id']=$id;
            $links[$order]['name']=@$category->category[$id]['catname'];
            $links[$order]['url']=self::url($id);
        }
        return $links;
    }
    static function gettemplate($catid,$tag='listtemplate',$up=true) {
        if (!$catid &&front::get('parentid')) $catid=front::get('parentid');
        $category=self::getInstance();
        if (@$category->category[$catid]['template'] &&$tag == 'listtemplate') return $category->category[$catid]['template'];
        if (@$category->category[$catid][$tag]) return $category->category[$catid][$tag];
        if (!$up) return;
        $parents=$category->getparents($catid,true);
        ksort($parents);
        foreach ($parents as $pid) {
            if ($pid == $catid) continue;
            if (@$category->category[$pid][$tag]) return $category->category[$pid][$tag];
        }
        $default=array(
                'listtemplate'=>'archive/list.html',
                'showtemplate'=>'archive/show.html',
        );
        if (isset($default[$tag])) return $default[$tag];
    }
    static function gettemplatewap($catid,$tag='listtemplatewap',$up=true) {
    	//echo 11;
    	if (!$catid &&front::get('parentid')) $catid=front::get('parentid');
    	$category=self::getInstance();
    	if (@$category->category[$catid]['templatewap'] &&$tag == 'listtemplatewap') return $category->category[$catid]['templatewap'];
    	if (@$category->category[$catid][$tag]) return $category->category[$catid][$tag];
    	if (!$up) return;
    	//echo 22;
    	$parents=$category->getparents($catid,true);
    	ksort($parents);
    	foreach ($parents as $pid) {
    		if ($pid == $catid) continue;
    		if (@$category->category[$pid][$tag]) return $category->category[$pid][$tag];
    	}
    	$default=array(
    			'listtemplatewap'=>'archive/list.html',
    			'showtemplatewap'=>'archive/show.html',
    	);
    	//echo 11;
    	if (isset($default[$tag])) return $default[$tag];
    }
    static function gethtmlrule($catid,$tag='listhtmlrule') {
        if (!$catid &&front::get('parentid')) $catid=front::get('parentid');
        $category=self::getInstance();
        if (@$category->category[$catid]['htmlrule'] &&$tag == 'listhtmlrule') return $category->category[$catid]['htmlrule'];
        if (@$category->category[$catid]['showhtmlrule'] &&$tag == 'showhtmlrule') return $category->category[$catid]['showhtmlrule'];
        $parents=$category->getparents($catid,true);
        ksort($parents);
        foreach ($parents as $pid) {
            if ($pid == $catid) continue;
            if (@$category->category[$pid][$tag]) return $category->category[$pid][$tag];
        }
        $default=array(
                'listhtmlrule'=>'{$dir}/{$page}.html',
                'showhtmlrule'=>'{$dir}/show-{$aid}-{$page}.html',
        );
        if (isset($default[$tag])) return $default[$tag];
    }
    static function getishtml($catid) {
        if (config::get('list_page_php') == '1') return true;
        if (config::get('list_page_php') == '2') return false;
        $category=self::getInstance();
        if (@$category->category[$catid]['ishtml'] == '1') return true;
        $parents=$category->getparents($catid,true);
        ksort($parents);
        foreach ($parents as $pid) {
            if ($pid == $catid) continue;
            if (@$category->category[$pid]['ishtml'] == '1') return true;
            if (@$category->category[$pid]['ishtml'] == '2') return false;
        }
        return false;
    }
    static function getiswaphtml($catid) {
    	if (config::get('wap_list_page_php') == '1') return true;
    	if (config::get('wap_list_page_php') == '2') return false;
    	$category=self::getInstance();
    	if (@$category->category[$catid]['iswaphtml'] == '1') return true;
    	$parents=$category->getparents($catid,true);
    	ksort($parents);
    	foreach ($parents as $pid) {
    		if ($pid == $catid) continue;
    		if (@$category->category[$pid]['iswaphtml'] == '1') return true;
    		if (@$category->category[$pid]['iswaphtml'] == '2') return false;
    	}
    	return false;
    }
    static function getarcishtml($arc) {
        if (config::get('show_page_php') == '1') return true;
        if (config::get('show_page_php') == '2') return false;
        if ($arc['ishtml']) return true;
        if (self::getishtml($arc['catid'])) return true;
        return false;
    }
    static function getarciswaphtml($arc) {
    	if (config::get('wap_show_page_php') == '1') return true;
    	if (config::get('wap_show_page_php') == '2') return false;
    	if ($arc['iswaphtml']) return true;
    	if (self::getiswaphtml($arc['catid'])) return true;
    	return false;
    }
    static function getattr($categoryid,$attr) {
        $category=self::getInstance();
        if (@$category->category[$categoryid][$attr]) return $category->category[$categoryid][$attr];
        $parents=$category->getparents($categoryid,true);
        ksort($parents);
        foreach ($parents as $pid) {
            if ($pid == $categoryid) continue;
            if (@$category->category[$pid][$attr]) return $category->category[$categoryid][$attr];
        }
        return false;
    }
    static function getwidthofthumb($catid) {
        $width=self::getattr($catid,'thumb_width');
        if (!$width) $width=config::get('thumb_width');
        return $width;
    }
    static function getheightofthumb($catid) {
        $height=self::getattr($catid,'thumb_height');
        if (!$height) $height=config::get('thumb_height');
        return $height;
    }
    static function getcategorydata($_catid=0,&$data=array(),&$level=0) {
        $category=self::getInstance();
        $categorys=$category->son($_catid);
        foreach ($categorys as $catid) {
            $info_=$category->category[$catid];
            $strpre=$level >0 ?str_pad('',$level * 12,'&nbsp;').'└&nbsp;': '';
            $info_['catname']=$strpre.$info_['catname'].'<font color="Blue">'.(self::check($catid,'islast') ?('('.countarchiveformcategory($catid).')') : '').'</font>';
            $info_['level']=$level;
            $data[]=$info_;
            if (is_array($category->son($catid))) {
                $level++;
                self::getcategorydata($catid,$data,$level);
                $level--;
            }
        }
        return $data;
    }
    static function listcategorydata($_catid=0,&$data=array(),&$level=0) {
        $category=self::getInstance();
        $categorys=$category->son($_catid);
        foreach ($categorys as $catid) {
            $info_=$category->category[$catid];
            $strpre=$level >0 ?str_pad('',$level * 12,'&nbsp;').'└&nbsp;': '';
            $info_['catname']=$strpre.$info_['catname'];
            $info_['url']=category::url($info_['catid']);
            $info_['level']=$level;
            $info_['parentid']=$category->getparent($info_['catid']);
            $data[]=$info_;
            if (is_array($category->son($catid))) {
                $level++;
                self::listcategorydata($catid,$data,$level);
                $level--;
            }
        }
        return $data;
    }
    
    static function check($catid,$tag='isnotlast') {
        return true;
        $_category=self::getInstance();
        $category=$_category->category[$catid];
        if ($tag == 'islast'&&!$category['islast']) return false;
        if ($tag == 'isnotlast'&&$category['islast']) return false;
        if ($tag == 'tolast') {
            if ($_category->category[$catid]['islast']) return true;
            $sons=$_category->sons($catid);
            foreach ($sons as $tid) {
                if ($_category->category[$tid]['islast']) return true;
            }
            return false;
        }
        return true;
    }
    static function htmlcache($catid) {
    }
    
    public function sitemap($path='./',$filename='sitemap.html'){
    	category::listcategorydata(0,$arr,$level);
    	front::$view->archive = $arr;
    	$html = front::$view->fetch('system/sitemap.html');
    	file_put_contents($path.$filename,$html);
    }
    
    static function listdata($parentid=0,$limit=10,$order='catid asc',$where=null,$includeson=true) {
        $category=new category();
        $where='parentid='.($parentid?$parentid:'0').($where ?' and '.$where : '');
        $categories=$category->getrows($where,$limit,$order);
        foreach ($categories as $order=>$category) {
            $categories[$order]['url']=category::url($category['catid']);
        }
        return $categories;
    }
}