<?php if (!defined('ROOT'))    exit('Can\'t Access !');class config_admin extends admin{    function init()    {    }    function getcnzz_action()    {        $cnzz = new cnzz();        $infos = $cnzz->getinfo();        config::modify(array('cnzz_user' => $infos[0]));        config::modify(array('cnzz_pass' => $infos[1]));        front::redirect(url('config/system/set/cnzz'));    }    function system_action()    {        $type = front::get('set');        if (!$type)            $type = '[^-]+';        $source = file_get_contents(config::$path);        $tpl_str = '';        foreach (front::$view->default_tpl_list() as $tpl) {            if (!preg_match('/user/', $tpl) && !preg_match('/wap/', $tpl) && !preg_match('/admin/', $tpl) && !preg_match('/\./', $tpl)) {                $tpl_str .= "$tpl/$tpl/";            }        }        $tpl_str = rtrim($tpl_str, '/');        $source = str_replace('[默认default]', "=>$tpl_str", $source);        preg_match_all("%//$type-(\S+?)\{(.+?)//\}%s", $source, $result, PREG_PATTERN_ORDER);        $this->view->units = $result[1];        //var_dump($result);exit;        foreach ($result[2] as $order => $source2) {            preg_match_all('%\'(\w+)\'=>\'(.*?)\',\s*//([^=\n\[]+)(\[(.+)\])?(=>(.+))?%', $source2, $result2, PREG_SET_ORDER);            foreach ($result2 as $key => $res) {                $item = array();                $item['name'] = $res[1];                $item['value'] = $res[2];                $item['title'] = $res[3];                if (isset($res[5])) {                    $item['intro'] = $res[5];                }                if (isset($res[7]) && !strstr($res[7], "image")) {                    if (strstr($res[7], '$mF')) {                        $arr = array_combine(config::get('$mF'), config::get('$mF'));                        $item['select'] = $arr;                    } else {                        $item['select'] = url::toarray($res[7]);                    }                } elseif (isset($res[7]) && strstr($res[7], "image")) {                    $item['image'] = true;                }                $this->view->items[$order][] = $item;            }        }        //后台模板        foreach (front::$view->admin_tpl_list() as $tpl) {            if (preg_match('/admin/', $tpl) && !preg_match('/\./', $tpl)) {                $admin_str .= "$tpl@";            }        }        $admin_str = rtrim($admin_str, "@");        $tmp = explode('@', $admin_str);        $this->view->admintpllist = array_combine($tmp, $tmp);        //会员中心模板        foreach (front::$view->default_tpl_list() as $tpl) {            if (preg_match('/user/', $tpl) && !preg_match('/\./', $tpl)) {                $user_str .= "$tpl@";            }        }        $user_str = rtrim($user_str, "@");        $tmp = explode('@', $user_str);        $this->view->usertpllist = array_combine($tmp, $tmp);        foreach (front::$view->default_tpl_list() as $tpl) {            if (preg_match('/member/', $tpl) && !preg_match('/\./', $tpl)) {                $member_str .= "$tpl@";            }        }        $member_str = rtrim($member_str, "@");        $tmp = explode('@', $member_str);        $this->view->membertpllist = array_combine($tmp, $tmp);        //手机模板        foreach (front::$view->default_tpl_list() as $tpl) {            if (preg_match('/wap/', $tpl) && !preg_match('/\./', $tpl)) {                $mobile_str .= "$tpl@";            }        }        $mobile_str = rtrim($mobile_str, "@");        $tmp = explode('@', $mobile_str);        $this->view->mobiletpllist = array_combine($tmp, $tmp);        if (front::post('submit')) {            chkpw('system_' . front::$get['set']);            if (is_array(front::$post)) {                foreach (front::$post as $key => $value) {                    if (is_array($value)) {                        foreach ($value as $v) {                            if (false !== strstr($v, '\\')) {                                alerterror('有非法字符');                            }                        }                    } else if (false !== strstr($value, '\\')) {                        alerterror('有非法字符');                    }                }                //exit;            }            if (preg_match('/(php|asp|aspx|jsp|exe|dll|so|asa)/is', front::$post['upload_filetype'])) {                alerterror('不允许设置风险类型文件上传');            }            if (front::post('admin_dir') && front::post('admin_dir') != config::get('admin_dir')) {                $new_dir = ROOT . '/' . front::post('admin_dir');                if (ADMIN_DIRNAME != $new_dir) {                    rename(ADMIN_DIRNAME, $new_dir);                    if (is_dir($new_dir)) {                        front::flash('后台目录更改成功！&nbsp;&nbsp;');                    } else                        unset(front::$post['admin_dir']);                }            }            $this->setRewriteFile(front::$post['urlrewrite_on']);            config::modify(front::$post);            if (front::post('cnzz_user') && front::post('cnzz_pass')) {                $content = "user:" . config::get('cnzz_user') . "\tpass:" . config::get('cnzz_pass') . "\tdate:" . date('Y-m-d H:i:s') . "\n";                $file = ROOT . '/data/cnzz.txt';                $fp = fopen($file, 'ab');                fwrite($fp, $content);            }            event::log('修改网站配置', '成功');            config::modify(front::$post);            front::flash('设置成功！');            if (!empty($new_dir) || !front::post('submit')) {                front::$view->sysVar();                $new_url = front::$view->admin_url;                front::redirect($new_url);            }        }    }    private function setRewriteFile($urlrewrite_on)    {        //$_SERVER['SERVER_SOFTWARE'] = 'IIS6';        $base = config::get('base_url');        if ($urlrewrite_on) {            if (stristr($_SERVER['SERVER_SOFTWARE'], 'Apache') || stristr($_SERVER['SERVER_SOFTWARE'], 'IIS6')) {                $htaccess = 'RewriteEngine on' . "\n";                //$htaccess.= 'RewriteBase '.$base."\n";                $htaccess .= 'RewriteCond %{REQUEST_FILENAME} !-d' . "\n";                $htaccess .= 'RewriteRule !\.(mp3|wmv|wma|rm|rmvb|js|ico|gif|jpeg|jpg|png|css|swf|php|html|shtml|xml|xsl|wsdl|xslt|eot|svg|ttf|woff|woff2|map)$ ' . $base . '/index.php [NC]' . "\n";                $httpdurl = '.htaccess';                $httpd = $htaccess;            } else if (stristr($_SERVER['SERVER_SOFTWARE'], 'IIS/7') || stristr($_SERVER['SERVER_SOFTWARE'], 'IIS/10')) {                $web = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";                $web .= '<configuration>' . "\n";                $web .= '<system.webServer>' . "\n";                $web .= '<rewrite>' . "\n";                $web .= '<rules>' . "\n";                $web .= '<rule name="rule1">' . "\n";                $web .= '<match url="\.(mp3|wmv|wma|rm|rmvb|js|ico|gif|jpeg|jpg|png|css|swf|php|html|shtml|xml|xsl|wsdl|xslt|eot|svg|ttf|woff|woff2|map)$" negate="true" />' . "\n";                $web .= '<conditions logicalGrouping="MatchAll">' . "\n";                $web .= '<add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />' . "\n";                $web .= '</conditions>' . "\n";                $web .= '<action type="Rewrite" url="index.php" />' . "\n";                $web .= '</rule>' . "\n";                $web .= '</rules>' . "\n";                $web .= '</rewrite>' . "\n";                $web .= '</system.webServer>' . "\n";                $web .= '</configuration>' . "\n";                $httpdurl = 'web.config';                $httpd = $web;            } else if (stristr($_SERVER['SERVER_SOFTWARE'], 'nginx')) {                $htaccess = 'if ($request_filename !~* \.(mp3|wmv|wma|rm|rmvb|js|ico|gif|jpeg|jpg|png|css|swf|php|html|shtml|xml|xsl|wsdl|xslt|eot|svg|ttf|woff|woff2|map$)) {' . "\n";                $htaccess .= 'rewrite .* ' . $base . '/index.php last;' . "\n";                $htaccess .= '}' . "\n";                $httpdurl = '.htaccess';                $httpd = $htaccess;            } else {                $httpd = '[ISAPI_Rewrite]' . "\n";                $httpd .= '# 3600 = 1 hour' . "\n";                $httpd .= 'CacheClockRate 3600' . "\n";                $httpd .= 'RepeatLimit 32' . "\n";                $httpd .= 'RewriteRule !\.(mp3|wmv|wma|rm|rmvb|js|ico|gif|jpeg|jpg|png|css|swf|php|html|shtml|xml|xsl|wsdl|xslt|eot|svg|ttf|woff|woff2|map)$ ' . $base . '/index.php [L]' . "\n";                $httpdurl = 'httpd.ini';            }            $fp = fopen(ROOT . '/' . $httpdurl, w);            fputs($fp, $httpd);            fclose($fp);        } else {            if (file_exists(ROOT . '/httpd.ini')) @unlink(ROOT . '/httpd.ini');            if (file_exists(ROOT . '/.htaccess')) @unlink(ROOT . '/.htaccess');            if (file_exists(ROOT . '/web.config')) @unlink(ROOT . '/web.config');        }    }    function _url($action = 'getmoinf')    {        form_admin::init();        $this->__table = new defind($this->table);        $pars = array(            'action' => $action,            'tel' => config::get('tel'),            'sitename' => config::get('sitename'),            'site_url' => config::get('site_url'),            'encoding' => 'utf-8',            'host' => $_SERVER['HTTP_HOST'],            'version' => 'cmseasy3.0',            'release' => config::get('version'),            'os' => PHP_OS,            'php' => phpversion(),            'mysql' => $this->__table->verison(),            'browser' => urlencode($_SERVER['HTTP_USER_AGENT']),            'address' => config::get('address'),            'email' => config::get('email'),            'serverip' => $_SERVER['REMOTE_ADDR'],            'serverfilename' => urlencode($_SERVER['SCRIPT_FILENAME']),        );        $data = http_build_query($pars);        $verify = md5($data . config::get('email'));        return 'http://info.cmseasy.cn/server/upgrade.php?' . $data . '&verify=' . $verify;    }    function config_info()    {        //return '<script type="text/javascript" src="'.$this->_url('getmoinf').'"></script>';    }    function remove_action()    {        chkpw('cache_update');        front::remove(ROOT . '/cache/data');        front::remove(ROOT . '/cache/template');        $user = new user();        $user->close_db();        cookie::del('passinfo');        front::flash('缓存更新成功！');        front::redirect(url('index', true));    }    function sms_action()    {    }    function hottag_action()    {        $set = settings::getInstance();        if (front::post('submit')) {            $var = front::$post;            unset($var['submit']);            $sets = $set->getrow(array('tag' => 'table-hottag'));            if (empty($sets)) {                $a = $set->rec_insert(array('value' => serialize($var), 'tag' => 'table-hottag', 'array' => addslashes(var_export($var, true))));            } else {                $a = $set->rec_update(array('value' => serialize($var), 'tag' => 'table-hottag', 'array' => addslashes(var_export($var, true))), array('tag' => 'table-hottag'));            }            event::log("标签修改", '成功');            front::flash('标签修改成功！');        }        $sets = $set->getrow(array('tag' => 'table-hottag'));        if (empty($sets)) {            $set->rec_insert(array('tag' => 'table-hottag'));            $sets = $set->getrow(array('tag' => 'table-hottag'));        }        if (empty($sets['value']))            $this->view->hottags = NULL;        else            $this->view->hottags = unserialize($sets['value']);    }    function end()    {        $this->render('index.php');        //echo $this->config_info();    }}