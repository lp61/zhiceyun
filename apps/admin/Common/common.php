<?php

function formatsize($fileSize) {
    $size = sprintf("%u", $fileSize);
    if ($size == 0) {
        return("0 Bytes");
    }
    $sizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizename[$i];
}

//获取测试业务标题
function getBusinessTitle($businessID) {
    return M('app_test_package')->where("businessID='$businessID'")->getField('business_title');
}

//获取测试包标题
function getPackageTitle($package_type) {
    return M('app_test_package')->where("package_type='$package_type'")->getField('title');
}

/*
 * $status  下发状态
 * $state   测试状态
 */

function get_Send_status($status, $state, $send_num, $retry_device_type) {
    if ($status == 4) {
        return "网络连接异常";
    }
    if ($state == 0) { //未开始
        switch ($status) {
            case -1: //apk上传中
                $title = "APK上传中";
                break;
            case 1: //排队中
                $title = "排队中-下发次数(" . $send_num . ")";
                break;
            case 2: //下发失败
                $title = "排队中（下发失败）";
                break;
        }
    } elseif ($state == 1) { //测试中
        switch ($status) {
            case 0: //下发成功
                $title = "测试中";
                break;
            case 2: //下发失败
                $title = "排队中（下发失败）";
                break;
            case 3: //重测
                $title = "测试中-重测终端(" . $retry_device_type . ")";
                break;
            case 5: //apk异常
                $title = "apk文件异常";
                break;
        }
    } elseif ($state == 2) {
        $title = "测试完成";
    } else {
        $title = "测试失败-失败终端(" . $retry_device_type . ")";
    }
    return $title;
}

function get_date($timestamp) {
    if ($timestamp <= 0) {
        return '';
    }
    return date("Y-m-d H:i", $timestamp);
}

function getPayStatus($key) {
    if ($key == 1) {
        return "已支付";
    } else {
        return "未支付";
    }
}

function getBillStatus($status) {
    switch ($status) {
        case 0:
            $show = "待审核";
            break;
        case 1:
            $show = "已确认";
            break;
        case 2:
            $show = "已拒绝";
            break;
    }
    return $show;
}

/*
 * 解析查询条件数组，生成查询条件
 */

function get_map($map) {
    if (empty($map)) {
        return "";
    }

    foreach ($map as $key => $value) {
        if (!is_array($value)) {
            $str .= "`" . $key . "` = '" . $value . "' AND ";
        } elseif (in_array($value[0], array('in', 'not in', 'between'))) {
            $str .= "`" . $key . "` " . $value[0] . " " . $value[1] . " AND ";
        } else {
            $str .= "`" . $key . "` " . $value[0] . " '" . $value[1] . "' AND ";
        }
    }
    return substr($str, 0, -4);
}

function getPackageStatus($status) {
    switch ($status) {
        case 1:
            $show = "正常";
            break;
        case 2:
            $show = "忙碌(请检查包状态；如是自定义终端任务或重测任务，请检查指定终端状态)";
            break;
        case 3:
            $show = "终端不足(请检查包内在线可用终端数)";
            break;
        case 4:
            $show = "网络错误（请检查内网服务器是否连接正常）";
            break;
        case 5:
            $show = "未知异常（请联系后台）";
            break;
    }
    return $show;
}

function getPhoneState($devState) {
    if ($devState == 1) {
        $show = "在线";
    } else {
        $show = "离线";
    }
    return $show;
}

function getPhoneEnable($isEnabled) {
    if ($isEnabled == 1) {
        $show = "启用";
    } else {
        $show = "禁用";
    }
    return $show;
}
