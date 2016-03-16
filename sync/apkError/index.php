<?php

/*
 * Interface-6  上行-APK解析失败反馈
 */

require_once '../include/dbinc.php';

$_PARAM_str = file_get_contents("php://input");
$_PARAM = json_decode($_PARAM_str, TRUE);
LogWrite('apkError', '传参', $_PARAM_str);

$p_uuid = $_PARAM['uuid'];
$uuid = substr($p_uuid, 0, 36);         //任务UUID

if (!empty($uuid)) {
    //安装包异常
    $sql = "UPDATE  ts_app_test_task  SET  `send_status`=5,`send_num`=send_num+1  WHERE  `uuid`='$uuid'";
    $db->Query($sql);
    LogWrite('apkError', 'STEP-1', $sql);
}

$return['code'] = 1;
$return['message'] = "接收反馈信息成功";
$returnStr = json_encode($return);
echo $returnStr;

LogWrite('apkError', '返回值', $returnStr . "\r\n");
