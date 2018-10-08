<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class stats extends table {
    public $name='stats';
    static $me;
    public static function getInstance() {
        if (!self::$me) {
            $class=new stats();
            self::$me=$class;
        }
        return self::$me;
    }
    public static function getbot() {
        $ServerName = $_SERVER["SERVER_NAME"];
        $ServerPort = $_SERVER["SERVER_PORT"];
        $ScriptName = $_SERVER["SCRIPT_NAME"];
        $QueryString = $_SERVER["QUERY_STRING"];
        $serverip = $_SERVER["REMOTE_ADDR"];
        $GetLocationURL=self::geturl();
        $agent1 = $_SERVER["HTTP_USER_AGENT"];
        $agent=strtolower($agent1);
        $Bot="";
        if(strpos($agent,"googlebot")>-1) {
            $Bot = "Google";
        }
        if(strpos($agent,"mediapartners-google")>-1) {
            $Bot = "Google Adsense";
        }
        if(strpos($agent,"baiduspider")>-1) {
            $Bot = "Baidu";
        }
        if(strpos($agent,"sogou")>-1) {
            $Bot = "Sogou";
        }
        if(strpos($agent,"yahoo")>-1) {
            $Bot = "Yahoo!";
        }
        if(strpos($agent,"msn")>-1) {
            $Bot = "MSN";
        }
        if(strpos($agent,"soso")>-1) {
            $Bot = "Soso";
        }
        if(strpos($agent,"iaarchiver")>-1) {
            $Bot = "Alexa";
        }
        if(strpos($agent,"sohu")>-1) {
            $Bot = "Sohu";
        }
        if(strpos($agent,"sqworm")>-1) {
            $Bot = "AOL";
        }
        if(strpos($agent,"yodaobot")>-1) {
            $Bot = "Yodao";
        }
        if(strpos($agent,"iaskspider")>-1) {
            $Bot = "Iask";
        }
        if(strlen($Bot)>0 &&!front::get('admin_dir')) {
            $stats = self::getInstance();
            $insert = $stats->rec_insert(array('bot'=>$Bot,'url'=>$GetLocationURL,'ip'=>$serverip,'time'=>date('Y-m-d H:i:s')));
        }
    }
    public static function geturl() {
        if(!empty($_SERVER["REQUEST_URI"])) {
            $scrtName = htmlspecialchars($_SERVER["REQUEST_URI"],ENT_QUOTES);
            $nowurl = $scrtName;
        }else {
            $scrtName = $_SERVER["PHP_SELF"];
            if(empty($_SERVER["QUERY_STRING"])) {
                $nowurl = $scrtName;
            }else {
                $nowurl = $scrtName."?".htmlspecialchars($_SERVER["QUERY_STRING"],ENT_QUOTES);
            }
        }
        return (isset($_SERVER["HTTPS"])&&$_SERVER["HTTPS"] == "on")?'https://':'http://'.htmlspecialchars($_SERVER['HTTP_HOST'],ENT_QUOTES).$nowurl;
    }
}