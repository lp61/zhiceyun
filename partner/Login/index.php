<?php

/*
 * Login 登陆接口
 */

include_once '../include/dbinc.php';

//$_PARAM_str = file_get_contents("php://input");
$_PARAM_str = json_encode($_REQUEST);
$_PARAM = json_decode($_PARAM_str, TRUE);
LogWrite('Login', 'POST传参', json_encode($_POST));
LogWrite('Login', 'GET传参', json_encode($_GET));

if ($_PARAM['token'] != QIHU_TOKEN || empty($_PARAM['timestamp']) || empty($_PARAM['sig']) || empty($_PARAM['authtoken'])) {
    $return['code'] = 1;
    $return['msg'] = '参数错误！';
    $returnStr = json_encode($return);
    echo $returnStr;
    LogWrite('Login', '返回值', $returnStr . "\r\n");
    exit();
}

$signature = $_PARAM['sig'];
unset($_PARAM['sig']);
if (!checkSignature($_PARAM, $signature)) {
    $return['code'] = 2;
    $return['msg'] = '签名验证失败！';
} else {
    $authtoken = $_PARAM['authtoken'];
    $sql = "SELECT  `uid`  FROM  `ts_user`  WHERE  `api_key`='" . $authtoken . "'  LIMIT 1";
    LogWrite('Login', 'SQL', $sql);
    $row = $db->get_one($sql);
    if ($row['uid'] > 0) {
        $uid = $row['uid'];
    } else {
        $request_data['authtoken'] = $authtoken;
        $request_data['clientid'] = CLIENT_ID;
        $request_data['timestamp'] = time();
        $sig_tmp = signature($request_data);
        $request_data['sig'] = $sig_tmp;
        LogWrite('Login', QIHU_CHECKUSER_URL . "?" . http_build_query($request_data));
        $result = request_get(QIHU_CHECKUSER_URL . "?" . http_build_query($request_data));
        LogWrite('Login', 'QIHU_CHECKUSER_2', $result);
        $data = json_decode($result, TRUE);
        if ($data['code'] === 0) {
            $appuid = $data['data']['appuid']; //第三方APP的用户唯一标示信息
            $name = $data['data']['name']; //第三方APP的用户唯一标示信息
            $email = $data['data']['email']; //第三方APP的用户唯一标示信息
            //检查该邮箱是否已注册，已注册直接返回uid，未注册新增360用户
            $sql1 = "SELECT  `uid`  FROM  `ts_user`  WHERE  `email`='" . $email . "'  LIMIT 1";
            $row1 = $db->get_one($sql1);
            if ($row1['uid'] > 0) {
                $uid = $row1['uid'];
                $sql2 = "UPDATE  `ts_user`  SET  `api_key`='$authtoken'  WHERE  uid='" . $uid . "'";
                LogWrite('Login', 'UPDATE_SQL', $sql2);
                $db->Query($sql2);
            } else {
                $saveData['email'] = $email;
                $saveData['uname'] = $name;
                $saveData['api_key'] = $authtoken; //第三方用户key
                $saveData['is_active'] = 1; //是否激活：1-已激活
                $saveData['user_type'] = 0; //用户类型：0-普通用户
                $saveData['is_audit'] = 1; //是否已审核：1-已审核
                $saveData['status'] = -1; //状态：-1-二期用户
                $saveData['source'] = 3; //用户来源：3-360开平
                $uid = add($db, $saveData, 'user');
            }
        } else {
            $return['code'] = 3;
            $return['msg'] = '用户验证失败！';
            $returnStr = json_encode($return);
            echo $returnStr;
            LogWrite('Login', '返回值', $returnStr . "\r\n");
            exit();
        }
    }
    $return['code'] = 0;
    $return['msg'] = '登陆成功！';
    $return['data']['sid'] = $uid;
}

$returnStr = json_encode($return);
echo $returnStr;
LogWrite('Login', '返回值', $returnStr . "\r\n");
