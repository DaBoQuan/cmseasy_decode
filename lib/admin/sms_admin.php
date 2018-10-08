<?php 

if (!defined('ROOT')) exit('Can\'t Access !');
class sms_admin  extends admin {
    function manage_action() {
        if(!config::get('sms_username') || !config::get('sms_password')){
            echo '<script>alert("您需要先设置用户名和密码才能使用短信管理功能！");window.location.href="'.url('config/system/set/sms').'";</script>';
            exit;
        }
        include_once("phprpc/phprpc_client.php");
        $client = new PHPRPC_Client();
        $client->setProxy(NULL);
        $client->useService('http://pay.cmseasy.cn/sms.php');
        $client->setKeyLength(128);
        $client->setEncryptMode(3);
        $info = $client->getInfo(config::get('sms_username'),md5(config::get('sms_password')));
        //var_dump($info);
        //var_dump(config::get('sms_username'));
        //var_dump(config::get('sms_password'));
        $info[0] = intval($info[0]);
        $info[1] = intval($info[1]);
        $this->view->info = $info;
        if (front::post('submit')) {
            if (front::post('act') == 'test') {
                $rs = sendMsg(front::post('mobile'),'test');
                if($rs == '0'){
                    front::flash('发送成功');
                }else{
                    front::flash('发送失败,请检查用户名、密码或剩余条数'.$rs);
                }
				front::redirect(front::$from);
            }
        }else{
			if($info[0] < 50) front::flash('你的剩余短信不足50条,请及时充值');
		}
		$this->render('sms/manage.php');
        exit;
    }
    
    function end() {
        $this->render('index.php');
    }
}