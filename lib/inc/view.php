<?php 

final class view
{
    public $_var;
    public $lang = array();

    function __construct(act $act)
    {
        $this->_var = new stdClass();
        if (isset($act->style))
            $this->_style = $act->style;
        $this->setTemplate();
        $this->sysVar();
        //new template();
        templatetag::_getVer();
    }

    function setTemplate()
    {
        if (front::$admin && !front::$html) {
            $this->_style = config::get('admin_template_dir') ? config::get('admin_template_dir') : 'admin';
            $this->_tpl_ext = '.php';
        } else {
            $this->_style = ltrim(THIS_URL, '/');
            if (!$this->_style || front::$html) {
                $this->_style = config::get('template_dir');
            }
            if (front::$ismobile) {
                $this->_style = config::get('template_mobile_dir');
            }
            if (front::$case == 'user' || front::$case == 'manage'
                || front::$case == 'union' || (front::$case == 'archive' && front::$act == 'orders')
                || (front::$case == 'archive' && front::$act == 'payorders')
                || (front::$case == 'archive' && front::$act == 'email')
                || (front::$case == 'archive' && front::$act == 'choosepaytype')
                || front::$case == 'attachment'
                || front::$case == 'form'
            ) {
                $this->_style = config::get('user_template_dir') ? config::get('user_template_dir') : 'user';
                $this->_style .= '/';
            }
            if (front::$ismobile && (front::$case == 'user' || front::$case == 'manage'
                    || front::$case == 'union' || (front::$case == 'archive' && front::$act == 'orders')
                    || (front::$case == 'archive' && front::$act == 'payorders')
                    || (front::$case == 'archive' && front::$act == 'email')
                    || front::$case == 'attachment')
            ) {
                $this->_style = config::get('member_template_dir') ? config::get('member_template_dir') : 'user';
                //$this->_style .= '/wap';
				$this->_style .= '/';
            }
            //var_dump($this->_style);
            $this->_tpl_ext = '.html';
        }
        $this->_tpl = front::$case . '/' . front::$act . $this->_tpl_ext;
    }

    function archive_tpl_list($type = '')
    {
        $dir = preg_replace('%\/.*%', '', $type);
        $_tpls = front::scan_all(TEMPLATE . '/' . config::get('template_dir') . '/' . $dir, $dir . '/');
        $tpls = array('0' => '继承');
        foreach ($_tpls as $tpl) {
            if (preg_match('/\.htm(l)?$/', $tpl) && !preg_match('/#/', $tpl)) {
                if ($type)
                    if (!preg_match("%^$type%", $tpl))
                        continue;
                $_tpl = str_replace('.', '_', $tpl);
                $_tpl = help::tpl_name($_tpl);
                if ($_tpl)
                    $_tpl = $_tpl . "($tpl)";
                else
                    $_tpl = $tpl;
                $tpls[$tpl] = $_tpl;
            }
        }

        return $tpls;
    }

    function mobile_tpl_list($type = '')
    {
        $dir = preg_replace('%\/.*%', '', $type);
        $_tpls = front::scan_all(TEMPLATE . '/' . config::get('template_mobile_dir') . '/' . $dir, $dir . '/');
        $tpls = array('0' => '继承');
        foreach ($_tpls as $tpl) {
            if (preg_match('/\.htm(l)?$/', $tpl) && !preg_match('/#/', $tpl)) {
                if ($type)
                    if (!preg_match("%^$type%", $tpl))
                        continue;
                $_tpl = str_replace('.', '_', $tpl);
                $_tpl = help::tpl_name($_tpl);
                if ($_tpl)
                    $_tpl = $_tpl . "($tpl)";
                else
                    $_tpl = $tpl;
                $tpls[$tpl] = $_tpl;
            }
        }
        return $tpls;
    }

    function user_tpl_list($type = '')
    {
        $dir = preg_replace('%\/.*%', '', $type);
        $_tpls = front::scan_all(TEMPLATE . '/' . config::get('template_user_dir') . '/' . $dir, $dir . '/');
        $tpls = array('0' => '继承');
        foreach ($_tpls as $tpl) {
            if (preg_match('/\.htm(l)?$/', $tpl) && !preg_match('/#/', $tpl)) {
                if ($type)
                    if (!preg_match("%^$type%", $tpl))
                        continue;
                $_tpl = str_replace('.', '_', $tpl);
                $_tpl = help::tpl_name($_tpl);
                if ($_tpl)
                    $_tpl = $_tpl . "($tpl)";
                else
                    $_tpl = $tpl;
                $tpls[$tpl] = $_tpl;
            }
        }
        return $tpls;
    }

    function show($string, $whole = false)
    {
        return $string;
    }

    function default_tpl_list()
    {
        return front::scan(TEMPLATE);
    }

    function admin_tpl_list()
    {
        return front::scan(ADMIN_TEMPLATE);
    }

    function special_tpl_list()
    {
        $_tpls = front::scan_all(TEMPLATE . '/' . config::get('template_dir') . '/special', 'special/');
        //var_dump($_tpls);
        $tpls = array();
        foreach ($_tpls as $tpl) {
            if (preg_match('/\.htm(l)?$/', $tpl) && !preg_match('/#/', $tpl)) {
                $_tpl = str_replace('.', '_', $tpl);
                $_tpl = help::tpl_name($_tpl);
                if ($_tpl)
                    $_tpl = $_tpl . "($tpl)";
                else
                    $_tpl = $tpl;
                $tpls[$tpl] = $_tpl;
            }
        }
        return $tpls;
    }

    function myform_tpl_list()
    {
        $_tpls = front::scan_all(TEMPLATE . '/' . config::get('user_template_dir') . '/myform', 'myform/');
        $tpls = array();
        foreach ($_tpls as $tpl) {
            if (preg_match('/\.htm(l)?$/', $tpl) && !preg_match('/#/', $tpl)) {
                $_tpl = str_replace('.', '_', $tpl);
                $_tpl = help::tpl_name($_tpl);
                if ($_tpl)
                    $_tpl = $_tpl . "($tpl)";
                else
                    $_tpl = $tpl;
                $tpls[$tpl] = $_tpl;
            }
        }
        return $tpls;
    }

    function sysVar()
    {
        $this->base_url = config::get('base_url');
        $this->skin_path = $this->base_url . '/template/' . $this->_style . '/skin';
        if(front::$admin){
            $this->skin_path = $this->base_url . '/template_admin/' . $this->_style . '/skin';
        }
        
        $this->skin_url = $this->skin_path;
        $this->template_path = $this->base_url . '/template/' . $this->_style . '';
        $this->admin_url = config::get('base_url') . '/index.php?admin_dir=' . config::get('admin_dir');
        $this->roles = session::get('roles');
    }

    function fetch($tpl = null)
    {
        if($tpl && !in_array(fileext($tpl),array('html','php'))){
            exit('模版文件错误!');
        }
        if (!$tpl && get('spid') && front::$case == 'table' && front::$act == 'list') {
            $_tpl = 'table/special/manage.php';
            $tpl = $_tpl;
        }
        if (!$tpl && get('spid') && front::$case == 'table' && front::$act == 'list') {
            $_tpl = 'table/spider/manage.php';
            $tpl = $_tpl;
        }

        if (front::$case == 'user' && !$tpl) {
            $_tpl = front::$act . '.html';
            $tpl = $_tpl;
        }

        if (front::$case == 'table' && !$tpl && preg_match('/^(htmlrule|import|result|viewcnzz|send|sendsms|mail|list|add|edit|setting|show|manage)$/', front::$act) && front::get('table') && front::$admin && preg_match('/^my_/',
                front::get('table'))
        ) {
            $_tpl = 'myform/' . front::$act . '.php';
            $tpl = $_tpl;
        } elseif (front::$case == 'table' && !$tpl && preg_match('/^(htmlrule|import|result|viewcnzz|send|sendsms|mail|list|add|edit|setting|show|manage)$/', front::$act) && front::get('table') && front::$admin) {
            if (front::$get['table'] == 'category' && front::$act == 'list') {
                if (!chkpower('category_list')) {
                    return '无权限操作';
                }
            }

            if (front::$get['table'] == 'archive' && front::$act == 'list') {
                if (!chkpower('archive_list')) {
                    return '无权限操作';
                }
            }
            if (front::$get['table'] == 'type' && front::$act == 'list') {
                if (!chkpower('type_list')) {
                    return '无权限操作';
                }
            }
            if (front::$get['table'] == 'special' && front::$act == 'list') {
                if (!chkpower('special_list')) {
                    return '无权限操作';
                }
            }
            if (front::$get['table'] == 'user' && front::$act == 'list') {
                if (!chkpower('user_list')) {
                    return '无权限操作';
                }
            }
            if (front::$get['table'] == 'usergroup' && front::$act == 'list') {
                if (!chkpower('usergroup_list')) {
                    return '无权限操作';
                }
            }
            if (front::$get['table'] == 'orders' && front::$act == 'list') {
                if (!chkpower('order_list')) {
                    return '无权限操作';
                }
            }
            if (front::$get['table'] == 'ballot' && front::$act == 'list') {
                if (!chkpower('func_ballot_list')) {
                    return '无权限操作';
                }
            }
            if (front::$get['table'] == 'comment' && front::$act == 'list') {
                if (!chkpower('func_comment_list')) {
                    return '无权限操作';
                }
            }
            if (front::$get['table'] == 'guestbook' && front::$act == 'list') {
                if (!chkpower('func_book_list')) {
                    return '无权限操作';
                }
            }
            if (front::$get['table'] == 'announcement' && front::$act == 'list') {
                if (!chkpower('func_announc_list')) {
                    return '无权限操作';
                }
            }
            if ($this->table == 'templatetag' && front::get('tagfrom') == 'define' && front::$act == 'list') {
                if (!chkpower('templatetag_list_define')) {
                    return '无权限操作';
                }
            }
            if ($this->table == 'templatetag' && front::get('tagfrom') == 'category' && front::$act == 'list') {
                if (!chkpower('templatetag_list_category')) {
                    return '无权限操作';
                }
            }
            if ($this->table == 'templatetag' && front::get('tagfrom') == 'content' && front::$act == 'list') {
                if (!chkpower('templatetag_list_content')) {
                    return '无权限操作';
                }
            }
            if ($this->table == 'templatetag' && front::get('tagfrom') == 'system' && front::$act == 'list') {
                if (!chkpower('templatetag_list_system')) {
                    return '无权限操作';
                }
            }
            if ($this->table == 'templatetag' && front::get('tagfrom') == 'function' && front::$act == 'list') {
                if (!chkpower('templatetag_list_function')) {
                    return '无权限操作';
                }
            }
            if (front::$get['table'] == 'linkword' && front::$act == 'list') {
                if (!chkpower('seo_linkword_list')) {
                    return '无权限操作';
                }
            }
            if (front::$get['table'] == 'friendlink' && front::$act == 'list') {
                if (!chkpower('seo_friendlink_list')) {
                    return '无权限操作';
                }
            }

            $_tpl = 'table/' . front::get('table') . '/' . front::$act . '.php';
            if (file_exists(TEMPLATE_ADMIN . '/' . $this->_style . '/' . $_tpl)) {
                $tpl = $_tpl;
            }
        } elseif (front::$case == 'stats' && !$tpl && front::get('table') == 'stats' && front::$act == 'list') {
            if (!chkpower('seo_status_list')) {
                return '无权限操作';
            }
        } elseif (front::$case == 'field' && !$tpl && front::get('table') == 'archive' && front::$act == 'list') {
            if (!chkpower('defined_field_content')) {
                return '无权限操作';
            }
        }

        if (!isset($tpl))
            $tpl = $this->_tpl;

        if($tpl == 'message/error.html'){
            $file = TEMPLATE . '/' . 'user/'.$tpl;
        }else if($tpl == 'system/close.html'){
            $file = TEMPLATE.'/user/'.$tpl;
        }else if($tpl == 'system/suspend.html'){
            $file = TEMPLATE.'/user/'.$tpl;
        }else {
            if (front::$admin) {
                $file = TEMPLATE_ADMIN . '/' . $this->_style . '/' . $tpl;
            }else{
                $file = TEMPLATE . '/' . $this->_style . '/' . $tpl;
            }
        }
        //var_dump($this->_style);
        if (!file_exists($file)) {
            echo $file;exit("模板不存在");
        }
        $tFile = preg_replace('/([\w-]+)\.(\w+)$/', '#$1.$2', preg_replace('/\.html?$/ix', '.php', $tpl));
        $cacheFile = ROOT . '/cache/template/' . $this->_style . '/' . $tFile;
        tool::mkdir(dirname($cacheFile));
        $tmp = explode('.', $file);

        $ext = end($tmp);
        if($ext != 'php' && $ext != 'html'){
            exit("模板文件类型错误");
        }

        if (!file_exists($cacheFile) || filemtime($cacheFile) < filemtime($file) || front::$admin && !session::get('passinfo')) {
            $source = $this->compile(file_get_contents($file));
            file_put_contents($cacheFile, $source);
        } else {
            $source = file_get_contents($cacheFile);
        }
        $content = $this->_eval($source, $cacheFile);

        if (front::$admin)
            return $this->show($content);
        $rs = config::get('filter_word');
        $rs1 = config::get('filter_x');
        $rs = str_replace('，', ',', $rs);
        $rs1 = str_replace('，', ',', $rs1);
        $rs = explode(',', $rs);
        if (is_array($rs)) {
            foreach ($rs as $k => $v) {
                if (strtolower($v) == 'cmseasy') {
                    $rs[$k] = 'liuliwei';
                }
            }
        }
        $rs1 = explode(',', $rs1);
        $content = str_replace($rs, $rs1, $content);
        /*if (is_array($rs))
            foreach ($rs as $rp) {
            if ($rp)
                $content=str_replace(trim($rp),config::get('filter_x'),$content);
        }*/
        return $this->show($content);
    }

    function render($tpl = null)
    {
        echo $this->fetch($tpl);
    }

    function _eval($content, $file = null)
    {
        foreach ($this as $var => $value) if (!preg_match('/^_/', $var))
            $$var = $value;
        if (is_object($this->_var))
            foreach ($this->_var as $var => $value) $$var = $value;
        ob_start();
        if ($file)
            include $file;
        else
            eval('?' . '>' . trim($content));
        $content = ob_get_contents();
        ob_end_clean();
        $this->_var = new stdClass();
        return $content;
    }

    function compile($source)
    {
        $source = admin_system::_pcompile_($source);
        if (front::$act == 'visual') {
            $source = preg_replace('/(\{tag_([^}]+)\})/se', " '<span class=tagedit tagid='.templatetag::id('$2').'>$1</span>' ", $source);
            $source = preg_replace('/(\{js_([^}]+)\})/se', " '<span class=tagedit tagid='.templatetag::id('$2').'>$1</span>' ", $source);
            $source = preg_replace('/(\{tagwap_([^}]+)\})/se', " '<span class=tagedit tagid='.templatetagwap::id('$2').'>$1</span>' ", $source);
            $source = preg_replace('/(\{jswap_([^}]+)\})/se', " '<span class=tagedit tagid='.templatetagwap::id('$2').'>$1</span>' ", $source);
        } else {
            $source = preg_replace('/\{tag_([^}]+)\}/s', '{templatetag::tag(\'$1\')}', $source);
            $source = preg_replace('/\{js_([^}]+)\}/s', '{templatetag::js(\'$1\')}', $source);
            $source = preg_replace('/\{tagwap_([^}]+)\}/s', '{templatetagwap::tag(\'$1\')}', $source);
            $source = preg_replace('/\{jswap_([^}]+)\}/s', '{templatetagwap::js(\'$1\')}', $source);
        }
        $source = preg_replace("/([\n\r]+)\t+/s", "\\1", $source);
        $source = preg_replace("%\/\/\{(.+?)\}%", "", $source);
        $source = preg_replace("/\{template\s+(.+)\}/", "<?php echo template(\\1); ?>", $source);
        $source = preg_replace("/\{=(.+?)\}/", "<?php echo \\1;?>", $source);
        $source = preg_replace("/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $source);
        $source = preg_replace("/\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $source);
        $source = preg_replace("/\{(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/", "<?php echo \\1;?>", $source);
        $source = preg_replace_callback("/\{(\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/s", array($this, 'addquote'), $source);
        $source = preg_replace("/\{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)\}/s", "<?php echo \\1;?>", $source);
        $source = preg_replace("/\{\\$([a-zA-Z_]+)\.([a-zA-Z_]+)\}/s", "<?php echo \$\\1['\\2'];?>", $source);
        $source = preg_replace("/\{\\$([a-zA-Z_]+)\.(\\$[a-zA-Z_]+)\}/s", "<?php echo \$\\1[\\2];?>", $source);
        $source = preg_replace("/\{\\$([a-zA-Z_]+)\.(\\$[a-zA-Z_]+)\.([a-zA-Z_]+)\}/s", "<?php echo \$\\1[\\2]['\\3'];?>", $source);
        $source = preg_replace('/\{(\\$[a-zA-Z_]+)\.(\\$[a-zA-Z_]+)\|([^,}]+)(.*?)\}/i', '<?php echo \\3(\\1[\\2]\\4);?>', $source);
        $source = preg_replace('/\{(\\$[a-zA-Z_]+)\.([a-zA-Z_]+)\|([^,}]+)(.*?)\}/i', "<?php echo \\3(\\1['\\2']\\4);?>", $source);
        $source = preg_replace('/\{(\\$[a-zA-Z_]+)\|([^,}]+)(.*?)\}/i', "<?php echo \\2(\\1\\3);?>", $source);
        $source = preg_replace("/\\$([a-zA-Z0-9_]+)\[([a-zA-Z0-9_]+)\]/s", "\$\\1['\\2']", $source);
        $source = "<?php defined('ROOT') or exit('Can\'t Access !'); ?>\r\n" . $source;
        return $source;
    }

    function addquote($var)
    {

        $str = "<?php echo " . str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var[1])) . ";?>";
        //var_dump($str);
        //exit;
        return $str;
    }
}