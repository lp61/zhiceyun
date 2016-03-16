<?php

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
 * 系统错误统一回复代码定义
 */

function get_error($type, $ext = '') {
    $ErrorArray = array(
        1 => array('code' => '4001', 'message' => '用户PIN为空'),
        2 => array('code' => '4002', 'message' => '不存在该用户'),
        3 => array('code' => '4003', 'message' => '请求参数异常', 'ext' => $ext)
    );
    return $ErrorArray[$type];
}

/*
 * 检查参数正确性
 */

function check_param($Error) {
    if ($Error === 1) {
        echo json_encode(get_error(3));
        exit;
    }
}

/*
 * 用给定角度旋转图像
 */

function rotate($filename, $degrees) {
    //创建图像资源，以jpeg格式为例
    $source = imagecreatefrompng($filename);
    //使用imagerotate()函数按指定的角度旋转
    $rotate = imagerotate($source, $degrees, 0);
    //旋转后的图片保存
    imagepng($rotate, $filename);
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

/*
 * 解析查询条件数组，生成查询条件
 */

function get_map($map) {
    $str = "";
    foreach ($map as $key => $value) {
        $str .= $key . "='" . $value . "' AND ";
    }
    return substr($str, 0, -4);
}

/*
 * 函数的作用 验证请求的合法性
 * 采用了公钥和私钥的判断方式
 * 其中私匙 是客户端时间戳
 * $str_des 加密后的字符串
 * $key 公用密钥
 *
 * 当请求非法时会直接终止程序 相关json返回如下
 * Code=9997  Message="重复请求"
 * Code=9999  Message="非法请求"（DES解密错误）
 */

function check_des($str_des, $key) {
    include_once ('../include/3dsclass.php');
    error_log(date("Y-m-d H:i:s") . "\t TOKEN解密前：str_des=$str_des,key=$key\r\n", 3, LOG_PATH . 'dbinc.log');
    $des = new DesComponent();
    $tmp_str = $des->decrypt($str_des, $key);
    error_log(date("Y-m-d H:i:s") . "\t TOKEN解密结果：" . $tmp_str . "\r\n", 3, LOG_PATH . 'dbinc.log');
    if ($tmp_str) {
        $flag = true;
    } else {
        $json_array["code"] = "9999";
        $json_array["message"] = "非法请求";
        echo json_encode($json_array);
        exit;
    }
    $key_array = @file(LOG_PATH . "des.log");

    //通过验证后做如下判断
    if ($flag) {
        switch (count($key_array)) {
            case 0:
                $fp = fopen(LOG_PATH . "des.log", "a+");
                fwrite($fp, $tmp_str . "\n");
                fclose($fp);
                break;
            case 30000:
                $count_array = array_count_values($key_array);
                $search_key = $tmp_str . "\n";
                if ($count_array[$search_key] > 1) {
                    $json_array["code"] = "9997";
                    $json_array["message"] = "重复请求";
                    echo json_encode($json_array);
                    exit;
                } else {
                    unlink(LOG_PATH . "des.log");
                    $fp = fopen(LOG_PATH . "des.log", "a+");
                    fwrite($fp, $tmp_str . "\n");
                    fclose($fp);
                }
                break;
            default:
                $count_array = array_count_values($key_array);
                $search_key = $tmp_str . "\n";
                if ($count_array[$search_key] > 1) {
                    $json_array["code"] = "9997";
                    $json_array["message"] = "重复请求";
                    echo json_encode($json_array);
                    exit;
                } else {
                    $fp = fopen(LOG_PATH . "des.log", "a+");
                    fwrite($fp, $tmp_str . "\n");
                    fclose($fp);
                }
        }
    }
}

//解密App与服务器交互数据
function dec_value($arr, $key) {
    $des = new DesComponent();
    error_log(date('Y-m-d h:i:s') . "dec_value：\t" . json_encode($arr) . "\r\n", 3, LOG_PATH . 'dbinc.log');
    foreach ($arr as $keys => $val) {
        //参数解密
        $value = $des->decrypt($val, $key);
        error_log(date('Y-m-d h:i:s') . $keys . "\t" . $val . "\t" . $value . "\r\n", 3, LOG_PATH . 'dbinc.log');
        if ($value == FALSE) {
            $json_array["code"] = "9999";
            $json_array["message"] = "非法请求";
            echo json_encode($json_array);
            exit;
        }
        $arr[$keys] = base64_decode($value);
        //为空的情况
        if ($arr[$keys] == "A90000000000000000000000000001") {
            $arr[$keys] = "";
        }
        //为0的情况
        if ($arr[$keys] == "B90000000000000000000000000001") {
            $arr[$keys] = "0";
        }
        $fp = fopen(LOG_PATH . "poststr.txt", "a+");
        fwrite($fp, $keys . "=" . $arr[$keys] . "\n");
        fclose($fp);
    }
    return $arr;
}

//返回服务器毫秒数
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

/*
 * 写入消息
 * +----------------------------------------
 * @param $sid 消息发送方id
 * @param $pid 消息接收方id
 * @param $moduletype 消息所属模块类型   //参照MODULE_CODE字典类型
 * @param $eventtype  事件（消息）类型   //参照EVENT_SORT字典类型
 * @param $title 消息标题 
 * @param $content  消息内容
 * @param $ext1 扩展字段1	//扩展字段根据不同业务意义有所不同
 * @param $ext2 扩展字段2
 */

function send_system_msg($db, $sid, $pid, $moduletype, $eventtype, $title, $content, $ext1 = '', $ext2 = '') {
    $id = $db->get_maxid("MSG_ID");
    $ptime = date('Y-m-d H:i:s', time());
    $sql = "INSERT INTO tb_msg "
        . "SET id='" . $id . "',pid='" . $pid . "',sid='" . $sid . "',moduletype='" . $moduletype . "',eventtype='" . $eventtype . "',title='" . $title . "',content='" . $content . "',ext1='" . $ext1 . "',ext2='" . $ext2 . "',ptime='" . $ptime . "',mread=0,flag=1";
    if ($db->Query($sql)) {
        //消息发送成功
        return TRUE;
    } else {
        return FALSE;
    }
}

//取系统变量
function C($key) {
    global $config;
    return $config[$key];
}

/*
 * MD5加密
 * +---------------------
 * $key 密钥
 * $str 加密字符串
 */

function Fun_MD5($str, $key = "VC201508") {
    $type = 'md5';
    $str = $str . $key;
    return hash($type, $str);
}

/*
 * 检查指定目录是否存在，不存在则创建目录
 */

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
 * 上传文件
 * $stream 文件流
 * $ext    文件扩展名
 * $path   文件存储路径
 */

function upload_file($stream, $ext, $path) {
    CreateAllDir($path);

    $rand = substr(str_shuffle('0123456789aisbmnwuhsjzqpzkx'), 0, 6);
    $name = time() . $rand . "." . $ext;
    $fp = fopen($path . $name, 'w');
    $result = fwrite($fp, $stream);
    if ($fp == FALSE || $result == FALSE) {
        LogWrite($path . $name, $fp . "|" . $result, $stream);
    }
    fclose($fp);
    return $name;
}

function deleteMedia($url) {
    if (!empty($url)) {
        $filePath = str_replace(SITE_PATH, ROOT_PATH, $url);
        @unlink($filePath);
    }
}

//删除指定目录或文件
function rmdirr($path) {
    if (is_dir($path)) {
        $file_list = scandir($path);
        foreach ($file_list as $file) {
            if ($file != '.' && $file != '..') {
                rmdirr($path . '/' . $file);
            }
        }
        @rmdir($path);  //这种方法不用判断文件夹是否为空,  因为不管开始时文件夹是否为空,到达这里的时候,都是空的     
    } else {
        @unlink($path);    //这两个地方最好还是要用@屏蔽一下warning错误,看着闹心
    }
}

/**
  +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码
 * 默认长度6位 字母和数字混合 支持中文
  +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
  +----------------------------------------------------------
 * @return string
  +----------------------------------------------------------
 */
function rand_string($len = 6, $type = '', $addChars = '') {
	$str = '';
	switch ($type) {
		case 0 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		case 1 :
			$chars = str_repeat('0123456789', 3);
			break;
		case 2 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
			break;
		case 3 :
			$chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		default :
			// 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
			$chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
			break;
	}
	if ($len > 10) { //位数过长重复字符串一定次数
		$chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
	}
	if ($type != 4) {
		$chars = str_shuffle($chars);
		$str = substr($chars, 0, $len);
	} else {
		// 中文随机字
		for ($i = 0; $i < $len; $i ++) {
			$str .= msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
		}
	}
	return $str;
}

?>