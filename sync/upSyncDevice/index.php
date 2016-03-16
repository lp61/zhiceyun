<?php

/*
 * 上行：终端同步
 */

include_once '../include/dbinc.php';

/*
 * 参数样例
  {
  "packages": [
  {
  "packageId": "0d1862e3-ce19-43aa-984d-db4146e5e700",
  "packageName": "免费测试包",
  "businessId": "5a33e6c7-c150-42b4-b498-311a01861e6a",
  "businessName": "兼容性测试",
  "sourceId": "ae2860f0-0e48-4502-8eb3-67164e657df5",
  "sourceName": "360助手",
  "devices": [
  {
  "imei": "865863023307718",
  "sn": "760BBKQ22JX2",
  "raspberryName": "B8-27-EB-A2-4D-3D",
  "raspberryIp": "192.168.0.178",
  "height": 2560,
  "width": 1536,
  "osName": "Android",
  "osVersion": "4.4.4",
  "phoneType": "MX4 Pro",
  "devState": 0,
  "isEnabled": 1
  }
  ]
  },
  {
  "packageId": "6dc71446-bd66-4d6d-be12-f4bc51b564c7",
  "packageName": "收费测试包",
  "businessId": "a156fb1d-e3d7-4ed4-a7f3-c84e3ee78f15",
  "businessName": "新机兼容性测试",
  "sourceId": "3eb48926-aaf1-40de-8329-073e052b68fb",
  "sourceName": "应用宝",
  "devices": [
  {
  "imei": "864819020319313",
  "sn": "Coolpad8750-0x2a36450",
  "raspberryName": "28-E3-47-AE-9B-69",
  "raspberryIp": "192.168.0.111",
  "height": 1280,
  "width": 720,
  "osName": "Android",
  "osVersion": "4.3",
  "phoneType": "Coolpad 8720L",
  "devState": 0,
  "isEnabled": 1
  }
  ]
  }
  ]
  }
 */
$_PARAM_str = file_get_contents("php://input");
$_PARAM = json_decode($_PARAM_str, TRUE);

LogWrite('upSyncDevice', '传参', $_PARAM_str);
$syncFlag = $_PARAM['rsyncType']; //同步标志 1-重置 0-普通更新
if (!empty($_PARAM['packages'])) {
    $data = $_PARAM['packages'];
} else {
    $return['code'] = 0;
    $return['message'] = "上传数据为空";
    echo json_encode($return);
    exit();
}

//STEP-1 获取系统中已有的终端配置信息项
$config = $db->get_array("SELECT type,content,display_order FROM ts_app_config WHERE type<5  ORDER BY display_order asc");
$a = $b = $d = array(); //a-品牌 b-操作系统 d-分辨率
$b_max = $d_max = 0; //最大排序编号
foreach ($config as $key => $_one) {
    switch ($_one['type']) {
        case 1: //1-品牌/厂商
            $a[] = $_one['content'];
            $a_max = $_one['display_order'];
            break;
        case 2: //2-操作系统
            $b[] = $_one['content'];
            $b_max = $_one['display_order'];
            break;
        case 4: //4-分辨率
            $d[] = $_one['content'];
            $d_max = $_one['display_order'];
            break;
        default:
            break;
    }
}
//STEP-2 更新终端库
if ($syncFlag == 1) {
    //终端库重置处理：所有终端先重置为离线&不可用状态；然后以本次同步的终端信息为准，依据IMEI号更新终端信息状态。
    $db->Query("UPDATE  `ts_app_client`  SET  devState=0,isEnabled=0");
}
$result = $db->get_array("SELECT imei FROM ts_app_client");
$exit_device = Arr2toArr1($result, 'imei'); //终端库已有设备终端的IMEI（更新依据）
$row = $db->get_one("SELECT max(display_order) as max_order FROM ts_app_client");
$app_max = $row['max_order']; //排序编号
foreach ($data as $key => $_one) {
    $timestamp = time();
    $packageType = $_one['packageType']; //测试包类型ID
    $packageID = $_one['packageId']; //终端包ID
    $packageName = $_one['packageName']; //终端包名
    $businessID = $_one['businessId']; //测试业务ID
    $businessName = $_one['businessName']; //测试业务名称
    $sourceID = $_one['sourceId']; //测试来源ID
    $sourceName = $_one['sourceName']; //测试来源名称
    $devices = $_one['devices']; //本次同步的设备终端信息
    if (empty($devices)) {
        continue;
    }
    foreach ($devices as $key => $va) {
        $model = $va['manufacturer']; //手机品牌（厂商）
        $os = $va['osName'] . " " . $va['osVersion']; //系统
        $resolution = $va['height'] . "×" . $va['width']; //分辨率
        $raspberryIp = $va['raspberryIp']; //树莓IP
        $raspberryName = $va['raspberryName']; //树莓MAC地址（唯一标识）
        $sn = $va['sn']; //SN
        $imei = $va['imei']; //IMEI
        $phoneType = $va['phoneType']; //手机型号
        $devState = $va['devState']; //是否启用
        $isEnabled = $va['isEnabled']; //是否可用
        $cpuName = $va['cpuName']; //cpu型号
        $cpuFrequency = $va['cpuFrequency']; //Hz 里面存放最小hz和最大hz，以逗号分隔；如312000,1183000
        $cpuNum = $va['cpuNum']; //cpu核数
        if (!in_array($imei, $exit_device)) {
            $app_max++;
            //该终端不在已有的终端库里，插入终端库，并更新终端配置表：无尺寸（size）信息
            $sql = "INSERT INTO ts_app_client SET package_type='$packageType',packageID='$packageID',packageName='$packageName',businessID='$businessID',businessName='$businessName',sourceID='$sourceID',sourceName='$sourceName',"
                . "raspberryIp='$raspberryIp',raspberryName='$raspberryName',imei='$imei',sn='$sn',name='$phoneType',device_type='$phoneType',devState='$devState',isEnabled='$isEnabled',"
                . "cpu='$cpuName',cpu_kernel='$cpuNum',cpu_hz='$cpuFrequency',model='$model',os='$os',resolution='$resolution',display_order='$app_max',ctime='$timestamp',mtime='$timestamp'";
            //LogWrite('upSyncDevice', 'ISNERT', $sql);
            $db->Query($sql);
            //如果不存在此配置类型，则自动新增手机配置记录
            if (!in_array($model, $a)) { //1-品牌/厂商
                $a_max++;
                $sql2 = "INSERT INTO ts_app_config SET type=1,content='$model',display_order='$a_max',ctime='$timestamp',mtime='$timestamp'";
                $db->Query($sql2);
                $a[] = $model;
            }
            if (!in_array($os, $b)) { //2-系统
                $b_max++;
                $sql2 = "INSERT INTO ts_app_config SET type=2,content='$os',display_order='$b_max',ctime='$timestamp',mtime='$timestamp'";
                $db->Query($sql2);
                $b[] = $os;
            }
            if (!in_array($resolution, $d)) { //4-分辨率
                $d_max++;
                $sql3 = "INSERT INTO ts_app_config SET type=4,content='$resolution',width='" . $va['width'] . "',height='" . $va['height'] . "',display_order='$d_max',ctime='$timestamp',mtime='$timestamp'";
                $db->Query($sql3);
                $d[] = $resolution;
            }
        } else {
            //该终端在已有的终端库里，更新终端库
            $sql = "UPDATE ts_app_client SET package_type='$packageType',packageID='$packageID',packageName='$packageName',businessID='$businessID',businessName='$businessName',sourceID='$sourceID',sourceName='$sourceName',"
                . "raspberryIp='$raspberryIp',raspberryName='$raspberryName',devState='$devState',isEnabled='$isEnabled',mtime='$timestamp'"
                . " WHERE imei='$imei'";
            //LogWrite('upSyncDevice', 'UPDATE', $sql);
            $db->Query($sql);
        }
    }
}

$return['code'] = 1;
$return['message'] = "终端同步成功";
$returnStr = json_encode($return);
echo $returnStr;

LogWrite('upSyncDevice', '返回值', $returnStr . "\r\n");
