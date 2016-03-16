<?php

//系统常量定义
define('LOG_PATH', '/app/zhiceyun/webroot/zhiceyun/logs/'); //日志路径
define('LOG_API_PATH', LOG_PATH . 'api'); //接口日志路径
define('LOG_ANALYSIS_PATH', LOG_PATH . 'analysis'); //接口日志路径
define('LOG_SQL_PATH', LOG_PATH . 'sql'); //SQL日志路径

define('TOKEN', 'Ez5YY4nuNI'); //MD5加密串

define('SAVE_PATH', '/app/zhiceyun/webroot/zhiceyun/src/'); //结果临时存储路径
define('SITE_PATH', 'http://42.96.171.6:8008/zhiceyun/src/');

define('TMP_PATH', '/app/zhiceyun/webroot/zhiceyun/logs/tmp'); //报告临时存储路径
define('SITE_URL', 'http://new.smarterapps.cn');

ini_set('display_errors', true);
//error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Shanghai');
}

/*
 * 获得客户端真实的IP地址 字符串形式
 */

function getip() {
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
        $ip = getenv("REMOTE_ADDR");
    } else if (isset($_SERVER ['REMOTE_ADDR']) && $_SERVER ['REMOTE_ADDR'] && strcasecmp($_SERVER ['REMOTE_ADDR'], "unknown")) {
        $ip = $_SERVER ['REMOTE_ADDR'];
    } else {
        $ip = "unknown";
    }
    return ($ip);
}

/*
 * 二维数组转一维数组
 * @param $arrs 二维数组
 * @param $value 新键值所对应的字段
 * @param $key 新键名所对应的字段（默认为空，键名取0,1,2...）
 */

function Arr2toArr1($array, $value, $key = '') {
    $data = array();
    if (!empty($array)) {
        if ($key == '') {
            foreach ($array as $i => $arr) {
                $data[$i] = $arr[$value];
            }
        } else {
            foreach ($array as $i => $arr) {
                $data[$arr[$key]] = $arr[$value];
            }
        }
    }

    return $data;
}

/*
 * 将数组转换为用逗号分隔的字符串
 * @param $arrs 二维数组
 * @param $key 键名
 */

function Create_str($arrs, $key) {
    $str = "";
    if (!empty($arrs)) {
        foreach ($arrs as $arr) {
            $str .= "'" . $arr[$key] . "',";
        }
        $str = substr($str, 0, -1);
    }
    return $str;
}

function Create_strstr($arrs, $key, $sep = ',') {
    $str = "";
    if (!empty($arrs)) {
        foreach ($arrs as $arr) {
            $str .= $arr[$key] . $sep;
        }
        $str = substr($str, 0, -strlen($sep));
    }
    return $str;
}

//将数组转换为用逗号分隔的字符串
function ArrAsStr($arr) {
    $str = "";
    if (!empty($arr)) {
        foreach ($arr as $value) {
            if ($str == "") {
                $str = $value;
            } else {
                $str.="," . $value;
            }
        }
    }
    return $str;
}

/*
 * 解析查询条件数组，生成查询条件
 */

function get_map($where) {
    $str = "";
    foreach ($where as $key => $value) {
        $str .= $key . "='" . $value . "' AND ";
    }
    return substr($str, 0, -4);
}

//返回服务器毫秒数
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

/*
 * MD5加密
 * +---------------------
 * $key 密钥
 * $str 加密字符串
 */

function Fun_MD5($str, $key = TOKEN) {
    $type = 'md5';
    $str = $str . $key;
    return hash($type, $str);
}

/**
 * 发送post请求
 * @param string $url
 * @param string $param
 * @return bool|mixed
 */
function request_post($url = '', $param = '') {
    if (empty($url) || empty($param)) {
        return false;
    }
    $postUrl = $url;
    $curlPost = $param;
    $ch = curl_init(); //初始化curl
    curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch); //运行curl
    curl_close($ch);
    return $data;
}

/*
 * 检查指定目录是否存在，不存在则创建目录
 */

function CreateAllDir($dir) {
    if (is_dir($dir)) {
        return;
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

//插入表记录
function add($db, $data, $table) {
    $tableName = 'ts_' . $table; //处理表前缀
    if (empty($data)) {
        return FALSE;
    }
    $fields = $db->getTableFields($tableName);
    $insert_sql = "INSERT INTO $tableName SET ";
    foreach ($data as $field => $value) {
        if (in_array($field, $fields)) {
            $sql .= "`" . $field . "`='" . $value . "',";
        } else {
            LogWrite("Sql_Insert_warning", $table, $field . " not exist!", LOG_PATH);
        }
    }
    $insert_sql .= substr($sql, 0, -1);
    if (!$db->Query($insert_sql)) {
        return FALSE;
    }
    return $db->Insert_ID();
}

//测试报告生成
function generateReport($db, $uuid, $package_type) {
    CreateAllDir(TMP_PATH);
    if ($package_type == 1) {
        $localFile = quickUpload($db, $uuid);
    } elseif ($package_type == 5) {
        $localFile = netUpload($db, $uuid);
    } else { //默认兼容性测试报告
        $localFile = compUpload($db, $uuid);
    }
    LogWrite('upSendResult', 'generateReport', $localFile);
    //连接HDFS
    try {
        $obj = new phdfs("10.1.10.101", "9000");
        $obj->connect();
        //HDFS文件上传路径(注：文件路径中不允许有空格)
        $hdfsFile = str_replace(' ', '', '/apktest/' . $uuid . "/" . basename($localFile));
        $contents = base64_encode(file_get_contents($localFile));
        if (!$obj->write($hdfsFile, $contents, O_WRONLY | O_CREAT)) {
            LogWrite('uploadHDFS', $uuid, "TestReport upload failed!", LOG_PATH);
        }
        //@unlink($localFile);
        unset($contents);
        return "http://42.96.171.6:8008/zhiceyun/file/download.php?file=" . urlencode("streamFile" . $hdfsFile); //report_url-报告下载路径
    } catch (Exception $ex) {
        LogWrite('uploadHDFS', $uuid, $ex->getMessage(), LOG_PATH);
        return '';
    }
}

//兼容性测试报告
function compUpload($db, $uuid) {
    $file_pdf[] = $tmpPdf = TMP_PATH . "/" . $uuid . "_basic.pdf";
    $url = SITE_URL . "/home/TestRecord/report_summary/" . $uuid;
    system("/usr/local/bin/wkhtmltopdf " . $url . " " . $tmpPdf);

    $sql = "SELECT  `id`,`device_type`  FROM ts_apptest_result_comp_device  WHERE  uuid='$uuid'  GROUP BY device_type";
    $tmp = $db->get_array($sql);
    if ($tmp) {
        foreach ($tmp as $_one) {
            $_one['device_type'] = str_replace(' ', '-', $_one['device_type']);
            $file_pdf[] = $tmpPdf = TMP_PATH . "/" . $uuid . "_" . $_one['device_type'] . "_detail.pdf";
            $url = SITE_URL . "/home/TestRecord/report_detail/" . $_one['id'];
            system("/usr/local/bin/wkhtmltopdf " . $url . " " . $tmpPdf);
        }
    }
    $zip = new ZipArchive();
    $file_zip = TMP_PATH . "/" . $uuid . ".zip";
    if ($zip->open($file_zip, ZipArchive::OVERWRITE) === TRUE) {
        foreach ($file_pdf as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close(); //关闭处理的zip文件
    }
    foreach ($file_pdf as $file) {
        @unlink($file); //删除缓存PDF文件
    }
    return $file_zip;
}

function netUpload($db, $uuid) {
    $sql = "SELECT  `id`,`device_type`  FROM ts_apptest_result_network  WHERE  uuid='$uuid'  GROUP BY device_type";
    $tmp = $db->get_array($sql);
    if ($tmp) {
        foreach ($tmp as $_one) {
            $_one['device_type'] = str_replace(' ', '-', $_one['device_type']);
            $file_pdf[] = $tmpPdf = TMP_PATH . "/" . $uuid . "_" . $_one['device_type'] . "_network.pdf";
            $url = SITE_URL . "/home/TestRecord/report_network/" . $_one['id'];
            system("/usr/local/bin/wkhtmltopdf " . $url . " " . $tmpPdf);
        }
    }
    $zip = new ZipArchive();
    $file_zip = TMP_PATH . "/" . $uuid . ".zip";
    if ($zip->open($file_zip, ZipArchive::OVERWRITE) === TRUE) {
        foreach ($file_pdf as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close(); //关闭处理的zip文件
    }
    foreach ($file_pdf as $file) {
        @unlink($file); //删除缓存PDF文件
    }
    return $file_zip;
}

//快速测试报告下载
function quickUpload($db, $uuid) {
    $file_pdf[] = $tmpPdf = TMP_PATH . "/" . $uuid . "_basic.pdf";
    $url = SITE_URL . "/home/TestRecord/report_quick/" . $uuid;
    system("/usr/local/bin/wkhtmltopdf " . $url . " " . $tmpPdf);

    $sql = "SELECT  `id`,`device_type`  FROM ts_apptest_result_comp_device  WHERE  uuid='$uuid'  GROUP BY device_type";
    $tmp = $db->get_array($sql);
    if ($tmp) {
        foreach ($tmp as $_one) {
            $_one['device_type'] = str_replace(' ', '-', $_one['device_type']);
            $file_pdf[] = $tmpPdf = TMP_PATH . "/" . $uuid . "_" . $_one['device_type'] . "_detail.pdf";
            $url = SITE_URL . "/home/TestRecord/report_quick_detail/" . $_one['id'];
            system("/usr/local/bin/wkhtmltopdf " . $url . " " . $tmpPdf);
        }
    }
    $zip = new ZipArchive();
    $file_zip = TMP_PATH . "/" . $uuid . ".zip";
    if ($zip->open($file_zip, ZipArchive::OVERWRITE) === TRUE) {
        foreach ($file_pdf as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close(); //关闭处理的zip文件
    }
    foreach ($file_pdf as $file) {
        @unlink($file); //删除缓存PDF文件
    }
    return $file_zip;
}

?>