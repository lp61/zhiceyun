<?php

include_once 'common.php';

//读取TOKEN文件
$token = getToken();

//创建菜单
header("Content-type: text/html; charset=utf-8");

function createMenu($data, $access_token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $tmpInfo = curl_exec($ch);
    if (curl_errno($ch)) {
        return curl_error($ch);
    }
    curl_close($ch);
    return $tmpInfo;
}

$data = '{
     "button":[
        {
           "name":"我的快递",
           "sub_button":[
			{	
               "type":"view",
               "name":"我发出的",
               "url":"http://42.96.201.183/shoufashi/wxwap/send_1.php"
            },
			{	
               "type":"view",
               "name":"寄给我的",
               "url":"http://42.96.201.183/shoufashi/wxwap/collect_1.php"
            }
            ]
        },
        {	
           "name":"订单查询",
           "sub_button":[
			{	
               "type":"view",
               "name":"快递订单查询",
               "url":"http://42.96.201.183/shoufashi/wxwap/index_3.php"
            }
            ]
        },
        {
           "type":"view",
           "name":"手机绑定",
           "url":"http://42.96.201.183/shoufashi/wxwap/index_2.php"
        }
	]
 }';

$response = createMenu($data, $token); //创建菜单
//返回结果处理
$arr = json_decode($response, TRUE);
$errcode = $arr['errcode'];

echo $response . PHP_EOL;
?>