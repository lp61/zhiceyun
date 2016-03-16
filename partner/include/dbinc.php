<?php

//公共函数文件
require_once('fun.inc.php');
//数据库链接类
require_once('mysql.lib.php');
//包含写日志文件
require_once('log.inc.php');

// 数据库初始化

class db_agent {

    var $mDbHost = "42.96.171.6";
    var $mDbUser = "zhiceyun_dbuser";
    var $mDbPassword = "a1v!13GkQJq";
//    var $mDbUser = "yuanxi";
//    var $mDbPassword = "a1b2#4v%^&*1234567890";
    var $mDbPort = "3306";
    var $mDbDatabase = "zhiceyundb";

}

$db_agent = new db_agent();
$db = new Connect_Database($db_agent);

?>