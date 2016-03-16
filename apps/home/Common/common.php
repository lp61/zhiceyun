<?php

//获取测试业务标题
function getBusinessTitle($businessID) {
    return M('app_test_package')->where("businessID='$businessID'")->getField('business_title');
}

//获取测试业务标题
function getPackageTitle($businessID) {
    return M('app_test_package')->where("businessID='$businessID'")->getField('title');
}

//获取测试结果
function getResult($status) {
    switch ($status) {
        case 1:
            $show = "通过";
            break;
        case 0:
            $show = "未通过";
            break;
        case -1:
            $show = '--';
            break;
    }
    return $show;
}

//获取apk名
function getapkname($uuid) {
    return M('app_test_task')->where("uuid='$uuid'")->getField('apk_name');
}

//获取网络状况
function getNet($status) {
    switch ($status) {
        case 0:
            $show = "无网络";
            break;
        case 1:
            $show = "WIFI";
            break;
        case 2:
            $show = "其他网络";
            break;
        default :
            $show = "未知";
    }
    return $show;
}

//获取交易项目类型
function getTradeType($key) {
    switch ($key) {
        case 'pay_ment':
            $show = "充值";
            break;
        case '':
            $show = "积分消耗";
            break;
        default:
            $show = "网站奖励";
            break;
    }
    return $show;
}

//获取花费时间
function getCostTime($start, $end) {
    if (empty($end)) {
        $cost = time() - $start;
    } else {
        $cost = $end - $start;
    }
    $day = floor($cost / (3600 * 24));
    $hour = floor(($cost % (3600 * 24)) / 3600);
    if ($day > 0) {
        $title = $hour > 0 ? $day . "天" . $hour . "小时" : $day . "天";
    } else {
        $title = $hour > 0 ? $hour . "小时" : ceil(($cost % 3600) / 60) . "分钟";
    }
    return $title;
}

// 短地址
function getContentUrl($url) {
    return getShortUrl($url[1]) . ' ';
}

/**
 * [getMenuState 检测tab是否选择状态]
 * @param  [type]  $url    [description]
 * @param  boolean $params [参数，其中class是控制输出样式]
 * @return [type]          [description]
 */
function getMenuState($url, $params = false) {
    $retString = ' class="on"';

    if (empty($url)) {
        return;
    }
    $urlArr = explode('/', $url);

    $allowArr = array(
        3 => APP_NAME . '/' . MODULE_NAME . '/' . ACTION_NAME,
        2 => APP_NAME . '/' . MODULE_NAME . '/*',
        1 => APP_NAME . '/*/*',
    );
    $count = count($urlArr);
    if ($count < 3) {
        $fillArr = array_fill($count - 1, 3 - $count, '*');
        $urlArr = array_merge($urlArr, $fillArr);
    }
    $urlString = implode('/', $urlArr);

    // 目录检测
    if (strtoupper($urlString) != strtoupper($allowArr[$count])) {
        return;
    }

    // 参数检测
    if ($params && is_array($params)) {
        if (isset($params['class'])) {
            $retString = ' class="' . $params['class'] . '"';
            unset($params['class']);
        }
        foreach ($params as $k => $v) {
            if ($v != $_REQUEST[$k])
                return;
        }
    }

    return $retString;
}

function getUserType($type) {
    $show = "";
    switch ($type) {
        case 0:
            $show = "普通用户";
            break;
        case 1:
            $show = "VIP用户";
            break;
        case 2:
            $show = "企业用户";
            break;
    }
    return $show;
}

function getEmail($uid) {
    $title = M('user')->where("uid='$uid'")->getField('email');
    if (empty($title)) {
        $title = '游客';
    }
    return $title;
}
