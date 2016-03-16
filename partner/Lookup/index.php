<?php

/*
 * Lookup 查询结果接口
 */

include_once '../include/dbinc.php';

$_PARAM_str = file_get_contents("php://input");
//$_PARAM_str = json_encode($_REQUEST);
$_PARAM = json_decode($_PARAM_str, TRUE);
LogWrite('Lookup', '传参', json_encode($_PARAM));

//参数检测
if ($_PARAM['token'] != QIHU_TOKEN) {
    $return['code'] = 1;
    $return['msg'] = 'token错误';
    $returnStr = json_encode($return);
    echo $returnStr;
    exit();
}
if ($_PARAM['timestamp'] < strtotime("-1 hour")) {
    $return['code'] = 1;
    $return['msg'] = '时间戳错误';
    $returnStr = json_encode($return);
    echo $returnStr;
    exit();
}
if ($_PARAM['sid'] < 1 || !$db->get_one("SELECT  1  FROM  `ts_user`  WHERE  `uid`='" . $_PARAM['sid'] . "'  LIMIT 1")) {
    $return['code'] = 1;
    $return['msg'] = '用户ID错误';
    $returnStr = json_encode($return);
    echo $returnStr;
    exit();
}
if (empty($_PARAM['uuid'])) {
    $return['code'] = 1;
    $return['msg'] = '参数UUID错误';
    $returnStr = json_encode($return);
    echo $returnStr;
    exit();
}

$uid = $_PARAM['sid']; //用户ID
$uuid = $_PARAM['uuid']; //任务ID

include_once '../include/testData.class.php';
$testData = new testData($db, $uid, $uuid);
$result = $testData->getData();
if ($result) {
    $return['code'] = 0;
    $return['msg'] = '查询成功';
    $return['data'] = $result;
} else {
    $return['code'] = 1;
    $return['msg'] = $testData->errMsg;
}

$returnStr = json_encode($return);
echo $returnStr;
LogWrite('Lookup', '返回值', $returnStr . "\r\n");

