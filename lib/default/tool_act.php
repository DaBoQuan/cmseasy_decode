<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');

class tool_act extends act
{

    function init()
    {
    }

    function index_action()
    {
    }

    function geetest_action()
    {
        require_once ROOT . '/lib/plugins/geetestlib.php';
        $GtSdk = new GeetestLib();
        $return = $GtSdk->register();
        if ($return) {
            $_SESSION['gtserver'] = 1;
            $result = array(
                'success' => 1,
                'gt' => config::get('gee_id'),
                'challenge' => $GtSdk->challenge
            );
            echo json_encode($result);
        } else {

            $_SESSION['gtserver'] = 0;
            $rnd1 = md5(rand(0, 100));
            $rnd2 = md5(rand(0, 100));
            $challenge = $rnd1 . substr($rnd2, 0, 2);
            $result = array(
                'success' => 0,
                'gt' => config::get('gee_id'),
                'challenge' => $challenge
            );
            $_SESSION['challenge'] = $result['challenge'];
            echo json_encode($result);
        }
        exit;
    }

    function verify_action()
    {
        echo verify::show();
    }

    public function smscode_action()
    {
        $mobile = front::$post['mobile'];
        if (!$mobile) {
            $username = front::$post['username'];
            $user_obj = new user();
            $user = $user_obj->getrow(array('username' => $username));
            $mobile = $user['tel'];
        }
        if (!preg_match('/^1([0-9]+){5,}$/is', $mobile)) {
            exit(lang('phone_number_format_is_wrong'));
        }
        $func = 'chkcode';
        $smsCode = new SmsCode();
        $smsCode->getCode();
        $content = $smsCode->getTemplate($func);
        if ($rs = sendMsg($mobile, $content) == 0) {
            exit(lang('successfully_sent_please_check'));
        } else {
            exit(lang('sms_send_failure'));
        }
    }

    function qrcode_action()
    {
        require_once(ROOT . '/lib/plugins/phpqrcode/qrlib.php');
        $url = $_GET['data'];
        if ($url) {
            $url = htmlspecialchars_decode(urldecode($url));
            //var_dump($url);
        } else {
            $url = $_SERVER['HTTP_REFERER'];
        }
        QRcode::png($url);
    }

    function wxqrcode_action()
    {
        $url = $_GET['data'];
        $url = htmlspecialchars_decode(urldecode($url));
        require_once(ROOT . '/lib/plugins/phpqrcode/qrlib.php');
        QRcode::png($url);
    }

    function upload_action()
    {
        $res = array();
        $uploads = array();
        if (is_array($_FILES)) {
            $upload = new upload();
            foreach ($_FILES as $name => $file) {
                if (!$file['name'] || !preg_match('/\.(jpg|gif|png|bmp)$/', $file['name'])) {
                    continue;
                }
                $uploads[$name] = $upload->run($file);
                $res[$name]['name'] = '';
                if (config::get('base_url') == '/') {
                    $res[$name]['name'] = '/' . $uploads[$name];
                } else {
                    $res[$name]['name'] = config::get('base_url') . '/' . $uploads[$name];
                }
                if (empty($uploads[$name])) {
                    $res['error'] = $name . lang('upload_failed');
                    break;
                }
                $path = $upload->save_path;
                chmod($path, 0644);
                $catid = get('catid');
                $type = get('type');
                if ($type == 'thumb' && !get('cut')) {
                    $thumb = new thumb();
                    $thumb->set($path, 'file');
                    if ($catid)
                        $thumb->create($path, category::getwidthofthumb($catid), category::getheightofthumb($catid));
                    else
                        $thumb->create($path, config::get('thumb_width'), config::get('thumb_height'));
                }
                $_name = str_replace('_upload', '', $name);
                $res[$name]['code'] = "
                if(document.form1) {
                //document.form1.$_name.value=data[key].name;
                $('#$_name').val(data[key].name);
                }
                else
                $('#$_name').val(data[key].name);
                image_preview('$_name',data[key].name);
                        ";
            }
        }
        echo json::encode($res);
    }

    function upload_thumb_action()
    {
        $res = array();
        $uploads = array();
        if (is_array($_FILES)) {
            $upload = new upload();
            foreach ($_FILES as $name => $file) {
                if (!$file['name'] || !preg_match('/\.(jpg|gif|png|bmp)$/', $file['name'])) {
                    continue;
                }
                $uploads[$name] = $upload->run($file);
                if (empty($uploads[$name])) {
                    $res['error'] = $name . lang('upload_failed');
                    break;
                }
                $res[$name]['name'] = $uploads[$name];
                $path = $upload->save_path;
                chmod($path, 0644);
                $thumb = new thumb();
                $thumb->set($path, 'file');
                $catid = get('catid');
                $type = get('type');
                if ($catid)
                    $thumb->create($path, category::getwidthofthumb($catid), category::getheightofthumb($catid));
                else
                    $thumb->create($path, config::get('thumb_width'), config::get('thumb_height'));
                $_name = str_replace('_upload', '', $name);
                $res[$name]['code'] = "
                document.form1.$_name.value=data[key].name;
                image_preview('$_name',data[key].name);
                        ";
            }
        }
        echo json::encode($res);
    }

    function upload3_action()
    {
        $res = array();
        $uploads = array();
        if (is_array($_FILES)) {
            $upload = new upload();
            foreach ($_FILES as $name => $file) {
                if (!$file['name'] || !preg_match('/\.(jpg|gif|png|bmp)$/', $file['name'])) {
                    continue;
                }
                $uploads[$name] = $upload->run($file);
                $res[$name]['name'] = front::$view->base_url . '/' . $uploads[$name];
                $path = $upload->save_path;
                chmod($path, 0644);
                $thumb = new thumb();
                $thumb->set($path, 'file');
                $thumb->create($path, config::get('slide_width'));
                $_name = str_replace('_upload', '', $name);
                $res[$name]['code'] = "document.config_form.$_name.value=data[key].name;image_preview('$_name',data[key].name);";
            }
        }
        echo json::encode($res);
    }

    function upload1_action()
    {
        $res = array();
        $uploads = array();
        if (is_array($_FILES)) {
            $upload = new upload();
            foreach ($_FILES as $name => $file) {
                if (!$file['name'] || !preg_match('/\.(jpg|gif|png|bmp)$/', $file['name'])) {
                    continue;
                }
                $uploads[$name] = $upload->run($file);
                $res[$name]['name'] = $uploads[$name];
                $path = $upload->save_path;
                chmod($path, 0644);
                $_name = str_replace('_upload', '', $name);
                $res[$name]['code'] = "document.form1.$_name.value=data[key].name;image_preview('$_name',data[key].name);";
            }
        }
        echo json::encode($res);
    }

    function upload2_action()
    {
        $res = array();
        $uploads = array();
        if (is_array($_FILES)) {
            $upload = new upload();
            foreach ($_FILES as $name => $file) {
                if (!$file['name'] || !preg_match('/\.(jpg|gif|png|bmp)$/', $file['name'])) {
                    continue;
                }
                $uploads[$name] = $upload->run($file);
                $res[$name]['name'] = $uploads[$name];
                $path = $upload->save_path;
                chmod($path, 0644);
                $_name = str_replace('_upload', '', $name);
                $res[$name]['code'] = "document.form1.$_name.value=data[key].name;image_preview('$_name',data[key].name);";
            }
        }
        echo json::encode($res);
    }

    function upload_file_action()
    {
        $res = array();
        $uploads = array();
        if (is_array($_FILES)) {
            $upload = new upload();
            $upload->dir = 'attachment';
			$upload->type = explode(',', config::get('upload_filetype'));
            $_file_type = str_replace(',', '|', config::get('upload_filetype'));
            foreach ($_FILES as $name => $file) {
                if (!$file['name'] || !preg_match('/\.(' . $_file_type . ')$/', $file['name']))
                    continue;
                $uploads[$name] = $upload->run($file);
                $res[$name]['name'] = $uploads[$name];
                $_name = str_replace('_upload', '', $name);
                /*
                $res[$name]['code']="
                document.form1.$_name.value=data[key].name;
                ";*/
                $res[$name]['code'] = "$('#$_name').val(data[key].name);image_preview('$_name',data[key].name);";

            }
        }
        echo json::encode($res);
    }

    function uploadfile_action()
    {
        $res = array();
        $uploads = array();
        if (is_array($_FILES)) {
            $upload = new upload();
            $upload->dir = 'attachment';
            $upload->max_size = config::get('upload_max_filesize') * 1024 * 1024;
            $attachment = new attachment();
            $_file_type = str_replace(',', '|', config::get('upload_filetype'));
            $upload->type = explode('|', $_file_type);
            foreach ($_FILES as $name => $file) {
                $res[$name]['size'] = ceil($file['size'] / 1024);
                if ($file['size'] > $upload->max_size) {
                    $res[$name]['code'] = "alert('" . lang('attachment_exceeding_the_upper_limit') . "(" . ceil($upload->max_size / 1024) . "K)！');";
                    break;
                }
                if (!front::checkstr(file_get_contents($file['tmp_name']))) {
                    $res[$name]['code'] = lang('upload_failed_attachment_is_not_verified');
                    break;
                }
                if (!$file['name'] || !preg_match('/\.(' . $_file_type . ')$/', $file['name']))
                    continue;
                $uploads[$name] = $upload->run($file);
                if (!$uploads[$name]) {
                    $res[$name]['code'] = "alert('" . lang('attachment_save_failed') . ");";
                    break;
                }
                $res[$name]['name'] = $uploads[$name];
                $res[$name]['type'] = $file['type'];
                $attachment->rec_insert(array('path' => $uploads[$name], 'intro' => front::post('attachment_intro'), 'adddate' => date('Y-m-d H:i:s')));
                $res[$name]['id'] = $attachment->insert_id();
                $rname = preg_replace('%(.*)[\\\\\/](.*)_\d+(\.[a-z]+)$%i', '$2$3', $uploads[$name]);
                $res[$name]['code'] = "
                document.form1.attachment_id.value=data[key].id;
                if(!document.form1.attachment_intro.value) {
                document.form1.attachment_intro.value='$rname';
                }
                document.form1.attachment_path.value=data[key].name;
                get('attachment_path_i').innerHTML=data[key].name;
                get('file_info').innerHTML=lang('attachment_has_been_saved_size')+data[key].size+'K ';
                        ";
                session::set('attachment_id', $res[$name]['id']);
            }
        }
        echo json::encode($res);
    }

    function uploadimage_action()
    {
        $res = array();
        $uploads = array();
        if (is_array($_FILES)) {
            $upload = new upload();
            $upload->dir = 'images';
            $upload->max_size = config::get('upload_max_filesize') * 1024 * 1024;
            $attachment = new attachment();
            $_file_type = str_replace(',', '|', config::get('upload_filetype'));
            foreach ($_FILES as $name => $file) {
                $res[$name]['size'] = ceil($file['size'] / 1024);
                if ($file['size'] > $upload->max_size) {
                    $res[$name]['code'] = "alert('" . lang('attachment_exceeding_the_upper_limit') . "(" . ceil($upload->max_size / 1024) . "K)！');";
                    break;
                }
                if (!front::checkstr(file_get_contents($file['tmp_name']))) {
                    $res[$name]['code'] = lang('upload_failed_attachment_is_not_verified');
                    break;
                }
                if (!$file['name'] || !preg_match('/\.(' . $_file_type . ')$/', $file['name']))
                    continue;
                $uploads[$name] = $upload->run($file);
                if (!$uploads[$name]) {
                    $res[$name]['code'] = "alert('" . lang('attachment_save_failed') . "');";
                    break;
                }
                $res[$name]['name'] = $uploads[$name];
                $res[$name]['type'] = $file['type'];
                $rname = preg_replace('%(.*)[\\\\\/](.*)_\d+(\.[a-z]+)$%i', '$2$3', $uploads[$name]);
                $res[$name]['code'] = "
                document.form1.attachment_id.value=data[key].id;
                if(!document.form1.attachment_intro.value) {
                document.form1.attachment_intro.value='$rname';
                }
                get('attachment_path').innerHTML=data[key].name;
                get('file_info').innerHTML=lang('attachment_has_been_saved_size')+data[key].size+'K ';
                        ";
                if (substr(config::get('base_url'), -1, 1) != '/') {
                    $ex = '/';
                }
                $str = config::get('base_url') . $ex . $uploads[$name];
                echo $str;
                return;
            }
        }
        echo json::encode($res);
    }

    function uploadimage2_action()
    {
        $res = array();
        $uploads = array();
        if (is_array($_FILES)) {
            $upload = new upload();
            $upload->dir = 'images';
            $upload->max_size = config::get('upload_max_filesize') * 1024 * 1024;
            $attachment = new attachment();
            $_file_type = str_replace(',', '|', config::get('upload_filetype'));
            foreach ($_FILES as $name => $file) {
                $res[$name]['size'] = ceil($file['size'] / 1024);
                if ($file['size'] > $upload->max_size) {
                    $res[$name]['code'] = "alert('" . lang('attachment_exceeding_the_upper_limit') . "(" . ceil($upload->max_size / 1024) . "K)！');";
                    break;
                }
                if (!front::checkstr(file_get_contents($file['tmp_name']))) {
                    $res[$name]['code'] = lang('upload_failed_attachment_is_not_verified');
                    break;
                }
                if (!$file['name'] || !preg_match('/\.(' . $_file_type . ')$/', $file['name']))
                    continue;
                $uploads[$name] = $upload->run($file);
                if (!$uploads[$name]) {
                    $res[$name]['code'] = "alert('" . lang('attachment_save_failed') . "');";
                    break;
                }
                $res[$name]['name'] = $uploads[$name];
                $res[$name]['type'] = $file['type'];
                $rname = preg_replace('%(.*)[\\\\\/](.*)_\d+(\.[a-z]+)$%i', '$2$3', $uploads[$name]);
                $res[$name]['code'] = "
                document.form1.attachment_id.value=data[key].id;
                if(!document.form1.attachment_intro.value) {
                document.form1.attachment_intro.value='$rname';
                }
                get('attachment_path').innerHTML=data[key].name;
                get('file_info').innerHTML=lang('attachment_has_been_saved_size')+data[key].size+'K ';
                        ";
                /*if(substr(config::get('base_url'),-1,1) != '/'){
                    $ex = '/';
                }*/
                $str = config::get('site_url') . $uploads[$name];
                echo $str;
                return;
            }
        }
        echo json::encode($res);
    }

    function cut_image_action()
    {
        die("request error");
    }

    function deleteattachment_action()
    {
        $attachment = new attachment();
        $id = intval(front::get('id'));
        $attachment->del($id);
    }

    function ding_action()
    {
        echo tool::text_javascript('null');
    }
}