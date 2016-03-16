<?php

/*
 * Submit 提交测试接口
 */

include_once '../include/dbinc.php';

$_PARAM_str = file_get_contents("php://input");
//$_PARAM_str = json_encode($_REQUEST);
$_PARAM = json_decode($_PARAM_str, TRUE);
LogWrite('Submit', '传参', json_encode($_PARAM));

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
if (empty($_PARAM['packageUrl'])) {
    $return['code'] = 1;
    $return['msg'] = '下载地址异常';
    $returnStr = json_encode($return);
    echo $returnStr;
    exit();
}
if (empty($_PARAM['callbackUrl'])) {
    $return['code'] = 1;
    $return['msg'] = '回调地址异常';
    $returnStr = json_encode($return);
    echo $returnStr;
    exit();
}


$uid = $_PARAM['sid']; //用户ID
$url = $_PARAM['packageUrl']; //apk下载链接
$apk_type = isset($_PARAM['apkType']) ? (int) $_PARAM['apkType'] : 0; //默认apk类型：未知
$package_type = in_array($_PARAM['packageType'], array(2, 5)) ? $_PARAM['packageType'] : 2; //默认包类型：兼容性包
$signature = $_PARAM['sig']; //参数签名
unset($_PARAM['sig']);
if (!checkSignature($_PARAM, $signature)) {
    //签名验证未通过
    $return['code'] = 2;
    $return['msg'] = '签名验证失败！';
} else {
    //STEP-1 新增任务记录
    $data['uuid'] = create_uuid(); //任务UUID
    $data['user_id'] = $uid; //用户ID
    //apk文件信息
    $data['apk_url'] = $data['apkurl'] = $url;
    //其它
    $data['apk_type'] = $apk_type; //apk类型
    $data['package_type'] = $package_type; //测试包类型
    $data['businessID'] = $package_type == 2 ? 2 : 3; //测试业务类型
    $data['sourceID'] = 1; //测试任务源:3 1-网站 2-应用宝 3-360开平
    $data['device_type'] = ''; //指定的检测终端类型
    $data['retry'] = 0; //重测次数:0
    $data['is_login'] = 0; //是否要登陆 1-是 0-否
    $data['account_type'] = 1; //账户类型：1 1-可共用 0-不可共用
    $data['account'] = ''; //账户密码（不可共用时使用）
//    $data['ctime'] = time();
//    $data['send_status'] = 1;
//    $data['status'] = 1;
//    $task_id = add($db, $data, 'app_test_task');
//    $data['id'] = $task_id; //记录ID

    require_once '../include/sendTask.class.php'; //任务发送类
    $sendTask = new sendTask();
    $result = $sendTask->send($data);
    if ($result) {
        //任务下发成功
        //任务首次下发，记录实际下发的终端型号到device_type字段
        $models = $result['models'];
        $device_type = "";
        if (!empty($models)) {
            foreach ($models as $one) {
                $device_type .= $one . ",";
            }
            $device_type = substr($device_type, 0, -1);
        }
        $data['device_num'] = $result['modelNum'];
        $data['device_type'] = $device_type;
        $data['send_status'] = 0;
        $data['task_status'] = 1;
        $data['ctime'] = $data['stime'] = time();
        $data['status'] = 1;
        $task_id = add($db, $data, 'app_test_task');

//        //更新记录  下发状态：下发成功；任务状态：测试中；下发时间；实际测试终端数量；实际测试终端型号
//        $sql = "UPDATE  ts_app_test_task  SET  "
//            . "send_status=0,task_status=1,stime='" . time() . "',device_num='" . $result['modelNum'] . "',device_type='" . $device_type . "'"
//            . "  WHERE  id='$task_id'";
//        $db->Query($sql);
        $return['code'] = 0;
        $return['msg'] = '任务下发成功';
        $return['data'] = array('uuid' => $data['uuid']);
    } else {
        //任务下发失败
        $return = $sendTask->getError();
    }
}

$returnStr = json_encode($return);
echo $returnStr;
LogWrite('Submit', '返回值', $returnStr . "\r\n");

