<?php
/*
 * 说明：游客访问的黑/白名单，不需要开放的，可以注释掉、或删除
 * 规则：设置的key由APP_NAME/MODULE_NAME/ACTION_NAME组成，只要设置在当前数组中，游客就可以访问
 * 例如：设置成‘blog/Index/news’ => true, 用户就可以访问最新博客页面，否则必须先登录到系统才能访问
 */
return array(
    "access"    =>  array(
        //核心模块
        'home/Appinfo/*'            => true, // 应用测试相关
        'home/Public/*'             => true, // 公共模块注册、登录等,不可删除
        'home/Index/index'          => true, // 默认首页
        'home/Space/*'              => true, // 个人空间
        'home/Attach/*'             => true, // 上传文件
        'home/Apptest/*'            => true, // 应用测试
        'api/*/*'                   => true, // Api接口
        'admin/*/*'                 => true, // 管理后台的权限由它自己控制,不可删除
        
        'home/TestRecord/*'            => true, // 测试报告
	
        'wap/*/*'                   => true, // 默认首页
    )
);