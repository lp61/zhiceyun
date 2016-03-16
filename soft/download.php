<?php

/*
 * hdfs文件下载
 */
define('TOKEN', 'Ez5YY4nuNI'); //MD5加密串

$file = $_GET['file'];
$sg = $_GET['sg'];
if ($sg != md5($file . TOKEN)) {
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo '非法的下载链接！';
    exit();
}

$path = base64_decode($file);
//error_log(date("Y-m-d H:i:s：") . $file . "\t" . $path . "\r\n", 3, '/app/zhiceyun/webroot/zhiceyun/logs/soft2015/download.log');
try {
    $obj = new phdfs("10.1.10.101", "9000");
    $obj->connect();

    $resFile_name = basename($path);
    $stream = file_get_contents('http://10.1.10.102:50075/' . $path);
    $filestream = base64_decode($stream);
    header('Content-type: application/vnd.android.package-archive');
    //header('Content-type: image/png');
    header('Content-Disposition: attachment; filename=' . $resFile_name);
    header('Content-Length: ' . strlen($filestream));
    error_log(date("Y-m-d H:i:s：") . strlen($filestream) . "  " . $path . "\r\n", 3, '/app/zhiceyun/webroot/zhiceyun/logs/soft2015/download.log');
    echo $filestream;
} catch (Exception $ex) {
    echo ($ex->getMessage());
}