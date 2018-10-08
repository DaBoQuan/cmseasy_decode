<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');

class ueditor_act extends act {

    function init() {
        set_time_limit(0);
        include ROOT."/lib/plugins/Uploader.class.php";
    }

    function config(){
        $arr['imageActionName'] = 'uploadimage';
        $arr['imageFieldName'] = 'upfile';
        $arr['imageMaxSize'] = 200 * 1024 * 1024;
        $arr['imageAllowFiles'] = array('.png','.jpg','.jpeg','.gif','.bmp','.ico');
        $arr['imageCompressEnable'] = true;
        $arr['imageCompressBorder'] = 1600;
        $arr['imageInsertAlign'] = 'none';
        $arr['imageUrlPrefix'] = '';
        $arr['imagePathFormat'] = $this->base_url. rtrim(config::get('html_prefix'),'/').'/upload/images/{yyyy}{mm}/{time}{rand:4}';

        /* 涂鸦图片上传配置项 */
        $arr['scrawlActionName'] =  'uploadscrawl'; /* 执行上传涂鸦的action名称 */
        $arr['scrawlFieldName'] = 'upfile'; /* 提交的图片表单名称 */
        $arr['scrawlPathFormat'] = $this->base_url. rtrim(config::get('html_prefix'),'/').'/upload/images/{yyyy}{mm}/{time}{rand:4}'; /* 上传保存路径,可以自定义保存路径和文件名格式 */
        $arr['scrawlMaxSize'] = 200 * 1024 * 1024; /* 上传大小限制，单位B */
        $arr['scrawlUrlPrefix'] = ''; /* 图片访问路径前缀 */
        $arr['scrawlInsertAlign'] = 'none';

        /* 截图工具上传 */
        $arr['snapscreenActionName'] = 'uploadimage'; /* 执行上传截图的action名称 */
        $arr['snapscreenPathFormat'] = $this->base_url. rtrim(config::get('html_prefix'),'/').'/upload/images/{yyyy}{mm}/{time}{rand:4}'; /* 上传保存路径,可以自定义保存路径和文件名格式 */
        $arr['snapscreenUrlPrefix'] = ''; /* 图片访问路径前缀 */
        $arr['snapscreenInsertAlign'] = 'none'; /* 插入的图片浮动方式 */

        /* 抓取远程图片配置 */
        $arr['catcherLocalDomain'] = array("127.0.0.1", "localhost", "img.baidu.com");
        $arr['catcherActionName'] = 'catchimage'; /* 执行抓取远程图片的action名称 */
        $arr['catcherFieldName'] = 'source'; /* 提交的图片列表表单名称 */
        $arr['catcherPathFormat'] = $this->base_url. rtrim(config::get('html_prefix'),'/').'/upload/images/{yyyy}{mm}/{time}{rand:4}'; /* 上传保存路径,可以自定义保存路径和文件名格式 */
        $arr['catcherUrlPrefix'] = ''; /* 图片访问路径前缀 */
        $arr['catcherMaxSize'] = 200 * 1024 * 1024; /* 上传大小限制，单位B */
        $arr['catcherAllowFiles'] = array(".png", ".jpg", ".jpeg", ".gif", ".bmp"); /* 抓取图片格式显示 */

        /* 上传视频配置 */
        $arr['videoActionName'] = 'uploadvideo'; /* 执行上传视频的action名称 */
        $arr['videoFieldName'] = 'upfile'; /* 提交的视频表单名称 */
        $arr['videoPathFormat'] = $this->base_url. rtrim(config::get('html_prefix'),'/').'/upload/videos/{yyyy}{mm}/{time}{rand:4}'; /* 上传保存路径,可以自定义保存路径和文件名格式 */
        $arr['videoUrlPrefix'] = ''; /* 视频访问路径前缀 */
        $arr['videoMaxSize'] = 200 * 1024 * 1024; /* 上传大小限制，单位B，默认100MB */
        $arr['videoAllowFiles'] = array(
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"
        ); /* 上传视频格式显示 */

        /* 上传文件配置 */
        $arr['fileActionName'] = 'uploadfile'; /* controller里,执行上传视频的action名称 */
        $arr['fileFieldName'] = 'upfile'; /* 提交的文件表单名称 */
        $arr['filePathFormat'] = $this->base_url. rtrim(config::get('html_prefix'),'/').'/upload/files/{yyyy}{mm}/{time}{rand:4}'; /* 上传保存路径,可以自定义保存路径和文件名格式 */
        $arr['fileUrlPrefix'] = ''; /* 文件访问路径前缀 */
        $arr['fileMaxSize'] = 200 * 1024 * 1024; /* 上传大小限制，单位B，默认50MB */
        $arr['fileAllowFiles'] = array(
                ".png", ".jpg", ".jpeg", ".gif", ".bmp",
                ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
                ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
                ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        ); /* 上传文件格式显示 */

        /* 列出指定目录下的图片 */
        $arr['imageManagerActionName'] = 'listimage'; /* 执行图片管理的action名称 */
        $arr['imageManagerListPath'] = $this->base_url. rtrim(config::get('html_prefix'),'/').'/upload/images/'; /* 指定要列出图片的目录 */
        $arr['imageManagerListSize'] = 20; /* 每次列出文件数量 */
        $arr['imageManagerUrlPrefix'] = ''; /* 图片访问路径前缀 */
        $arr['imageManagerInsertAlign'] = 'none'; /* 插入的图片浮动方式 */
        $arr['imageManagerAllowFiles'] = array(".png", ".jpg", ".jpeg", ".gif", ".bmp"); /* 列出的文件类型 */

        /* 列出指定目录下的文件 */
        $arr['fileManagerActionName'] = 'listfile'; /* 执行文件管理的action名称 */
        $arr['fileManagerListPath'] = $this->base_url. rtrim(config::get('html_prefix'),'/').'/upload/files/'; /* 指定要列出文件的目录 */
        $arr['fileManagerUrlPrefix'] = ''; /* 文件访问路径前缀 */
        $arr['fileManagerListSize'] = 20; /* 每次列出文件数量 */
        $arr['fileManagerAllowFiles'] = array(
                ".png", ".jpg", ".jpeg", ".gif", ".bmp",
                ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
                ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
                ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        );
        return json_encode($arr);
    }

    function upload(){
        $CONFIG = json_decode($this->config(),true);
        /* 上传配置 */
        $base64 = "upload";
        switch (htmlspecialchars($_GET['action'])) {
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $CONFIG['imagePathFormat'],
                    "maxSize" => $CONFIG['imageMaxSize'],
                    "allowFiles" => $CONFIG['imageAllowFiles']
                );
                $fieldName = $CONFIG['imageFieldName'];
                break;
            case 'uploadscrawl':
                $config = array(
                    "pathFormat" => $CONFIG['scrawlPathFormat'],
                    "maxSize" => $CONFIG['scrawlMaxSize'],
                    "allowFiles" => $CONFIG['scrawlAllowFiles'],
                    "oriName" => "scrawl.png"
                );
                $fieldName = $CONFIG['scrawlFieldName'];
                $base64 = "base64";
                break;
            case 'uploadvideo':
                $config = array(
                    "pathFormat" => $CONFIG['videoPathFormat'],
                    "maxSize" => $CONFIG['videoMaxSize'],
                    "allowFiles" => $CONFIG['videoAllowFiles']
                );
                $fieldName = $CONFIG['videoFieldName'];
                break;
            case 'uploadfile':
            default:
                $config = array(
                    "pathFormat" => $CONFIG['filePathFormat'],
                    "maxSize" => $CONFIG['fileMaxSize'],
                    "allowFiles" => $CONFIG['fileAllowFiles']
                );
                $fieldName = $CONFIG['fileFieldName'];
                break;
        }

        /* 生成上传实例对象并完成上传 */
        $up = new Uploader($fieldName, $config, $base64);

        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
         *     "url" => "",            //返回的地址
         *     "title" => "",          //新文件名
         *     "original" => "",       //原始文件名
         *     "type" => ""            //文件类型
         *     "size" => "",           //文件大小
         * )
         */

        /* 返回数据 */
        return json_encode($up->getFileInfo());

    }

    function actList(){
        $CONFIG = json_decode($this->config(),true);
        /* 判断类型 */
        switch ($_GET['action']) {
            /* 列出文件 */
            case 'listfile':
                $allowFiles = $CONFIG['fileManagerAllowFiles'];
                $listSize = $CONFIG['fileManagerListSize'];
                $path = $CONFIG['fileManagerListPath'];
                break;
            /* 列出图片 */
            case 'listimage':
            default:
                $allowFiles = $CONFIG['imageManagerAllowFiles'];
                $listSize = $CONFIG['imageManagerListSize'];
                $path = $CONFIG['imageManagerListPath'];
        }
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        /* 获取参数 */
        $size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
        $start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
        $end = $start + $size;

        /* 获取文件列表 */
        $path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            return json_encode(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "total" => count($files)
            ));
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }
        //倒序
        //for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
        //    $list[] = $files[$i];
        //}

        /* 返回数据 */
        $result = json_encode(array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "total" => count($files)
        ));

        return $result;
    }

    /**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    function getfiles($path, $allowFiles, &$files = array())
    {
        if (!is_dir($path)) return null;
        if(substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
                        $files[] = array(
                            'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                            'mtime'=> filemtime($path2)
                        );
                    }
                }
            }
        }
        return $files;
    }

    function crawler(){
        $CONFIG = json_decode($this->config(),true);
        /* 上传配置 */
        $config = array(
            "pathFormat" => $CONFIG['catcherPathFormat'],
            "maxSize" => $CONFIG['catcherMaxSize'],
            "allowFiles" => $CONFIG['catcherAllowFiles'],
            "oriName" => "remote.png"
        );
        $fieldName = $CONFIG['catcherFieldName'];

        /* 抓取远程图片 */
        $list = array();
        if (isset($_POST[$fieldName])) {
            $source = $_POST[$fieldName];
        } else {
            $source = $_GET[$fieldName];
        }
        foreach ($source as $imgUrl) {
            $item = new Uploader($imgUrl, $config, "remote");
            $info = $item->getFileInfo();
            array_push($list, array(
                "state" => $info["state"],
                "url" => $info["url"],
                "size" => $info["size"],
                "title" => htmlspecialchars($info["title"]),
                "original" => htmlspecialchars($info["original"]),
                "source" => htmlspecialchars($imgUrl)
            ));
        }

        /* 返回抓取数据 */
        return json_encode(array(
            'state'=> count($list) ? 'SUCCESS':'ERROR',
            'list'=> $list
        ));
    }

    function index_action() {
        $action = $_GET['action'];
        switch ($action) {
            case 'config':
                $result =  $this->config();
                break;

            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = $this->upload();
                break;

            /* 列出图片 */
            case 'listimage':
                $result = $this->actList();
                break;
            /* 列出文件 */
            case 'listfile':
                $result = $this->actList();
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = $this->crawler();
                break;

            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }
}