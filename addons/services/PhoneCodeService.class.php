<?php

/**
 * 手机短信服务
 */
class PhoneCodeService extends Service {

    private $option = array();

    public function __construct() {
        $this->init();
    }

    /**
     * 加载phpmailer, 初始化默认参数
     */
    public function init() {
        include_once(SITE_PATH . '/addons/libs/SendSms.class.php');
        include_once(SITE_PATH . '/addons/models/PhoneCodeModel.class.php');
    }

    /**
     * [sendCode 发送手机确认码]
     * @param  [type] $phone [description]
     * @return [type]        [description]
     */
    public function sendCode($phone) {
        if (empty($phone)) {
            $data = array(
                'code' => 1,
                'message' => '手机格式不对'
            );
            return $data;
        }
        $info = model('PhoneCode')->getStatusByPhone($phone);
        $data = array(
            'code' => 0,
            'message' => 'ok'
        );
        if ($info && $info['valid'] == 0) {
            $data = array(
                'code' => 1,
                'message' => '60秒后才能再次发送'
            );
            return $data;
        }

        $code = SendSms::createCode();
        $message = '手机验证码为：' . $code . ', 5分钟内有效!【智测云】';

        $ret = SendSms::send($phone, $message);
        $log = $_SERVER['REMOTE_ADDR'] . "\t" . $phone . "\t" . $ret;
        log::write($log, LOG::INFO, 3, SITE_PATH . "/logs/phonecode.log");
        if ('ok' === $ret) {
            model('PhoneCode')->addRecord($phone, $code);
        } else {
            $data = array(
                'code' => 1,
                'message' => '发送失败'
            );
        }
        return $data;
    }

    public function checkSendCode($phone, $code) {
        if (empty($phone)) {
            $data = array(
                'code' => 1,
                'message' => '手机格式不对'
            );
            return $data;
        }
        $info = model('PhoneCode')->getStatusByPhone($phone, 3);
        $data = array(
            'code' => 0,
            'message' => 'ok'
        );
        if (empty($info)) {
            $data = array(
                'code' => 1,
                'message' => '请先点击发送验证码'
            );
        } elseif (($info['ctime'] < (time() - 300)) || ($info['valid'] == 1)) {
            $data = array(
                'code' => 1,
                'message' => '验证码已经失效'
            );
        } elseif ($info['code'] != $code) {
            $data = array(
                'code' => 1,
                'message' => '验证码错误'
            );
        }
        return $data;
    }

    public function setRecordValid($phone) {
        return model('PhoneCode')->setRecordValid($phone);
    }

    public function run() {
        
    }

    public function _start() {
        
    }

    public function _stop() {
        
    }

    public function _install() {
        
    }

    public function _uninstall() {
        
    }

}

?>