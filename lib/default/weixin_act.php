<?php 

class weixin_act extends act
{

    public $db_weixin = null;

    function init(){
        $this->db_weixin = new weixin();
    }

    public function interface_action()
    {
        $wid = front::$get['wid'];
        $where = array('oldid'=>$wid);
        $row = $this->db_weixin->getrow($where);
        //var_dump($row);
        if($this->checkSignature($row['token'])){
            $this->db_weixin->rec_update(array('checksuc'=>'2'),$where);
            echo front::$get['echostr'];
        }else{
            $this->db_weixin->rec_update(array('checksuc'=>'1'),$where);
            return;
        }
        //file_put_contents('logs.txt',var_export($GLOBALS["HTTP_RAW_POST_DATA"],true));
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if(!empty($postStr)){
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            //file_put_contents('logs.txt',var_export($postObj,true));

            if($postObj->MsgType == 'event' && $postObj->Event == 'CLICK'){
                $id = str_replace("KEY","",$postObj->EventKey);
                $this->getReplyMsg($id,$postObj->FromUserName,$postObj->ToUserName);
                return;
            }
            if($postObj->MsgType == 'event' && $postObj->Event == 'subscribe'){
                $this->AutoReplyMsg($postObj->FromUserName,$postObj->ToUserName);
                return;
            }
            if($postObj->MsgType == 'text' && $postObj->Content != ''){
                if($this->doTextMsg($postObj->FromUserName,$postObj->ToUserName,$postObj->Content)){
                    return;
                }
            }
            $this->DefaultMsg($postObj->FromUserName,$postObj->ToUserName);
        }
    }

    private function doTextMsg($toUserName,$fromUserName,$content){
        $wid = front::$get['wid'];
        $where = array('oldid'=>$wid);
        $row = $this->db_weixin->getrow($where);
        $where = "wid='{$row['id']}' AND msgtype=3";
        $weixinreply = new weixinreply();
        $row = $weixinreply->getrows($where,'','id asc');
        $ismatch = false;
        if(is_array($row) && !empty($row)){
            $isall = false;
            foreach($row as $arr){
                $tmparr = explode('|',$arr['word']);
                foreach($tmparr as $str){
                    if($str && $str == $content){
                        $this->doReply($arr,$toUserName,$fromUserName);
                        $isall = true;
                        $ismatch = true;
                        break;
                    }
                }
            }
            if(!$isall){
                foreach($row as $arr) {
                    $tmparr = explode('|', $arr['keyword']);
                    foreach ($tmparr as $str) {
                        if ($str && preg_match("/$str/is", $content)) {
                            $this->doReply($arr, $toUserName, $fromUserName);
                            $ismatch = true;
                            break;
                        }
                    }
                }
            }
        }
        return $ismatch;
    }

    private function DefaultMsg($toUserName,$fromUserName){
        $wid = front::$get['wid'];
        $where = array('oldid'=>$wid);
        $row = $this->db_weixin->getrow($where);
        $weixinreply = new weixinreply();
        $reply = $weixinreply->getrow(array('wid'=>$row['id'],'msgtype'=>2));
        $this->doReply($reply,$toUserName,$fromUserName);
    }

    private function AutoReplyMsg($toUserName,$fromUserName){
        $wid = front::$get['wid'];
        $where = array('oldid'=>$wid);
        $row = $this->db_weixin->getrow($where);
        $weixinreply = new weixinreply();
        $reply = $weixinreply->getrow(array('wid'=>$row['id'],'msgtype'=>1));
        $this->doReply($reply,$toUserName,$fromUserName);
    }

    private function getReplyMsg($id,$toUserName,$fromUserName){
        $weixinmenu = new weixinmenu();
        $menu = $weixinmenu->getrow($id);
        $this->doReply($menu,$toUserName,$fromUserName);
    }


    private function doReply($arr,$toUserName,$fromUserName){
        switch($arr['typeid']){
            case 3:
                $resultStr = $this->getTxtMsg($arr,$toUserName,$fromUserName);
                break;
            case 4:
                $resultStr = $this->getTuwenMsg($arr,$toUserName,$fromUserName);
                break;
            case 5:
                $resultStr = $this->getSiteNews($arr,$toUserName,$fromUserName);
                break;
            default:
                echo "";
                break;
        }
        echo $resultStr;
    }

    private function getTxtMsg($arr,$toUserName,$fromUserName){
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    </xml>";
        $resultStr = sprintf($textTpl, $toUserName,$fromUserName , time(), $arr['txt']);
        return $resultStr;
    }

    private function getSiteNews($menu,$toUsername,$fromUsername){
        $article = new archive();
        $catid = intval($menu['catid']);
        $category = new category();
        $where = "state='1' AND checked='1' AND thumb !=''";
        if($category->hasson($catid)){
            $catids = $category->sons($catid);
            $where .= " AND catid in(".$catid.','.implode(',',$catids).')';
        }else{
            $where .= " AND catid = '$catid'";
        }
        //file_put_contents('logs1.txt',var_export($where,true));
        $articles = $article->getrows($where,$menu['num'],'aid desc','aid,title,thumb,introduce');
        $i = 1;
        $tpl = "";
        if(config::get('base_url') != '/'){

        }
        if(is_array($articles) && !empty($articles)) {
            $tpl = "<xml>
                <ToUserName><![CDATA[$toUsername]]></ToUserName>
                <FromUserName><![CDATA[$fromUsername]]></FromUserName>
                <CreateTime>" . time() . "</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>" . count($articles) . "</ArticleCount>
                <Articles>";
            foreach ($articles as $tmp) {
                $tpl .= "<item>
                <Title><![CDATA[" . $tmp['title'] . "]]></Title>
                <Description><![CDATA[" .$tmp['introduce']. "]]></Description>
                <PicUrl><![CDATA[http://" . $_SERVER['HTTP_HOST'].$tmp['thumb'] . "]]></PicUrl>
                <Url><![CDATA[http://" . $_SERVER['HTTP_HOST'].archive::url($tmp) . "]]></Url>
                </item>";
                $i++;
            }
            $tpl .= "</Articles></xml>";
        }
        return $tpl;
    }

    private function getTuwenMsg($menu,$toUsername,$fromUsername){
        $tmparr = explode('|',$menu['imgtext']);
        $i = 1;
        $tpl = "";
        if(is_array($tmparr) && !empty($tmparr)) {
            $tpl = "<xml>
                <ToUserName><![CDATA[$toUsername]]></ToUserName>
                <FromUserName><![CDATA[$fromUsername]]></FromUserName>
                <CreateTime>" . time() . "</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>" . count($tmparr) . "</ArticleCount>
                <Articles>";
            foreach ($tmparr as $str) {
                $tmp = explode('*', $str);
                if($i == 1){
                    $intro = $menu['intro'];
                }else{
                    $intro = $tmp[0];
                }
                $tpl .= "<item>
                <Title><![CDATA[" . $tmp[0] . "]]></Title>
                <Description><![CDATA[" .$intro. "]]></Description>
                <PicUrl><![CDATA[" . $tmp[2] . "]]></PicUrl>
                <Url><![CDATA[" . $tmp[1] . "]]></Url>
                </item>";
                $i++;
            }
            $tpl .= "</Articles></xml>";
        }
        //file_put_contents('logs.txt',var_export($tpl,true));
        return $tpl;
    }

    private function checkSignature($token)
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

} 