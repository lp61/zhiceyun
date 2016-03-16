<?php

/*
 * 发送短信
 * +---------------------------------------------------------- 
 * @param string $message 短信内容
 * @param string $phone 手机号 
 * +----------------------------------------------------------
 */
class SendSms {
    public static function send($phone, $message) {
        $uid = "100021";
        $pwd = md5("zc12344zc");
        $sms_content = urlencode(iconv("utf-8", "gb2312//IGNORE", $message));
        $sms_url = 'http://211.137.43.229:9885/c123/sendsms?uid=' . $uid . '&pwd=' . $pwd . '&mobile=' . $phone . '&content=' . $sms_content;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sms_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        $output = curl_exec($ch);
        switch ($output) {
            case 100:$error = 'ok';
                break;
            case 101:$error = '验证失败';
                break;
            case 102:$error = '短信不足';
                break;
            case 103:$error = '操作失败';
                break;
            case 104:$error = '非法字符';
                break;
            case 105:$error = '内容过多';
                break;
            case 106:$error = '号码过多';
                break;
            case 107:$error = '频率过快';
                break;
            case 108:$error = '号码内容空';
                break;
            case 109:$error = '账号冻结';
                break;
            case 110:$error = '禁止频繁单条发送';
                break;
            case 111:$error = '系统暂定发送';
                break;
            case 112:$error = '子号不正确';
                break;
            case 120:$error = '系统升级';
                break;
            default:$error = '未知的错误';
        }
        return $error;
    }

    public static function createCode($len = 4) {
        $len = $len > 4 ? $len : 4;

        $randS = pow(10, $len-1);
        $randE = pow(10, $len) - 1;
        return rand($randS, $randE);

    }
}
// SendSms::send('18313769007', '注册验证码为：123456【智测云】');
// echo SendSms::createCode();