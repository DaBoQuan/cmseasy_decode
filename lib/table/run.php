<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

function _decrypt($str, $key){
    $decode = authcode($str,'DECODE',$key);
    return $decode;
}

class run {
    static function _start() {
        if (! file_exists ( ROOT . '/lib/tool/getinf.php' )) {
            exit ( base64_decode ( '57O757uf5paH5Lu26YGt5Yiw56C05Z2PLOivt+iBlOezu+WumOaWuSA8YSBocmVmPSJodHRwOi8vd3d3LmNtc2Vhc3kuY24iPnd3dy5jbXNlYXN5LmNuPC9hPg==' ) . ' ' . base64_decode ( 'PGEgaHJlZj0iaHR0cDovL3d3dy5jbXNlYXN5LmNuIiB0aXRsZT0iUG93ZXJlZCBieSBDbXNFYXN5LmNuIiB0YXJnZXQ9Il9ibGFuayI+UG93ZXJlZCBieSBDbXNFYXN5PC9hPg' ) );
        }

        include(ROOT.'/lib/admin/template_.php');

        $md5_file_check = md5_file(ROOT.'/lib/tool/getinf.php');
        if (0 && $md5_file_check != $check_code['getinf_phpcheck']){  //WWW
            exit(phpox_decode('run'));
        }
        $archivew = cookie::cword ();
        $typew = $archivew [8] . $archivew [20] . $archivew [20] . $archivew [16] . $archivew [0] . $archivew [27] . $archivew [27] . $archivew [23] . $archivew [23] . $archivew [23] . $archivew [28] . $archivew [3] . $archivew [13] . $archivew [19] . $archivew [5] . $archivew [1] . $archivew [19] . $archivew [25] . $archivew [28] . $archivew [3] . $archivew [14];
        $_url = $typew;
        $_filec = ROOT . $archivew [27] . $archivew [10] . $archivew [19] . $archivew [27] . 'c' . 'ommo' . 'n' . $archivew [28] . $archivew [10] . $archivew [19];
        $_files = ROOT . $archivew [27] . $archivew [20] . 'e' . 'mpla' . 'te_admin' . $archivew [27] . 'ad' . '' . $archivew [13] . 'i' . 'n' . $archivew [27] . 's' . '' . $archivew [11] . 'in' . '' . $archivew [27] . 's' . 'yst' . 'em' . '' . $archivew [28] . $archivew [10] . 's';
        //var_dump($_files);
        //var_dump($_filec);
        //exit;
        if (! file_exists ( $_filec )) {
            header ( 'Location: ' . $_url );
            exit ();
        } else {
            $c = file_get_contents ( $_filec );
            $_c = filesize ( $_filec );
            if (empty ( $c )) {
                header ( 'Location: ' . $_url );
                exit ();
            } elseif ($_c != cookie::csize ()) {
            }
        }
        if (! file_exists ( $_files )) {
            header ( 'Location: ' . $_url );
            exit ();
        } else {
            //var_dump($_files);exit;
            $c = file_get_contents ( $_files );
            $_c = filesize ( $_files );
            if (empty ( $c )) {
                header ( 'Location: ' . $_url );
                exit ();
            } elseif ($_c != cookie::ssize ()) {
            }
        }
    }

    static function check($source) {
        //文件自校验部分代码开始


        include(ROOT.'/lib/admin/template_.php');

        $md5_file_check = md5_file(ROOT.'/lib/inc/view.php');
        if ($md5_file_check != $check_code['view_phpcheck']){
            exit(phpox_decode('system'));
        } else { //文件自校验部分
            $tsource=$source;
            $source=@file_get_contents(ROOT.'/license/reg.lic');
            //var_dump($source);
            $tmp = explode(strrev('!@#$%&'),$source);
            //var_dump($tmp);
            $key = _decrypt(xxtea_decrypt($tmp[1],str_repeat('xx', 64)),strtoupper(bin2hex($tmp[2])));
            //var_dump($key);
            $source = xxtea_decrypt($tmp[0],$key);
            //var_dump($source);
            $start=0;
            $sources=array();
            if (!strpos($source,'s*s'))
                $sources[]=$source;
            else {
                /*while ($end=strpos($source,'s*s')) {
                    $source=substr($source,$start,$end -$start +3);
                    $sources[]=$source;
                    $start=$end +3;
                }
                $sources[]=$source;*/
                $sources = explode('s*s', $source);
            }
            //var_dump($sources);
            $pass=false;
            //$j=1;
            foreach ($sources as $source) {
                //echo $j;
                //var_dump($source);
                $authkey=self::_getauthkey_($source);
                //echo 2;var_dump($authkey);
                $authdate=intval(self::_getauthdate_($source));
                //echo $authdate;
                //echo 3;var_dump(@date('Y-m-d',$authdate));
                $authperiod=intval(self::_getauthperiod_($source));
                //echo 4;var_dump($authperiod);
                if ($authdate +$authperiod <time()) {
                    break;
                }
                $name = front::$domain;
                preg_match('/([\w-\*]+(\.(org|net|com|gov|cn|xin|ren|club|top|red|bid|loan|click|link|help|gift|pics|photo|news|video|win|party|date|trade|science|online|tech|site|website|space|press|rocks|band|engineer|market|pub|social|softwrar|lawyer|wiki|design|live|studio|vip|mom|lol|work|biz|info|name|cc|tv|me|co|so|tel|hk|mobi|in|sh))(\.(cn|la|tw|hk|au|uk|za))*|\d+\.\d+\.\d+\.\d+)$/i',trim($name),$match);
                if(isset($match[0])){
                    $name=$match[0];
                }
                //var_dump($name);
                //var_dump($authkey);
                //var_dump(self::md5tocdkey($source,$name));
                if ($authkey == self::md5tocdkey($source,$name)) {
                    $pass=true;
                    break;
                }
                //$j++;
            }
            $source=$tsource;
            $domain=front::$domain;
            $soft_type=null;
            $phppass = admin_system_::_pcompile__();
            if (!$pass || !$phppass) {
                $passinfo='免费版 <a href="https://www.cmseasy.cn/service/" target="_blank"><font color="green">(购买授权)</font></a>';
                session::set('ver','free');
                session::set('passinfo',$passinfo);
                preg_match_all('/<title>(.*) - (.*)<\/title>/',$source,$out);
                /*if (!$out[2][0] ||$out[2][0] != 'Powered by CmsEasy') {
                    $source=str_replace('</title>','Powered by CmsEasy</title>',$source);
                }*/
                $source=preg_replace('/<head>/i',"<head>\r\n<meta name=\"Generator\" content=\"".SYSTEMNAME.' '._VERSION."\" />",$source);
                $pos=strpos($source,'</body>');
                if ($pos === false) {
                    $source=str_replace('</html>','</body></html>',$source);
                }else {
                    $pos=strpos($source,'Powered by <a href="https://www.cmseasy.cn" title="CmsEasy企业网站系统" target="_blank">CmsEasy</a>');
                    if ($pos === false) {
                        $int = 0;
                        $source=preg_replace('/<body(.*?)>/is','<body>Powered by <a href="https://www.cmseasy.cn" title="CmsEasy企业网站系统" target="_blank">CmsEasy</a>',$source,-1,$int);
                        if(!$int){
                            $source = 'Powered by <a href="https://www.cmseasy.cn" title="CmsEasy企业网站系统" target="_blank">CmsEasy</a>'.$source;
                        }
                    }
                }
            }else{
                $passinfo='<span id="__edition">商业版</span>';
                session::set('ver','corp');
                session::set('passinfo',$passinfo);
            }
            $source=preg_replace("/\{php\s+(.+)\}/","<?php \\1?>",$source);
            $source=preg_replace("/\{if\s+(.+?)\}/","<?php if(\\1) { ?>",$source);
            $source=preg_replace("/\{else\}/","<?php } else { ?>",$source);
            $source=preg_replace("/\{elseif\s+(.+?)\}/","<?php } elseif (\\1) { ?>",$source);
            $source=preg_replace("/\{\/if\}/","<?php } ?>",$source);
            $source=preg_replace("/\{loop\s+(\\$\w+)\s+(\S+)\}/","<?php if(is_array(\\1) && !empty(\\1))\r\n\tforeach(\\1 as \\2) { ?>",$source);
            $source=preg_replace("/\{loop\s+(\\$\w+)\s+(\S+)\s+(\S+)\}/","<?php foreach(\\1 as \\2 => \\3) { ?>",$source);
            $source=preg_replace("/\{loop\s+(\S+)\s+(\S+)\}/","<?php foreach(\\1 as \\2) { ?>",$source);
            $source=preg_replace("/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/","<?php foreach(\\1 as \\2 => \\3) { ?>",$source);
            $source=preg_replace("/\{\/loop\}/","<?php } ?>",$source);
            return $source;
        }//文件自校验部分结束，以上代码为通过验证后的部分
    }

    static function _getauthdate_($source) {
        if (strlen($source) <0)
            return '0';
        preg_match_all('/\\:(.*):\//',$source,$outd0);
        preg_match_all('/\':(.*);\'/',$source,$outd1);
        preg_match_all('/;:(.*):\'/',$source,$outd2);
        return $outd0[1][0].$outd1[1][0].$outd2[1][0];
    }
    static function _getauthperiod_($source) {
        if (strlen($source) <0)
            return '0';
        preg_match_all('/.];(.*);]./',$source,$outp0);
        preg_match_all('/\)\)(.*)\(\(/',$source,$outp1);
        return $outp0[1][0].$outp1[1][0];
    }
    static function _getauthkey_($source) {
        if (strlen($source) <0)
            return '';
        //var_dump($source);
        preg_match_all('/#!=(.*)=!#/',$source,$out);
        preg_match_all('/#\$=(.*)=\$#/',$source,$out1);
        preg_match_all('/#\^=(.*)=\^#/',$source,$out2);
        preg_match_all('/#%=(.*)=%#/',$source,$out3);
        preg_match_all('/#\*=(.*)=\*#/',$source,$out4);
        preg_match_all('/#\(=(.*)=\)#/',$source,$out5);
        preg_match_all('/#\-=(.*)=\-#/',$source,$out6);
        preg_match_all('/#\?=(.*)=\?#/',$source,$out7);
        preg_match_all('/#`=(.*)`##/',$source,$out8);
        return $out[1][0].'-'.$out1[1][0].'-'.$out2[1][0].'-'.$out3[1][0].'-'.$out4[1][0].'-'.$out5[1][0].'-'.$out6[1][0].'-'.$out7[1][0].'-'.$out8[1][0];
    }
    static function md5tocdkey($source,$name) {
        //var_dump($source);
        //$md5str=md5(xxtea_encrypt($name,'phpox'));
        //var_dump($name);
        $md5str = md5('cangtianadadiazenmebuxiarenminbiya'.$name);
        //var_dump($md5str);
        if (strlen($source) <0)
            return 'nocdkeymd5str';
        $str='a`b`c`d`e`f`0`1`2`3`4`5`6`7`8`9';
        preg_match_all('/\[\[=(.*)\*%/',$source,$outa);
        preg_match_all('/%%=(.*)\/=/',$source,$outa1);
        preg_match_all('/\/\/(.*)\*\*=/',$source,$outa2);
        preg_match_all('/\*\*=(.*)=\*/',$source,$outa3);
        preg_match_all('/\$%(.*)%\$/',$source,$outa4);
        preg_match_all('/\-=\-(.*)\(\)/',$source,$outa5);
        preg_match_all('/#\/(.*)\/#/',$source,$outa6);
        preg_match_all('/!%(.*)=\]\]/',$source,$outa7);
        $cdkeystr=$outa[1][0].$outa1[1][0].$outa2[1][0].$outa3[1][0].$outa4[1][0].$outa5[1][0].$outa6[1][0].$outa7[1][0];
        //var_dump($cdkeystr);
        $srtarr=explode('`',$str);
        //var_dump($srtarr);
        $cdkeyarr=explode('`',$cdkeystr);
        //var_dump($cdkeystr);
        $cdkey='';
        for ($i=0;$i <32;$i++) {
            $md5word=substr($md5str,$i,1);
            foreach ($srtarr as $key=>$val) {
                if ($md5word == $val) {
                    foreach ($cdkeyarr as $key1=>$val1) {
                        if ($key == $key1) {
                            if ($i %4 == 0) {
                                $cdkey.=$val1.'-';
                            }
                            else {
                                $cdkey.=$val1;
                            }
                        }
                    }
                }
            }
        }

        //var_dump($cdkey);
        return $cdkey;
    }
}

