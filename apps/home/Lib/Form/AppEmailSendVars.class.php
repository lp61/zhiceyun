<?php
/**
 *  邮件发送相关信息配置
 */
class AppEmailSendVars {

    const IS_WAITING      = 0;  // 等待发送
    const SUCC_SENDING    = 1;  // 发送成功
    const FAIL_SENDING    = -1; // 发送失败
    const NO_AVAIL_RECORD = -2; // 发送记录不完整
    
    const REGISTER_ACTIVE   = 1;
    const PASSWORD_RECOVERY = 2;
    const REGISTER_INVITE   = 3;
    const NORMAL_EMAIL      = 4;
    const TIPS_EMAIL        = 5;

    const SEND_AT_ONCE_NAME  = '实时发送';
    const SEND_BY_QUEUE_NAME = '队列发送';
    const SEND_BOTH_OR_NAME  = '实时发送/队列发送';

    const SEND_AT_ONCE  = 1;
    const SEND_BY_QUEUE = 2;
    const SEND_BOTH_OR  = 3;

    public static $APP_EMAIL_SEND_CONFIG = array(
        self::REGISTER_ACTIVE => array(
            'id'             => self::REGISTER_ACTIVE,
            'name'           => '注册邮件激活',
            'send_type'      => self::SEND_AT_ONCE,
            'send_type_name' => self::SEND_AT_ONCE_NAME,
        ),
        self::PASSWORD_RECOVERY => array(
            'id'             => self::PASSWORD_RECOVERY,
            'name'           => '找回密码邮件',
            'send_type'      => self::SEND_AT_ONCE,
            'send_type_name' => self::SEND_AT_ONCE_NAME,
        ),
        self::REGISTER_INVITE => array(
            'id'             => self::REGISTER_INVITE,
            'name'           => '邮件邀请注册',
            'send_type'      => self::SEND_BY_QUEUE,
            'send_type_name' => self::SEND_BY_QUEUE_NAME,
        ),
        self::NORMAL_EMAIL => array(
            'id'             => self::NORMAL_EMAIL,
            'name'           => '实时性邮件',
            'send_type'      => self::SEND_AT_ONCE,
            'send_type_name' => self::SEND_AT_ONCE_NAME,
        ),
        self::TIPS_EMAIL => array(
            'id'             => self::TIPS_EMAIL,
            'name'           => '计划性邮件',
            'send_type'      => self::SEND_BY_QUEUE,
            'send_type_name' => self::SEND_BY_QUEUE_NAME
        ),
    );

    /**
     * [getAppEmailSendConfig 发送邮件配置]
     * @return [type]    [description]
     */
    public static function getAppEmailSendConfig() {
        $appEmailSendConfig = self::$APP_EMAIL_SEND_CONFIG;
        return $appEmailSendConfig;
    }
}
