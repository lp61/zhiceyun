<?php
if (!defined('SITE_PATH')) exit();

return array(
    // 数据库常用配置
    'DB_TYPE'           =>  'mysql',            // 数据库类型
    'DB_HOST'           =>  'localhost',            // 数据库服务器地址
    'DB_NAME'           =>  'zhiceyundb',         // 数据库名
    'DB_USER'           =>  'root',     // 数据库用户名
    'DB_PWD'            =>  '',     // 数据库密码
    'DB_PORT'           =>  3306,               // 数据库端口
    'DB_PREFIX'         =>  'ts_',      // 数据库表前缀（因为漫游的原因，数据库表前缀必须写在本文件）
    'DB_CHARSET'        =>  'utf8',             // 数据库编码
    'DB_FIELDS_CACHE'   =>  true,               // 启用字段缓存

    //'COOKIE_DOMAIN'   =>  'www.smarterapps.cn',  //cookie域,请替换成你自己的域名 以.开头

    //Cookie加密密码
    'SECURE_CODE'       =>  'zhiceyun18#c&',

    // 默认应用
    'DEFAULT_APPS'      => array('admin', 'home', 'cron', 'wap'),

    // 是否开启URL Rewrite
    'URL_ROUTER_ON'     => TRUE,

    // 是否开启调试模式 (开启AllInOne模式时该配置无效, 将自动置为false)
    'APP_DEBUG'         => TRUE,
        
    'TEST_OTHERS_SITE'  => FALSE, // 三方测试

    'ADMIN_DOMAIN' => 'admin.smarterapps.cn', // 后台管理的域名

    'HOME_DOMAIN'  => 'www.smarterapps.cn', // 前台管理的域名
);