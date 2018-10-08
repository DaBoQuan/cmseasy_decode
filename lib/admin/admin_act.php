<?php 

if (!defined('ROOT'))
    exit('Can\'t Access !');

class admin_act extends act
{

    function login_action()
    {
        cookie::del('passinfo');
        //$_SESSION['test'] = 'test';
        $this->view->loginfalse = cookie::get('loginfalse' . md5($_SERVER['REQUEST_URI']));
        if (front::post('submit')) {
            //var_dump($_SESSION);
            //if ($this->view->loginfalse) {
            if(config::get('verifycode') == 1) {
                if (!session::get('verify') || front::post('verify') <> session::get('verify')) {
                    front::flash(lang('验证码错误！'));
                    $this->render();
                    exit;
                }
            }else if(config::get('verifycode') == 2){
                if (!verify::checkGee()) {
                    front::flash(lang('验证码错误！'));
                    $this->render();
                    exit;
                }
            }
            session::set('verify', null);
            //}
            if (config::get('mobilechk_enable') && config::get('mobilechk_admin')) {
                $mobilenum = front::$post['mobilenum'];
                $smsCode = new SmsCode();
                if (!$smsCode->chkcode($mobilenum)) {
                    front::flash(lang('手机验证码错误！') . "<a href=''>" . lang('返回前一页') . "</a>");
                    $this->render();
                    exit;
                }
            }

            $user = new user();
            $user = $user->getrow(array('username' => front::post('username'), 'password' => md5(front::post('password'))));
            if (is_array($user)) {
                $roles = usergroup::getRoles($user['groupid']);
                session::set('roles', null);
                if ($roles) {
                    session::set('roles', $roles);
                } else {
                    front::alert(lang('without_permission'));
                }
                if (!front::post('expire')) {
                    cookie::set('login_username', $user['username']);
                    cookie::set('login_password', front::cookie_encode($user['password']));
                } else {
                    $expire = time() + front::post('expire');
                    cookie::set('login_username', $user['username'], $expire);
                    cookie::set('login_password', front::cookie_encode($user['password']), $expire);
                }

                session::set('username', $user['username']);
                event::log(lang('后台登录'), lang('成功'));
                front::$user = $user;
				front::redirect(url('index/index',true));
            } elseif (!is_array(front::$user) || !isset(front::$isadmin)) {
                $loginfalsetime = intval(config::get('loginfalsetime'));
                if (!$loginfalsetime) $loginfalsetime = 3600;
                cookie::set('loginfalse' . md5($_SERVER['REQUEST_URI']), (int)cookie::get('loginfalse' . md5($_SERVER['REQUEST_URI'])) + 1, time() + $loginfalsetime);
                event::log('loginfalse', lang('failure') . ' user=' . front::post('username'));
                alerterror(lang('password_error'));
                //front::flash('密码错误或不存在该管理员！');
                //front::refresh(url('admin/login',true));
            }
        }

        $this->render();
    }

    function remotelogin_action()
    {
        cookie::del('passinfo');
        $this->view->loginfalse = cookie::get('loginfalse' . md5($_SERVER['REQUEST_URI']));
        if (front::$args) {
            $user = new user();
            $args = xxtea_decrypt(base64_decode(front::$args), config::get('cookie_password'));
            if (inject_check($args)) {
                exit('参数非法');
            }
            $user = $user->getrow(unserialize($args));
            if (is_array($user)) {
                if ($user['groupid'] == '2')
                    front::$isadmin = true;
                cookie::set('login_username', $user['username']);
                cookie::set('login_password', front::cookie_encode($user['password']));
                session::set('username', $user['username']);
                front::$user = $user;
            } elseif (!is_array(front::$user) || !isset(front::$isadmin)) {
                cookie::set('loginfalse' . md5($_SERVER['REQUEST_URI']), (int)cookie::get('loginfalse' . md5($_SERVER['REQUEST_URI'])) + 1, time() + 3600);
                event::log('loginfalse', lang('失败') . ' user=' . $user['username']);
                front::flash(lang('密码错误或不存在该管理员！'));
                front::refresh(url('admin/login', true));
            }
        }
        $this->render();
    }

    function loginfalsemaxtimes()
    {
        if (cookie::get('loginfalse' . md5($_SERVER['REQUEST_URI'])) > 10 || event::loginfalsemaxtimes()) {
            front::flash(lang('帐号输入错误次数太多！请1小时后再登录！'));
            return true;
        }
    }
}