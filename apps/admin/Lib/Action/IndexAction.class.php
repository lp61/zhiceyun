<?php

class IndexAction extends AdministratorAction {

    //后台框架页
    public function index() {
        $this->assign('channel', $this->_getChannel());
        $this->assign('menu', $this->_getMenu());
        $this->display();
    }

    protected function _getChannel() {
        return array(
            'index' => '首页',
            'global' => '平台配置',
            'user' => '用户管理',
            'content' => '内容管理',
            'log' => '日志信息',
        );
    }

    protected function _getMenu() {
        $menu = array();

        // 后台管理首页
        $menu['index'] = array(
            '首页' => array(
                '首页' => U('admin/Home/statistics'),
                '缓存更新' => SITE_URL . '/cleancache.php?all',
            ),
        );

        //平台配置
        $menu['global'] = array(
            '全局配置' => array(
                '站点配置' => U('admin/Global/siteopt'),
                '积分配置' => U('admin/Global/credit'),
                '邮件配置' => U('admin/Global/email'),
            ),
            '数据统计' => array(
                '用户统计' => U('admin/Global/userStatics'),
                '测试任务统计' => U('admin/Global/taskStatics'),
            ),
        );

        //用户管理
        $menu['user'] = array(
            '用户' => array(
                '用户管理' => U('admin/User/user'),
                '用户组管理' => U('admin/User/userGroup'),
                '消息群发' => U('admin/User/message'),
                '登录日志' => U('admin/Login/index'),
            ),
            '权限' => array(
                '节点管理' => U('admin/User/node'),
                '权限管理' => U('admin/User/popedom'),
            ),
        );

        //内容管理
        $menu['content'] = array(
            '内容管理' => array(
                'FAQ设置' => U('admin/Content/faq'),
                '行业报告' => U('admin/Content/appArticle'),
                '用户反馈' => U('admin/Content/feedback'),
                'IOT信息' => U('admin/Content/ask_iot'),
                '终端体验' => U('admin/Content/ask_experince'),
            ),
            '终端管理' => array(
                '终端配置' => U('admin/Content/appSystemConfig'),
                '手机终端' => U('admin/Content/appPhone'),
                '测试包类型' => U('admin/Content/appPackage'),
                '测试配置' => U('admin/Content/appConfig'),
            ),
            '支付管理' => array(
                '订单管理' => U('admin/Content/order'),
                '发票管理' => U('admin/Content/bill'),
            ),
            'hadoop管理' => array(
                '文件管理' => U('admin/Hadoop/hadoopInfo'),
            ),
        );

        //日志信息
        $menu['log'] = array(
            '日志信息' => array(
                '积分记录' => U('admin/Log/creditLog'),
                '附件管理' => U('admin/Content/attach'),
                '后台操作日志' => U('admin/Content/adminLog'),
                '邮件发送记录' => U('admin/Log/sendEmail'),
                '邀请统计' => U('admin/Tool/inviteRecord'),
            ),
        );

        return $menu;
    }

}
