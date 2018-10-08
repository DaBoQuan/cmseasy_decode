<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');

class wapcache_admin extends admin {

    public $archive;

    function init() {
        header('Cache-control: private, must-revalidate');
        front::$admin = false;
        front::$html = true;
    }

    function make_index_action() {
    	$servip = gethostbyname($_SERVER['SERVER_NAME']);
    	if($servip==front::ip()&&front::get('ishtml')==1){
    		
    	}else{
    		chkpw('cache_index');
    	}
        $case = 'index';
        $act = 'index';
        $_GET = array('case' => $case, 'act' => $act,'t'=>'wap');
        //$front = new front();
        front::$ismobile = true;
        front::$admin = false;
        front::$html = true;
        $case = $case . '_act';
        $case = new $case();
        $case->init();
        $method = $act . '_action';
        $view = $case->view;
        file_put_contents(ROOT . '/wap/index.html', $case->fetch('index/index.html'));
        front::flash('成功生成手机版首页！');
        front::redirect(front::$from);
    }
    
    function make_special_action() {
    	chkpw('cache_special');
        header('Cache-control: private, must-revalidate');
        @set_time_limit(0);
        if (!front::post('submit'))
            return;
        $speciaid = intval(front::$post['specialid']);
        $special=new special();
        $specials=$special->getrow($speciaid);
        if(!$specials['ishtml']){
        	front::flash("没有生成html！");
        	return;
        }
        $archive_all = new archive();
        $archive_num = $archive_all->rec_count('spid=' . $speciaid . ' and checked=1 and `state`=1');
        $pagesize = config::get('list_pagesize');
        if(!$archive_num) $archive_num = 1;
        $cpage = ceil($archive_num/$pagesize);
        $j=0;
        for($i=1;$i<=$cpage;$i++){
            $path = 'special_wap/'.$speciaid.'/list_'.$i.'.html';
            tool::mkdir(dirname($path));
            $data = file_get_contents(config::get('site_url').'index.php?case=special&act=show&t=wap&spid='.$speciaid.'&page='.$i);
            if(file_put_contents($path, $data)){
                $j++;
            }
        }
        if ($j > 0){
        	
        	$path = 'special_wap/'.$speciaid.'/index.html';
            tool::mkdir(dirname($path));
            $data = file_get_contents(config::get('site_url').'index.php?case=special&act=show&t=wap&spid='.$speciaid.'&page=1');
            if(file_put_contents($path, $data)){
                //front::flash("成功生成html <b>1</b> 页！");
            }
            front::flash("成功生成html <b>$j</b> 页！");
        }
    }
    
	
    
    function make_tag_action() {
    	chkpw('cache_tag');
    	header('Cache-control: private, must-revalidate');
    	set_time_limit(0);
    	if(!front::$get['tag']){
    		front::$get['tag'] = front::$post['tag'];
    	}
    	if(!front::$get['submit']){
    		front::$get['submit'] = front::$post['submit'];
    	}
    	$otag=new tag();
    	$tags=$otag->getrows("",0);
    	//var_dump($tags);
    	$tags = $this->view->hottags = array_to_hashmap($tags,'tagid','tagname');
    	
    	if (!front::get('submit'))
    		return;
    	if(!config::get('tag_html')||!front::$get['tag']){
    		front::flash("没有设置生成html！");
    		front::redirect(front::$from);
    		return;
    	}
    	$tagid = front::$get['tag'];
    	$tag = $tags[$tagid];
    	$pinyin = pinyin::get($tag);
    	
    	$arctag=new arctag();
    	$archive_num=$arctag->rec_count('tagid='.$tagid);

    	front::$record_count = $archive_num;
    	$pagesize = config::get('list_pagesize');
    	front::$pages = $pagesize;
    	$cpage = ceil($archive_num/$pagesize);
    	$j=0;
    	for($i=1;$i<=$cpage;$i++){
    		$path = 'tags_wap/'.$pinyin.'_'.$tagid.'_'.$i.'.html';
    		tool::mkdir(dirname($path));
    		$url = config::get('site_url').'index.php?case=tag&act=show&t=wap&tag='.urlencode($tag).'&page='.$i;
    		//exit($url);
    		$data = file_get_contents($url);
    		if(file_put_contents($path, $data)){
    			$j++;
    		}
    	}
    	if ($j > 0){
    		front::flash("成功生成html <b>$j</b> 页！");
    		front::redirect(front::$from);
    	}else{
    		front::flash("没有数据生成html！");
    		front::redirect(front::$from);
    	}
    }

    function make_type_action() {
    	chkpw('cache_type');
        header('Cache-control: private, must-revalidate');
        @set_time_limit(0);
        if (!front::post('submit'))
            return;

        $case = 'type';
        $act = 'list';
        $_GET = array('case' => $case, 'act' => $act);
        //$front = new front();
        front::$admin = false;
        front::$html = true;
        front::$rewrite = false;
        front::$get['t'] = 'wap';
        $case = $case . '_act';
        $case = new $case();
        $case->init();
        //$method = $act . '_action';
        //$totalpage = 100;
        //$time_start = time::getTime();

        if(!config::get('wap_type_html')){
            front::flash("没有配置生成手机分类HTML！");
            return;
        }
        $type = type::getInstance();
        $typeid = front::post('typeid');
        /*if ($typeid && !$type->getishtml($typeid)) {
            front::flash("该分类不需要生成html！");
            return;
        }*/
        if($typeid){
        	$arrtype = $type->getrows($typeid);
        }else{
        	$arrtype = $type->getrows('',0);
        }
        $cpage = 0;
        if(is_array($arrtype) && !empty($arrtype)){
        	foreach($arrtype as $v){
        		if (!$type->getWapishtml($v['typeid'])) {
        			continue;
        		}
        		$archive_all = new archive();
        		$archive_num = $archive_all->rec_count('typeid=' . $v['typeid'] . ' and checked=1 and `state`=1');
        		for (front::$get['page'] = 1;; front::$get['page']++) {
        			$view = $case->view;
        			$pagesize = config::get('list_pagesize');
        			$limit = ((front::$get['page'] - 1) * $pagesize) . ',' . $pagesize;
        			$archive = new archive();
        			$case->view->archives = $archive->getrows("typeid=".$v['typeid']." and checked=1 and `state`=1", $limit, '`listorder` desc,aid desc');
        			$case->view->page = front::$get['page'];
        			$case->view->type = $v;
        			$case->view->typeid = $v['typeid'];
        			$case->view->pages = $v['ispages'];
        		
        			foreach ($case->view->archives as $order => $arc) {
        				$articles = $arc;
        				if (!$arc['introduce'])
        					$arc['introduce'] = cut($arc['content'], 200);
        				$articles['url'] = archive::url($arc);
        				$articles['catname'] = category::name($arc['catid']);
        				$articles['caturl'] = category::url($arc['catid']);
        				$articles['sthumb'] = @strstr($arc['thumb'], "http://") ? $arc['thumb'] : config::get('base_url') . '/' . $arc['thumb'];
        				$articles['strgrade'] = archive::getgrade($arc['grade']);
        				$articles['adddate'] = sdate($arc['adddate']);
        				$articles['buyurl'] = url('archive/orders/aid/' . $arc['aid']);
        				$articles['stitle'] = strip_tags($arc['title']);
        				$case->view->archives[$order] = $articles;
        			}
        			if (!isset($page_count)) {
        				front::$record_count = $case->view->record_count = $archive_num;
        				$case->view->page_count = ceil($case->view->record_count / $pagesize);
        				$page_count = $case->view->page_count;
        			}
        		
        			if (front::get('page') > 1 && front::get('page') > $case->view->page_count) {
        				$page_count = null;
        				break;
        			}
        			//$tpl = type::gettemplate($v['typeid']);
                    $tpl = type::gettemplate($v['typeid']);
        			//$tpl = 'wap/type_list.html';
        			$content = $case->fetch($tpl);
        			$path = type::url($v['typeid'], front::$get['page']);
        			//var_dump($path);exit;
        			if (!preg_match('/\.[a-zA-Z]+$/', $path))
        				$path = rtrim(rtrim($path, '/'), '\\') . '/index.html';
        			$path = rtrim($path, '/');
        			$path = rtrim($path, '\\');
        			$path = str_replace('//', '/', $path);
        			if(config::get('base_url') == '/'){
        				$path = ROOT.substr($path,1);
        			}else{
        				$path = ROOT.str_replace(config::get('base_url'), '', $path);
        			}
        			//exit($path);
        			tool::mkdir(dirname($path));
        			//var_dump($path);
        			//var_dump($content);
        			if (!file_put_contents($path, $content)) {
        				front::flash("写入HTML失败！");
        			}
        			$indexpath = dirname($path) . '/index.html';
        			if (front::$get['page'] == 1 && $indexpath != ROOT . '/index.html') {
        				file_put_contents($indexpath, $content);
        			}
        			$cpage++;
        			$case->view = $view;
        		}
        	}
        }
        
        if ($cpage > 0)
            front::flash("成功生成html <b>$cpage</b> 页！");
        else
            front::flash("没有生成html！");
        front::$admin = true;
    }

    function make_list_action() {
    	$servip = gethostbyname($_SERVER['SERVER_NAME']);
    	if($servip==front::ip()&&front::get('ishtml')==1){
    	
    	}else{
    		chkpw('cache_category');
    	}
        header('Cache-control: private, must-revalidate');
        @set_time_limit(0);
        if (!front::post('submit'))
            return;
        $case = 'archive';
        $act = 'list';
        $_GET = array('case' => $case, 'act' => $act);
        //$front = new front();
        front::$admin = false;
        front::$html = true;
        front::$rewrite = false;
        front::$ismobile = true;
        front::$get['t'] = 'wap';
        $case = $case . '_act';
        $case = new $case();
        $case->init();
        $method = $act . '_action';
        $totalpage = 100;
        $time_start = time::getTime();
        $category = category::getInstance();
        $categories = $category->sons(front::post('catid'));
        $categories[] = front::post('catid');
        $cpage = 0;
        $archive_all = new archive();
        foreach ($categories as $key => $catid) {
            $new_categories = $category->sons($catid);
            $new_categories[] = $catid;
            $archive_num[$catid] = $archive_all->rec_count('catid in(' . implode(',', $new_categories) . ') and checked=1 and `state`=1');
        }
        $i = 0;
        foreach ($categories as $catid) {
            if ($catid == 0)
                continue;
            if (!category::getiswaphtml($catid))
                continue;
            front::$get['catid'] = $catid;
            $case->view->categories = category::getpositionlink2($catid);
            $_categories = $category->sons($catid);
            $_categories[] = $catid;
            $case->view->ifson = category::hasson($catid);
            for (front::$get['page'] = 1;; front::$get['page']++) {
                $view = $case->view;
                $_catpage = category::categorypages($catid);
                if ($_catpage) {
                    $pagesize = $_catpage;
                } else {
                    $pagesize = config::get('list_pagesize');
                }
                $limit = ((front::$get['page'] - 1) * $pagesize) . ',' . $pagesize;
                $archive = new archive();
                $tops = array();
                $tops = $archive->getrows("checked=1 AND (state IS NULL or state<>'-1') AND toppost!=0", 0, 'toppost DESC,listorder=0,listorder ASC,aid DESC');
                if (@$category->category[$catid]['includecatarchives']) {
                    $case->view->archives = $archive->getrows('catid in(' . implode(',', $_categories) . ') and checked=1 and (state IS NULL or state<>\'-1\')', $limit, 'listorder=0,`listorder` asc,`aid` DESC');
                } else {
                    $case->view->archives = $archive->getrows("catid=$catid and checked=1 and `(state IS NULL or state<>'-1')", $limit, 'listorder=0,`listorder` asc,`aid` DESC');
                }
                $case->view->page = front::$get['page'];

                if(is_array($tops) && !empty($tops)){
                	foreach($tops as $order => $arc){
                		if($arc['toppost'] == 3){
                			$tops[$order]['title'] = "[全站置顶]".$arc['title'];
                		}
                		if($arc['toppost'] == 2){
                			$subcatids = $category->sons($arc['catid']);
                			if($arc['catid'] != front::get('catid') && !in_array(front::get('catid'), $subcatids)){
                				unset($tops[$order]);
                			}else{
                				$tops[$order]['title'] = "[栏目置顶]".$arc['title'];
                			}
                		}
                	}
                	$case->view->archives = array_merge($tops , $case->view->archives);
                }
                
                foreach ($case->view->archives as $order => $arc) {
                    $articles = $arc;
                    if (!$arc['introduce'])
                        $arc['introduce'] = cut($arc['content'], 200);
                    $articles['url'] = archive::url($arc);
                    $articles['catname'] = category::name($arc['catid']);
                    $articles['caturl'] = category::url($arc['catid']);
                    $articles['image'] = @strstr($arc['image'], "http://") ? $arc['image'] : config::get('base_url') . '/' . $arc['image'];
                    $articles['strgrade'] = archive::getgrade($arc['grade']);
                    $articles['adddate'] = sdate($arc['adddate']);
                    $articles['buyurl'] = url('archive/orders/aid/' . $arc['aid']);
                    $articles['stitle'] = strip_tags($arc['title']);
                    if(strtolower(substr($arc['thumb'],0,7)) == 'http://'){
                    	$articles['sthumb'] = $arc['thumb'];
                    }else{
                    	$articles['sthumb'] = config::get('base_url').'/'.$arc['thumb'];
                    }
                    
                    if($arc['strong']) {
                    	$articles['title'] = '<strong>'.$arc['title'].'</strong>';
                    }
                    if($arc['color']) {
                    	$articles['title'] = '<font style="color:'.$arc['color'].';">'.$articles['title'].'</font>';
                    }
                    
                    $case->view->archives[$order] = $articles;
                }
                
                if (!isset($page_count)) {
                    front::$record_count = $case->view->record_count = $archive_num[$catid];
                    $case->view->page_count = ceil($case->view->record_count / $pagesize);
                    $page_count = $case->view->page_count;
                }

                $case->view->catid = $catid;
                $case->view->topid = category::gettopparent($catid);
                $case->view->parentid = $category->getparent($catid);
                $case->view->pages = @$category->category[$catid]['ispages'];
                $case->view->subids = $category->son($catid);
                //var_dump($case->view->subids);exit;
                if (front::get('page') > 1 && front::get('page') > $case->view->page_count) {
                    $page_count = null;
                    break;
                }
                if (front::get('page') > 1 && !@$category->category[$catid]['ispages']) {
                    $page_count = null;
                    break;
                }
                $template = @$category->category[$catid]['templatewap'];
                if ($template && file_exists(TEMPLATE . '/' . $case->view->_style . '/' . $template))
                    $tpl = $template;
                else
                    $tpl = category::gettemplatewap($case->view->catid);
                $content = $case->fetch($tpl);
                $path = ROOT . category::url($catid, front::$get['page'] > 1 ? front::$get['page'] : null, true);
                //echo $path;exit;
                if (!preg_match('/\.[a-zA-Z]+$/', $path))
                    $path = rtrim(rtrim($path, '/'), '\\') . '/index.html';
                $path = rtrim($path, '/');
                $path = rtrim($path, '\\');
                $path = str_replace('//', '/', $path);
                tool::mkdir(dirname($path));
                file_put_contents($path, $content);
                $indexpath = dirname($path) . '/index.html';
                if (front::$get['page'] == 1 && $indexpath != ROOT . '/index.html') {
                    file_put_contents($indexpath, $content);
                    $cpage++;
                }
                $cpage++;
                $case->view = $view;
                $case->view->archives = null;
            }
            $i++;
        }
        if ($cpage > 0)
            front::flash("成功生成html <b>$cpage</b> 页！");
        else
            front::flash("没有生成html！");
        front::$admin = true;
    }

    function make_sitemap_action() {
        chkpw('cache_google');
    }

    function make_sitemap_baidu_action() {
        chkpw('cache_baidu');
    }
    
    function make_sitemap_google_action() {
    	chkpw('cache_baidu');
    }

    function make_baidu_action() {
        $limit = front::$post['XmlOutNum'];
        $p = front::$post['XmlMaxPerPage'];
        if (!$p)
            $p = 100;
        $frequency = front::$post['frequency'];
        $this->archive = new archive();
        $articles = $this->archive->getrows('', $limit);
        $site_url = config::get('site_url');
        $email = config::get('email');
        $head = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
        $head .= '<urlset>' . "\r\n";
        //$head .= "<webSite>{$site_url}</webSite>\r\n";
        //$head .= "<webMaster>{$email}</webMaster>\r\n";
        //$head .= "<updatePeri>{$frequency}</updatePeri>\r\n";
        $foot = "</urlset>";
        $code = '';
        $i = 1;
        $j = 1;
        if (is_array($articles) && !empty($articles)) {
        	
        	foreach ($articles as $arr) {
        		$url = substr($site_url,0,-1).archive::url($arr);
        		$adddate = date("Y-m-d",strtotime($arr['adddate']));
        		$code .= "<url><loc>{$url}</loc><lastmod>{$adddate}</lastmod></url>\r\n";
        	}
        	file_put_contents("sitemaps_baidu.xml", $head . $code . $foot);
            /*foreach ($articles as $arr) {
            	$url = substr($site_url,0,-1).archive::url($arr);
                $text = mb_substr(strip_tags($arr['content']), 0, 588,'UTF-8');
                $code .= "<item>\r\n<title><![CDATA[{$arr['title']}]]></title>\r\n<link><![CDATA[{$url}]]></link>\r\n<text><![CDATA[{$text}]]></text>\r\n";
                $code .= "<image/>\r\n";
                if ($arr['keyword'] != '') {
                    $code .= "<keywords>{$arr['keyword']}</keywords>\r\n";
                } else {
                    $code .= "<keywords/>\r\n";
                }
                $code .= "<author>{$arr['author']}</author>\r\n";
                $code .= "<source>互联网</source>\r\n";
                $code .= "<pubDate>{$arr['adddate']}</pubDate>\r\n</item>\r\n";
                if ($i % $p == 0) {
                    file_put_contents("baidumap_article_$j.xml", $head . $code . $foot);
                    $j++;
                }
                $i++;
            }
            file_put_contents("baidumap_article_$j.xml", $head . $code . $foot);*/
        }
        echo '<script>alert("生成完成!");window.location="index.php?case=cache&act=make_sitemap_baidu&admin_dir='.config::get('admin_dir').'"</script>';
        exit;
    }
    
    function make_google_action() {
    	$limit = front::$post['XmlOutNum'];
    	$p = front::$post['XmlMaxPerPage'];
    	if (!$p)
    		$p = 100;
    	$frequency = front::$post['frequency'];
    	$this->archive = new archive();
    	$articles = $this->archive->getrows('', $limit);
    	$site_url = config::get('site_url');
    	$email = config::get('email');
    	$head = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
    	$head .= "<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\">\r\n";
    	$foot = "</urlset>";
    	$code = '';
    	$i = 1;
    	$j = 1;
    	if (is_array($articles) && !empty($articles)) {
    		//var_dump($articles);
    		foreach ($articles as $arr) {
    			$url = substr($site_url,0,-1).archive::url($arr);
    			$adddate = date("Y-m-d\TH:i:s+00:00",strtotime($arr['adddate']));
    			$code .= "<url><loc>{$url}</loc><lastmod>{$adddate}</lastmod></url>\r\n";
    			//echo $url;
    			/*$text = mb_substr(strip_tags($arr['content']), 0, 588,'UTF-8');
    			$code .= "<item>\r\n<title><![CDATA[{$arr['title']}]]></title>\r\n<link><![CDATA[{$url}]]></link>\r\n<text><![CDATA[{$text}]]></text>\r\n";
    			$code .= "<image/>\r\n";
    			if ($arr['keyword'] != '') {
    				$code .= "<keywords>{$arr['keyword']}</keywords>\r\n";
    			} else {
    				$code .= "<keywords/>\r\n";
    			}
    			$code .= "<author>{$arr['author']}</author>\r\n";
    			$code .= "<source>互联网</source>\r\n";
    			$code .= "<pubDate>{$arr['adddate']}</pubDate>\r\n</item>\r\n";
    			if ($i % $p == 0) {
    				file_put_contents("baidumap_article_$j.xml", $head . $code . $foot);
    				$j++;
    			}
    			$i++;*/
    		}
    		file_put_contents("sitemaps.xml", $head . $code . $foot);
    	}
    	echo '<script>alert("生成完成!");window.location="index.php?case=cache&act=make_sitemap_google&admin_dir='.config::get('admin_dir').'"</script>';
    	exit;
    }

    function make_show_action() {
        header('Cache-control: private, must-revalidate');
        @set_time_limit(0);
        $submit = front::post('submit') ? front::post('submit') : front::get('submit');
        if (!$submit)
            return;
        chkpw('cache_content');
        $post = front::$post + front::$get;
        unset($post['submit']);
        $c_url = preg_replace('#&make_page=(\d+)#', '', $_SERVER['QUERY_STRING']);
        $c_url = preg_replace('#&aid_start=(\d+)#', '', $c_url);
        $c_url = preg_replace('#&aid_end=(\d+)#', '', $c_url);
        $c_url = preg_replace('#&catid=(\d+)#', '', $c_url);
        $c_url = preg_replace('#&submit=(\d+)#', '', $c_url);
        $c_url = 'index.php?' . $c_url;
        $c_url.='&submit=1';
        if ($post['aid_start']) {
            $aid_start = $post['aid_start'];
            $aid_end = $post['aid_end'];
            $where = "aid>=$aid_start and aid<=$aid_end AND (ishtml IS NULL OR ishtml!=2)";
            $c_url.='&aid_start=' . $aid_start . '&aid_end=' . $aid_end;
        } elseif (isset($post['catid'])) {
            $catid = $post['catid'];
            $category = category::getInstance();
            $categories = $category->sons($catid);
            $categories[] = $catid;
            $categories = implode(',', $categories);
            $where = "catid in(" . $categories . ') and checked=1 AND (ishtml IS NULL OR ishtml!=2)';
            $c_url.='&catid=' . $catid;
        }else{
            return;
        }
        $case = 'archive';
        $act = 'show';
        $_GET = array('case' => $case, 'act' => $act);
        //$front = new front();
        front::$admin = false;
        front::$html = true;
        front::$ismobile = true;
        front::$rewrite = false;
        front::$get['t'] = 'wap';
        $case = $case . '_act';
        $case = new $case();
        $case->init();
        $method = $act . '_action';
        $category = category::getInstance();
        $time_start = time::getTime();
        $archive = new archive();
        if (config::get('group_on')) {
            $make_page = $post['make_page'] == '' ? 1 : $post['make_page'];
            $archive->getrows($where);
            $archive_num = $archive->record_count;
            $group_count = config::get('group_count');
            $make_page_num = ceil($archive_num / $group_count);
            $totalpage = (($make_page - 1) * $group_count) . ',' . $group_count;
            $c_url.='&make_page=' . ($make_page + 1);
        } else {
            $totalpage = "";
        }
        
        $archives = $archive->getrows($where, $totalpage, '1');
        //var_dump($archives);
        $cpage = 0;
        foreach ($archives as $arc) {
            if (!category::getarciswaphtml($arc))
                continue;
            front::$get['aid'] = $arc['aid'];
            $case->view->archive = $arc;
            $case->view->aid = $arc['aid'];
            $case->view->catid = $arc['catid'];
            $case->view->catid = $case->view->catid;
            $case->view->topid = category::gettopparent($case->view->catid);
            $case->view->parentid = $category->getparent($case->view->catid);
            $template = @$arc['templatewap'];
            //echo $template;exit;
            $content = $arc['content'];
            $case->view->categories = category::getpositionlink2($case->view->catid);
            $linkword = new linkword();
            $linkwords = $linkword->getrows(null, 1000, 'linkorder desc');
            foreach ($linkwords as $linkword) {
                if (trim($linkword['linkurl']) && !preg_match('%^http://$%', trim($linkword['linkurl']))) {
                    $linkword['linktimes'] = (int) $linkword['linktimes'];
                    $link = "<a href='$linkword[linkurl]' target='_blank'>$linkword[linkword]</a>";
                } else {
                    $link = "<a href='" . url('archive/search/keyword/' . urlencode($linkword['linkword'])) . "' target='_blank'>$linkword[linkword]</a>";
                }
                if (isset($link)) {
                    $content = preg_replace("%(?!\"]*>)$linkword[linkword](?!\s*\")%i", "\\1$link\\2", $content, $linkword['linktimes']);
                    /* $content=preg_replace("%(?!\"]*>alt=\")(<a.*?>)(\"?!\s*\")%i","\\1\\2",$content,$linkword['linktimes']); */
                }
                unset($link);
            }
            
            $case->view->likenews = $case->getLike($case->view->archive['tag'], $case->view->archive['keyword']);
            
            $contents = preg_split('%<div style="page-break-after(.*?)</div>%si', $content);
            if (!empty($contents)) {
                $case->view->pages = count($contents);
                front::$record_count = $case->view->pages * config::get('list_pagesize');
                $case->view->pages = count($contents);
            } else {
                $case->view->pages = 1;
            }
            $taghtml = '';
            $tag_table = new tag();
            foreach ($tag_table->urls($case->view->archive['tag']) as $tag => $url) {
                $taghtml.="<a href='$url' target='_blank'>$tag</a>&nbsp;&nbsp;";
            }
            $case->view->archive['tag'] = $taghtml;
            $case->view->archive['special'] = null;
            if ($case->view->archive['spid']) {
                $spurl = special::url($case->view->archive['spid'],special::getishtml($case->view->archive['spid']));
                $sptitle = special::gettitle($case->view->archive['spid']);
                $case->view->archive['special'] = "<a href='$spurl' target='_blank'>$sptitle</a>&nbsp;&nbsp;";
            }
            $case->view->archive['type'] = null;
            if ($case->view->archive['typeid']) {
                $typeurl = type::url($case->view->archive['typeid'],1);
                $typetitle = type::name($case->view->archive['typeid']);
                $case->view->archive['type'] = "<a href='$typeurl' target='_blank'>$typetitle</a>&nbsp;&nbsp;";
            }
            
            $arc = $case->view->archive;
            for ($c = 1; $c <= $case->view->pages; $c++) {
                front::$get['page'] = $c;
                $case->view->page = $c;
                if (!empty($contents)) {
                    $content = $contents[$c - 1];
                }
                $arc['content'] = $content;
                $aid = $arc['aid'];
                $catid = $arc['catid'];
                $sql1 = "SELECT * FROM `{$archive->name}` WHERE catid = '$catid' AND aid > '$aid' ORDER BY aid ASC LIMIT 0,1";
                $sql2 = "SELECT * FROM `{$archive->name}` WHERE catid = '$catid' AND aid < '$aid' ORDER BY aid DESC LIMIT 0,1";
                $n = $archive->rec_query_one($sql1);
                $p = $archive->rec_query_one($sql2);
                $arc['p'] = $p;
                $arc['n'] = $n;
                $arc['p']['url'] = archive::url($p);
                $arc['n']['url'] = archive::url($n);
                $arc['strgrade'] = archive::getgrade($arc['grade']);
                $arc['pics'] = unserialize($arc['pics']);
                if(is_array($arc['pics']) && !empty($arc['pics'])){
                	foreach ($arc['pics'] as $k => $v){
                		if(strtolower(substr($v,0,7)) == 'http://'){
                			$arc['pics'][$k] = $v;
                		}else{
                			$arc['pics'][$k] = $v;
                		}
                	}
                }
                //$arc['pics'] = serialize($arc['pics']);
                $case->view->archive['pics'] = serialize($case->view->archive['pics']);
                $case->view->archive = $arc;
                //var_dump($template);
                if ($template && file_exists(TEMPLATE . '/' . $case->view->_style . '/' . $template))
                    $tpl = $template;
                else
                    $tpl = category::gettemplatewap($case->view->catid, 'showtemplatewap');
                //var_dump($tpl);exit;
                $content = $case->fetch($tpl);
                $path = ROOT . archive::url($arc, front::$get['page'] > 1 ? front::$get['page'] : null, true);
                if (!preg_match('/\.[a-zA-Z]+$/', $path))
                    $path = rtrim(rtrim($path, '/'), '\\') . '/index.html';
                $path = rtrim($path, '/');
                $path = rtrim($path, '\\');
                $path = str_replace('//', '/', $path);
                tool::mkdir(dirname($path));
                file_put_contents($path, $content);
                $cpage++;
                if ($case->view->pages > 1 && $c == 1) {
                    $path = ROOT . archive::url($arc, 1, true);
                    if (!preg_match('/\.[a-zA-Z]+$/', $path))
                        $path = rtrim(rtrim($path, '/'), '\\') . '/index.html';
                    $path = rtrim($path, '/');
                    $path = rtrim($path, '\\');
                    $path = str_replace('//', '/', $path);
                    tool::mkdir(dirname($path));
                    file_put_contents($path, $content);
                    $cpage++;
                }
            }
        }
        $totalpage = count($archives);
        if (!isset($archives[0]))
            $totalpage = 0;
        if ($make_page >= $make_page_num) {
            $show_msg = "本组生成html <b>{$cpage}</b> 页！  生成html完毕，本次共生成 <b>{$archive_num}</b> 页！ 2秒后自动返回内容生成首页面！\n";
            $c_url = preg_replace('#&make_page=(\d+)#', '', $_SERVER['QUERY_STRING']);
            $c_url = preg_replace('#&aid_start=(\d+)#', '', $c_url);
            $c_url = preg_replace('#&aid_end=(\d+)#', '', $c_url);
            $c_url = preg_replace('#&catid=(\d+)#', '', $c_url);
            $c_url = preg_replace('#&submit=(\d+)#', '', $c_url);
            $c_url = 'index.php?' . $c_url;
        } else {
            $show_msg = "第 <b>{$make_page}</b> 组成功生成html <b>{$cpage}</b> 页！ 本次共需生成 <b>{$archive_num}</b> 页！ 已经生成 <b>" . ($make_page * $group_count) . "</b> 页！ 2秒后自动跳入下组生成！\n";
        }
        $getnexturl = "<script>";
        $getnexturl.="var t=4;\n";
        $getnexturl.="setInterval('testTime()',1000);\n";
        $getnexturl.="function testTime() \n";
        $getnexturl.=" { \n";
        $getnexturl.="if(t == 0) location = '" . $c_url . "'; \n";
        $getnexturl.=" t--;\n";
        $getnexturl.="}\n</script> \n";
        if ($cpage > 0) {
            if (!config::get('group_on')) {
                front::flash("成功生成html <b>{$cpage}</b> 页！ 2秒后自动返回内容生成首页面！\n" . $getnexturl);
            } else {
                front::flash($show_msg . "\n" . $getnexturl);
            }
        } else {
            front::flash("没有需要生成的html，可能您选择的栏目暂无内容或者网站并未开启内容生成静态功能！");
        }
        front::$admin = true;
        front::$post = $post;
    }

    function end() {
        front::$html = false;
        front::$admin = true;
        $this->render('index.php');
    }

}