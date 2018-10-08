<?php 
/**
 * cmseasy: index.php
 * ============================================================================
 * 版权所有 2018 cmseasy，并保留所有权利。
 * -------------------------------------------------------
 * 这不是一个自由软件！也不是一个开源软件！您不能在任何目的的前提下对程序代码进行破解、修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 您可以免费应用与商业网站，但需要保留软件版权及版权链接。
 * ============================================================================
 * @version:    v6.4 r20180823
 * ---------------------------------------------
 * $Id: index.php 2018/08/23
 */


header("Pragma:no-cache\r\n");
header("Cache-Control:no-cache\r\n");
header("Expires:0\r\n");

header("Content-Type: text/html; charset=utf-8");
header('Cache-control: private, must-revalidate'); //支持页面回跳
date_default_timezone_set('Etc/GMT-8');
$_GET['site']='default';
error_reporting(0);
//error_reporting(E_ALL & ~(E_NOTICE | E_STRICT | E_DEPRECATED));

class time {

    static $start;
    static function start() {
        self::$start=self::getMicrotime();
    }

    static function getMicrotime() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    static function getTime($length=6) {
        return round(self::getMicrotime()-self::$start, $length);
    }
}

function is_mobile() {
    if(!config::get('mobile_open')){
        return false;
    }elseif(config::get('mobile_open') == 2){
        return true;
    }else {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = Array("240x320", "acer", "acoon", "acs-", "abacho", "ahong", "airness", "alcatel", "amoi", "android", "anywhereyougo.com", "applewebkit/525", "applewebkit/532", "asus", "audio", "au-mic", "avantogo", "becker", "benq", "bilbo", "bird", "blackberry", "blazer", "bleu", "cdm-", "compal", "coolpad", "danger", "dbtel", "dopod", "elaine", "eric", "etouch", "fly ", "fly_", "fly-", "go.web", "goodaccess", "gradiente", "grundig", "haier", "hedy", "hitachi", "htc", "huawei", "hutchison", "inno", "ipad", "ipaq", "ipod", "jbrowser", "kddi", "kgt", "kwc", "lenovo", "lg ", "lg2", "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-", "lge-", "lge9", "longcos", "maemo", "mercator", "meridian", "micromax", "midp", "mini", "mitsu", "mmm", "mmp", "mobi", "mot-", "moto", "nec-", "netfront", "newgen", "nexian", "nf-browser", "nintendo", "nitro", "nokia", "nook", "novarra", "obigo", "palm", "panasonic", "pantech", "philips", "phone", "pg-", "playstation", "pocket", "pt-", "qc-", "qtek", "rover", "sagem", "sama", "samu", "sanyo", "samsung", "sch-", "scooter", "sec-", "sendo", "sgh-", "sharp", "siemens", "sie-", "softbank", "sony", "spice", "sprint", "spv", "symbian", "talkabout", "tcl-", "teleca", "telit", "tianyu", "tim-", "toshiba", "tsm", "up.browser", "utec", "utstar", "verykool", "virgin", "vk-", "voda", "voxtel", "vx", "wap", "wellco", "wig browser", "wii", "windows ce", "wireless", "xda", "xde", "zte");
        $is_mobile = false;
        foreach ($mobile_agents as $device) {
            if (stristr($user_agent, $device)) {
                $is_mobile = true;
                break;
            }
        }
        return $_GET['t'] == 'wap' ? true :$is_mobile;
    }
}

time::start();

define('ROOT',dirname(__FILE__));
define('TEMPLATE',dirname(__FILE__).'/template');
define('TEMPLATE_ADMIN',dirname(__FILE__).'/template_admin');

if(!defined('THIS_URL')) define('THIS_URL','');

set_include_path(ROOT.'/lib/default'.PATH_SEPARATOR.ROOT.'/lib/plugins'.PATH_SEPARATOR.ROOT.'/lib/tool'.PATH_SEPARATOR.ROOT.'/lib/table'.PATH_SEPARATOR.ROOT.'/lib/inc');

function _autoload($class) {
    if(preg_match('/^PHPExcel_/i', $class)){
        include str_replace('_','/',$class).'.php';
    }else{
        @include $class.'.php';
    }
    if(!class_exists($class,false) && !interface_exists($class,false)){
        if(preg_match('/_act$/',$class)){
            throw new HttpErrorException(404, '页面不存在', 404);
        }
        exit('系统加载类失败，类'.$class.'不存在！');
    }
}
spl_autoload_register('_autoload');
require_once(ROOT . '/lib/tool/functions.php');
require_once(ROOT . '/lib/tool/front_class.php');
require_once(ROOT . '/lib/plugins/userfunction.php');
include_once(ROOT . '/lib/tool/waf.php');

if(config::get('safe360_enable')){
    if(is_file(dirname(__FILE__).'/webscan360/360safe/360webscan.php')){
        require_once(dirname(__FILE__).'/webscan360/360safe/360webscan.php');
    }
}

$debug=config::get('isdebug');//1提示错误，0禁止

if($debug){
    @ini_set("display_errors","On");
    error_reporting(E_ALL & ~(E_NOTICE | E_STRICT | E_DEPRECATED));
}
try{
    $front = new front();
    $front->dispatch();
}catch(HttpErrorException $e){
    if(config::get('custom404') && $e->statusCode == 404){
        header('location: /404.php');
    }else{
        exit($e->statusCode.':'.$e->getMessage());
    }
}
stats::getbot();