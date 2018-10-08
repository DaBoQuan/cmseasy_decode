<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class templatetagwap {
    static $me;
    static $setting=array();
    static $fields=
            array(
            0=>
            array(
                            'Field'=>'id',
                            'Type'=>'int(11)',
                            'Null'=>'NO',
                            'Key'=>'PRI',
                            'Default'=>NULL,
                            'Extra'=>'auto_increment',
            ),
            1=>
            array(
                            'Field'=>'name',
                            'Type'=>'varchar(100)',
                            'Null'=>'NO',
                            'Key'=>'UNI',
                            'Default'=>NULL,
                            'Extra'=>'',
            ),
            2=>
            array(
                            'Field'=>'tagmodule',
                            'Type'=>'varchar(32)',
                            'Null'=>'YES',
                            'Key'=>'',
                            'Default'=>NULL,
                            'Extra'=>'',
            ),
            4=>
            array(
                            'Field'=>'tagcontent',
                            'Type'=>'text',
                            'Null'=>'NO',
                            'Key'=>'',
                            'Default'=>NULL,
                            'Extra'=>'',
            ),
            6=>
            array(
                            'Field'=>'note',
                            'Type'=>'text',
                            'Null'=>'YES',
                            'Key'=>'',
                            'Default'=>NULL,
                            'Extra'=>'',
            ),
            7=>
            array(
                            'Field'=>'tagfrom',
                            'Type'=>'varchar(16)',
                            'Null'=>'YES',
                            'Key'=>'',
                            'Default'=>'define',
                            'Extra'=>'',
            ),
            8=>
            array(
                            'Field'=>'tagtype',
                            'Type'=>'varchar(20)',
                            'Null'=>'YES',
                            'Key'=>'',
                            'Default'=>'',
                            'Extra'=>'',
            ),
    );
    public function __construct() {
        $setting_file=self::getfilename();
        @mkdir(dirname($setting_file));
        if (!file_exists($setting_file))
            file_put_contents($setting_file,'<?php return array();');
        else
            self::$setting=include $setting_file;
    }
    public function getfilename() {
        return TEMPLATE.'/'.config::get('template_mobile_dir').'/data/templatetag.php';
    }
    public function savesetting() {
        if (empty(self::$setting))
            return;
        $setting_file=self::getfilename();
        file_put_contents($setting_file,'<?php return '.var_export(self::$setting,true).';');
    }
    function getrow($tagid) {
        if (is_numeric($tagid))
            foreach (self::$setting as $set) {
                if ($set['id'] == $tagid)
                    return $set;
            }
        elseif (preg_match("/name='([^']+)'/",$tagid,$match))
            foreach (self::$setting as $set) {
                if ($set['name'] == $match[1])
                    return $set;
            }
        return false;
    }
    function getrows($condition) {
        preg_match("/tagfrom='(\w+)'/",$condition,$match);
        $rows=array();
        foreach (self::$setting as $set) {
            if ($set['tagfrom'] == $match[1])
                $rows[]=$set;
        }
        return $rows;
    }
    public static function getInstance() {
        if (!self::$me) {
            $class=new templatetagwap();
            self::$me=$class;
        }
        return self::$me;
    }
    static function _getVer() {
        define('SYSTEMNAME','C'.'m'.'s'.'E'.'a'.'s'.'y');
        include_once 'version.php';
    }
    function getcols($act=null) {
        return 'id,name,tagmodule,tagcontent,note,tagfrom,tagtype';
    }
    function get_form() {
        return array(
                'tagmodule'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(array('all'=>'全部','archive'=>'文章','user'=>'会员','other'=>'其他')),
                ),
                'tagfrom'=>array(
                        'selecttype'=>'select',
                        'select'=>form::arraytoselect(array('system'=>'系统','function'=>'函数','define'=>'自定义')),
                        'default'=>'define',
                ),
                'template_name'=>array(
                        'default'=>config::get('template_mobile_dir'),
                ),
                'tagtype'=>array(
                        'tips'=>"&nbsp;可以自定一个类别名称，便于查找",
                ),
        );
    }
    function getFields() {
        static $fields;
        static $primary_key;
        if (!isset($fields)) {
            $fields=array();
            $_field=array();
            foreach (self::$fields as $field) {
                $_type=preg_match('/(\w+)(\((\w+)\))?/i',$field['Type'],$result);
                $_field['name']=$field['Field'];
                $_field['type']=$result[1];
                $_field['len']=isset($result[3]) ?$result[3] : 0;
                $_field['primary_key']=$field['Key'] == 'PRI';
                $_field['notnull']=$field['Null'] == 'NO';
                $_field['selecttype']=isset($this->_form[$_field['name']]['selecttype']) ?$this->_form[$_field['name']]['selecttype'] : '';
                $_field['select']=isset($this->_form[$_field['name']]['select']) ?$this->_form[$_field['name']]['select'] : '';
                $_field['tips']=isset($this->_form[$_field['name']]['tips']) ?$this->_form[$_field['name']]['tips'] : '';
                $fields[$field['Field']]=$_field;
                if ($field['Key'] == 'PRI') {
                    $this->primary_key=$field['Field'];
                    $primary_key=$this->primary_key;
                }
            }
        }
        $this->primary_key=$primary_key;
        return $fields;
    }
    public function rec_insert($tag_info) {
        $setting=array();
        foreach ($tag_info as $key=>$tag) {
            if (!in_array($key,explode(',',$this->getcols()))) {
                unset($tag_info[$key]);
                $setting[$key]=$tag;
            }
        }
        $max_id=0;
        foreach (self::$setting as $set) {
            if ($set['name'] == $tag_info['name']) {
                front::flash("标签 $tag_info[name] 已存在！");
                return false;
            }
            if ($set['id'] >$max_id)
                $max_id=$set['id'];
        }
        $this->insert_id=$max_id +1;
        $tag_info=array_merge(array('id'=>$max_id +1),$tag_info,array('tagfrom'=>get('tagfrom'),'setting'=>$setting));
        self::$setting[]=$tag_info;
        $this->savesetting();
        return true;
    }
    public function rec_update($tag_info,$id) {
        if (!$id)
            return false;
        $setting=array();
        foreach (self::$setting as $order=>$set) {
            if ($set['id'] == $id) {
                foreach ($tag_info as $key=>$tag) {
                    if (!in_array($key,explode(',',$this->getcols()))) {
                        unset($tag_info[$key]);
                        $setting[$key]=$tag;
                    }
                }
                self::$setting[$order]=array_merge(array('id'=>$id),$tag_info,array('tagfrom'=>get('tagfrom'),'setting'=>$setting));
                $this->savesetting();
                return true;
            }
        }
        return false;
    }
    public function rec_delete($id) {
        if (!$id)
            return false;
        $ids=explode(',',preg_replace('/.*\(|\).*/','',$id));
        $count=0;
        foreach (self::$setting as $order=>$set) {
            if (in_array($set['id'],$ids)) {
                unset(self::$setting[$order]);
                $count++;
            }
        }
        if ($count)
            $this->savesetting();
        return $count;
    }
    public function insert_id() {
        return $this->insert_id;
    }
    static function tag($tagid) {
        if (!is_numeric($tagid))
            $tagid="name='$tagid'";
        $tag=self::getInstance()->getrow($tagid);
        if (is_array($tag)) {
            if (front::$case == 'tag') {
                $get=array_slice(front::$get,2);
                if (is_array($get))
                    foreach ($get as $key=>$value) front::$view->_var->$key=$value;
            }
            $content=$tag['tagcontent'];
            if ($tag['tagfrom'] == 'category'||$tag['tagfrom'] == 'content')
                $content=self::getlisttagcontent($tag,$tag['setting']);
            $content=preg_replace('/\{(tagwap|js|sys)(_[^}]+)\}/i','',$content);
            return front::$view->_eval(front::$view->compile($content));
        }
    }
    static function js($tagid) {
        if (!is_numeric($tagid)) {
            $tagid="name='$tagid'";
            $tag=self::getInstance()->getrow($tagid);
            $tagid=$tag['id'];
        }
        $url=url::create("templatetagwap/get/id/$tagid/".url::arrayto(array_slice(front::$get,2)));
        return "<script src=\"$url\"></script>";
    }
    static function id($tagid) {
        if (!is_numeric($tagid)) {
            $tagid="name='$tagid'";
            $tag=self::getInstance()->getrow($tagid);
            $tagid=$tag['id'];
        }
        return $tagid;
    }
    public function getlisttagcontent($tag,$tag_config) {
        $path=ROOT.'/template/'.config::get('template_mobile_dir').'/tpltag/'.$tag_config['tagtemplate'];
        $tag_tpl_content=@file_get_contents($path);
        if ($tag['tagfrom'] == 'content') {
            $tag_config['area']="'".$tag_config['province_id'].",".$tag_config['city_id'].",".$tag_config['section_id']."'";
            if ($tag_config['thumb'] == 'on') {
                $tag_config['thumb']='true';
            }
            else {
                $tag_config['thumb']='false';
            }
            foreach ($tag_config as $key=>$value) {
                if (empty($value))
                    $tag_config[$key]='0';
                else if ($key <>'area'&&$value !='false'&&$value !='true'&&!is_numeric($value))
                    $tag_config[$key]="'$value'";
            }
            //var_dump($tag_config);
            $patterns[0]='/\$_catid/';
            $patterns[1]='/\$_typeid/';
            $patterns[2]='/\$_spid/';
            $patterns[3]='/\$_area/';
            $patterns[4]='/\$_length/';
            $patterns[5]='/\$_ordertype/';
            $patterns[6]='/\$_limit/';
            $patterns[7]='/\$_image/';
            $patterns[8]='/\$_attr1/';
			$patterns[9]='/\$_son/';
			$patterns[10]='/\$_wheretype/';
			$patterns[11]='/\$_tpl/';
            $patterns[12]='/\$_intro_len/';
            $patterns[13]='/\$_istop/';
            $replacements[0]=$tag_config['catid'];
            $replacements[1]=$tag_config['typeid'];
            $replacements[2]=$tag_config['spid'];
            $replacements[3]=$tag_config['area'];
            $replacements[4]=$tag_config['length'];
            $replacements[5]=$tag_config['ordertype'];
            $replacements[6]=$tag_config['limit'];
            $replacements[7]=$tag_config['thumb'];
            $replacements[8]=$tag_config['attr1'];
			$replacements[9]=$tag_config['son'];
			$replacements[10]=$tag_config['wheretype']?$tag_config['wheretype']:'\'\'';
			$replacements[11]=$tag_config['tpl']?$tag_config['tpl']:'\'\'';
            $replacements[12]=$tag_config['introduce_length'];
            $replacements[13]=(int)$tag_config['istop'];
            $tag_tpl_content=preg_replace($patterns,$replacements,$tag_tpl_content);
            $content=$tag_tpl_content;
        }
        if ($tag['tagfrom'] == 'category') {
            if ($tag_config['catname'] == 'on') {
                $tag_config['catname']='{$cat[catname]}';
            }
            else {
                $tag_config['catname']='';
            }
            if ($tag_config['categorycontent'] == 'on') {
                $tag_config['categorycontent']='{$cat[categorycontent]}';
            }
            else {
                $tag_config['categorycontent']='';
            }
            if ($tag_config['catimage'] == 'on') {
                $tag_config['catimage']='{$cat[image]}';
            }
            else {
                $tag_config['catimage']='';
            }
            if ($tag_config['subcat'] == 'on') {
                $tag_config['subcat']='<!--子栏目开始-->
  {loop categories($cat[catid]) $cat}
    栏目名称：{$cat[catname]}
    封面内容：{$cat[categorycontent]}
    栏目图片：{$cat[image]}
    <a href="{$cat[url]}">{$cat[catname]}</a>
  {/loop}
  <!--子栏目结束-->';
            }
            else {
                $tag_config['subcat']='';
            }
            $patterns[0]='/\$_catid/';
            $patterns[1]='/\$_subcat/';
            $patterns[2]='/\$_catname/';
            $patterns[3]='/\$_categorycontent/';
            $patterns[4]='/\$_image/';
            $patterns[5]='/\$_son/';
            $replacements[0]=$tag_config['catid'];
            $replacements[1]=$tag_config['subcat'];
            $replacements[2]=$tag_config['catname'];
            $replacements[3]=$tag_config['categorycontent'];
            $replacements[4]=$tag_config['catimage'];
            $replacements[5]=$tag_config['son'];
            $tag_tpl_content=preg_replace($patterns,$replacements,$tag_tpl_content);
            $content=$tag_tpl_content;
        }
        return $content;
    }
}