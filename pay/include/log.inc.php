<?php

/*
 * 写日志
 * $interface-接口
 * $page-前端页面
 * $content-内容
 * $describe 描述
 * $path 日志路径
 */

function LogWrite($interface = '', $content = '', $describe = '', $log_path = LOG_PATH) {
    global $IS_LOGIN, $appname, $player_info;
    $pid = $player_info['id'];
    $pin = $player_info['pin'];
    $mobile = $player_info['mobile'];
    $now = date('Y-m-d H:i:s');
    $strlog = $now . "\t" . $pin . "\t" . $interface . "\t" . $content . "\t" . $describe . "\r\n";
    $pdate = date('Ymd');
    if ($IS_LOGIN == 0) {
        $pid = $appname;
    }
    $destination = $log_path . $pid . "-" . $mobile . "-" . $pdate . ".txt";
    error_log($strlog, 3, $destination);
}

/*
 * 将数据库SQL语句输出成文本
 */

function SqlWrite($sql, $log_path = LOG_PATH) {
    global $IS_LOGIN, $appname, $player_info, $start_time;
    $pid = $player_info['id'];
    $now = date('Y-m-d H:i:s');
    $pdate = date('Ymd');
    if ($IS_LOGIN == 0) {
        $pid = $appname;
    }
    $destination = $log_path . $pid . "-" . $pdate . ".sql";
    $str = $now . "\t" . $start_time . "\t" . $sql . "\r\n";
    error_log($str, 3, $destination);
}

?>