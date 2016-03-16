<?php

/*
 * Interface-5  上行-上报结果接口
 */

require_once '../include/dbinc.php';
require_once '../include/ftp.class.php';
require_once '../upSendResult/analysis_comp.php';

$_PARAM_str = file_get_contents("php://input");
$_PARAM = json_decode($_PARAM_str, TRUE);
LogWrite('upSendResult', '传参', $_PARAM_str);

$p_uuid = $_PARAM['uuid'];
$uuid = substr($p_uuid, 0, 36);         //任务UUID
$success_device = $_PARAM['success'];   //成功设备终端型号
$faild_device = $_PARAM['faild'];       //失败设备终端型号
//如果重复上传结果，则直接返回成功
$sql = "SELECT  id  FROM  ts_apptest_result_comp_device  WHERE  `uuid`='$uuid'  LIMIT 1";
if ($db->get_one($sql)) {
    $return['code'] = 1;
    $return['message'] = "结果已上传";
    $returnStr = json_encode($return);
    echo $returnStr;
    LogWrite('upSendResult', '返回值', $returnStr . "\r\n");
    exit();
}
LogWrite('upSendResult', 'SQL', $sql);
$sql1 = "SELECT  `user_id`,`apk_name`,`retry`,`device_type`,`package_type`  FROM  ts_app_test_task  WHERE  `uuid`='$uuid'  LIMIT  1";
$row = $db->get_one($sql1);

$flag = FALSE; //任务完成标志 TRUE-已完成 FALSE-未完成
$package_type = $row['package_type']; //测试包类型
//STEP-1 下载测试成功的终端的测试数据
if (!empty($success_device)) {
    //连接FTP
    $ftp_config = array(
        'hostname' => '10.1.10.118',
        'username' => 'taikai',
        'password' => 'TK@tech1717#',
        'port' => 21
    );
    $ftp = new Ftp();
    $link = $ftp->connect($ftp_config);
    LogWrite('upSendResult', 'STEP-1', $link ? "FTP连接成功" : "FTP连接失败");
    //连接HDFS
    try {
        $obj = new phdfs("10.1.10.101", "9000");
        $res = $obj->connect();
        LogWrite('upSendResult', 'STEP-2', $res ? "HDFS连接成功" : "HDFS连接失败");
        foreach ($success_device as $key => $_one) {
            //远程FTP文件下载路径
            $remotepath = '/nglogs/' . $p_uuid . "/" . $_one . "/";
            //本地文件临时存储路径
            $localpath = SAVE_PATH . $uuid . "/" . $_one . "/";
            CreateAllDir($localpath);
            //HDFS文件上传路径(注：文件路径中不允许有空格)
            $file_dir = str_replace(' ', '', '/apktest/' . $uuid . "/" . $_one . "/");
            $log1 = $obj->create_directory($file_dir);
            if ($ftp->download($remotepath . $p_uuid . ".trclog", $localpath . $uuid . ".trclog", $mode)) {
                //把整个文件读入一个字符串中
                $contents = base64_encode(file_get_contents($localpath . $uuid . ".trclog"));
                if (!$obj->write($file_dir . $uuid . ".trclog", $contents, O_WRONLY | O_CREAT)) {
                    $message .= "trclog上传失败 \t";
                }
                unset($contents);
            }
            if ($ftp->download($remotepath . "traverse.log", $localpath . "traverse.log", $mode)) {
                $contents = base64_encode(file_get_contents($localpath . "traverse.log"));
                if (!$obj->write($file_dir . "traverse.log", $contents, O_WRONLY | O_CREAT)) {
                    $message .= "traverse.log上传失败 \t";
                }
                unset($contents);
            }
            if ($ftp->download($remotepath . "logcatAnalysis.xml", $localpath . "logcatAnalysis.xml", $mode)) {
                $contents = base64_encode(file_get_contents($localpath . "logcatAnalysis.xml"));
                if (!$obj->write($file_dir . "logcatAnalysis.xml", $contents, O_WRONLY | O_CREAT)) {
                    $message .= "logcatAnalysis.xml上传失败 \t";
                }
                unset($contents);
            }
            if ($message) { //严重错误，有文件上传HDFS失败
                LogWrite('uploadHDFS', $uuid . "-" . $_one, $message, LOG_PATH);
            }
        }
    } catch (Exception $ex) {
        LogWrite('uploadHDFS', $uuid . "-" . $_one, $ex->getMessage(), LOG_PATH);
    }
    //按包下发的任务：若成功的终端数量大于此包下发时所要求的最小终端数量，则认为此任务成功（该情况下不考虑是否存在失败的终端）
    if (in_array($package_type, array(1, 2, 3, 5, 9, 10, 11))) {
        $success_num = count($success_device); //成功的终端数量
        $sql = "SELECT  `mininum`  FROM  `ts_app_test_package`  WHERE  `package_type`='$package_type'  LIMIT  1";
        $result = $db->get_one($sql);
        if ($success_num >= $result['mininum']) {
            $flag = TRUE; //任务已完成
        }
    }
    LogWrite('upSendResult', 'STEP-3', "文件下载完成-$flag");
}

//STEP-2 重发任务：存在失败终端（排除快速测试）
if (!empty($faild_device) && !$flag) {
    $sql2 = "SELECT  `value`  FROM  ts_app_test_config  WHERE  `key`='retry_num'  LIMIT  1";
    $config = $db->get_one($sql2);

    $retry = $row['retry'] + 1;
    $retry_device_num = count($faild_device);
    $retry_device_type = implode(',', $faild_device);
    LogWrite('upSendResult', 'STEP-4-1', $retry . "|" . $retry_device_num . "|" . $retry_device_type);
    if ($retry > $config['value']) {
        //测试失败：超限
        $sql = "UPDATE  ts_app_test_task  SET  `task_status`=3,`send_status`=-2,`send_num`=0,`retry`='$retry',`retry_device_num`='$retry_device_num',`retry_device_type`='$retry_device_type'  WHERE  `uuid`='$uuid'";
    } else {
        //测试失败：重发
        $sql = "UPDATE  ts_app_test_task  SET  `task_status`=1,`send_status`=3,`send_num`=0,`retry`='$retry',`retry_device_num`='$retry_device_num',`retry_device_type`='$retry_device_type'  WHERE  `uuid`='$uuid'";
    }
    $db->Query($sql);
} else {
    $flag = TRUE;
}

//STEP-3 若任务已完成：更新状态,进行数据分析
$timestamp = time();
if ($flag) {
    //1.分析测试结果
    $id = analysis_one($db, $uuid, $row['device_type'], $timestamp);
    LogWrite('upSendResult', 'STEP-4-2', $id > 0 ? "ts_apptest_result_comp插入成功" : "ts_apptest_result_comp插入失败");
    //2.生成测试报告
    $report_url = generateReport($db, $uuid, $package_type);
    //3.更新任务状态
    $sql = "UPDATE  ts_app_test_task  SET  `task_status`=2,`send_status`=0,`send_order`=0,`send_num`=0,`report_url`='$report_url',`mtime`='$timestamp'  WHERE  `uuid`='$uuid'";
    $db->Query($sql);
    //4.发送微信推送通知(队列)
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
LogWrite('upSendResult', 'STEP-4', $sql);


$return['code'] = 1;
$return['message'] = "接收上传结果成功";
$returnStr = json_encode($return);
echo $returnStr;

LogWrite('upSendResult', '返回值', $returnStr . "\r\n");
