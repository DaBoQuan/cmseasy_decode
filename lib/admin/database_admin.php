<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class database_admin extends admin {
    function init() {
    }
    function index_action() {
    }
    function baker_action() {
    	chkpw('func_data_baker');
        if(front::post('submit')) {
            if(!is_array(front::post('select'))) return;
            tdatabase::getInstance()->bakTablesBags();
            front::flash('成功备份 '.count(front::post('select')).' 个表 ！');
        }
    }
	function backAll_action(){
		$dir=ROOT.'/data/backup';
		if(front::post('submit') &&is_array(front::post('select'))) {
			foreach(front::post('select') as $d) {
				@unlink($dir.'/'.$d);
			}
			front::flash('成功删除 '.count(front::post('select')).' 个档案！');
		}
		$dirs=front::scan($dir);
		$db_dirs=array();
		foreach($dirs as $dir) {
			if(!preg_match('/\.\./',$dir)) $db_dirs[]=$dir;
		}
		//var_dump($db_dirs);
		$this->view->db_dirs=$db_dirs;
	}
	function dobackAll_action(){
		$random = randomkeys(8);
		$database = new tdatabase();
		$database->autoBakTablesBags();
		if(!file_exists(ROOT.'/data/backup/'))@mkdir (ROOT.'/data/backup/', 0777,true);
		$pclzip = new PclZip(ROOT.'/data/backup/'.date('YmdHis').$random.'.zip');
		$pclzip->create(ROOT,PCLZIP_OPT_REMOVE_PATH,ROOT);
		if($pclzip == 0){
			die("Error : ".$pclzip->errorInfo(true));
		}else{
			die('ok');
		}
	}
    function restore_action() {
    	chkpw('func_data_restore');
        $dir=ROOT.'/data';
        if(front::post('submit') &&is_array(front::post('select'))) {
            foreach(front::post('select') as $d) {
                front::remove($dir.'/'.$d);
            }
            front::flash('成功删除 '.count(front::post('select')).' 个档案！');
        }
        $dirs=front::scan($dir);
        $db_dirs=array();
        foreach($dirs as $dir) {
            if(!preg_match('/\./',$dir) &&!preg_match('/hotsearch/',$dir)) $db_dirs[]=$dir;
        }
        $this->view->db_dirs=$db_dirs;
    }
    function dorestore_action() {
        $dir=ROOT.'/data/'.front::get('db_dir');
        if(is_dir($dir)) {
            $db_files=front::scan($dir);
            foreach($db_files as $db_file) {
                if(!preg_match('/^\./',$db_file)) tdatabase::getInstance()->restoreTables($dir.'/'.$db_file);
            }
            front::flash('数据库还原成功！');
        }
        front::redirect(url::create('database/restore'));
    }
    function str_replace_action() {
        chkpw('func_data_replace');
        if(front::post('submit') &&front::post('sfield') &&front::post('replace1')) {
            $field=front::post('sfield');
            $table=front::post('stable');
            $table=new $table();
            $replace1=front::post('replace1');
            $replace2=front::post('replace2');
            $where=front::post('where');
            if(!$where) {
                $table->getFields();
                $where=$table->primary_key.'>0';
            }
            $table->rec_update( " `$field` = REPLACE($field,'$replace1','$replace2')",$where);
            front::flash("成功替换！");
        }
        $_tables=tdatabase::getInstance()->getTables();
        $this->view->tables=array(0=>'请选则项目...');
        if(config::get('test_data')) $prefix='test_';
        else $prefix=config::get('database','prefix');
        foreach($_tables as $table) {
            if(!preg_match("/$prefix/is",$table['name'])) continue;
            $name=str_replace($prefix,'',$table['name']);
            $name=str_replace('a_','',$name);
            $_name=lang($name);
            if($_name<>$name)
                $this->view->tables[$name]=$_name;
        }
    }
    function dbfield_select_action() {
        $res=array();
        $res['content']='&nbsp;&nbsp;没有可以进行替换的字段。';
        $table=front::post('stable');
        if(@class_exists($table)) {
            $table=new $table;
            $_fields=array();
            foreach($table->getFields() as $field) {
                if(preg_match('/text|var/',$field['type']) &&!preg_match('/^[a-zA-Z_]+$/',lang($field['name'])))
                    $_fields[]=$field['name'];
            }
            $fields=array(0=>null);
            foreach($_fields as $field) $fields[$field]=lang($field);
            if(count($_fields)>0)
                $res['content']='&nbsp;&nbsp;字段=>'.form::select('sfield',0,$fields,'style="font-size:16px"');
        }
        $res['id']='fieldlist';
        echo json::encode($res);
        exit;
    }
    /**导phpweb的数据到cmseasy中*/
    function phpwebinsert_action(){
    	chkpw('func_data_phpweb');
        //插入数据库的总条目数
        $total_num = 0;
        $set=settings::getInstance();
        $set->name = $set->prefix.'user';
        //目标表前缀
        $d_prefix = $set->prefix;
        $user_info = $set->rec_select_one("`username`='{$_COOKIE['login_username']}'","*","`userid`");
               
        if(!empty(front::$post['submit'])){    	       	
            //判断是否填写原表前缀
         	if(!empty(front::$post['phpweb_prefix'])){
         		$s_prefix = front::$post['phpweb_prefix'].'_';
         	}else{
       		    front::flash('请填写原表前缀');
       		    return ;
          	}
          	//判断上传的数据库文件是否存在
          	$filename = ROOT.'/'.front::$post['data'];
          	if(!file_exists(ROOT.'/'.front::$post['data'])){
          		front::flash('请检查是否正确上传数据库文件');
          		return ;
          	}
            //记录前面插入的category的id
    	    $cat_id = array();
            $sql_file = fopen($filename,'r');
            while ($row = fgets($sql_file)){
               //如果这一行不是INSERT语句就略过
               if(!strstr($row,'INSERT')) continue; 
           
               $tmp = strstr($row,'(');
               $tmp = trim($tmp,"\n\t\r\0\x0B(); ");
               $tmp_arr = explode('),(',$tmp);
            
               //如果是feedback_info表,则选择对应数据插入guestbook中
               if(strstr($row,$s_prefix.'feedback_info')){
           	       foreach($tmp_arr as $v){
           	            $arr = super_explode($v);
           	            $arr_data = array(
           	                      'username'  =>$arr[4],
           	                      'adddate'   =>date('Y-m-d H:i:s',$arr[26]),
           	                      'state'     =>$arr[29],
           	                      'guesttel'  =>$arr[6],
           	                      'guestemail'=>$arr[8],           	                 
           	                      'guestqq'   =>$arr[10],
           	                      'title'     =>$arr[2],
           	                      'content'   =>$arr[3],
           	            );
           	           $id = put_into_db($d_prefix.'guestbook',$arr_data);
           	           if($id) $total_num++;
           	       
           	       }
                   continue;
               }
          
               //如果是advs_link表,则选择对应数据插入linkword中
               if(strstr($row,$s_prefix.'advs_link')){
           	       foreach($tmp_arr as $v){
           	            $arr = super_explode($v);
           	            $arr_data = array(
           	                      'linkword'  =>$arr[2],
           	                      'linkurl'   =>$arr[3],
           	                      'linktimes' =>mktime(),
           	            );
           	            $id = put_into_db($d_prefix.'linkword',$arr_data);
           	            if($id) $total_num++;
           	       }
                   continue;
               }
           
               //如果是pollindex表,则选择对应数据插入ballot中
               if(strstr($row,$s_prefix.'tools_pollindex')){
           	       foreach($tmp_arr as $v){
           	           $arr = super_explode($v);
           	            $arr_data = array(
           	                      'id'    =>$arr[0],
           	                      'title' =>$arr[1],
           	                          'type'  =>'radio',
           	            );
               	       $id = put_into_db($d_prefix.'ballot',$arr_data);
               	       if($id) $total_num++;
           	   }
                   continue;
               }
           
               //如果是tools_polldata表,则选择对应数据插入option中
               if(strstr($row,$s_prefix.'tools_polldata')){
               	   foreach($tmp_arr as $v){
               	        $arr = super_explode($v);
               	        $arr_data = array(
              	                  'bid'  =>$arr[1],
               	                  'name' =>$arr[3],
               	                  'num'  =>$arr[5],
               	                  'order'=>$arr[2],
               	                );
               	       $id = put_into_db($d_prefix.'option',$arr_data);
               	       if($id) $total_num++;
               	   }
                   continue;
               }
           
               //如果是product_cat表,则选择对应数据插入b_category中
               if(strstr($row,$s_prefix.'product_cat')){
               	   foreach($tmp_arr as $v){
               	        $arr = super_explode($v);
               	        $arr_data = array(
               	                  'parentid'          =>3,
           	                      'catname'           =>$arr[2],
           	                      'listorder'         => $arr[3],
           	                      'htmldir'           =>pinyin::get($arr[2]),
           	                      'showtemplate'      =>0,
           	                      'template'          =>'archive/list_pic.html',           	                   	        
               	                  'listtemplate'      =>'archive/list_pic.html',
               	                  'showtemplate'      =>'archive/show_products.html',
               	                  'includecatarchives'=>1,
           	                      'ispages'           =>1,
           	                      'ishtml'            =>0,      
           	                      'includecatarchives'=>1,     	                 
           	                      'thumb_width'       =>0,
           	                      'thumb_height'      =>0,
               	                  'isnav'             =>0, //是否在导航栏显示字段
               	                );
               	       $id = put_into_db($d_prefix.'b_category',$arr_data);
           	           $cat_id['product_cat'][$arr[0]] = $id;
           	           if($id) $total_num++;
           	       }
                   continue;
               }
           
               //如果是product_con表,则选择对应数据插入archive中
               if(strstr($row,$s_prefix.'product_con')){
               	   foreach($tmp_arr as $v){
           	            $arr = super_explode($v);
           	            $arr_data = array(
           	                      'catid'        =>isset($cat_id['product_cat'][$arr[1]]) ? $cat_id['product_cat'][$arr[1]] : -1,
           	                      'title'        =>$arr[5],
           	                      'username'     =>$user_info['username'],
               	                  'userid'       =>$user_info['userid'],
               	                  'view'         =>7,//确认首页是以图片的版面来显示
               	                  'spid'         =>0,
           	                      'tag'          =>$arr[43], 
           	                      'keyword'      =>$arr[43],           	                  
           	                      'listorder'    =>0,
           	                      'adddate'      =>date('Y-m-d H:i:s',$arr[16]),
           	                      'author'       =>$arr[17],
               	                  'thumb'        =>$arr[15],//列表显示的图片
               	                  'state'        =>1,           	                 
               	                  'checked'      =>1,
           	                      'introduce'    =>$arr[22],
           	                      'introduce_len'=>200,
           	                      'content'      =>$arr[6],
           	                      'template'     =>'archive/show_products.html',
           	                      'ishtml'       =>0,
               	                  'attr2'        =>9,//产品金额
               	                  'pics'         =>'a:1:{i:0;s:0:"";}',//内容多图
               	                  'city_id'      =>0,
               	                  'section_id'   =>0,
           	                    );
           	            $id = put_into_db($d_prefix.'archive',$arr_data);
           	            if($id) $total_num++;
               	   }
                   continue;
               }

               //如果是news_cat表,则选择对应数据插入b_category中
               if(strstr($row,$s_prefix.'news_cat')){
           	       foreach($tmp_arr as $v){
           	            $arr = super_explode($v);
           	            $arr_data = array(
           	                      'parentid'          =>2,
           	                      'catname'           =>$arr[2],
           	                      'listorder'         =>$arr[3],
           	                      'htmldir'           =>pinyin::get($arr[2]),          	                  
           	                      'template'          =>'archive/list_text.html',             	                         	                   	        
           	                      'listtemplate'      =>'archive/list_text.html',
           	                      'showtemplate'      =>0,
           	                      'includecatarchives'=>1,
           	                      'ispages'           =>1,
           	                      'ishtml'            =>0,    
           	                      'includecatarchives'=>1,       	                 
           	                      'thumb_width'       =>0,
           	                      'thumb_height'      =>0,
           	                      'isnav'             =>0, //是否在导航栏显示字段
           	                    );
           	           $id = put_into_db($d_prefix.'b_category',$arr_data);
           	           $cat_id['news_cat'][$arr[0]] = $id;
           	           if($id) $total_num++;
           	       }
                   continue;
               }
           
               //如果是news_con表,则选择对应数据插入archive中
               if(strstr($row,$s_prefix.'news_con')){
           	       foreach($tmp_arr as $v){
           	            $arr = super_explode($v);
           	            $arr_data = array(
           	                      'catid'        =>isset($cat_id['news_cat'][$arr[1]]) ? $cat_id['news_cat'][$arr[1]] : -1 ,
           	                      'title'        =>$arr[5],
           	                      'tag'          =>$arr[46], 
           	                      'username'     =>$user_info['username'],
           	                      'userid'       =>$user_info['userid'],
           	                      'view'         =>0,//确认首页是以文本版面显示
           	                      'spid'         =>0,
           	                      'keyword'      =>$arr[46],       	                  
           	                      'listorder'    =>0,
           	                      'adddate'      =>date('Y-m-d H:i:s',$arr[16]),
           	                      'author'       =>$arr[17],
           	                      'thumb'        =>'',//列表显示的图片
           	                      'state'        =>1,           	                 
           	                      'checked'      =>1,
           	                      'introduce'    =>$arr[22],
           	                      'introduce_len'=>200,
           	                      'content'      =>$arr[6],
           	                      'template'     =>0,
           	                      'ishtml'       =>0,
           	                      'attr2'        =>'',//产品金额
           	                      'pics'         =>'a:0:{}',
           	                      'city_id'      =>0,
           	                      'section_id'   =>0,
           	                    );
           	            $id = put_into_db($d_prefix.'archive',$arr_data);
           	            if($id) $total_num++;
           	       }
                   continue;
               }
           
               //如果是down_cat表,则选择对应数据插入b_category中
               if(strstr($row,$s_prefix.'down_cat')){
           	       foreach($tmp_arr as $v){
           	            $arr = super_explode($v);
           	            $arr_data = array(
           	                      'parentid'          =>6,
           	                      'catname'           =>$arr[2],
           	                      'listorder'         =>$arr[3],
           	                      'htmldir'           =>pinyin::get($arr[2]),          	                  
           	                      'template'          =>'archive/list_down.html',             	                         	                   	        
           	                      'listtemplate'      =>'archive/list_down.html',
           	                      'showtemplate'      =>0,
           	                      'includecatarchives'=>1,
           	                      'ispages'           =>1,
           	                      'ishtml'            =>0,    
           	                      'includecatarchives'=>1,       	                 
           	                      'thumb_width'       =>0,
           	                      'thumb_height'      =>0,
           	                      'isnav'             =>0, //是否在导航栏显示字段
           	                    );
           	           $id = put_into_db($d_prefix.'b_category',$arr_data);
           	           $cat_id['down_cat'][$arr[0]] = $id;
           	           if($id) $total_num++;
           	       }
                   continue;
               }
           
               //如果是down_con表,则选择对应数据插入archive中
               if(strstr($row,$s_prefix.'down_con')){
           	       foreach($tmp_arr as $v){
           	            $arr = super_explode($v);
           	            $arr_data = array(
           	                      'catid'        =>isset($cat_id['down_cat'][$arr[1]]) ? $cat_id['down_cat'][$arr[1]] : -1 ,
           	                      'title'        =>$arr[5],
           	                      'tag'          =>$arr[45], 
           	                      'username'     =>$user_info['username'],
           	                      'userid'       =>$user_info['userid'],
           	                      'view'         =>0,//确认首页是以文本版面显示
           	                      'spid'         =>0,
           	                      'keyword'      =>$arr[45],       	                  
           	                      'listorder'    =>0,
           	                      'adddate'      =>date('Y-m-d H:i:s',$arr[16]),
           	                      'author'       =>$arr[17],
           	                      'thumb'        =>'',//列表显示的图片
           	                      'state'        =>1,           	                 
           	                      'checked'      =>1,
           	                      'introduce'    =>$arr[22],
           	                      'introduce_len'=>200,
           	                      'content'      =>$arr[6],
           	                      'template'     =>0,
           	                      'ishtml'       =>0,
           	                      'linkto'       =>$arr[43],
           	                      'attr1'        =>$arr[44],//存放文件被下载的次数
           	                      'pics'         =>'a:1:{i:0;s:0:"";}',
           	                      'city_id'      =>0,
           	                      'section_id'   =>0,
           	                    );
           	            $id = put_into_db($d_prefix.'archive',$arr_data);
           	            if($id) $total_num++;
           	       }
                   continue;
               }
           
               //如果是photo_cat表,则选择对应数据插入b_category中
               if(strstr($row,$s_prefix.'photo_cat')){
           	       foreach($tmp_arr as $v){
           	            $arr = super_explode($v);
           	            $arr_data = array(
           	                      'parentid'          =>2,
               	                  'catname'           =>$arr[2],
               	                  'listorder'         => $arr[3],
               	                  'htmldir'           =>pinyin::get($arr[2]),          	                  
           	                      'template'          =>'archive/list_text.html',             	                         	                   	        
           	                      'listtemplate'      =>'archive/list_text.html',
           	                      'showtemplate'      =>0,
           	                      'includecatarchives'=>1,
           	                      'ispages'           =>1,
               	                  'ishtml'            =>0,    
               	                  'includecatarchives'=>1,       	                 
               	                  'thumb_width'       =>0,
           	                      'thumb_height'      =>0,
           	                      'isnav'             =>0, //是否在导航栏显示字段
           	                    );
           	           $id = put_into_db($d_prefix.'b_category',$arr_data);
           	           $cat_id['photo_cat'][$arr[0]] = $id;
           	           if($id) $total_num++;
           	       }
                   continue;
               }
           
               //如果是photo_con表,则选择对应数据插入archive中
               if(strstr($row,$s_prefix.'photo_con')){
               	   foreach($tmp_arr as $v){
           	            $arr = super_explode($v);
           	            $arr_data = array(
               	                  'catid'        =>isset($cat_id['photo_cat'][$arr[1]]) ? $cat_id['photo_cat'][$arr[1]] : -1 ,
               	                  'title'        =>$arr[5],
               	                  'tag'          =>$arr[22], 
           	                      'username'     =>$user_info['username'],
           	                      'userid'       =>$user_info['userid'],
           	                      'view'         =>0,//确认首页是以文本版面显示
           	                      'spid'         =>0,
           	                      'keyword'      =>$arr[22],       	                  
               	                  'listorder'    =>0,
               	                  'adddate'      =>date('Y-m-d H:i:s',$arr[16]),
               	                  'author'       =>$arr[17],
           	                      'image'        =>$arr[15],//列表显示的图片
           	                      'state'        =>1,           	                 
           	                      'checked'      =>1,
           	                      'introduce'    =>$arr[22],
           	                      'introduce_len'=>200,
           	                      'content'      =>$arr[6],
           	                      'template'     =>0,
           	                      'ishtml'       =>0,
           	                      'attr2'        =>'',//产品金额
           	                      'pics'         =>'a:0:{}',
           	                      'city_id'      =>0,
           	                      'section_id'   =>0,
           	                    );
           	            $id = put_into_db($d_prefix.'archive',$arr_data);
           	            if($id) $total_num++;
           	       }
                   continue;
               } 
             }
             front::flash('已共插入'.$total_num.'条数据');
         }
    }
    function dir_path($path) {
        $path = str_replace('\\','/',$path);
        if(substr($path,-1) != '/') $path = $path.'/';
        return $path;
    }
    function end() {
        $this->render('index.php');
    }
}