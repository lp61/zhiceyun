<?php

/*
 * Interface-5  上行-上报结果接口(网络友好测试)
 * +--------------------------------------------------
 * 1.网络友好测试：只要有一款终端ARO测试成功，则认为测试成功
 * 2.快速测试：不论是否有ARO测试结果，都认为测试成功（不会触发重发任务）
 * +--------------------------------------------------
 * {"uuid":"00eb4f01ec549e6c9639158a85899156","type":"1","success":["HUAWEI G750-T00","YTONE L930"]}
 */

require_once '../include/dbinc.php';

$_PARAM_str = file_get_contents("php://input");
$_PARAM = json_decode($_PARAM_str, TRUE);
LogWrite('upSendResultNet', '传参', $_PARAM_str);

$p_uuid = $_PARAM['uuid'];
$uuid = substr($p_uuid, 0, 36);
$businessID = $_PARAM['type']; // 1-快速测试业务 3-网络友好业务
$success_device = $_PARAM['success'];
$timestamp = time();
$sql1 = "SELECT  `user_id`,`apk_name`,`retry`,`package_type`  FROM  ts_app_test_task  WHERE  `uuid`='$uuid'  LIMIT  1";
$row = $db->get_one($sql1);
$package_type = $row['package_type']; //测试包类型

if (empty($success_device) && $businessID == 3) {    //不存在测试成功的终端
    //网络友好业务，ARO测试无结果会重新下发测试任务。
    $sql2 = "SELECT  `value`  FROM  ts_app_test_config  WHERE  `key`='retry_num'  LIMIT 1";
    $config = $db->get_one($sql2);

    $retry = $row['retry'] + 1;
    if ($retry > $config['value']) {
        //测试失败：超限
        $sql = "UPDATE ts_app_test_task SET task_status=3,send_status=-2,retry='$retry',retry_device_num=device_num,retry_device_type=device_type WHERE uuid='$uuid'";
    } else {
        //测试失败：重发
        $sql = "UPDATE ts_app_test_task SET task_status=1,send_status=3,retry='$retry',retry_device_num=device_num,retry_device_type=device_type WHERE uuid='$uuid'";
    }
    $db->Query($sql);
} else {
    //1.分析测试结果
    require_once '../upSendResult_Net/analysis_net.php';
    analysis_net_one($db, $p_uuid, $businessID);
    //2.生成测试报告
    $report_url = generateReport($db, $uuid, $package_type);
    //3.更新任务状态
    $sql = "UPDATE ts_app_test_task SET `task_status`=2,`send_status`=0,`send_order`=0,`send_num`=0,`report_url`='$report_url',`mtime`='$timestamp' WHERE uuid='$uuid'";
    $db->Query($sql);
    //4.发送微信推送通知(队列)
    if ($businessID == 3) {
        $uid = $row['user_id'];
        $sql1 = "SELECT  openID  FROM  ts_wx_user  WHERE  uid='$uid'  AND  status=1 LIMIT 1";
        $wx = $db->get_one($sql1);
        $touser = $wx['openID'];
        if ($touser) {
            $type = 6; //6、任务处理结果提醒
            $first = '您好，亲爱的开发者，您提交的测试任务已完成！';
            $key1 = $uuid; //任务ID
            $key2 = $row['apk_name']; //任务名称
            $key3 = '已完成'; //状态
            $sql = "INSERT  INTO  ts_wx_push  SET uid='$uid',openID='$touser',type='$type',first='$first',key1='$key1',key2='$key2',key3='$key3',ctime=now()";
            $db->Query($sql);
        }
    }
}
LogWrite('upSendResultNet', 'STEP-2', $sql);

$return['code'] = 1;
$return['message'] = "上传结果成功";
$returnStr = json_encode($return);
echo $returnStr;

LogWrite('upSendResultNet', '返回值', $returnStr . "\r\n");
