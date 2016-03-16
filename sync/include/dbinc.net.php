<?php

/*
 * 网络友好——测试结果数据库连接
 */

class sdb_agent {

    var $mDbHost = "10.1.10.168";
    var $mDbUser = "zengchenxi";
    var $mDbPassword = "000000";
    var $mDbPort = "3306";
    var $mDbDatabase = "datadump";

}

$sdb_agent = new sdb_agent();
$sdb = new Connect_Database($sdb_agent);