<?php

/*
 * 写日志
 * $interface-接口
 * $content-内容
 * $describe 描述
 * $log_path 日志路径
 */

function LogWrite($interface = '', $content = '', $describe = '', $log_path = LOG_API_PATH) {
    $log_path = $log_path . date("Y") . "/" . date("m") . "/";
    CreateAllDir($log_path);
    $now = date('Y-m-d H:i:s');
    $strlog = $now . "\t" . $content . "\t" . $describe . "\r\n";
    $pdate = date('Ymd');
    $destination = $log_path . $interface . "_" . $pdate . ".txt";
    error_log($strlog, 3, $destination);
}

/*
 * 将数据库SQL语句输出成文本
 */

function SqlWrite($sql, $log_path = LOG_SQL_PATH) {
    $now = date('Y-m-d H:i:s');
    $strlog = $now . "\t" . $sql . "\r\n";
    $pdate = date('Ymd');
    $destination = $log_path . "-" . $pdate . ".sql";
    error_log($strlog, 3, $destination);
}

?>