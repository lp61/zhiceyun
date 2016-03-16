<?php

/*
 * hdfs文件下载
 * +================================================
 * 测试文件目录：streamFile/apktest/$uuid/$deviceType/***.***
 */

$path = urldecode($_GET['file']);
$log_path = "/app/zhiceyun/webroot/zhiceyun/logs/file/" . date("Y");
CreateAllDir($log_path);
$log_file = $log_path . "/download_" . date("md") . ".log";
try {
    $obj = new phdfs("10.1.10.101", "9000");
    $obj->connect();
//    $log = $obj->file_info(substr($path, 10));
//    if ($log) {
//        $message = "fileInfo:" . json_encode($log);
//    } else {
//        $message = $path . " not exists!";
//    }

    $stream = file_get_contents("http://10.1.10.102:50075/" . $path);
    $filestream = base64_decode($stream);
    header('Content-type: application/text/plain');
    //header('Content-type: image/png');
    header('Content-Disposition: attachment; filename=' . basename($path));
    header('Content-Length: ' . strlen($filestream));
//    error_log(date("Y-m-d H:i:s：") . strlen($filestream) . " | " . $message . "\r\n\r\n", 3, $log_file);
    echo $filestream;
} catch (Exception $ex) {
    $message = $path . " failed! " . $ex->getMessage();
    error_log(date("Y-m-d H:i:s：") . $message . "\r\n\r\n", 3, $log_file);
    echo ($ex->getMessage());
}

/*
 * 检查指定目录是否存在，不存在则创建目录
 */

function CreateAllDir($dir) {
    if (is_dir($dir)) {
        return TRUE;
    }
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
