<?php

//全局变量定义
define('ROOT_PATH', '/data/zhiceyun/webroot/zhiceyun/');
define('SITE_PATH', 'http://www.smarterapps.cn/');
define('LOG_PATH', ROOT_PATH . 'logs/pay/');

//公共函数文件
require_once('../include/fun.inc.php');
//包含写日志文件
require_once('../include/log.inc.php');
//数据库链接类
require_once('../include/mysql.lib.php');

// 数据库初始化

class db_agent {

    var $mDbHost = "localhost";
    var $mDbUser = "zhiceyun_dbuser";
    var $mDbPassword = "a1v!13GkQJq";
    var $mDbPort = "3306";
    var $mDbDatabase = "zhiceyundb";

}

$db_agent = new db_agent();
$db = new Connect_Database($db_agent);
mysql_query("SET NAMES UTF8");
?>