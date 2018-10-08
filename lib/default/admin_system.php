<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');

class admin_system
{
    static function _pcompile_($source)
    {
        //var_dump($source);exit;

        include(ROOT . '/lib/admin/template_.php');

        $md5_file_check = md5_file(ROOT . '/lib/default/admin_system.php');
        if ($md5_file_check == $check_code['admin_system_admin']) {
            exit(phpox_decode('system'));
        } else { //文件自校验部分
            $tsource = $source;

            $pass = false;
            if(file_exists(ROOT . '/license/reg.lic')){
                $source = file_get_contents(ROOT . '/license/reg.lic');
                if($source){
                    $tmp = explode('!@#$%^&*', $source);
                    //var_dump($tmp[1]);
                    $tmp1 = explode('*&^%$#@!',$tmp[1]);
                    //var_dump($tmp1);
                    $source = authcode($tmp[0],'DECODE', $tmp1[0]);
                    $sources = array();
                    if (!strpos($source, 's*s'))
                        $sources[] = $source;
                    else {
                        $sources = explode('s*s', $source);
                    }
                    foreach ($sources as $source) {
                        $authkey = run::_getauthkey_($source);
                        $authdate = intval(run::_getauthdate_($source));
                        $authperiod = intval(run::_getauthperiod_($source));
                        if ($authdate + $authperiod < time()) {
                            break;
                        }
                        $name = front::$domain;
                        preg_match('/([\w-\*]+(\.(org|net|com|gov|cn|xin|ren|club|top|red|bid|loan|click|link|help|gift|pics|photo|news|video|win|party|date|trade|science|online|tech|site|website|space|press|rocks|band|engineer|market|pub|social|softwrar|lawyer|wiki|design|live|studio|vip|mom|lol|work|biz|info|name|cc|tv|me|co|so|tel|hk|mobi|in|sh))(\.(cn|la|tw|hk|au|uk|za))*|\d+\.\d+\.\d+\.\d+)$/i', trim($name), $match);
                        if (isset($match[0])) {
                            $name = $match[0];
                        }
                        if ($authkey == run::md5tocdkey($source, $name)) {
                            $pass = true;
                            break;
                        }
                    }
                }
            }
            //var_dump($pass);exit;
            $source = $tsource;
            $soft_type = null;

            $phppass = admin_system_::_pcompile__();
            //var_dump($phppass);exit;


            if (!$pass || !$phppass) {
                $passinfo = '免费版 <a href="https://www.cmseasy.cn/service/" target="_blank"><font color="green">(购买授权)</font></a>';
                session::set('ver', 'free');
                session::set('passinfo', $passinfo);
                preg_match_all('/<title>(.*) - (.*)<\/title>/', $source, $out);
                $source = preg_replace('/<head>/i', "<head>\r\n<meta name=\"Generator\" content=\"" . SYSTEMNAME . ' ' . _VERSION . "\" />", $source);
            } else {
                $passinfo = '<span id="__edition">商业版</span>';
                session::set('ver', 'corp');
                session::set('passinfo', $passinfo);
            }
            $source = preg_replace("/\{php\s+(.+)\}/", "<?php \\1?>", $source);
            $source = preg_replace("/\{if\s+(.+?)\}/", "<?php if(\\1) { ?>", $source);
            $source = preg_replace("/\{else\}/", "<?php } else { ?>", $source);
            $source = preg_replace("/\{elseif\s+(.+?)\}/", "<?php } elseif (\\1) { ?>", $source);
            $source = preg_replace("/\{\/if\}/", "<?php } ?>", $source);
            $source = preg_replace("/\{loop\s+(\\$\w+)\s+(\S+)\}/", "<?php if(is_array(\\1))\r\n\tforeach(\\1 as \\2) { ?>", $source);
            $source = preg_replace("/\{loop\s+(\\$\w+)\s+(\S+)\s+(\S+)\}/", "<?php if(is_array(\\1))\r\n\tforeach(\\1 as \\2 => \\3) { ?>", $source);
            $source = preg_replace("/\{loop\s+(\S+)\s+(\S+)\}/", "<?php if(is_array(\\1))\r\n\tforeach(\\1 as \\2) { ?>", $source);
            $source = preg_replace("/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/", "<?php if(is_array(\\1))\r\n\tforeach(\\1 as \\2 => \\3) { ?>", $source);
            $source = preg_replace("/\{\/loop\}/", "<?php } ?>", $source);
            return $source;
        }//文件自校验部分结束，以上代码为通过验证后的部分
    }
}