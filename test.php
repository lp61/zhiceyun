<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/18 0018
 * Time: 下午 6:16
 */
 function checkProgress($uuid = 'feee1025-0e79-cf8b-199a-263878e423e9') {
//        if (!$this->check_connect()) {
//            echo 'remote server connect failed!'; //远程服务器连接失败，直接返回
//            return;
//        }

     /** @var TYPE_NAME $checkProgress_url */
     $checkProgress_url = 'http://10.1.10.100/management/rest/uri/planProgress'; //查看任务进度接口地址
     $post['url'] = checkProgress_url; //任务下发接口请求地址
     $data['uuid'] = $uuid;
     $post['data'] = $data;
     $check_result = $this->request_post($this->url, json_encode($post));
    if ($check_result['status'] == 1) {
        //echo $check_result['progress'];
        //获取进度成功
        return $check_result['progress']; //string类型
    } else {
        //echo $check_result['message'];
        //获取进度失败
        return FALSE;
    }
}

 function test($ref)
{
    test_nested($ref);
}
function test_nested($ref)
{
    print($ref);
// or: debug(get_defined_vars());
// or: debug();
}
test("hahaha");
checkProgress("3bfdbad6-83cc-c5d9-a329-5dd8b47cf4a1");