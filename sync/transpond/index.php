<?php

/*
 * 下行：post转发接口
 */

//公共函数文件
require_once('../include/fun.inc.php');
//包含写日志文件
require_once('../include/log.inc.php');

$_PARAM_str = file_get_contents("php://input");
LogWrite('sendTask', '传参', $_PARAM_str);

$_PARAM = json_decode($_PARAM_str, TRUE);
$url = $_PARAM['url']; //下发接口地址
$data = json_encode($_PARAM['data']); //下发接口数据

$resultStr = request_post($url, $data);

//$resultStr = '{"status":1,"message":"创建任务前检测成功","deviceStatus":1,"deviceNum":1}';
echo $resultStr;
LogWrite('sendTask', '返回值', $resultStr . "\r\n");
