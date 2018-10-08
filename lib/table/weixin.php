<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class weixin extends table {

    public function getAccessToken($wid){
        $row = $this->getrow($wid);
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$row['appid'].'&secret='.$row['appsecret'];
        $res = $this->getUrlJsonData($url);
        if($res['access_token']){
            return $res['access_token'];
        }else{
            exit($res['errcode'].':'.$res['errmsg']);
        }
    }

    public function getUrlJsonData($url){
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'timeout'=>60,
            )
        );

        $context = stream_context_create($opts);

        $html = file_get_contents($url, false, $context);

        return json_decode($html,true);
    }

    public function PostJsonData($url,$data){
        //$data = http_build_query($data);
        $opts = array (
            'http' => array (
                'method' => 'POST',
                //'header'=> "Content-type: application/x-www-form-urlencodedrn" . "Content-Length: " . strlen($data),
                'content' => $data,
                'timeout'=>60
            )
        );
        $context = stream_context_create($opts);
        $html = file_get_contents($url, false, $context);
        return json_decode($html,true);
    }
} 