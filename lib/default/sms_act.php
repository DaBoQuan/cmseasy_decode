<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class sms_act  extends act {

    private function sendmail($smtpemailto,$title,$mailbody) {
        if(!$fp = @fsockopen(config::get('smtp_host'),25,$errno,$errstr,30)) {
            $charset='utf-8';
            $headers = "From:".config::get('smtp_user_add')."\r\n";
            $headers .= 'Content-type: text/html; charset='.$charset."\r\n";
            $email_subject = '=?'.$charset.'?B?'.base64_encode(str_replace("\r",'',str_replace("\n",'',$title))).'?=';
            @mail($smtpemailto,$email_subject,$mailbody,$headers);
        }else {
            include_once(ROOT.'/lib/plugins/smtp.php');
            $mailsubject = mb_convert_encoding($title,'GB2312','UTF-8');
            $mailtype = "HTML";
            $smtp = new include_smtp(config::get('smtp_mail_host'),config::get('smtp_mail_port'),config::get('smtp_mail_auth'),config::get('smtp_mail_username'),config::get('smtp_mail_password'));
            $smtp->debug = false;
            $smtp->sendmail($smtpemailto,config::get('smtp_user_add'),$mailsubject,$mailbody,$mailtype);
        }
    }
    
    function consult_action() {
		if(isset(front::$post['submit'])) {
			if(front::post('content') == ''){
				echo '<script>alert("' .lang('please_fill_in_the_content_you_want_to_consult') . '");history.go(-1);</script>';
                return false;
			}
			if(front::post('u_mobile') == ''){
				echo '<script>alert("' .lang('please_fill_in_your_phone_number') . '");history.go(-1);</script>';
                return false;
			}
			$user='';
        	if(cookie::get('login_username') &&cookie::get('login_password')) {
            	$user=new user();
            	$user=$user->getrow(array('username'=>cookie::get('login_username')));
        	}
			if($user['username'] == ''){
				$user['username'] = lang('tourist');
			}
            $smtpemailto = config::get('email');

            $title = lang('tourist').front::ip().lang('in').date('Y-m-d H:i:s').lang('message_success');
            $code = $user['username'].'('.front::post('u_mobile').')在'.date('Y-m-d H:i:s').lang('message').front::post('content');
            if($smtpemailto) {
                $this->sendmail($smtpemailto,$title,$code);
            }
        	if(config::get('sms_on') && config::get('sms_consult_admin_on') && $mobile = config::get('site_mobile')){
        		sendMsg($mobile,$user['username'].'('.front::post('u_mobile').')在'.date('Y-m-d H:i:s').'说'.front::post('content'));
				$f = fopen('data/message/'.date('Ymd').'.txt','ab');
				fwrite($f,$user['username']."\t".front::post('u_mobile')."\t".date('Y-m-d H:i:s')."\t".front::post('content')."\r\n");
				fclose($f);
				echo '<script>alert("' .lang('advisory_information_sent_successfully') . '");history.go(-1);</script>';
                return false;
        	}
			echo '<script>alert("' .lang('advisory_information_transmission_failure') . '");history.go(-1);</script>';
            return false;
		}
    }
    
    function end() {
        if(isset($this->end) &&!$this->end) return;
        if(front::$debug)
            $this->render('style/index.html');
        else
            $this->render();
    }
}
?>