<?php

import('home.Form.AppTestVars');

class GlobalAction extends AdministratorAction {

    private function __isValidRequest($field, $array = 'post') {
        $field = is_array($field) ? $field : explode(',', $field);
        $array = $array == 'post' ? $_POST : $_GET;
        foreach ($field as $v) {
            $v = trim($v);
            if (!isset($array[$v]) || $array[$v] == '')
                return false;
        }
        return true;
    }

    /** 系统配置 - 站点配置 * */
    //站点设置
    public function siteopt() {
        $site_opt = model('Xdata')->lget('siteopt');
        if (!$site_opt['site_logo']) {
            $site_opt['site_logo'] = 'logo.png';
            $this->assign('site_logo', THEME_URL . '/images/' . $site_opt['site_logo']);
        }
        $this->assign($site_opt);
        require_once ADDON_PATH . '/libs/Io/Dir.class.php';
        $theme_list = new Dir(SITE_PATH . '/public/themes/');
        $expression_list = new Dir(SITE_PATH . '/public/themes/' . $site_opt['site_theme'] . '/images/expression/');
        $this->assign('expression_list', $expression_list->toArray());
        $this->assign('theme_list', $theme_list->toArray());

        $this->display();
    }

    //设置站点
    public function doSetSiteOpt() {
        if (empty($_POST)) {
            $this->error('参数错误');
        }

        //验证数字参数
        if (intval($_POST['max_post_time']) < 0 || intval($_POST['max_refresh_time']) < 0 || intval($_POST['max_following']) < 0 || intval($_POST['max_search_time']) < 0
        ) {
            $_POST['max_post_time'] = 5; //全站评论发布频率限制 s
            $_POST['max_refresh_time'] = 2; //页面刷新频率限制 s
            $_POST['max_following'] = 1000; //用户关注人数限制 人
            $_POST['max_search_time'] = 5; //全站搜索频率限制 s
            //$this->error('数字变量的值必须大于等于0');
        }
        $_POST['max_post_time'] = intval($_POST['max_post_time']);
        $_POST['max_refresh_time'] = intval($_POST['max_refresh_time']);
        $_POST['max_following'] = intval($_POST['max_following']);
        $_POST['max_search_time'] = intval($_POST['max_search_time']);

        if (intval($_POST['length']) <= 0) {
            $_POST['length'] = 140;
            //$this->error('全站评论字数限制的值必须大于0');
        }

        //保存LOGO
        if (!empty($_FILES['site_logo']['name'])) {
            $logo_options['save_to_db'] = false;
            $logo = X('Xattach')->upload('site_logo', $logo_options);
            if ($logo['status']) {
                $logofile = UPLOAD_URL . '/' . $logo['info'][0]['savepath'] . $logo['info'][0]['savename'];
            }
            $_POST['site_logo'] = $logofile;
        }

        if (!empty($_FILES['banner_logo']['name'])) {
            $logo_options['save_to_db'] = false;
            $logo = X('Xattach')->upload('site_logo', $logo_options);
            if ($logo['status']) {
                $logofile = UPLOAD_URL . '/' . $logo['info'][0]['savepath'] . $logo['info'][0]['savename'];
            }
            $_POST['banner_logo'] = $logofile;
        }



        $_POST['site_name'] = t($_POST['site_name']);
        $_POST['site_slogan'] = t($_POST['site_slogan']);
        $_POST['site_header_keywords'] = t($_POST['site_header_keywords']);
        $_POST['site_header_description'] = t($_POST['site_header_description']);
        $_POST['site_closed'] = intval($_POST['site_closed']);
        $_POST['site_closed_reason'] = t($_POST['site_closed_reason']);
        $_POST['site_icp'] = t($_POST['site_icp']);
        $_POST['site_verify'] = isset($_POST['site_verify']) ? $_POST['site_verify'] : '';
        $_POST['expression'] = t($_POST['expression']);
        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '3';
        $data[] = '全局 - 站点配置 ';
        $site_opt = model('Xdata')->lget('siteopt');
        $data[] = $site_opt;
        if ($_POST['__hash__'])
            unset($_POST['__hash__']);
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = model('Xdata')->lput('siteopt', $_POST);
        if ($res) {
            //表情需要flush一下
            model('Expression')->getAllExpression(true);
            $this->assign('jumpUrl', U('admin/Global/siteopt'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    /** 系统配置 - 积分配置 * */
    //积分类别设置
    public function creditType() {
        $creditType = M('credit_type')->order('id ASC')->findAll();
        $this->assign('creditType', $creditType);
        $this->display();
    }

    public function editCreditType() {
        $type = $_GET['type'];
        if ($cid = intval($_GET['cid'])) {
            $creditType = M('credit_type')->where("`id`=$cid")->find(); //积分类别
            if (!$creditType)
                $this->error('无此积分类型');
            $this->assign('creditType', $creditType);
        }

        $this->assign('type', $type);
        $this->display();
    }

    public function doAddCreditType() {
        // if ( !$this->__isValidRequest('name') ) $this->error('数据不完整');
        $name = h(t($_POST['name']));
        $alias = h(t($_POST['alias']));
        if (empty($name)) {
            $this->error('名称不能为空');
        }
        if (empty($alias)) {
            $this->error('别名不能为空');
        }

        $_POST = array_map('t', $_POST);
        $_POST = array_map('h', $_POST);

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '1';
        $data[] = '全局 - 积分配置  - 积分类型';

        if ($_POST['__hash__'])
            unset($_POST['__hash__']);

        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('credit_type')->add($_POST);
        if ($res) {
            $db_prefix = C('DB_PREFIX');
            $model = M('');

            $addFieldSql = "ALTER TABLE {$db_prefix}%s ADD {$_POST['name']} INT(11) DEFAULT 0;";
            $setting = $model->query(sprintf($addFieldSql, 'credit_setting'));
            $user = $model->query(sprintf($addFieldSql, 'credit_user'));
            $log = $model->query(sprintf($addFieldSql, 'credit_user_log'));

            // 清缓存
            F('_service_credit_type', null);
            $this->assign('jumpUrl', U('admin/Global/creditType'));
            $this->success('保存成功,请执行"首页 - 缓存更新"清除缓存，否则将不起效！！！！');
        } else {
            $this->error('保存失败');
        }
    }

    public function doEditCreditType() {
        // if ( !$this->__isValidRequest('id,name') ) $this->error('数据不完整');
        $name = h(t($_POST['name']));
        $alias = h(t($_POST['alias']));
        if (empty($name)) {
            $this->error('名称不能为空');
        }
        if (empty($alias)) {
            $this->error('别名不能为空');
        }
        $_POST = array_map('t', $_POST);
        $_POST = array_map('h', $_POST);
        $creditTypeDao = M('credit_type');
        //获取原字段名
        $oldName = $creditTypeDao->find($_POST['id']);
        //修改字段名
        $res = $creditTypeDao->save($_POST);

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '3';
        $data[] = '全局 - 积分配置 - 积分类型 ';
        $data[] = $oldName;
        if ($_POST['__hash__'])
            unset($_POST['__hash__']);
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        if ($res) {
            $db_prefix = C('DB_PREFIX');
            $model = M('');
            $changeFieldSql = "ALTER TABLE {$db_prefix}%s CHANGE {$oldName['name']} {$_POST['name']} INT(11) DEFAULT 0;";
            $setting = $model->query(sprintf($changeFieldSql, 'credit_setting'));
            $user = $model->query(sprintf($changeFieldSql, 'credit_user'));
            $log = $model->query(sprintf($changeFieldSql, 'credit_user_log'));

            // 清缓存
            F('_service_credit_type', null);
            $this->assign('jumpUrl', U('admin/Global/creditType'));
            $this->success('保存成功,请执行"首页 - 缓存更新"清除缓存，否则将不起效！！！！');
        } else {
            $this->error('保存失败');
        }
    }

    public function doDeleteCreditType() {
        $ids = t($_POST['ids']);
        $ids = explode(',', $ids);
        if (empty($ids)) {
            echo 0;
            return;
        }

        $map['id'] = array('in', $ids);
        $creditTypeDao = M('credit_type');
        //获取字段名
        $typeName = $creditTypeDao->where($map)->findAll();

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '全局 - 积分配置 - 积分类型 ';
        $data[] = $typeName;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        //清除type信息和对应字段
        $res = M('credit_type')->where($map)->delete();
        if ($res) {
            $db_prefix = C('DB_PREFIX');
            $model = M('');
            foreach ($typeName as $v) {
                $setting = $model->query("ALTER TABLE {$db_prefix}credit_setting DROP {$v['name']};");
                $user = $model->query("ALTER TABLE {$db_prefix}credit_user DROP {$v['name']};");
                $log = $model->query("ALTER TABLE {$db_prefix}credit_user_log DROP {$v['name']};");
            }
            // 清缓存
            F('_service_credit_type', null);
            echo 1;
        } else {
            echo 0;
        }
    }

    //积分规则设置
    public function credit() {
        $list = M('credit_setting')->order('id ASC')->findPage(30);
        $creditType = M('credit_type')->order('id ASC')->findAll();
        $this->assign('creditType', $creditType);
        $this->assign($list);
        $this->display();
    }

    public function addCredit() {
        $creditType = M('credit_type')->order('id ASC')->findAll(); //积分类别
        $this->assign('creditType', $creditType);
        $this->assign('type', 'add');
        $this->display('editCredit');
    }

    public function doAddCredit() {
        $name = trim($_POST['name']);
        if ($name == "" && $_POST['name'] != "") {
            $this->error('名称不能为空格');
        }
        if (!$this->__isValidRequest('name'))
            $this->error('数据不完整');

        $_POST = array_map('t', $_POST);
        $_POST = array_map('h', $_POST);

        $creditType = M('credit_type')->order('id ASC')->findAll();
        foreach ($creditType as $v) {
            if (!is_numeric($_POST[$v['name']])) {
                $this->error($v['alias'] . '的值必须为数字！');
            }
        }

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '1';
        $data[] = '全局 - 积分配置 - 积分规则 ';
        if ($_POST['__hash__'])
            unset($_POST['__hash__']);
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('credit_setting')->add($_POST);
        if ($res) {
            // 清缓存
            F('_service_credit_rules', null);
            $this->assign('jumpUrl', U('admin/Global/credit'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function editCredit() {
        $cid = intval($_GET['cid']);
        $credit = M('credit_setting')->where("`id`=$cid")->find();
        if (!$credit)
            $this->error('无此积分规则');

        $creditType = M('credit_type')->order('id ASC')->findAll(); //积分类别
        $this->assign('creditType', $creditType);

        $this->assign('credit', $credit);
        $this->assign('type', 'edit');
        $this->display();
    }

    public function doEditCredit() {
        $name = trim($_POST['name']);
        if ($name == "" && $_POST['name'] != "") {
            $this->error('名称不能为空格');
        }
        if (!$this->__isValidRequest('id,name'))
            $this->error('数据不完整');

        $_POST = array_map('t', $_POST);
        $_POST = array_map('h', $_POST);

        $creditType = M('credit_type')->order('id ASC')->findAll();
        foreach ($creditType as $v) {
            if (!is_numeric($_POST[$v['name']])) {
                $this->error($v['alias'] . '的值必须为数字！');
            }
        }

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '3';
        $data[] = '全局 - 积分配置 - 积分规则 ';
        $credit_info = M('credit_setting')->where('id=' . $_POST['id'])->find();
        $data[] = $credit_info;
        $_POST['info'] = $credit_info['info'];

        if ($_POST['__hash__'])
            unset($_POST['__hash__']);

        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('credit_setting')->save($_POST);
        if ($res) {
            // 清缓存
            F('_service_credit_rules', null);
            $this->assign('jumpUrl', U('admin/Global/credit'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function doDeleteCredit() {
        $ids = t($_POST['ids']);
        $ids = explode(',', $ids);
        if (empty($ids)) {
            echo 0;
            return;
        }

        $map['id'] = array('in', $ids);

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '全局 - 积分配置 - 积分规则 ';
        $data[] = M('credit_setting')->where('id=' . $_POST['id'])->find();
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('credit_setting')->where($map)->delete();
        if ($res) {
            // 清缓存
            F('_service_credit_rules', null);
            echo 1;
        } else {
            echo 0;
        }
    }

    //批量用户积分设置
    public function creditUser() {
        $creditType = M('credit_type')->order('id ASC')->findAll();
        $this->assign('creditType', $creditType);
        $this->assign('grounlist', model('UserGroup')->getUserGroupByMap('', 'user_group_id,title'));
        $this->display();
    }

    public function doCreditUser() {
        set_time_limit(0);
        //查询用户ID
        $_POST['uId'] && $map['uid'] = array('in', explode(',', t($_POST['uId'])));
        $_POST['active'] != 'all' && $map['is_active'] = intval($_POST['active']);
        $model = D('User', 'home');
        $user = $model->where($map)->field('uid')->findAll();
        if ($_POST['gId'] != 'all') {
            if ($user != FALSE) {
                foreach ($user as $v) {
                    $a[] = $v['uid'];
                }
                $where['uid'] = array('in', $a);
            }
            $where['user_group_id'] = intval($_POST['gId']);
            $user = M('user_group_link')->where($where)->field('uid')->findAll();
        }
        if ($user == false) {
            $this->error('查询失败，没有这样条件的人');
        }
        //组装积分规则
        $setCredit = X('Credit');
        $creditType = $setCredit->getCreditType();
        foreach ($creditType as $v) {
            $action[$v['name']] = intval($_POST[$v['name']]);
        }

        if ($_POST['action'] == 'set') {//积分修改为
            $action['alias'] = '管理员重置';
            foreach ($user as $v) {
                $setCredit->setUserCredit($v['uid'], $action, 'reset');
                if ($setCredit->getInfo() === false)
                    $this->error('保存失败');
            }
        }else {//增减积分
            $action['alias'] = $_POST['score'] > 0 ? '系统奖励' : '系统扣减';
            foreach ($user as $v) {
                $setCredit->setUserCredit($v['uid'], $action);
                if ($setCredit->getInfo() === false)
                    $this->error('保存失败');
            }
        }

        $this->assign('jumpUrl', U('admin/Global/creditUser'));

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '1';
        if ($_POST['action'] == 'set') {
            $data[] = '全局 - 积分配置 - 设置用户积分 - 积分修改操作 ';
        } else {
            $data[] = '全局 - 积分配置 - 设置用户积分 - 积分增减操作 ';
        }
        $data['1'] = $action;
        $data['1']['uid'] = $_POST['uId'];
        $data['1']['gId'] = $_POST['gId'];
        $data['1']['active'] = $_POST['active'];
        $data['1']['action'] = $_POST['action'];
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $this->success('保存成功');
    }

    /** 系统配置 - 邮件配置 * */
    public function email() {
        if ($_POST) {
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = '全局 - 邮件配置 ';
            $data[] = model('Xdata')->lget('email');
            if ($_POST['__hash__'])
                unset($_POST['__hash__']);
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);

            unset($_POST['__hash__']);
            model('Xdata')->lput('email', $_POST);
            $this->assign('jumpUrl', U('admin/Global/email'));
            $this->success('保存成功');
        }else {
            $email = model('Xdata')->lget('email');
            $this->assign($email);
            $this->display();
        }
    }

    public function testSendEmail() {
        $service = service('Mail');
        $subject = '这是一封测试邮件';
        $content = '这是一封来自' . SITE_URL . '的测试邮件，您能收到这封邮件表明邮件服务器已配置正确。<br />
					如果您不清楚这封邮件的来由，请删除，为给您带来的不便表示歉意';
        echo ( $info = $service->send_email($_POST['testSendEmailTo'], $subject, $content) ) ? $info : '1';
    }

    public function userStatics() {
        if ($_REQUEST['statics_type'] == 1) {
            $type = '%Y-%m';
            $map['ctime'] = array('>=', strtotime(date('Y', time()) . "-01-01")); //默认显示近一年的数据
        } else {
            $type = '%Y-%m-%d';
            $map['ctime'] = array('>=', strtotime(date('Y-m-d', time())) - 30 * 24 * 3600); //默认显示近一月数据
        }
        if ($_POST) {
            if (!empty($_POST['pstart'])) {
                $map['ctime'] = array('>=', strtotime($_POST['pstart']));
            } else {
                unset($map['ctime']);
            }
            if (!empty($_POST['pend'])) {
                if (!empty($_POST['pstart'])) {
                    $map['ctime'] = array('between', strtotime($_POST['pstart']) . " and " . (strtotime($_POST['pend']) + 24 * 3600));
                } else {
                    $map['ctime'] = array('<=', strtotime($_POST['pend']) + 24 * 3600);
                }
            }
            if ($_POST['user_type'] != '') {
                $map['user_type'] = $_POST['user_type'];
            }
            if ($_POST['is_active'] != '') {
                $map['is_active'] = $_POST['is_active'];
            }
            if ($_POST['status'] != '') {
                if ($_POST['status'] == 0) {
                    $map['status'] = array('>=', '0');
                } else {
                    $map['status'] = $_POST['status'];
                }
            }
            if ($_POST['source'] != '') {
                $map['source'] = $_POST['source'];
            }
            $this->assign('isSearch', 1);
        }
        if (empty($map)) {
            $sql = "SELECT COUNT(1) AS sel_count, FROM_UNIXTIME(ctime, '" . $type . "') AS sel_month FROM `ts_user` GROUP BY sel_month asc";
        } else {
            $map_str = get_map($map);
            $sql = "SELECT COUNT(1) AS sel_count, FROM_UNIXTIME(ctime, '" . $type . "') AS sel_month FROM `ts_user` WHERE " . $map_str . " GROUP BY sel_month asc";
        }
        //dump($sql);
        $res = M()->query($sql);
        $max = 0;
        foreach ($res as $_one) {
            $selCountArr[] = (int) $_one['sel_count'];
            $selMonthArr[] = $_one['sel_month'];
            $max = max($_one['sel_count'], $max);
        }
        $this->assign('selCountArr', $selCountArr);
        $this->assign('selMonthArr', $selMonthArr);
        $this->assign('max', intval($max * 1.1));
        $this->assign('search', serialize($map));
        $this->assign('map', $map);
        $this->display();
    }

    public function StaticsExcel() {
        if ($_GET['statics_type'] == 1) {
            $type = '%Y-%m';
        } else {
            $type = '%Y-%m-%d';
        }
        include_once( SITE_PATH . '/addons/libs/PHPExcel.php' );
        if (empty($_GET['map'])) {
            $sql = "SELECT COUNT(1) AS sel_count, FROM_UNIXTIME(ctime, '" . $type . "') AS sel_month FROM `ts_user` GROUP BY sel_month asc";
        } else {
            $map_str = get_map($_GET['map']);
            $sql = "SELECT COUNT(1) AS sel_count, FROM_UNIXTIME(ctime, '" . $type . "') AS sel_month FROM `ts_user` WHERE " . $map_str . " GROUP BY sel_month asc";
        }
        $res = M()->query($sql);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '注册日期')
            ->setCellValue('B1', '注册人数');

        $n = 2;
        foreach ((array) $res as $key => $val) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $n, ' ' . $val['sel_month'])
                ->setCellValue('B' . $n, ' ' . $val['sel_count']);
            $n ++;
        }
        $objPHPExcel->setActiveSheetIndex(0);
        $filename = '用户统计信息.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

        exit;
    }

    public function taskStatics() {
        if ($_REQUEST['statics_type'] == 1) {
            $type = '%Y-%m';
            $map['ctime'] = array('>=', strtotime(date('Y', time()) . "-01-01")); //默认显示近一年的数据
        } else {
            $type = '%Y-%m-%d';
            $map['ctime'] = array('>=', strtotime(date('Y-m-d', time())) - 30 * 24 * 3600); //默认显示近一月数据
        }
        if ($_POST) {
            if (intval($_POST['user_id']) > 0) {
                $map['user_id'] = $_POST['user_id'];
            }
            if (!empty($_POST['pstart'])) {
                $map['ctime'] = array('>=', strtotime($_POST['pstart']));
            }
            if (!empty($_POST['pend'])) {
                if (!empty($_POST['pstart'])) {
                    $map['ctime'] = array('between', strtotime($_POST['pstart']) . " and " . (strtotime($_POST['pend']) + 24 * 3600));
                } else {
                    $map['ctime'] = array('<=', strtotime($_POST['pend']) + 24 * 3600);
                }
            }
            if (intval($_POST['apk_type']) > 0) {
                $map['apk_type'] = $_POST['apk_type'];
            }
            if (intval($_POST['businessID']) > 0) {
                $map['businessID'] = $_POST['businessID'];
            }
            $this->assign('isSearch', 1);
        }
        $map['status'] = 1; //提交成功的任务
        $map_str = get_map($map);
        $sql = "SELECT COUNT(1) AS sel_count, FROM_UNIXTIME(ctime, '" . $type . "') AS sel_month FROM `ts_app_test_task` WHERE " . $map_str . " GROUP BY sel_month asc";
        //dump($sql);
        $res = M()->query($sql);
        $max = 0;
        foreach ($res as $_one) {
            $selCountArr[] = (int) $_one['sel_count'];
            $selMonthArr[] = $_one['sel_month'];
            $max = max($_one['sel_count'], $max);
        }
        $this->assign('selCountArr', $selCountArr);
        $this->assign('selMonthArr', $selMonthArr);
        $this->assign('max', intval($max * 1.1));

        $this->assign('map', $map);
        $this->assign('appTypeConfig', AppTestVars::getAppTypeConfig()); //apk类型
        $this->assign('appTestConfig', AppTestVars::getAppTestConfig()); //测试业务类型
        $this->assign('appSourceConfig', AppTestVars::getAppSourceConfig()); //apk来源

        $this->assign('search', serialize($map));
        $this->display();
    }

    //导出测试任务统计EXCEL
    public function taskStaticsExcel() {
        if ($_GET['statics_type'] == 1) {
            $type = '%Y-%m';
        } else {
            $type = '%Y-%m-%d';
        }
        include_once( SITE_PATH . '/addons/libs/PHPExcel.php' );
        $map_str = get_map($_GET['map']);
        $sql = "SELECT COUNT(1) AS sel_count, FROM_UNIXTIME(ctime, '" . $type . "') AS sel_month FROM `ts_app_test_task` WHERE " . $map_str . " GROUP BY sel_month asc";
        $res = M()->query($sql);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '时间')
            ->setCellValue('B1', '提交任务数');

        $n = 2;
        foreach ((array) $res as $key => $val) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $n, ' ' . $val['sel_month'])
                ->setCellValue('B' . $n, ' ' . $val['sel_count']);
            $n ++;
        }
        $objPHPExcel->setActiveSheetIndex(0);
        $filename = '测试任务统计信息.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

}
