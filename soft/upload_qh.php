<?php

/*
 * hdfs文件上传
 */

function upload(){
$log_path = '/app/zhiceyun/webroot/zhiceyun/logs/soft';

include_once '../sync/include/dbinc.php';
//$_PARAM_str = file_get_contents("php://input");
$_PARAM_str = json_encode($_REQUEST);
LogWrite('Upload', '传参', $_PARAM_str, $log_path);

$_PARAM = json_decode($_PARAM_str, TRUE);
$uuid = $_PARAM['uuid']; //任务ID
$src_file = $_PARAM['apk_url']; //apk下载路径
$file_name = $_PARAM['apk_sname']; //apk原文件名
$attach_id = $_PARAM['attach_id']; //附件ID

if (empty($file_name) || strlen($file_name) > 100) {
    $file_name = pathinfo($src_file, PATHINFO_BASENAME);
}

$contents = base64_encode(file_get_contents($src_file));
try {
    $obj = new phdfs("10.1.10.101", "9000");
    $obj->connect();
    $file_dir = '/apktest/' . $uuid . "/"; //文件存储路径
    $file = $file_dir . str_replace(' ', '', $file_name); //文件完整地址
    $log = $obj->exists($file);
    if (!$log) { //文件不存在则写入此文件
        $log1 = $obj->create_directory($file_dir);
        $log2 = $obj->write($file, $contents, O_WRONLY | O_CREAT);
        if ($log2) {
            $path = base64_encode('streamFile' . $file);
            $sg = md5($path . TOKEN);
            $apkurl = 'http://42.96.171.6:8008/zhiceyun/soft/download.php?file=' . urlencode($path) . "&sg=" . $sg;
            $sql1 = "UPDATE ts_app_test_task SET apkurl='$apkurl',send_status=1 WHERE uuid='$uuid'";
            $db->Query($sql1);
            $sql2 = "UPDATE ts_attach SET apkurl='$apkurl',flag=1 WHERE id='$attach_id'";
            $db->Query($sql2);
        }
        echo '0';
        LogWrite('Upload', $log1 . "|" . $log2, $sql . "\r\n", $log_path);
    }
} catch (Exception $ex) {
    echo '1';
    LogWrite('Upload', $ex->getMessage(), '', $log_path);
}
}