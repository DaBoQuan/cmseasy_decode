<?php 

if (!defined('ROOT')) exit('Can\'t Access !');

class ologin extends table {
    public $name='p_ologin';
    static $me;
    public static function getInstance() {
        if (!self::$me) {
            $class=new ologin();
            self::$me=$class;
        }
        return self::$me;
    }
    public static function formatarray($cfg) {
        if (is_string($cfg) &&($arr = unserialize($cfg)) !== false) {
            $config = array();
            foreach ($arr as $key =>$val) {
                $config[$val['name']] = $val['value'];
            }
            return $config;
        }else {
            return false;
        }
    }
    public static function en_de_code($string,$operation='DECODE',$key='@LFK24s224%@safS3s%1f%') {
        $result = '';
        if ($operation == 'ENCODE') {
            for ($i = 0;$i <strlen($string);$i++) {
                $char = substr($string,$i,1);
                $keychar = substr($key,($i %strlen($key)) -1,1);
                $char = chr(ord($char) +ord($keychar));
                $result.=$char;
            }
            $result = base64_encode($result);
            $result = str_replace(array('+','/','='),array('-','_',''),$result);
        }elseif ($operation == 'DECODE') {
            $data = str_replace(array('-','_'),array('+','/'),$string);
            $mod4 = strlen($data) %4;
            if ($mod4) {
                $data .= substr('====',$mod4);
            }
            $string = base64_decode($data);
            for ($i = 0;$i <strlen($string);$i++) {
                $char = substr($string,$i,1);
                $keychar = substr($key,($i %strlen($key)) -1,1);
                $char = chr(ord($char) -ord($keychar));
                $result.=$char;
            }
        }
        return $result;
    }
    public static function url($code) {
        define('SERVER_HTTP',$_SERVER['SERVER_PORT'] == '443'?'https://': 'http://');
        define('SITE_URL',SERVER_HTTP.$_SERVER['HTTP_HOST']);
        return $link = SITE_URL.config::get('base_url')."/index.php?case=user&act=respond&ologin_code=".$code;
    }
    public static function get_payment($code) {
        $where=array();
        $where['pay_code']=$code;
        $where['enabled']=1;
        $payment1=pay::getInstance()->getrows($where);
        $payment = $payment1[0];
        if ($payment) {
            $config_list = unserialize($payment['pay_config']);
            foreach ($config_list AS $config) {
                $payment[$config['name']] = $config['value'];
            }
        }
        return $payment;
    }
    public static function check_money($id,$money) {
        $where=array();
        $where['id']=$id;
        $orders=orders::getInstance()->getrow($where);
        $archive=archive::getInstance()->getrow($orders['aid']);
        $where=array();
        $where['pay_code']=$_GET['code'];
        $pay=pay::getInstance()->getrows($where);
        $logisticsid = substr($_GET['subject'],15,1);
        $where=array();
        $where['id'] = $logisticsid;
        $logistics=logistics::getInstance()->getrows($where);
        if($logistics[0]['cashondelivery']) {
            $logistics[0]['price'] = 0.00;
        }else {
            if($logistics[0]['insure']) {
                $logistics[0]['price'] = $logistics[0]['price'] +($archive['attr2'] * $orders['pnums'])*($logistics[0]['insureproportion']/100);
            }
        }
        $pay[0]['pay_fee'] = $pay[0]['pay_fee']/100;
        $total = $archive['attr2'] * $orders['pnums'] +$logistics[0]['price'] +($archive['attr2'] * $orders['pnums'] * $pay[0]['pay_fee']);
        $amount = $total;
        if($money == $amount) {
            return true;
        }else {
            return false;
        }
    }
    public static function changeorders($id,$orderlog) {
        $where=array();
        $where['id']=$id;
        $where['status']=4;
        $where['orderlog']=serialize($orderlog);
        $update=orders::getInstance()->rec_update($where,$id);
        if($update<1) {
            exit('改变订单状态出错，请联系管理员');
        }
    }
}