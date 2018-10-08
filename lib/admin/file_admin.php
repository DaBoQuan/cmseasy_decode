<?php 

class file_admin extends admin {

    function init() {
        //chkpw('');自行加上chkpw验证权限
    }
    
    function delfile_action(){
        chkpw('file_del');
        if(front::$get['UD'] != 1){
            echo '2';exit;
        }
        if(front::$get['dfile'] == ''){
            echo '0';exit;
        }
        $f = str_ireplace(config::get('site_url'), '', front::$get['dfile']);
        $f = str_replace('.php', '', $f);
        if(@unlink(ROOT . '/'.$f)){
            echo 1;
        }else{
            echo 0;
        }
        exit;
    }

    function listdir_action() {
        $file_dir = ROOT .rtrim(config::get('html_prefix'),'/'). '/upload/images/';
        $dir_arr = array();
        if ($ch = opendir($file_dir)) {
            while ($dir = readdir($ch)) {
                if (!strstr('..', $dir))
                    $dir_arr[] = $dir;
            }
        }
        $this->view->dir_arr = $dir_arr;
        return $dir_arr;
    }

    function piclist_action() {
        if (!front::get('amid'))
            exit;
        $image_dir = ROOT . '/upload/images/' . front::get('amid');
        if (!is_dir($image_dir))
            exit;
        $handle = opendir($image_dir); //当前目录
        $img_array = array();
        while (false !== ($file = readdir($handle))) { //遍历该php文件所在目录
            list($filesname, $kzm) = explode(".", $file); //获取扩展名
            if ($kzm == "gif" or $kzm == "jpg" or $kzm == "png") { //文件过滤
                if (!is_dir('./' . $file)) { //文件夹过滤
                	//$img_arr[] = $file; //把符合条件的文件名存入数组
                    $img_arr['file'][] = $file; //把符合条件的文件名存入数组
                    $img_arr['time'][] = filemtime($image_dir.'/'.$file);
                }
            }
        }
        //rsort($img_arr);
        //var_dump($img_arr);
        @array_multisort($img_arr["time"], SORT_NUMERIC, SORT_DESC,$img_arr["file"], SORT_STRING, SORT_ASC);
        $img_arr = $img_arr['file'];
        $limit = 14;
        if (!front::get('page'))
            $page = 1;
        else
            $page = front::get('page');
        $total = ceil(count($img_arr) / $limit);
        if ($page < 1)
            $page = 1;
        if ($page > $total)
            $page = $total;
        $start = ($page - 1) * $limit;
        $end = $start + $limit - 1;
        $tmp = range($start, $end);
        echo "<ul>";
        $i=1+$page*$limit;
        if($img_arr) {
            foreach ($img_arr as $k => $v) {
                if (in_array($k, $tmp)) {
                    $file = $v;
                    //var_dump(config::get('base_url'));
                    //var_dump($this->base_url);
                    $base_url = $this->base_url;
                    if (substr($this->base_url, -1) != '/') {
                        $base_url .= '/';
                    }
                    $url = 'upload/images/' . front::get('amid') . '/' . $file;
                    $file = $base_url . 'upload/images/' . front::get('amid') . '/' . $file;
                    $info = @getimagesize($url);
                    echo '<li title="分辨率:' . $info[0] . 'x' . $info[1] . '" id="albumpic' . $i . '" onclick="alselected(\'albumpic' . $i . '\',\'' . $file . '\',\'selected\',1);"><p><img src="' . $file . '" width="100" height="100"><span class="panel_checkbox">已选中</span></p><p>分辨率:' . $info[0] . 'x' . $info[1] . '</p></li>';
                    $i++;
                }
            }
        }
        echo "</ul>";
        echo "<div class='clear'></div><div class='jspage'>".listPageJs($total, $limit, $page)."</div><div class='clear'></div>";
        exit;
    }
    
    function updialog_action(){
        $this->view->isadmin = 0;
        if (cookie::get('login_username')&&cookie::get('login_password')) {
        	$user=new user();
        	$user=$user->getrow(array('username'=>cookie::get('login_username')));
        	$roles = session::get('roles');
        	if ($roles && is_array($user)&&cookie::get('login_password')==front::cookie_encode($user['password'])) {
        		$this->view->isadmin = 1;
        	}
        }
        echo $this->view->fetch();
        exit;
    }
    
    function upfile_action(){
        echo $this->view->fetch();
        exit;
    }

    function upfile1_action(){
        echo $this->view->fetch();
        exit;
    }
    
    function netfile_action(){
        echo $this->view->fetch();
        exit;
    }
    function netfilesave_action(){
        if ($_POST['upfilepath']) {
            $filename = $_POST['upfilepath'];
            if(strtolower(substr($filename, 0, 7))!='http://'){
                echo "必须以HTTP://开始！";
                exit;
            }
            $ext = end(explode('.',$filename));
            if (!in_array($ext,array('jpg','png','gif'))) {
                echo "不允许的类型！";
                exit;
            }
            echo $filename.'|img|1|'.front::$post['alt'].'|'.front::$post['width'].'|'.front::$post['height'];
            exit;
        }
    }
    
    function ps_action(){
        $this->view->image_dir = image_admin::listdir();
        echo $this->view->fetch();
        exit;
    }

    function upfilesave1_action(){

    }
    
    function upfilesave_action(){
        if (is_array($_FILES['upfilepath'])) {
            $upload = new upload();
            $upload->dir = 'images';
            $upload->max_size = 200*1024*1024;
            $attachment = new attachment();
            $_file_type = str_replace(',','|',config::get('upload_filetype'));
            $file = $_FILES['upfilepath'];
            $file['name'] = strtolower($file['name']);
            if ($file['size'] > $upload->max_size) {
                echo "附件超过上限(".ceil($upload->max_size / 102400)."K)！');";
                exit;
            }
            if (!front::checkstr(file_get_contents($file['tmp_name']))) {
                echo '上传失败！请将图片保存为WEB格式！';
                exit;
            }
            if (!$file['name'] || !preg_match('/\.('.$_file_type.')$/',$file['name'])){
                echo '上传失败！不允许的文件类型！';
                exit;
            }
            $filename = $upload->run($file);
            if(config::get('watermark_open')) {
                include_once ROOT.'/lib/plugins/watermark.php';
                imageWaterMark($filename,config::get('watermark_pos'),config::get('watermark_path'),null,5,"#FF0000",config::get('watermark_ts'),config::get('watermark_qs'));
            }
            if (!$filename) {
                echo "附件保存失败！";
                exit;
            }
			//$img_info = getimagesize($filename);
            echo $filename.'|img|1|'.front::$post['alt'].'|'.$img_info[0].'|'.$img_info[1];
            exit;
        }
    }
    
    function swfsave_action(){
        if (is_array($_FILES['Filedata'])) {
            $upload = new upload();
            $upload->dir = 'images';
            $upload->max_size = 2048000;
            $attachment = new attachment();
            $_file_type = str_replace(',','|',config::get('upload_filetype'));
            $file = $_FILES['Filedata'];
            $file['name'] = strtolower($file['name']);
            if ($file['size'] > $upload->max_size) {
                echo "附件超过上限(".ceil($upload->max_size / 102400)."K)！');";
                exit;
            }
            if (!front::checkstr(file_get_contents($file['tmp_name']))) {
                echo '上传失败！请将图片保存为WEB格式！';
                exit;
            }
            if (!$file['name'] || !preg_match('/\.('.$_file_type.')$/',$file['name'])){
                echo '上传失败！不允许的文件类型！';
                exit;
            }
            $filename = $upload->run($file);
            if(config::get('watermark_open')) {
                include_once ROOT.'/lib/plugins/watermark.php';
                imageWaterMark($filename,config::get('watermark_pos'),config::get('watermark_path'),null,5,"#FF0000",config::get('watermark_ts'),config::get('watermark_qs'));
            }
            if (!$filename) {
                echo "附件保存失败！";
                exit;
            }
            echo 'ok_'.$filename;
            exit;
        }else{
            exit('请添加文件');
        }
    }

    function deleteimg_action() {
        if (!front::get('dir') || !front::get('imgname'))
            return;
        $img = ROOT . '/upload/images/' . front::get('dir') . '/' . str_replace('___', '.', front::get('imgname'));
        $img = str_replace('.php', '', $img);
        if (!file_exists($img))
            front::flash('图片不存在');
        if (!unlink($img))
            front::flash('删除失败，请检查权限');
        else
            front::flash('图片已删除');
        front::redirect(url::modify('act/listimg/dir/' . front::get('dir')));
    }

    function end() {
        $this->render('index.php');
    }

}