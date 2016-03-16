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
function getResult($status){
    switch ($status) {
        case 1:
            $show = "通过";
            break;
        case 0:
            $show = "未通过";
            break;
    }
    return $show;  
}

//获取apk名
function getapkname($uuid){
     return M('app_test_task')->where("uuid='$uuid'")->getField('apk_name');
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

//是否已设置头像
function isSetAvatar($uid) {
    return is_file(SITE_PATH . '/data/uploads/avatar/' . $uid . '/small.jpg');
}

//获取微博条数
function getMiniNum($uid) {
    return M('weibo')->where('uid=' . $uid . ' AND isdel=0')->count();
}

//获取关注数
function getUserFollow($uid) {
    $count['following'] = M('weibo_follow')->where("uid=$uid AND type=0")->count();
    $count['follower'] = M('weibo_follow')->where("fid=$uid AND type=0")->count();
    return $count;
}

// 短地址
function getContentUrl($url) {
    return getShortUrl($url[1]) . ' ';
}

// 登录页微博表情解析
function login_emot_format($content) {
    return preg_replace_callback('/(?:#[^#]*[^#^\s][^#]*#|(\[.+?\]))/is', 'replaceEmot', $content);
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
// 测试类型
function getTaskType($status) {
    switch ($status) {
        case 1:
            $show = "快速";
            break;
        case 2:
            $show = "兼容";
            break;
        case 3:
            $show = "网络友好";
            break;
        case 4:
            $show = "新机兼容";
            break;
        case 5:
            $show = "专机";
            break;
    }
    return $show;
}
function getTaskStatus($status) {
    switch ($status) {
        case 0:
            $show = "排队中";
            break;
        case 1:
        case 3:
            $show = "测试中";
            break;
        case 2:
            $show = "测试完成";
            break;
    }
    return $show;
}

