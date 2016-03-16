<?php

/*
 * @author vini
 */

//常量定义
$dir = getcwd();
define('APPID', 'wx853e30fa671758e0');
define('APPSECRET', 'fd0ce8cc6de36ef771e2fc5f9b260876');
define('SITE_PATH', substr($dir, 0, strrpos($dir, '/'))); //服务器目录设置
define('HOST', 'new.smarterapps.cn'); //域名设置

/*
 * 获取ACCESS_TOKEN
 * access_token是公众号的全局唯一票据，公众号调用各接口时都需使用access_token。
 * 正常情况下access_token有效期为7200秒，重复获取将导致上次获取的access_token失效。
 */

function getToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    $json_file = SITE_PATH . "/access_token.json";
    $data = json_decode(file_get_contents($json_file));
    if (empty($data) || $data->expire_time < time()) {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . APPID . '&secret=' . APPSECRET;
        $res = json_decode(file_get_contents($url));
        $access_token = $res->access_token;
        if ($access_token) {
            $data->expire_time = time() + 7000;
            $data->access_token = $access_token;
            $fp = fopen($json_file, "w");
            fwrite($fp, json_encode($data));
            fclose($fp);
        }
    } else {
        $access_token = $data->access_token;
    }
    return $access_token;
}

function CreateAllDir($dir) {
    $dir_array = explode("/", $dir);
    $DirName = "";
    for ($i = 0; $i < count($dir_array); $i++) {
        if ($dir_array[$i] != "") {
            $DirName .= "/" . $dir_array[$i];
            if (!is_dir($DirName)) {
                mkdir($DirName, 0755);
            }
        }
    }
}

/*
 * 记录日志
 */

function LogWrite($msg, $path = "") {
    $time = date('Y-m-d H:i:s');
    $date = substr($time, 0, 10);
    if ($path == "") {
        $dir = SITE_PATH . "/logs/wxapi/";
        CreateAllDir($dir);
        $path = $dir . $date . ".txt";
    }
    $str = $time . "\t" . $msg . "\r\n";
    error_log($str, 3, $path);
}

?>