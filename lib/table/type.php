<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class type extends table
{
    static $me;

    function getcols($act)
    {
        if ($act == 'modify')
            return 'typeid,parentid,listorder,typename,subtitle,htmldir,template,listtemplate,showtemplate,ishtml,isshow,ispages,includecatarchives,linkto,htmlrule,listhtmlrule,showhtmlrule,image,description,keyword,stype,thumb,thumb_width,thumb_height';
        else return 'typeid,typename,subtitle,htmldir,isshow,ispages,linkto,stype,listorder';
    }

    function get_form()
    {
        return array(
            'parentid' => array(
                'selecttype' => 'select',
                'select' => form::arraytoselect(type::option(0, 'isnotlast')),
                'default' => get('parentid'),
            ),
            'thumb' => array(
                'filetype' => 'thumb',
            ),
            'ishtml' => array(
                'selecttype' => 'radio',
                'select' => form::arraytoselect(array(0 => '继承', 1 => '生成', 2 => '不生成')),
                'default' => 0,
            ),
            'isshow' => array(
                'selecttype' => 'radio',
                'select' => form::arraytoselect(array(1 => '正常显示', 0 => '禁用')),
                'default' => 1,
            ),
            'ispages' => array(
                'selecttype' => 'radio',
                'select' => form::arraytoselect(array(1 => '分页', 0 => '单页')),
                'default' => 1,
            ),
            'includecatarchives' => array(
                'selecttype' => 'radio',
                'select' => form::arraytoselect(array(1 => '包含', 0 => '不包含')),
                'default' => 1,
            ),
            'stype' => array(//'tips'=>"&nbsp;被调用的格式 type(\$typeid,'标记')",
            ),
            'htmlrule' => array(
                //'tips'=>" 默认：{?type::gethtmlrule(get('id'))}",
                'selecttype' => 'select',
                'select' => form::arraytoselect(getTypeHtmlRule('type')),
                'default' => '',
            ),
            'listhtmlrule' => array(
                //'tips'=>" 默认：{?type::gethtmlrule(get('id'),'listhtmlrule')}",
                'selecttype' => 'select',
                'select' => form::arraytoselect(getTypeHtmlRule('type')),
                'default' => '',
            ),
            'showhtmlrule' => array(//'tips'=>" 默认：{?type::gethtmlrule(get('id'),'showhtmlrule')}",
            ),
            'image' => array(
                'filetype' => 'thumb',
            ),
            'template' => array(
                'selecttype' => 'select',
                'select' => form::arraytoselect(front::$view->archive_tpl_list('type/list')),
                'default' => "{?type::gettemplate(get('id'),'listtemplate',false)}",
                //'tips'=>" 默认：{?type::gettemplate(get('id'))}",
            ),
            'listtemplate' => array(
                'selecttype' => 'select',
                'select' => form::arraytoselect(front::$view->archive_tpl_list('type/list')),
                'default' => "{?type::gettemplate(get('id'),'listtemplate',false)}",
                //'tips'=>" 默认：{?type::gettemplate(get('id'),'listtemplate')}",
            ),
        );
    }

    public static function getInstance()
    {
        if (!self::$me) {
            $class = new type();
            $class->init();
            self::$me = $class;
        }
        return self::$me;
    }

    function init()
    {
        $_type = $this->getrows(null, 1000, '`listorder` desc,1');
        $type = array();
        foreach ($_type as $one) {
            if (!front::$admin && !$one['isshow']) continue;
            $type[$one['typeid']] = $one;
        }
        $this->type = $type;
        $parent = array();
        foreach ($type as $one) {
            $parent[$one['typeid']] = $one['parentid'];
        }
        $this->parent = $parent;
        $this->tree = new tree($parent);
    }

    function son($id)
    {
        if (!isset($this->tree)) $this->init();
        return $this->tree->get_son($id);
    }

    function sons($id)
    {
        if (!isset($this->tree)) $this->init();
        $sons = array();
        $this->tree->get_sons($id, $sons);
        return $sons;
    }

    function hasson($id)
    {
        return self::getInstance()->tree->has_son($id);
    }

    function getparents($id, $up = true)
    {
        if (!isset($this->tree)) $this->init();
        return $this->tree->get_parents($id);
    }

    function getparent($id)
    {
        if (isset($this->tree->parent[$id])) return $this->tree->parent[$id];
        else return false;
    }

    function getposition($id)
    {
        if (!isset($this->tree)) $this->init();
        $position = $this->tree->get_parents($id);
        return $position;
    }

    function getposition1($id)
    {
        if (!isset($this->tree)) $this->init();
        $position = $this->tree->get_parents1($id);
        return $position;
    }

    static function gettopparent($id)
    {
        $position = self::getInstance()->getposition($id);
        return $position[count($position) - 1];
    }

    static function getparentsid($id, $up = true)
    {
        $category = self::getInstance();
        if (!isset($category->tree)) $category->init();
        return $category->tree->get_parents($id);
    }

    function htmlpath($id)
    {
        if (!isset($this->tree)) $this->init();
        $positions = $this->tree->get_parents($id);
        $path = array();
        foreach ($positions as $_id) {
            if ($_id && isset($this->type[$_id])) $path[] = $this->type[$_id]['htmldir'];
        }
        return implode('/', $path);
    }

    static function option($typeid = 0, $tag = 'all', &$option = array(0 => '请选择...'), &$level = 0)
    {
        $type = self::getInstance();
        if (is_array($type->son($typeid))) foreach ($type->son($typeid) as $_typeid) {
            if (!self::check($_typeid, $tag)) continue;
            $strpre = $level > 0 ? str_pad('', $level * 12, '&nbsp;') . '└&nbsp;' : '';
            $option[$_typeid] = $strpre . $type->type[$_typeid]['typename'];
            if (is_array($type->son($_typeid))) {
                $level++;
                self::option($_typeid, $tag, $option, $level);
                $level--;
            }
        }
        return $option;
    }

    static function name($typeid)
    {
        $type = self::getInstance();
        if (isset($type->type[$typeid]['typename'])) {
            return $type->type[$typeid]['typename'];
        } else {
            return '';
        }
    }

    static function image($typeid)
    {
        $type = self::getInstance();
        if (isset($type->type[$typeid]['image'])) return config::get('base_url') . '/' . $type->type[$typeid]['image'];
        else return '';
    }

    static function url($typeid, $page = null)
    {
        //var_dump($typeid);
        if (front::$get['t'] == 'wap') {
            if (config::get('wap_type_html')) {
                $type = self::getInstance();
                $rule = type::gethtmlrule($typeid, 'listhtmlrule');
                $rule = str_replace('{$caturl}', $type->htmlpath($typeid), $rule);
                $rule = str_replace('{$catid}', $typeid, $rule);
                $rule = str_replace('{$dir}', $type->type[$typeid]['htmldir'], $rule);
                if ($page) {
                    $rule = str_replace('{$page}', $page, $rule);
                } else {
                    $rule = preg_replace('/(type_.*?)\.html$/', 'index.html', $rule);
                }
                $rule = preg_replace('%/\.html$%', '/index.html', $rule);
                $rule = preg_replace('/[\(\)]/', '', $rule);
                $rule = preg_replace('%[\\/]index\.htm(l)?%', '', $rule);
                $rule = rtrim($rule, '/');
                $rule = trim($rule, '\\');
                $sp = substr(config::get('base_url'), -1, 1) == '/' ? '' : '/';
                return config::get('base_url') . $sp . 'type_wap/' . $rule;
            } else {
                return url('type/list/t/wap/typeid/' . $typeid . ($page ? '/page/' . $page : ''));
            }
        }

        if (!type::getishtml($typeid) && !front::$rewrite) {
            return url('type/list/typeid/' . $typeid . ($page ? '/page/' . $page : ''));
        } else if (front::$rewrite) {
            $sp = substr(config::get('base_url'), -1, 1) == '/' ? '' : '/';
            return config::get('base_url') . $sp . 'typelist_' . $typeid . '_' . $page . '.htm';
        } else {
            $type = self::getInstance();
            $rule = type::gethtmlrule($typeid, 'listhtmlrule');
            $rule = str_replace('{$caturl}', $type->htmlpath($typeid), $rule);
            $rule = str_replace('{$catid}', $typeid, $rule);
            $rule = str_replace('{$dir}', $type->type[$typeid]['htmldir'], $rule);
            if ($page) $rule = str_replace('{$page}', $page, $rule);
            else $rule = preg_replace('/(type_.*?)\.html$/', 'index.html', $rule);
            //else $rule=preg_replace('/(^\/.*?\.html)/',"/index.html",$rule);
            $rule = preg_replace('%/\.html$%', '/index.html', $rule);
            $rule = preg_replace('/[\(\)]/', '', $rule);
            $rule = preg_replace('%[\\/]index\.htm(l)?%', '', $rule);
            $rule = rtrim($rule, '/');
            $rule = trim($rule, '\\');
            $sp = substr(config::get('base_url'), -1, 1) == '/' ? '' : '/';
            return config::get('base_url') . $sp . 'type/' . $rule;
        }
    }

    static function getpositionlink($typeid)
    {
        $type = self::getInstance();
        if (!isset($type->type[$typeid])) return;
        $position = $type->getposition($typeid);
        $links = array();
        if (!$typeid) return $links;
        foreach ($position as $order => $id) {
            $links[$order]['id'] = $id;
            $links[$order]['name'] = @$type->type[$id]['typename'];
            $links[$order]['url'] = self::url($id);
        }
        return $links;
    }

    static function getpositionhtml($typeid)
    {
        $s = ' &gt; ';
        $html = '';
        foreach (self::getpositionlink($typeid) as $link) {
            $html .= "<a href=\"$link[url]\">$link[name]</a>" . $s;
        }
        return preg_replace("%$s$%", '', $html);
    }

    static function getpositionlink1($typeid)
    {
        $type = self::getInstance();
        if (!isset($type->type[$typeid])) return;
        $position = $type->getposition($typeid);
        $links = array();
        if (!$typeid) return $links;
        foreach ($position as $order => $id) {
            $links['id'] = $id;
            $links['name'] = @$type->type[$id]['typename'];
            $links['url'] = self::url($id);
            break;
        }
        return $links;
    }

    static function getpositionlink2($typeid)
    {
        $type = self::getInstance();
        if (!isset($type->type[$typeid])) return;
        $position = $type->getposition1($typeid);
        $links = array();
        if (!$typeid) return $links;
        foreach ($position as $order => $id) {
            $links[$order]['id'] = $id;
            $links[$order]['name'] = @$type->type[$id]['typename'];
            $links[$order]['url'] = self::url($id);
        }
        return $links;
    }

    static function gettemplate($typeid, $tag = 'listtemplate', $up = true)
    {
        if (!$typeid && front::get('parentid')) $typeid = front::get('parentid');
        $type = self::getInstance();
        if (@$type->type[$typeid]['template'] && $tag == 'listtemplate') return $type->type[$typeid]['template'];
        if (@$type->type[$typeid][$tag]) return $type->type[$typeid][$tag];
        if (!$up) return;
        $parents = $type->getparents($typeid, true);
        ksort($parents);
        foreach ($parents as $pid) {
            if ($pid == $typeid) continue;
            if (@$type->type[$pid][$tag]) return $type->type[$pid][$tag];
        }
        $default = array(
            'listtemplate' => 'type/list.html',
        );
        if (isset($default[$tag])) return $default[$tag];
    }

    static function gethtmlrule($typeid, $tag = 'listhtmlrule')
    {
        if (!$typeid && front::get('parentid')) $typeid = front::get('parentid');
        $type = self::getInstance();
        //var_dump($typeid);
        //var_dump($type->type);exit;
        if (@$type->type[$typeid]['htmlrule'] && $tag == 'listhtmlrule') return $type->type[$typeid]['htmlrule'];
        $parents = $type->getparents($typeid, true);
        ksort($parents);
        foreach ($parents as $pid) {
            if ($pid == $typeid) continue;
            if (@$type->type[$pid][$tag]) return $type->type[$pid][$tag];
        }
        $default = array(
            'listhtmlrule' => '{$caturl}/type-{$page}.html',
            'showhtmlrule' => '{$caturl}/show-{$aid}(-{$page}).html',
        );
        if (isset($default[$tag])) return $default[$tag];
    }

    static function getWapishtml($typeid)
    {
        $type = self::getInstance();
        if (@$type->type[$typeid]['ishtml'] == '1') return true;
        if (@$type->type[$typeid]['ishtml'] == '2') return false;
        $parents = $type->getparents($typeid, true);
        ksort($parents);
        foreach ($parents as $pid) {
            if ($pid == $typeid) continue;
            if (@$type->type[$pid]['ishtml'] == '1') return true;
            if (@$type->type[$pid]['ishtml'] == '2') return false;
        }
        if (config::get('wap_type_html') == '1') return true;
        return false;
    }

    static function getishtml($typeid)
    {
        $type = self::getInstance();
        if (@$type->type[$typeid]['ishtml'] == '1') return true;
        if (@$type->type[$typeid]['ishtml'] == '2') return false;
        $parents = $type->getparents($typeid, true);
        ksort($parents);
        foreach ($parents as $pid) {
            if ($pid == $typeid) continue;
            if (@$type->type[$pid]['ishtml'] == '1') return true;
            if (@$type->type[$pid]['ishtml'] == '2') return false;
        }
        if (config::get('list_page_php') == '1') return true;
        if (config::get('list_page_php') == '2') return false;
        return false;
    }

    static function getarcishtml($arc)
    {
        if (config::get('show_page_php') == '1') return true;
        if (config::get('show_page_php') == '2') return false;
        if ($arc['ishtml']) return true;
        if (self::getishtml($arc['typeid'])) return true;
        return false;
    }

    static function getattr($typeid, $attr)
    {
        $type = self::getInstance();
        if (@$type->type[$typeid][$attr]) return $type->type[$typeid][$attr];
        $parents = $type->getparents($typeid, true);
        ksort($parents);
        foreach ($parents as $pid) {
            if ($pid == $typeid) continue;
            if (@$type->type[$pid][$attr]) return $type->type[$typeid][$attr];
        }
        return false;
    }

    static function getwidthofthumb($typeid)
    {
        $width = self::getattr($typeid, 'thumb_width');
        if (!$width) $width = config::get('thumb_width');
        return $width;
    }

    static function getheightofthumb($typeid)
    {
        $height = self::getattr($typeid, 'thumb_height');
        if (!$height) $height = config::get('thumb_height');
        return $height;
    }

    static function gettypedata($_typeid = 0, &$data = array(), &$level = 0)
    {
        $type = self::getInstance();
        $types = $type->son($_typeid);
        foreach ($types as $typeid) {
            $info_ = $type->type[$typeid];
            $strpre = $level > 0 ? str_pad('', $level * 12, '&nbsp;') . '└&nbsp;' : '';
            $info_['typename'] = $strpre . $info_['typename'] . '<font color="Blue">' . (self::check($typeid, 'islast') ? ('(' . countarchiveformtype($typeid) . ')') : '') . '</font>';
            $info_['level'] = $level;
            $data[] = $info_;
            if (is_array($type->son($typeid))) {
                $level++;
                self::gettypedata($typeid, $data, $level);
                $level--;
            }
        }
        return $data;
    }

    static function check($typeid, $tag = 'isnotlast')
    {
        return true;
        $_type = self::getInstance();
        $type = $_type->type[$typeid];
        if ($tag == 'islast' && !$type['islast']) return false;
        if ($tag == 'isnotlast' && $type['islast']) return false;
        if ($tag == 'tolast') {
            if ($_type->type[$typeid]['islast']) return true;
            $sons = $_type->sons($typeid);
            foreach ($sons as $tid) {
                if ($_type->type[$tid]['islast']) return true;
            }
            return false;
        }
        return true;
    }

    static function htmlcache($typeid)
    {
    }

    static function listdata($parentid = 0, $limit = 10, $order = 'typeid asc', $where = null, $includeson = true)
    {
        $type = new type();
        $where = 'parentid=' . ($parentid ? $parentid : '0') . ($where ? ' and ' . $where : '');
        $types = $type->getrows($where, $limit, $order);
        foreach ($types as $order => $type) {
            $types[$order]['url'] = type::url($type['typeid']);
        }
        return $types;
    }
}