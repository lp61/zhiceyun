<?php

//签名
function signature($data) {
    foreach ($data as $_one) {
        $tmpArr[] = $_one;
    }
    sort($tmpArr, SORT_STRING);
    $tmpStr = md5(implode($tmpArr));

    return $tmpStr;
}

//下发任务接口
$data['token'] = 'f9da4WE1';
$data['timestamp'] = time();
$data['sid'] = 72;
$data['packageUrl'] = 'http://42.96.171.6:8008/zhiceyun/soft/download.php?file=c3RyZWFtRmlsZS9hcGt0ZXN0LzIyNzEwN2E2LTA0MzItZDFlMS1iMTgyLTFiYmNjYWMzMDY3Zi9zb3VodXhpbndlbl84Ni5hcGs%3D&sg=65452619c37657ab7d4e23b6c341ba2f';
$data['callbackUrl'] = 'http://';
$data['apkType'] = 1;
$data['packageType'] = 2;

//查询结果接口
//$data['token'] = 'f9da4WE1';
//$data['timestamp'] = time();
//$data['sid'] = 72;
//$data['uuid'] = '48ccf27e-75a9-525e-306e-2b4d71068fc8';
//$data['filter'] = 0;

$signature = signature($data);
$data['sig'] = $signature;

echo $signature . "<br/>";
echo json_encode($data);
