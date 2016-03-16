<?php

/*
 * FTP下载测试&&HDFS读写测试
 */

/*
  require_once 'include/ftp.class.php';

  $config = array(
  'hostname' => '10.1.10.110',
  'username' => 'taikai',
  'password' => 'TK@tech1717#',
  'port' => 21
  );
  $ftp = new Ftp();
  $link = $ftp->connect($config);
  if ($link) {
  echo "FTP连接成功";
  } else {
  echo "FTP连接失败";
  }
  $remotepath = '/home/taikai/server/nglogs/aa6404ea-9de3-f2c5-774d-8f626a04fe66_0/DOOV L5/traverse.log';
  $localpath = '/app/zhiceyun/webroot/zhiceyun/ftp_tmp/aa6404ea-9de3-f2c5-774d-8f626a04fe66/DOOV L5/traverse.log';
  $result = $ftp->download($remotepath, $localpath, $mode);
  if ($result) {
  echo 'FTP下载成功';
  } else {
  echo 'FTP下载失败';
  }
 */

/*
$uuid = "72ffa979-0fe9-8c8e-e41f-bd6a5d86cbb3";
$_one = "Coolpad 8690";
//$result = $ftp->download($remotepath, $localpath, $mode);

$s_file = "/app/zhiceyun/webroot/zhiceyun/src/" . $uuid . "/" . $_one . "/traverse.log";
$file_dir = '/apktest/' . $uuid . "/Coolpad_8690/cc traverse.log";
if (file_exists($s_file)) {
    $contents = base64_encode(file_get_contents($s_file));
    $obj = new phdfs("10.1.10.101", "9000");
    $res = $obj->connect();
    if (!$obj->write($file_dir, $contents, O_WRONLY | O_CREAT)) {
        echo "trclog上传失败";
    } else {
        $path = 'streamFile' . $file_dir;
        $url = 'http://42.96.171.6:8008/zhiceyun/file/download.php?file=' . urlencode($path);
        echo $url . PHP_EOL;
        echo file_get_contents($s_file);
    }
} else {
    echo "源文件不存在";
}
*/