<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');

class cache_admin extends admin
{

    public $archive;

    function init()
    {
        header('Cache-control: private, must-revalidate');
        front::$admin = false;
        front::$isadmin = false;
        front::$html = true;
    }

    function make_index_action()
    {
        $servip = gethostbyname($_SERVER['SERVER_NAME']);
        if ($servip == front::ip() && front::get('ishtml') == 1) {

        } else {
            chkpw('cache_index');
        }
        $case = 'index';
        $act = 'index';
        $_GET = array('case' => $case, 'act' => $act);
        $front = new front();
        front::$admin = false;
        front::$isadmin = false;
        front::$html = true;
        $case = $case . '_act';
        $case = new $case();
        $case->init();
        $method = $act . '_action';
        $view = $case->view;
        file_put_contents(ROOT . '/index.html', $case->fetch());
        front::flash(lang('generation_of_home'));
        front::redirect(front::$from);
    }

    function make_special_action()
    {
        chkpw('cache_special');
        header('Cache-control: private, must-revalidate');
        @set_time_limit(0);
        if (!front::post('submit'))
            return;
        $speciaid = intval(front::$post['specialid']);
        $special = new special();
        $specials = $special->getrow($speciaid);
        if (!$specials['ishtml']) {
            front::flash(lang('none_of_the_generated_HTML'));
            return;
        }
        $archive_all = new archive();
        $archive_num = $archive_all->rec_count('spid=' . $speciaid . ' and checked=1 and `state`=1');
        $pagesize = config::get('list_pagesize');
        if (!$archive_num) $archive_num = 1;
        $cpage = ceil($archive_num / $pagesize);
        $j = 0;
        for ($i = 1; $i <= $cpage; $i++) {
            $path = 'special/' . $speciaid . '/list-' . $i . '.html';
            tool::mkdir(dirname($path));
            $data = file_get_contents(config::get('site_url') . 'index.php?case=special&act=show&spid=' . $speciaid . '&page=' . $i);
            if (file_put_contents($path, $data)) {
                $j++;
            }
        }
        if ($j > 0) {

            $path = 'special/' . $speciaid . '/index.html';
            tool::mkdir(dirname($path));
            $data = file_get_contents(config::get('site_url') . 'index.php?case=special&act=show&spid=' . $speciaid . '&page=1');
            if (file_put_contents($path, $data)) {
                //front::flash("成功生成html <b>1</b> 页！");
            }
            front::flash(lang('generate_html') . " <b>$j</b> " . lang('npage') . "！");
        }
    }

    function make_area_action()
    {
        chkpw('cache_area');
        header('Cache-control: private, must-revalidate');
        @set_time_limit(0);

        if (!front::post('submit'))
            return;
        if (!config::get('area_html')) {
            front::flash(lang('none_of_the_generated_HTML'));
            return;
        }
        $archive_all = new archive();

        if (front::post('province_id')) {
            $where = 'checked=1 and `state`=1';
            $where .= ' and province_id=' . front::post('province_id');
            $archive_num = $archive_all->rec_count($where);
            $pagesize = config::get('list_pagesize');
            $cpage = ceil($archive_num / $pagesize);
            $j = 0;
            for ($i = 1; $i <= $cpage; $i++) {
                $path = 'area/province/' . intval(front::post('province_id')) . '_list_' . $i . '.html';
                tool::mkdir(dirname($path));
                $data = file_get_contents(config::get('site_url') . 'index.php?case=area&act=list&province_id=' . intval(front::post('province_id')) . '&city_id=' . intval(front::post('city_id')) . '&section_id=' . intval(front::post('section_id')) . '&page=' . $i);
                if (file_put_contents($path, $data)) {
                    $j++;
                }
            }
            if ($j > 0) {
                front::flash(lang('generate_html') . " <b>$j</b> " . lang('npage') . "！");
            } else {
                front::flash(lang('none_of_the_generated_HTML'));
            }
        }
        if (front::post('city_id')) {
            $where = 'checked=1 and `state`=1';
            $where .= ' and city_id=' . front::post('city_id');
            $archive_num = $archive_all->rec_count($where);
            $pagesize = config::get('list_pagesize');
            $cpage = ceil($archive_num / $pagesize);
            $j = 0;
            for ($i = 1; $i <= $cpage; $i++) {
                $path = 'area/city/' . intval(front::post('city_id')) . '_list_' . $i . '.html';
                tool::mkdir(dirname($path));
                $data = file_get_contents(config::get('site_url') . 'index.php?case=area&act=list&province_id=' . intval(front::post('province_id')) . '&city_id=' . intval(front::post('city_id')) . '&section_id=' . intval(front::post('section_id')) . '&page=' . $i);
                if (file_put_contents($path, $data)) {
                    $j++;
                }
            }
            if ($j > 0) {
                front::flash(lang('generate_html') . " <b>$j</b> " . lang('npage') . "！");
            } else {
                front::flash(lang('none_of_the_generated_HTML'));
            }
        }
        if (front::post('section_id')) {
            $where = 'checked=1 and `state`=1';
            $where .= ' and section_id=' . front::post('section_id');
            $archive_num = $archive_all->rec_count($where);
            $pagesize = config::get('list_pagesize');
            $cpage = ceil($archive_num / $pagesize);
            $j = 0;
            for ($i = 1; $i <= $cpage; $i++) {
                $path = 'area/section/' . intval(front::post('section_id')) . '_list_' . $i . '.html';
                tool::mkdir(dirname($path));
                $data = file_get_contents(config::get('site_url') . 'index.php?case=area&act=list&province_id=' . intval(front::post('province_id')) . '&city_id=' . intval(front::post('city_id')) . '&section_id=' . intval(front::post('section_id')) . '&page=' . $i);
                if (file_put_contents($path, $data)) {
                    $j++;
                }
            }
            if ($j > 0) {
                front::flash(lang('generate_html') . " <b>$j</b> " . lang('npage') . "！");
            } else {
                front::flash(lang('none_of_the_generated_HTML'));
            }
        }
    }

    function make_tag_action()
    {
        chkpw('cache_tag');
        header('Cache-control: private, must-revalidate');
        set_time_limit(0);
        if (!front::$get['tag']) {
            front::$get['tag'] = front::$post['tag'];
        }
        if (!front::$get['submit']) {
            front::$get['submit'] = front::$post['submit'];
        }
        $otag = new tag();
        $tags = $otag->getrows("", 0);
        //var_dump($tags);
        $tags = $this->view->hottags = array_to_hashmap($tags, 'tagid', 'tagname');

        if (!front::get('submit'))
            return;
        if (!config::get('tag_html') || !front::$get['tag']) {
            front::flash(lang('none_of_the_generated_HTML'));
            front::redirect(front::$from);
            return;
        }
        $tagid = front::$get['tag'];
        $tag = $tags[$tagid];
        $pinyin = pinyin::get($tag);

        $arctag = new arctag();
        $archive_num = $arctag->rec_count('tagid=' . $tagid);

        front::$record_count = $archive_num;
        $pagesize = config::get('list_pagesize');
        front::$pages = $pagesize;
        $cpage = ceil($archive_num / $pagesize);
        $j = 0;

        for ($i = 1; $i <= $cpage; $i++) {
            $path = 'tags/' . $pinyin . '-' . $tagid . '-' . $i . '.html';
            tool::mkdir(dirname($path));

            $data = file_get_contents(getSiteUrl() . '/index.php?case=tag&act=show&tag=' . urlencode($tag) . '&page=' . $i);
            if (file_put_contents($path, $data)) {
                $j++;
            }
        }
        if ($j > 0) {
            front::flash(lang('generate_html') . " <b>$j</b> " . lang('npage') . "！");
            front::redirect(front::$from);
        } else {
            front::flash(lang('none_of_the_generated_HTML'));
            front::redirect(front::$from);
        }
    }

    function make_type_action()
    {
        chkpw('cache_type');
        header('Cache-control: private, must-revalidate');
        @set_time_limit(0);
        if (!front::post('submit'))
            return;

        $case = 'type';
        $act = 'list';
        $_GET = array('case' => $case, 'act' => $act);
        $front = new front();
        front::$admin = false;
        front::$isadmin = false;
        front::$html = true;
        front::$rewrite = false;
        $case = $case . '_act';
        $case = new $case();
        $case->init();
        $method = $act . '_action';
        $totalpage = 100;
        $time_start = time::getTime();

        $type = type::getInstance();
        $typeid = front::post('typeid');
        if ($typeid && !$type->getishtml($typeid)) {
            front::flash(lang('none_of_the_generated_HTML'));
            return;
        }
        if ($typeid) {
            $arrtype = $type->getrows($typeid);
        } else {
            $arrtype = $type->getrows('', 0);
        }
        $cpage = 0;
        if (is_array($arrtype) && !empty($arrtype)) {
            foreach ($arrtype as $v) {
                if (!$type->getishtml($v['typeid'])) {
                    continue;
                }
                $types = array();
                $types = $type->sons($v['typeid']);
                $types[] = $v['typeid'];
                $where = 'typeid in (' . implode(',', $types) . ') AND checked=1 AND state=1';
                $archive_all = new archive();
                $archive_num = $archive_all->rec_count($where);
                for (front::$get['page'] = 1; ; front::$get['page']++) {
                    $view = $case->view;
                    $pagesize = config::get('list_pagesize');
                    $limit = ((front::$get['page'] - 1) * $pagesize) . ',' . $pagesize;
                    $archive = new archive();
                    $case->view->archives = $archive->getrows($where, $limit, '`listorder` desc,adddate desc');
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
						$prices = getPrices($arc['attr2']);
                    $articles['attr2'] = $prices['price'];
                    $articles['oldprice'] = $prices['oldprice'];
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
					

                    $tpl = type::gettemplate($v['typeid']);
                    $content = $case->fetch($tpl);
                    $path = type::url($v['typeid'], front::$get['page'], true);
                    if (!preg_match('/\.[a-zA-Z]+$/', $path))
                        $path = rtrim(rtrim($path, '/'), '\\') . '/index.html';
                    $path = rtrim($path, '/');
                    $path = rtrim($path, '\\');
                    $path = str_replace('//', '/', $path);
                    if (config::get('base_url') == '/') {
                        $path = ROOT . substr($path, 1);
                    } else {
                        $path = ROOT . str_replace(config::get('base_url'), '', $path);
                    }
                    tool::mkdir(dirname($path));
                    if (!file_put_contents($path, $content)) {
                        front::flash(lang('write_html_failed'));
                    }
                    $indexpath = dirname($path) . '/index.html';
                    if (front::$get['page'] == 1 && $indexpath != ROOT . '/index.html') {
                        file_put_contents($indexpath, $content);
                        $cpage++;
                    }
                    $cpage++;
                    $case->view = $view;
                }
            }
        }

        if ($cpage > 0)
            front::flash(lang('generate_html') . " <b>$cpage</b> " . lang('npage') . "！");
        else
            front::flash(lang('none_of_the_generated_HTML'));
        front::$admin = true;
    }

    function make_list_action()
    {
        $servip = gethostbyname($_SERVER['SERVER_NAME']);
        if ($servip != front::ip() && front::get('ishtml') == 1) {
            chkpw('cache_category');
        }
        header('Cache-control: private, must-revalidate');
        @set_time_limit(0);
        if (!front::post('submit'))
            return;
        $case = 'archive';
        $act = 'list';
        $_GET = array('case' => $case, 'act' => $act);
        $front = new front();
        front::$admin = false;
        front::$isadmin = false;
        front::$html = true;
        front::$rewrite = false;
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
            if (!category::getishtml($catid))
                continue;

            if ($category->category[$catid]['linkto']) {
                continue;
            }

            front::$get['catid'] = $catid;
            $case->view->categories = category::getpositionlink2($catid);
            $_categories = $category->sons($catid);
            $_categories[] = $catid;
            $case->view->ifson = category::hasson($catid);
            for (front::$get['page'] = 1; ; front::$get['page']++) {
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
                $tops = $archive->getrows("checked=1 AND (state IS NULL or state<>'-1') AND toppost!=0", 0, 'toppost DESC,listorder=0,listorder ASC,adddate DESC');

                if (@$category->category[$catid]['includecatarchives']) {
                    $case->view->archives = $archive->getrows('catid in(' . implode(',', $_categories) . ') and checked=1 and (state IS NULL or state<>\'-1\')', $limit, 'listorder=0,`listorder` asc,`adddate` DESC');
                } else {
                    $case->view->archives = $archive->getrows("catid=$catid and checked=1 and `(state IS NULL or state<>'-1')", $limit, 'listorder=0,`listorder` asc,`adddate` DESC');
                }
                $case->view->page = front::$get['page'];

                if (is_array($tops) && !empty($tops)) {
                    foreach ($tops as $order => $arc) {
                        if ($arc['toppost'] == 3) {
                            $tops[$order]['title'] = "[" . lang('the_total_top') . "]" . $arc['title'];
                        }
                        if ($arc['toppost'] == 2) {
                            $subcatids = $category->sons($arc['catid']);
                            if ($arc['catid'] != front::get('catid') && !in_array(front::get('catid'), $subcatids)) {
                                unset($tops[$order]);
                            } else {
                                $tops[$order]['title'] = "[" . lang('the_column_top') . "]" . $arc['title'];
                            }
                        }
                    }
                    $case->view->archives = array_merge($tops, $case->view->archives);
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
                    if (strtolower(substr($arc['thumb'], 0, 7)) == 'http://') {
                        $articles['sthumb'] = $arc['thumb'];
                    } else {
                        $articles['sthumb'] = config::get('base_url') . '/' . $arc['thumb'];
                    }

                    if ($arc['strong']) {
                        $articles['title'] = '<strong>' . $arc['title'] . '</strong>';
                    }
                    if ($arc['color']) {
                        $articles['title'] = '<font style="color:' . $arc['color'] . ';">' . $articles['title'] . '</font>';
                    }

                    $pics = unserialize($arc['pics']);
                    if(is_array($pics) && !empty($pics)){
                        $articles['pics'] = $pics;
                    }

                    $prices = getPrices($arc['attr2']);
                    $articles['attr2'] = $prices['price'];
                    $articles['oldprice'] = $prices['oldprice'];

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

                if (front::get('page') > 1 && front::get('page') > $case->view->page_count) {
                    $page_count = null;
                    break;
                }
                if (front::get('page') > 1 && !@$category->category[$catid]['ispages']) {
                    $page_count = null;
                    break;
                }
                $template = @$category->category[$catid]['template'];

                if ($template && file_exists(TEMPLATE . '/' . $case->view->_style . '/' . $template))
                    $tpl = $template;
                else
                    $tpl = category::gettemplate($case->view->catid);
                $content = $case->fetch($tpl);
                $path = ROOT . category::url($catid, front::$get['page'] > 1 ? front::$get['page'] : null, true);
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
            front::flash(lang('generate_html') . " <b>$cpage</b> " . lang('npage') . "！");
        else
            front::flash(lang('none_of_the_generated_HTML'));
        front::$admin = true;
        front::$isadmin = true;
    }

    function make_sitemap_action()
    {
        chkpw('cache_google');
    }

    function make_sitemap_baidu_action()
    {
        chkpw('cache_baidu');
    }

    function make_sitemap_google_action()
    {
        chkpw('cache_baidu');
    }

    function make_baidu_action()
    {
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
                $_url = archive::url($arr);
                if(preg_match('/^http/i',$_url)){
                    $url = archive::url($arr);;
                }else{
                    $url = substr($site_url, 0, -1) . archive::url($arr);;
                }
                //$url = substr($site_url, 0, -1) . archive::url($arr);
                //var_dump($url);
                $adddate = date("Y-m-d", strtotime($arr['adddate']));
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
        //exit;
        echo '<script>alert("' . lang('generate_html') . '");window.location="index.php?case=cache&act=make_sitemap_baidu&admin_dir=' . config::get('admin_dir') . '"</script>';
        exit;
    }

    function make_google_action()
    {
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
                //$url = substr($site_url, 0, -1) . archive::url($arr);
                $_url = archive::url($arr);
                if(preg_match('/^http/i',$_url)){
                    $url = archive::url($arr);;
                }else{
                    $url = substr($site_url, 0, -1) . archive::url($arr);;
                }
                $adddate = date("Y-m-d\TH:i:s+00:00", strtotime($arr['adddate']));
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
        echo '<script>alert("' . lang('generate_html') . '");window.location="index.php?case=cache&act=make_sitemap_google&admin_dir=' . config::get('admin_dir') . '"</script>';
        exit;
    }

    function make_show_action()
    {
        header('Cache-control: private, must-revalidate');
        @set_time_limit(0);
        $submit = front::post('submit') ? front::post('submit') : front::get('submit');
        if (!$submit)
            return;
        chkpw('cache_content');
        //time::start();
        $post = front::$post + front::$get;
        unset($post['submit']);
        $c_url = preg_replace('#&make_page=(\d+)#', '', $_SERVER['QUERY_STRING']);
        $c_url = preg_replace('#&aid_start=(\d+)#', '', $c_url);
        $c_url = preg_replace('#&aid_end=(\d+)#', '', $c_url);
        $c_url = preg_replace('#&catid=(\d+)#', '', $c_url);
        $c_url = preg_replace('#&submit=(\d+)#', '', $c_url);
        $c_url = 'index.php?' . $c_url;
        $c_url .= '&submit=1';

        $category = category::getInstance(); //实例化栏目类

        if ($post['aid_start']) {
            $aid_start = $post['aid_start'];
            $aid_end = $post['aid_end'];
            $where = "aid>=$aid_start and aid<=$aid_end AND checked=1 AND (ishtml IS NULL OR ishtml!=2)";
            $c_url .= '&aid_start=' . $aid_start . '&aid_end=' . $aid_end;
        } elseif (isset($post['catid'])) {
            $catid = $post['catid'];
            $categories = $category->sons($catid);
            $categories[] = $catid;
            $categories = implode(',', $categories);
            $where = "catid in(" . $categories . ') and checked=1 AND (ishtml IS NULL OR ishtml!=2)';
            $c_url .= '&catid=' . $catid;
        } else
            return;
        $case = 'archive';
        $act = 'show';
        $_GET = array('case' => $case, 'act' => $act);
        //$front = new front();
        front::$admin = false;
        front::$isadmin = false;
        front::$html = true;
        front::$rewrite = false;
        $case = $case . '_act';
        $case = new $case();
        $case->init();
        $method = $act . '_action';
        //$time_start = time::getTime();

        $archive = new archive(); //实例化文章类

        if (config::get('group_on')) { //启用分组生成
            $make_page = $post['make_page'] == '' ? 1 : $post['make_page'];
            $archive->getrows($where);
            $archive_num = $archive->record_count;
            $group_count = config::get('group_count');
            $make_page_num = ceil($archive_num / $group_count);
            $totalpage = (($make_page - 1) * $group_count) . ',' . $group_count;
            $c_url .= '&make_page=' . ($make_page + 1);
        } else {
            $totalpage = "";
        }

        $archives = $archive->getrows($where, $totalpage, '1'); //取到要生成的所有文章

        $cpage = 0;
        foreach ($archives as $arc) {
            if (!category::getarcishtml($arc))  //如果文章设置不生成则跳过
                continue;
            if ($arc['linkto']) { //如果有跳转连接则跳过生成
                continue;
            }
            $case->view->archive = $arc;
            front::$get['aid'] = $case->view->aid = $case->view->archive['aid'];
            $case->view->catid = $case->view->archive['catid'];

            $case->view->topid = category::gettopparent($case->view->catid);
            $case->view->parentid = $category->getparent($case->view->catid);

            $template = $case->view->archive['template'];
            $content = $case->view->archive['content'];

            $case->view->categories = category::getpositionlink2($case->view->catid);

            //关键字连接
            $linkword = new linkword();
            $linkwords = $linkword->getrows(null, 1000, 'linkorder desc');
            foreach ($linkwords as $linkword) {
                if (trim($linkword['linkurl']) && !preg_match('%^http://$%', trim($linkword['linkurl']))) {
                    $linkword['linktimes'] = (int)$linkword['linktimes'];
                    $link = "<a href='$linkword[linkurl]' target='_blank'>$linkword[linkword]</a>";
                } else {
                    $link = "<a href='" . url('archive/search/keyword/' . urlencode($linkword['linkword'])) . "' target='_blank'>$linkword[linkword]</a>";
                }
                if (isset($link)) {
                    $content = preg_replace("%(?!\"]*>)$linkword[linkword](?!\s*\")%i", "\\1$link\\2", $content, $linkword['linktimes']);
                }
                unset($link);
            }

            //相关文章
            $case->view->likenews = $case->getLike($case->view->archive['tag'], $case->view->archive['keyword']);

            //内容分页
            $contents = preg_split('%<div style="page-break-after(.*?)</div>%si', $content);
            if (!empty($contents)) {
                $case->view->pages = count($contents);
                front::$record_count = $case->view->pages * config::get('list_pagesize');
                $case->view->pages = count($contents);
            } else {
                $case->view->pages = 1;
            }

            //标签连接
            $taghtml = '';
            $tag_table = new tag();
            foreach ($tag_table->urls($case->view->archive['tag']) as $tag => $url) {
                $taghtml .= "<a href='$url' target='_blank'>$tag</a>&nbsp;&nbsp;";
            }
            $case->view->archive['tag'] = $taghtml;

            //专题连接
            $case->view->archive['special'] = null;
            if ($case->view->archive['spid']) {
                $spurl = special::url($case->view->archive['spid'], special::getishtml($case->view->archive['spid']));
                $sptitle = special::gettitle($case->view->archive['spid']);
                $case->view->archive['special'] = "<a href='$spurl' target='_blank'>$sptitle</a>&nbsp;&nbsp;";
            }

            //分类连接
            $case->view->archive['type'] = null;
            if ($case->view->archive['typeid']) {
                $typeurl = type::url($case->view->archive['typeid'], 1);
                $typetitle = type::name($case->view->archive['typeid']);
                $case->view->archive['type'] = "<a href='$typeurl' target='_blank'>$typetitle</a>&nbsp;&nbsp;";
            }

            //地区连接
            //$case->view->archive['area'] = null;
            //$case->view->archive['area'] = area::getpositonhtml($case->view->archive['province_id'], $case->view->archive['city_id'], $case->view->archive['section_id']);

            //$arc = $case->view->archive;
            for ($c = 1; $c <= $case->view->pages; $c++) {
                front::$get['page'] = $c;
                $case->view->page = $c;
                if (!empty($contents)) {
                    $content = $contents[$c - 1];
                }
                $case->view->archive['content'] = $content;


                $catid = $case->view->catid;
                if (!$case->view->archive['showform']) {
                    $this->getshowform($catid);
                } else if ($case->view->archive['showform'] && $case->view->archive['showform'] == '1') {
                    $this->showform = 1;
                } else {
                    $this->showform = $case->view->archive['showform'];
                }
                if (preg_match('/^my_/is', $this->showform)) {
                    $case->view->archive['showform'] = $this->showform;
                    $o_table = new defind($this->showform);
                    front::$get['form'] = $this->showform;
                    $this->view->primary_key = $o_table->primary_key;
                    $field = $o_table->getFields();
                    $fieldlimit = $o_table->getcols('user_modify');
                    helper::filterField($field, $fieldlimit);
                    $case->view->field = $field;
                } else {
                    $case->view->archive['showform'] = '';
                }


                //自定义字段
                cb_data($case->view->archive);
                $str = "";
                foreach ($case->view->archive as $key => $value) {
                    if (!preg_match('/^my/', $key) || !$value)
                        continue;
                    $sonids = $category->sons(setting::$var['archive'][$key]['catid']);
                    $sonids[] = setting::$var['archive'][$key]['catid'];
                    if (!in_array($case->view->archive['catid'], $sonids) && intval(setting::$var['archive'][$key]['catid'])) {
                        //unset($case->view->field[$key]);
                        continue;
                    }
                    $str .= '<p> ' . setting::$var['archive'][$key]['cname'] . ':' . $value . '</p>';
                }
                $arc['my_fields'] = $str;

                //上一篇,下一篇
                $aid = $case->view->archive['aid'];
                $catid = $case->view->archive['catid'];
                $sql1 = "SELECT * FROM `{$archive->name}` WHERE catid = '$catid' AND aid > '$aid' and state=1 ORDER BY aid ASC LIMIT 0,1";
                $sql2 = "SELECT * FROM `{$archive->name}` WHERE catid = '$catid' AND aid < '$aid' and state=1 ORDER BY aid DESC LIMIT 0,1";
                $n = $archive->rec_query_one($sql1);
                $p = $archive->rec_query_one($sql2);
                $case->view->archive['p'] = $p;
                $case->view->archive['n'] = $n;
                $case->view->archive['p']['url'] = archive::url($p);
                $case->view->archive['n']['url'] = archive::url($n);

                //评级
                $case->view->archive['strgrade'] = archive::getgrade($arc['grade']);
                $prices = getPrices($case->view->archive['attr2']);
                $case->view->archive['attr2'] = $prices['price'];
                $case->view->archive['oldprice'] = $prices['oldprice'];
                $case->view->groupname = $prices['groupname'];

                //图片
                $case->view->archive['pics'] = unserialize($case->view->archive['pics']);
                /*if(is_array($case->view->archive['pics']) && !empty($case->view->archive['pics'])){
                	foreach ($case->view->archive['pics'] as $k => $v){
                		if(strtolower(substr($v['url'],0,7)) == 'http://'){
                			$case->view->archive['pics'][$k] = $v;
                		}else{
                			$case->view->archive['pics'][$k] = $v;
                		}
                	}
                }*/
                //$case->view->archive['pics'] = serialize($case->view->archive['pics']);

                if ($template && file_exists(TEMPLATE . '/' . $case->view->_style . '/' . $template))
                    $tpl = $template;
                else
                    $tpl = category::gettemplate($case->view->catid, 'showtemplate');
                $content = $case->fetch($tpl);
                $path = ROOT . archive::url($case->view->archive, front::$get['page'] > 1 ? front::$get['page'] : null, true);
                if (!preg_match('/\.[a-zA-Z]+$/', $path))
                    $path = rtrim(rtrim($path, '/'), '\\') . '/index.html';
                $path = rtrim($path, '/');
                $path = rtrim($path, '\\');
                $path = str_replace('//', '/', $path);
                tool::mkdir(dirname($path));
                file_put_contents($path, $content);
                $cpage++;
                if ($case->view->pages > 1 && $c == 1) {
                    $path = ROOT . archive::url($case->view->archive, 1, true);
                    if (!preg_match('/\.[a-zA-Z]+$/', $path))
                        $path = rtrim(rtrim($path, '/'), '\\') . '/index.html';
                    $path = rtrim($path, '/');
                    $path = rtrim($path, '\\');
                    $path = str_replace('//', '/', $path);
                    tool::mkdir(dirname($path));
                    //file_put_contents('logs.txt', file_get_contents('logs.txt')."\r\n".$path);
                    $f = fopen($path, 'w');
                    fwrite($f, $content);
                    fclose($f);
                    $cpage++;
                }
            }
        }
        $totalpage = count($archives);
        if (!isset($archives[0]))
            $totalpage = 0;
        if ($make_page >= $make_page_num) {
            $show_msg = lang('this_group_generates_html') . " <b>{$cpage}</b> " . lang('npage') . "！  " . lang('generate_html') . lang('this_co_generation') . " <b>{$archive_num}</b> " . lang('npage') . "！ " . lang('automatic_return_ home_page') . "\n";
            $c_url = preg_replace('#&make_page=(\d+)#', '', $_SERVER['QUERY_STRING']);
            $c_url = preg_replace('#&aid_start=(\d+)#', '', $c_url);
            $c_url = preg_replace('#&aid_end=(\d+)#', '', $c_url);
            $c_url = preg_replace('#&catid=(\d+)#', '', $c_url);
            $c_url = preg_replace('#&submit=(\d+)#', '', $c_url);
            $c_url = 'index.php?' . $c_url;
        } else {
            $show_msg = lang('how_many') . " <b>{$make_page}</b> " . lang('group_html') . " <b>{$cpage}</b> " . lang('npage') . "！ " . lang('the_total_required') . " <b>{$archive_num}</b> " . lang('npage') . "！ " . lang('has_been_generated') . " <b>" . ($make_page * $group_count) . "</b> " . lang('npage') . "！ " . lang('automatic_jump_into_the_next_group_generated') . "\n";
        }
        $getnexturl = "<script>";
        $getnexturl .= "var t=4;\n";
        $getnexturl .= "setInterval('testTime()',1000);\n";
        $getnexturl .= "function testTime() \n";
        $getnexturl .= " { \n";
        $getnexturl .= "if(t == 0) location = '" . $c_url . "'; \n";
        $getnexturl .= " t--;\n";
        $getnexturl .= "}\n</script> \n";
        if ($cpage > 0) {
            if (!config::get('group_on')) {
                front::flash(lang('generate_html') . " <b>{$cpage}</b> " . lang('npage') . "！" . lang('when_used') . time::getTime() . "！\n");
            } else {
                front::flash($show_msg . "\n" . $getnexturl);
            }
        } else {
            front::flash(lang('none_of_the_generated_HTML'));
        }
        front::$admin = true;
        front::$isadmin = true;
        front::$post = $post;
    }

    function getshowform($cid)
    {
        $category = category::getInstance();
        $row = $category->getrow(array('catid' => $cid), '1 desc', 'catid,showform,parentid');
        if ($row['showform'] && $row['showform'] != 1) {
            $this->showform = $row['showform'];
        } else if ($row['showform'] && $row['showform'] == 1) {
            $this->showform = 1;
        } else if (!$row['showform']) {
            if ($row['parentid'] != 0) {
                $this->getshowform($row['parentid']);
            } else {
                $this->showform = '1';
            }
        }
    }

    function end()
    {
        front::$html = false;
        front::$admin = true;
        front::$isadmin = true;
        $this->render('index.php');
    }

}