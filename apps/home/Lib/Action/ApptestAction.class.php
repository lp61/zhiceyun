<?php

/**
 * Description of ApptestAction
 *
 * @author vini
 */
import('@.Form.AppTestVars');
import('@.Form.UsersFormNamespace');
import('cron.taskCronAction');

class ApptestAction extends Action {

    protected $sourceID = 1;

    /*
      1. 快速测试包  免费

      2. 兼容测试-高覆盖率包  按包收费
      3. 兼容测试-最新机型包  免费
      4. 兼容测试-自由选择包  按终端数收费

      5. 网络友好-免费测试包  免费
      6. 网络有好-深度测试     暂不告知终端包来源

      7. 新机兼容测试包
      8. 专机测试包
     */

    function _initialize() {
        if (!in_array(ACTION_NAME, array('index', 'pressTest', 'weakTest'))) {
            if (!$this->mid) {
//                $this->assign('jumpUrl', U('home/Public/login'));
//                $this->error("请先登录");
                redirect(U('home/Public/login'));
            } else {
                if ($this->user['is_active'] == 0) {
                    $this->assign('jumpUrl', U('home/Account/index'));
                    $this->error("您的账号未激活，请先进行邮件激活。");
                }
            }
        }
    }

    //定时任务测试
    function testTask() {
        include('apps/cron/taskCronAction.class.php');
        $e = new taskCronAction();
//        $value = $e->checkProgress("3bfdbad6-83cc-c5d9-a329-5dd8b47cf4a1");
//        print($value);
        $e->sendTask();
//        $e->sendApk();

    }
    //首页-选择测试类型
    public function index() {
        $this->display();
    }

    /*
     * 1-快速测试-上传APK
     */

    function quickTest() {
        //$this->_initUpgradeInfo(); //获取需要完善的资料

        $this->getCost(AppTestVars::QUICK_PACKAGE); //获取积分消费信息
        $this->getApkList(); //获取已上传apk列表
        $this->assign('appType', AppTestVars::getAppTypeConfig()); //应用类型设置

        $this->display();
    }

    //快速测试-提交
    function quickTestSubmit() {
        //apk文件上传处理 生成apk的相关信息：$_POST['apk_url']、attach_id、apk_icon、apk_name、apk_version、apk_size
        $data = $this->saveApk();

        //其他
        $data['uuid'] = $this->create_uuid();
        $data['user_id'] = $where['uid'] = $this->mid;
        $data['task_status'] = 0; //任务状态：排队中
        $data['ctime'] = time();
        $data['status'] = 1; //记录状态：提交成功
        $cost = $this->get_cost(AppTestVars::QUICK_PACKAGE); //获取花费积分  
        $data['cost'] = $cost['cost'];
        if (M('credit_user')->where($where)->getField('score') < $data['cost']) {
            $this->error("对不起，您的积分不足请充值！");
        }
        $data['package_type'] = AppTestVars::QUICK_PACKAGE; //测试包类型：快速测试包
        $data['businessID'] = AppTestVars::QUICK_TEST; //测试业务类型：快速测试
        $data['sourceID'] = AppTestVars::APP_TEST_SOURCE1; //任务来源：网站

        $mininum = M('app_test_package')->where("package_type=" . $data['package_type'])->getField('mininum');
        $data['device_num'] = $mininum; //最小测试设备数

        $this->assign('waitSecond', 3);
        $model = M('app_test_task');
        if (!$res = $model->add($data)) {
            //dump($model->getLastSql());
            $this->error("提交失败"); //提交失败
        } else {
            if ($data['cost'] > 0) {
                //扣除积分
                X('Credit')->setUserCredit($this->uid, array('score' => -$data['cost'], 'name' => 'app_test_quick', 'alias' => '提交快速测试'));
            }
            // X('Credit')->setUserCredit($this->uid, 'app_submit_test'); //成功提交一次测试奖励积分

            $this->assign('jumpUrl', U('home/TestRecord/index'));
            $this->assign('test_name', '快速测试');
            $this->display('testSubmit'); //提交成功
        }
    }

    /*
     * 2-兼容性测试-上传APK
     */

    function compatibleTest() {
        //$this->_initUpgradeInfo(); //获取需要完善的资料
        $this->getApkList(); //获取已上传apk列表
        $this->assign('appType', AppTestVars::getAppTypeConfig()); //应用类型设置
        $this->display();
    }

    //兼容性测试-选择终端
    function compatibleTestChoose() {
        //生成提交中的任务记录
        //apk文件上传处理 生成apk的相关信息：$_POST['apk_url']、attach_id、apk_icon、apk_name、apk_version、apk_size
        $data = $this->saveApk();

        $data['uuid'] = $this->create_uuid();
        $data['user_id'] = $this->mid;
        $data['status'] = 0;

        $model = M('app_test_task');
        if (!$res = $model->add($data)) {
            $this->assign('waitSecond', 300);
            //log::write($model->getLastSql(), LOG::SQL);
            $this->error("操作失败，请稍后再试"); //提交失败
        } else {
            //log::write($model->getLastSql(), LOG::SQL);
            $businessID = AppTestVars::COMPATIBLE_TEST; //测试业务类型：兼容性测试
            $rows = M('app_test_package')->where("businessID='$businessID'")->order('id asc')->findAll();

            $user_id = $this->mid;
            $user_type = $this->user['user_type']; //用户类型
            $rule = array();
            foreach ($rows as $key => $row) {
                $type = $row['type']; //1-免费 2-按包收费 3-按终端数收费
                $package_type = $row['package_type']; //包类型ID
                if ($type == 1) {
                    //免费包积分策略
                    $free_num = $row['free' . $user_type];
                    $day = date("Y-m-d 00:00:00");
                    $num = M('app_test_task')->where("user_id='$user_id' and package_type='$package_type' and status<>0 and ctime>=UNIX_TIMESTAMP('$day')")->count();
                    if ($num >= $free_num) {
                        $isShow = 1;
                        $score = $row['score' . $user_type];
                    } else {
                        $isShow = 0;
                        $score = 0;
                    }
                } else {
                    $isShow = 1;
                    $score = $row['score' . $user_type];
                }
                $rule[$key]['isShow'] = $isShow;
                $rule[$key]['costScore'] = $score;
                $rule[$key]['package_type'] = $package_type;
            }
            //dump($rule);
            //终端包展示
            $map['package_type'] = AppTestVars::COMP_HIGEH_PACKAGE;
            $map['devState'] = 1; //在线设备
            $map['isEnabled'] = 1; //启用设备
            $app_client = M('app_client');
            $list_high = $app_client->where($map)->field('name,os,size,resolution,release_time,coverage,pic_url')->group('device_type')->select();
            $map['package_type'] = AppTestVars::COMP_NEW_PACKAGE;
            $list_new = $app_client->where($map)->field('name,os,size,resolution,release_time,coverage,pic_url')->group('device_type')->select();
            $map['package_type'] = AppTestVars::COMP_OPT_PACKAGE;
            $list_opt = $app_client->where($map)->field('id,name,device_type')->group('device_type')->select();
            //log::write($app_client->getLastSql(),LOG::SQL);
            $this->assign('list_high', $list_high);
            $this->assign('list_new', $list_new);
            $this->assign('list_opt', $list_opt);
            //log::write(print_r($list_high,TRUE),LOG::INFO);
            //log::write(print_r($list_new,TRUE),LOG::INFO);
            //系统、分辨率、品牌选项展示
            unset($map);
            $os = $resolution = $model = array();
            $setData = M('app_config')->order('type asc,content asc')->findAll();
            foreach ($setData as $key => $_one) {
                switch ($_one['type']) {
                    case AppTestVars::APP_SYSTEM_OS:
                        $os[] = $_one;
                        break;
                    case AppTestVars::APP_SYSTEM_MODEL:
                        $model[] = $_one;
                        break;
                    default :
                        continue;
                }
            }
            $resolution = M('app_config')->where("type=" . AppTestVars::APP_SYSTEM_RESOLUTION)->order('width asc,height asc')->findAll();

            $this->assign('osList', $os); //系统
            $this->assign('resolution', $resolution); //分辨率
            $this->assign('model', $model); //品牌
            //log::write(print_r($resolution,TRUE),LOG::INFO);
            //log::write(print_r($model,TRUE),LOG::INFO);

            $this->assign('isShow', 0); //是否显示积分计算区域
            $this->assign('costScore', 0); //此次测试所需积分
            $this->assign('rule', $rule); //积分规则
            $this->assign('score', $this->user['credit']['score']['credit']); //当前用户所持有的积分
            $this->assign('task_id', $res);

            $this->display('compatibleTest_2'); //提交成功
        }
    }

    //兼容性测试-提交
    function compatibleTestSubmit() {
        $id = $_POST['task_id']; //测试任务ID
        //业务包的选择
        $data['package_type'] = $_POST['package_type']; //测试包类型："2"-覆盖率高 "3"-最新上市 "4"-自由选择
        $data['businessID'] = AppTestVars::COMPATIBLE_TEST; //测试业务类型：兼容性测试
        $data['sourceID'] = AppTestVars::APP_TEST_SOURCE1; //任务来源：网站
        $data['device_type'] = $_POST['device_type']; //测试终端型号
        $mininum = M('app_test_package')->where("package_type=" . $data['package_type'])->getField('mininum');
        $data['device_num'] = empty($data['device_type']) ? $mininum : count(explode(',', $data['device_type']));
        //判断测试包类型，获取花费积分
        if ($data['package_type'] == 2) {
            $cost = $this->get_cost(AppTestVars::COMP_HIGEH_PACKAGE); //覆盖率高
            $data['device_type'] = ''; //测试终端型号
        } elseif ($data['package_type'] == 3) {
            $cost = $this->get_cost(AppTestVars::COMP_NEW_PACKAGE); //最新上市
            $data['device_type'] = ''; //测试终端型号
        } elseif ($data['package_type'] == 4) {
            $opt_num = $this->get_cost(AppTestVars::COMP_OPT_PACKAGE); //自由选择
            $cost['cost'] = $opt_num['cost'] * $data['device_num'];
        } else {
            $this->error("非法请求！");
        }
        $data['cost'] = $cost['cost']; //测试消耗积分
        $where['uid'] = $this->mid;
        $user_score = M('credit_user')->where($where)->field('score')->find(); //获取用户积分
        $num_score = $user_score['score'] - $data['cost'];
        if ($num_score < 0) {
            $this->error("对不起，您的积分不足请充值！！");
        }
        $data['task_status'] = 0;
        $data['ctime'] = time();
        $data['status'] = 1;

        $this->assign('waitSecond', 3);
        $model = M('app_test_task');
        if (!$res = $model->where("id='$id'")->save($data)) {
            log::write($model->getLastSql(), LOG::SQL);
            $this->error("提交失败"); //提交失败
        } else {
            if ($data['cost'] > 0) {
                //扣除积分
                X('Credit')->setUserCredit($this->uid, array('score' => -$data['cost'], 'name' => 'app_test_compatible', 'alias' => '提交兼容性测试'));
            }
            //  X('Credit')->setUserCredit($this->uid, 'app_submit_test'); //成功提交一次测试奖励积分

            $this->assign('jumpUrl', U('home/TestRecord/index'));
            $this->assign('test_name', '兼容性测试');
            $this->display('testSubmit'); //提交成功
        }
    }

    /*
     * 3-网络友好测试
     * +-------------------------------------------------------------------------------------------------
     */

    function networkTest() {
        $this->display();
    }

    //网络友好-选择测试类型
    function networkTestNext() {
        $this->display('networkTest_2');
    }

    //网络友好——开始测试
    function networkTestFreeStart() {
        //$this->_initUpgradeInfo(); //获取需要完善的资料

        $this->getCost(AppTestVars::NET_FREE_PACKAGE); //测试包：网络友好-免费测试包
        $this->getApkList(); //获取已上传apk列表
        $this->assign('appType', AppTestVars::getAppTypeConfig()); //应用类型设置

        $this->display('networkTest_free'); //免费测试
    }

    function networkTestDeepStart() {
        //$this->_initUpgradeInfo(); //获取需要完善的资料

        $this->getCost(AppTestVars::NET_DEEP_PACKAGE); //测试包：网络友好-深度测试包
        $this->getApkList(); //获取已上传apk列表
        $this->assign('appType', AppTestVars::getAppTypeConfig()); //应用类型设置

        $this->display('networkTest_deep_upload'); //深度测试
    }

    function networkTestFreeSubmit() {
        $data = $this->saveApk();

        //其他
        $data['uuid'] = $this->create_uuid();
        $data['user_id'] = $this->mid;
        $data['task_status'] = 0;
        $data['ctime'] = time();
        $data['status'] = 1;

        $data['package_type'] = AppTestVars::NET_FREE_PACKAGE; //测试包类型：网络友好免费测试包
        $data['businessID'] = AppTestVars::NETWORK_TEST; //测试业务类型：网络友好测试
        $data['sourceID'] = AppTestVars::APP_TEST_SOURCE1; //任务来源：网站

        $mininum = M('app_test_package')->where("package_type=" . $data['package_type'])->getField('mininum');
        $data['device_num'] = $mininum;
        $where['uid'] = $this->mid;
        $cost = $this->get_cost(AppTestVars::NET_FREE_PACKAGE); //获取花费积分
        $data['cost'] = $cost['cost'];
        $user_score = M('credit_user')->where($where)->field('score')->find(); //获取用户积分
        $num_score = $user_score['score'] - $data['cost'];
        if ($num_score < 0) {
            $this->error("对不起，您的积分不足请充值！！");
        }
        $this->assign('waitSecond', 3);
        $model = M('app_test_task');
        if (!$res = $model->add($data)) {
            //dump($model->getLastSql());
            $this->error("提交失败"); //提交失败
        } else {
            if ($_POST['cost'] > 0) {
                //扣除积分
                X('Credit')->setUserCredit($this->uid, array('score' => -$_POST['cost'], 'name' => 'app_test_network', 'alias' => '提交网络友好免费测试'));
            }
            // X('Credit')->setUserCredit($this->uid, 'app_submit_test'); //成功提交一次测试奖励积分

            $this->assign('jumpUrl', U('home/TestRecord/index'));
            $this->assign('test_name', '网络友好测试');
            $this->display('testSubmit'); //提交成功
        }
    }

    function networkTestDeepUpload() {
        //生成提交中的任务记录
        //apk文件上传处理 生成apk的相关信息：$_POST['apk_url']、attach_id、apk_icon、apk_name、apk_version、apk_size
        $data = $this->saveApk();

        //测试包
        $package_type = AppTestVars::NET_DEEP_PACKAGE; //测试业务类型：网络友好测试-深度测试
        $data['package_type'] = $package_type;
        $data['businessID'] = AppTestVars::NETWORK_TEST; //测试业务类型：网络友好测试
        $data['sourceID'] = AppTestVars::APP_TEST_SOURCE1; //任务来源：网站
        //积分消耗
        $user_id = $this->mid;
        $user_type = $this->user['user_type']; //用户类型
        $row = M('app_test_package')->where("package_type='$package_type'")->find();
        $type = $row['type']; //1-免费 2-按包收费 3-按终端数收费
        if ($type == 1) {
            //免费包积分策略
            $free_num = $row['free' . $user_type];
            $day = date("Y-m-d 00:00:00");
            //当天已提交的次数达到免费次数上限，则开始收费
            $num = M('app_test_task')->where("user_id='$user_id' and package_type='$package_type' and status<>0 and ctime>=UNIX_TIMESTAMP('$day')")->count();
            $score = $num >= $free_num ? $row['score' . $user_type] : 0;
        } else {
            $score = $row['score' . $user_type];
        }
        $where['uid'] = $this->mid;
        $user_score = M('credit_user')->where($where)->field('score')->find(); //获取用户积分
        $num_score = $user_score['score'] - $score;
        if ($num_score < 0) {
            $this->error("对不起，您的积分不足请充值！");
        }

        $data['cost'] = $score;

        $data['uuid'] = $this->create_uuid();
        $data['user_id'] = $this->mid;
        $data['status'] = 0;

        //dump($data);
        $model = M('app_test_task');
        if (!$res = $model->add($data)) {
            //dump($model->getLastSql());
            $this->assign('waitSecond', 300);
            $this->error("操作失败，请稍后再试"); //提交失败
        } else {
            $this->assign('task_id', $res);
            $this->display('networkTest_deep'); //提交成功
        }
    }

    function networkTestDeepSubmit() {
        $id = $_POST['task_id'];
        $data['memo'] = $_POST['json'];
        $data['task_status'] = 0;
        $data['ctime'] = time();
        $data['status'] = 1; //已提交

        $this->assign('waitSecond', 3);
        $model = M('app_test_task');
        if (!$res = $model->where("id='$id'")->save($data)) {
            log::write($model->getLastSql(), LOG::SQL);
            $this->error("提交失败"); //提交失败
        } else {
            $tmp = $this->get_cost(AppTestVars::NET_DEEP_PACKAGE); //测试包积分计算：网络友好深度测试包
            if ($tmp['cost'] > 0) { //扣除积分
                X('Credit')->setUserCredit($this->uid, array('score' => -$tmp['cost'], 'name' => 'app_test_network', 'alias' => '提交网络友好深度测试'));
            }
            // X('Credit')->setUserCredit($this->uid, 'app_submit_test'); //成功提交一次测试奖励积分

            $this->assign('jumpUrl', U('home/TestRecord/index'));
            $this->assign('test_name', '网络友好测试深度测试');
            $this->display('testSubmit'); //提交成功
        }
    }

    /*
     * 4-新机兼容测试-选择终端
     */

    function newmacTest() {
//        $package_type = AppTestVars::NEW_PACKAGE; //测试包类型：新机兼容测试包
//        $this->getCost($package_type); //消耗积分计算：新机兼容测试包
//        $map['package_type'] = $package_type;
//        $map['devState'] = 1; //在线设备
//        $map['isEnabled'] = 1; //启用设备
//        $list = M('app_client')->where($map)->field('name,os,size,resolution,coverage,pic_url')->group('name')->select();
//        $this->assign('list', $list); //新机兼容测试设备展示
        $this->error('您请求的页面不存在！'); //新机兼容测试
    }

    //新机兼容测试-上传APK
//    function newmacTestUpload() {
//        //创建测试任务
//        $data['device_type'] = $_POST['device_type']; //测试终端型号
//        $data['device_num'] = count(explode(',', $data['device_type'])); //测试终端数量
//        //业务包的选择
//        $data['package_type'] = AppTestVars::NEW_PACKAGE; //测试包类型：新机兼容测试包
//        $data['businessID'] = AppTestVars::NEW_MAC_COMP_TEST; //测试业务类型：兼容性测试
//        $data['sourceID'] = AppTestVars::APP_TEST_SOURCE1; //任务来源：网站
//
//        $data['uuid'] = $this->create_uuid();
//        $data['user_id'] = $this->mid;
//        $data['status'] = 0; //任务提交中
//
//        $where['uid'] = $this->mid;
//        $cost = $this->get_cost(AppTestVars::NEW_PACKAGE); //获取花费积分
//        $data['cost']=$cost['cost'] * $data['device_num'];
//        $user_score = M('credit_user')->where($where)->field('score')->find(); //获取用户积分
//        $num_score = $user_score['score'] - $data['cost'];
//        if ($num_score < 0) {
//            $this->error("对不起，您的积分不足请充值！！");
//        }
//
//        $model = M('app_test_task');
//        if (!$res = $model->add($data)) {
//            //dump($model->getLastSql());
//            $this->assign('waitSecond', 300);
//            $this->error("操作失败，请稍后再试"); //提交失败
//        } else {
//            $this->assign('task_id', $res); //任务ID
//
//            //$this->_initUpgradeInfo(); //获取需要完善的资料
//            $this->getApkList(); //获取已上传apk列表
//            $this->assign('appType', AppTestVars::getAppTypeConfig()); //应用类型设置
//
//            $this->display('newmacTest_2');
//        }
//    }
    //新机兼容测试-提交
//    function netmacTestSubmit() {
//        //apk文件上传处理 生成apk的相关信息：$_POST['apk_url']、attach_id、apk_icon、apk_name、apk_version、apk_size
//        $id = $_POST['task_id'];
//        $data = $this->saveApk();
//        $data['task_status'] = 0;
//        $data['ctime'] = time();
//        $data['status'] = -1; 
//
//        $mininum = M('app_test_package')->where("package_type=" . AppTestVars::NEW_PACKAGE)->getField('mininum');
//        $data['device_num'] = $mininum;
//
//        $this->assign('waitSecond', 3);
//        $model = M('app_test_task');
//        if (!$res = $model->where("id='$id'")->save($data)) {
//            log::write($model->getLastSql(), LOG::SQL);
//            $this->error("提交失败"); //提交失败
//        } else {
//            $tmp['cost'] = $model->where("id='$id'")->getField('cost');
//            if ($tmp['cost'] > 0) {
//                //扣除积分
//                X('Credit')->setUserCredit($this->uid, array('score' => -$tmp['cost'], 'name' => 'app_test_newmac', 'alias' => '提交新机兼容测试'));
//            }
//            // X('Credit')->setUserCredit($this->uid, 'app_submit_test'); //成功提交一次测试奖励积分
//
//            $this->assign('jumpUrl', U('home/TestRecord/index'));
//            $this->assign('test_name', '新机兼容测试');
//            $this->display('testSubmit'); //提交成功
//        }
//    }

    /*
     * 5-专机测试-选择终端
     */

    function specialTest() {
        $package_type = AppTestVars::SPECIAL_PACKAGE; //测试包类型：专机测试包
        $this->getCost($package_type); //消耗积分计算：专机测试包

        $map['package_type'] = $package_type;
        $map['devState'] = 1; //在线设备
        $map['isEnabled'] = 1; //启用设备
        $list = M('app_client')->where($map)->field('name,os,size,resolution,coverage,pic_url')->group('name')->select();
        $this->assign('list', $list); //专机测试包设备展示

        $this->display(); //专机测试
    }

    //专机测试-上传APK
    function specialTestUpload() {
        //业务包的选择
        $package_type = AppTestVars::SPECIAL_PACKAGE; //测试包类型：专机测试包
        $data['package_type'] = $package_type; //测试包类型：专机测试包
        $data['businessID'] = AppTestVars::SPECIAL_MAC_TEST; //测试业务类型：专机测试
        $data['sourceID'] = AppTestVars::APP_TEST_SOURCE1; //任务来源：网站

        if (isset($_GET['device_type'])) {
            $data['device_type'] = $_GET['device_type'];
            $data['device_num'] = 1; //测试终端数量
        } else {
            $data['device_type'] = $_POST['device_type']; //测试终端型号
            $data['device_num'] = count(explode(',', $data['device_type'])); //测试终端数量
        }
        //创建测试任务
        $where['uid'] = $this->mid;
        $cost = $this->get_cost(AppTestVars::SPECIAL_PACKAGE); //获取花费积分
        $data['cost'] = $cost['cost'] * $data['device_num']; //测试消耗积分
        $user_score = M('credit_user')->where($where)->field('score')->find(); //获取用户积分
        $num_score = $user_score['score'] - $data['cost'];
        if ($num_score < 0) {
            $this->error("对不起，您的积分不足请充值！！");
        }
        $data['uuid'] = $this->create_uuid();
        $data['user_id'] = $this->mid;
        $data['status'] = 0; //任务提交中

        $model = M('app_test_task');
        if (!$res = $model->add($data)) {
            //dump($model->getLastSql());
            $this->assign('waitSecond', 300);
            $this->error("操作失败，请稍后再试"); //提交失败
        } else {
            $this->assign('task_id', $res); //任务ID
            //$this->_initUpgradeInfo(); //获取需要完善的资料
            $this->getApkList(); //获取已上传apk列表
            $this->assign('appType', AppTestVars::getAppTypeConfig()); //应用类型设置

            $this->display('specialTest_2');
        }
    }

    //专机测试-提交
    function specialTestSubmit() {
        $id = $_POST['task_id'];
        $data = $this->saveApk();
        $data['task_status'] = 0; //排队中
        $data['ctime'] = time();
        $data['status'] = 1;

        $mininum = M('app_test_package')->where("package_type=" . AppTestVars::SPECIAL_PACKAGE)->getField('mininum');
        $data['device_num'] = $mininum;

        $this->assign('waitSecond', 3);
        $model = M('app_test_task');
        if (!$res = $model->where("id='$id'")->save($data)) {
            $this->error("提交失败"); //提交失败
        } else {
            $tmp['cost'] = $model->where("id='$id'")->getField('cost');
            if ($tmp['cost'] > 0) {
                //扣除积分
                X('Credit')->setUserCredit($this->uid, array('score' => -$tmp['cost'], 'name' => 'app_test_special', 'alias' => '提交专机测试'));
            }
            //X('Credit')->setUserCredit($this->uid, 'app_submit_test'); //成功提交一次测试奖励积分

            $this->assign('jumpUrl', U('home/TestRecord/index'));
            $this->assign('test_name', '专机测试');
            $this->display('testSubmit'); //提交成功
        }
    }

    /*
     * 6-弱网络测试测试
     */

    function weakTest() {
        $this->display();
    }

    /*
     * 7-安全测试
     */

    function secureTest() {
        $this->error("此功能暂未开通，敬请期待~~");
        $this->display();
    }

    /*
     * 计算此次测试所需积分
     */

    //清华测试系统
    function qhTest() {
        //$this->_initUpgradeInfo(); //获取需要完善的资料
        $this->getApkList(); //获取已上传apk列表
        $this->assign('appType', AppTestVars::getAppTypeConfig()); //应用类型设置
        $this->display();
    }

    //清华测试-选择终端
    function qhTestChoose() {
        //生成提交中的任务记录
        //apk文件上传处理 生成apk的相关信息：$_POST['apk_url']、attach_id、apk_icon、apk_name、apk_version、apk_size
        $data = $this->saveApk();

        $data['uuid'] = $this->create_uuid();
        $data['user_id'] = $this->mid;
        $data['status'] = 0;

        $model = M('app_test_task');
        if (!$res = $model->add($data)) {
            $this->assign('waitSecond', 300);
            //log::write($model->getLastSql(), LOG::SQL);
            $this->error("操作失败，请稍后再试"); //提交失败
        } else {
            //log::write($model->getLastSql(), LOG::SQL);
            $businessID = AppTestVars::QH_TEST; //测试业务类型：清华兼容性测试
            $rows = M('app_test_package')->where("businessID='$businessID'")->order('id asc')->findAll();

            $user_id = $this->mid;
            $user_type = $this->user['user_type']; //用户类型
            $rule = array();
            foreach ($rows as $key => $row) {
                $type = $row['type']; //1-免费 2-按包收费 3-按终端数收费
                $package_type = $row['package_type']; //包类型ID
                if ($type == 1) {
                    //免费包积分策略
                    $free_num = $row['free' . $user_type];
                    $day = date("Y-m-d 00:00:00");
                    $num = M('app_test_task')->where("user_id='$user_id' and package_type='$package_type' and status<>0 and ctime>=UNIX_TIMESTAMP('$day')")->count();
                    if ($num >= $free_num) {
                        $isShow = 1;
                        $score = $row['score' . $user_type];
                    } else {
                        $isShow = 0;
                        $score = 0;
                    }
                } else {
                    $isShow = 1;
                    $score = $row['score' . $user_type];
                }
                $rule[$key]['isShow'] = $isShow;
                $rule[$key]['costScore'] = $score;
                $rule[$key]['package_type'] = $package_type;
            }
            //dump($rule);
            //终端包展示
            $map['package_type'] = AppTestVars::QH_HIGEH_PACKAGE;
            $map['devState'] = 1; //在线设备
            $map['isEnabled'] = 1; //启用设备
            $app_client = M('app_client');
            $list_high = $app_client->where($map)->field('name,os,size,resolution,release_time,coverage,pic_url')->group('device_type')->select();
            $map['package_type'] = AppTestVars::QH_NEW_PACKAGE;
            $list_new = $app_client->where($map)->field('name,os,size,resolution,release_time,coverage,pic_url')->group('device_type')->select();
            $map['package_type'] = AppTestVars::QH_OPT_PACKAGE;
            $list_opt = $app_client->where($map)->field('id,name,device_type')->group('device_type')->select();
            //log::write($app_client->getLastSql(),LOG::SQL);
            $this->assign('list_high', $list_high);
            $this->assign('list_new', $list_new);
            $this->assign('list_opt', $list_opt);
            //log::write(print_r($list_high,TRUE),LOG::INFO);
            //log::write(print_r($list_new,TRUE),LOG::INFO);
            //系统、分辨率、品牌选项展示
            unset($map);
            $os = $resolution = $model = array();
            $setData = M('app_config')->order('type asc,content asc')->findAll();
            foreach ($setData as $key => $_one) {
                switch ($_one['type']) {
                    case AppTestVars::APP_SYSTEM_OS:
                        $os[] = $_one;
                        break;
                    case AppTestVars::APP_SYSTEM_MODEL:
                        $model[] = $_one;
                        break;
                    default :
                        continue;
                }
            }
            $resolution = M('app_config')->where("type=" . AppTestVars::APP_SYSTEM_RESOLUTION)->order('width asc,height asc')->findAll();

            $this->assign('osList', $os); //系统
            $this->assign('resolution', $resolution); //分辨率
            $this->assign('model', $model); //品牌
            //log::write(print_r($resolution,TRUE),LOG::INFO);
            //log::write(print_r($model,TRUE),LOG::INFO);

            $this->assign('isShow', 0); //是否显示积分计算区域
            $this->assign('costScore', 0); //此次测试所需积分
            $this->assign('rule', $rule); //积分规则
            $this->assign('score', $this->user['credit']['score']['credit']); //当前用户所持有的积分
            $this->assign('task_id', $res);

            $this->display('qhTest_2'); //提交成功
        }
    }

    //清华测试-提交
    function qhTestSubmit() {
        $id = $_POST['task_id']; //测试任务ID
        //业务包的选择
        $data['package_type'] = $_POST['package_type']; //测试包类型："9"-清华覆盖率高 "10"-清华最新上市 "11"-清华自由选择
        $data['businessID'] = AppTestVars::QH_TEST; //测试业务类型：清华兼容性测试
        $data['sourceID'] = AppTestVars::APP_TEST_SOURCE1; //任务来源：网站
        $data['device_type'] = $_POST['device_type']; //测试终端型号
        $mininum = M('app_test_package')->where("package_type=" . $data['package_type'])->getField('mininum');
        $data['device_num'] = empty($data['device_type']) ? $mininum : count(explode(',', $data['device_type']));
        //判断测试包类型，获取花费积分
        if ($data['package_type'] == 9) {
            $cost = $this->get_cost(AppTestVars::QH_HIGEH_PACKAGE); //覆盖率高
            $data['device_type'] = ''; //测试终端型号
        } elseif ($data['package_type'] == 10) {
            $cost = $this->get_cost(AppTestVars::QH_NEW_PACKAGE); //最新上市
            $data['device_type'] = ''; //测试终端型号
        } elseif ($data['package_type'] == 11) {
            $opt_num = $this->get_cost(AppTestVars::QH_OPT_PACKAGE); //自由选择
            $cost['cost'] = $opt_num['cost'] * $data['device_num'];
        } else {
            $this->error("非法请求！");
        }
        $data['cost'] = $cost['cost']; //测试消耗积分
        $where['uid'] = $this->mid;
        $user_score = M('credit_user')->where($where)->field('score')->find(); //获取用户积分
        $num_score = $user_score['score'] - $data['cost'];
        if ($num_score < 0) {
            $this->error("对不起，您的积分不足请充值！！");
        }
        $data['task_status'] = 0;
        $data['ctime'] = time();
        $data['status'] = 1;

        $this->assign('waitSecond', 3);
        $model = M('app_test_task');
        if (!$res = $model->where("id='$id'")->save($data)) {
            log::write($model->getLastSql(), LOG::SQL);
            $this->error("提交失败"); //提交失败
        } else {
            if ($data['cost'] > 0) {
                //扣除积分
                X('Credit')->setUserCredit($this->uid, array('score' => -$data['cost'], 'name' => 'app_test_compatible', 'alias' => '提交清华兼容性测试'));
            }
            //  X('Credit')->setUserCredit($this->uid, 'app_submit_test'); //成功提交一次测试奖励积分

            $this->assign('jumpUrl', U('home/TestRecord/index'));
            $this->assign('test_name', '清华兼容性测试');
            $this->display('testSubmit'); //提交成功
        }
    }

    function getCost($package_type) {
        $data = $this->get_cost($package_type);
        $this->assign('isShow', $data['isShow']); //是否显示积分计算区域
        $this->assign('costScore', $data['cost']); //此次测试所需积分
        $this->assign('score', $this->user['credit']['score']['credit']); //当前用户所持有的积分
    }

    //获取积分消费
    function get_cost($package_type) {
        $row = M('app_test_package')->where("package_type='$package_type'")->find();
        $user_id = $this->mid;
        $user_type = $this->user['user_type']; //用户类型
        $type = $row['type']; //1-免费 2-按包收费 3-按终端数收费
        if ($type == 1) {
            //免费包积分策略
            $free_num = $row['free' . $user_type];
            $day = date("Y-m-d 00:00:00");
            $num = M('app_test_task')->where("user_id='$user_id' and package_type='$package_type' and status<>0 and ctime>=UNIX_TIMESTAMP('$day')")->count();
            if ($num >= $free_num) {
                $data['isShow'] = 1;
                $data['cost'] = $row['score' . $user_type];
            } else {
                $data['isShow'] = 0;
                $data['cost'] = 0;
            }
        } else {
            $data['isShow'] = 1;
            $data['cost'] = $row['score' . $user_type];
        }

        return $data;
    }

    public function saveApk() {
        //dump($_POST);exit(); //methodType 1-本地上传 2-链接下载 3-已上传APK中选择
        if ($_POST['methodType'] == 1) {
            //本地文件上传 接收文件上传数据
            $app = json_decode($_POST['fileJson'], TRUE);
            $model = M('Attach');
            $aid = $model->add($app);
            //log::write(print_r($model->getLastSql(), TRUE), LOG::INFO);
            $_POST['attach_id'] = intval($aid);
            $_POST['apk_sname'] = $app['name']; //原文件名
            $_POST['apk_url'] = $app['apk_url']; //下载地址
            $_POST['apk_name'] = $app['apk_name']; //apk名称
            $_POST['apk_icon'] = $app['apk_icon']; //apk图标
            $_POST['apk_version'] = $app['apk_version']; //apk版本
            $_POST['apk_size'] = $app['apk_size']; //apk大小
            $data['send_status'] = -1; //下发状态：apk转存中
        } elseif ($_POST['methodType'] == 2) {
            //链接下载，从远程下载apk
            $url = $_POST['apkLink']; //下载地址
            $app['extension'] = "apk"; //文件扩展名
            $app['savepath'] = date("Y/md/H/"); //保存路径
            $app['savename'] = uniqid() . "." . $app['extension']; //保存文件名
            $info = $this->getFile($url, UPLOAD_PATH . "/" . $app['savepath'], $app['savename']);
            if ($info['error'] != 0) {
                $this->error($info['msg']);
            }

            $app['attach_type'] = "app_test_upload";
            $app['userId'] = $this->mid;
            $app['uploadTime'] = time();
            $app['type'] = "application/octet-stream";

            $apk_info = X('Xattach')->getApkInfo($app['extension'], $app['savepath'], $app['savename']);
            //log::write(print_r($apk_info, TRUE), LOG::INFO);
            $_POST['apk_sname'] = $app['name'] = $app['savename']; //原文件名
            $_POST['apk_url'] = $app['apk_url'] = UPLOAD_URL . "/" . $app['savepath'] . $app['savename']; //下载地址
            $_POST['apk_name'] = $app['apk_name'] = $apk_info['apk_name']; //apk名称
            $_POST['apk_icon'] = $app['apk_icon'] = $apk_info['apk_icon']; //apk图标
            $_POST['apk_version'] = $app['apk_version'] = $apk_info['apk_version']; //apk版本
            $_POST['apk_size'] = $app['apk_size'] = $apk_info['apk_size']; //apk大小

            $model = M('Attach');
            $aid = $model->add($app);
            //log::write(print_r($model->getLastSql(), TRUE), LOG::INFO);
            $_POST['attach_id'] = intval($aid);
            $data['send_status'] = -1; //下发状态：apk转存中
        } else {
            //从已有APK中选择，需要apk的附件ID
            $attach_id = $task_map['id'] = $_POST['apkFileId'];
            $model = M('attach');
            $row = $model->where($task_map)->field('name,apk_url,apk_icon,apk_name,apk_version,apk_size,apkurl')->find();
            $_POST['attach_id'] = $attach_id;
            $_POST['apk_sname'] = $row['name'];
            $_POST['apk_url'] = $row['apk_url'];
            $_POST['apk_name'] = $row['apk_name'];
            $_POST['apk_icon'] = $row['apk_icon'];
            $_POST['apk_version'] = $row['apk_version'];
            $_POST['apk_size'] = $row['apk_size'];
            $data['apkurl'] = $row['apkurl']; //apk文件的hadoop下载地址
            $data['send_status'] = empty($row['apkurl']) ? -1 : 1; //下发状态：排队中
        }
        $data['attach_id'] = $_POST['attach_id'];
        $data['apk_sname'] = $_POST['apk_sname'];
        $data['apk_url'] = $_POST['apk_url'];
        $data['apk_name'] = $_POST['apk_name'];
        $data['apk_icon'] = $_POST['apk_icon'];
        $data['apk_version'] = $_POST['apk_version'];
        $data['apk_size'] = $_POST['apk_size'];

        $data['apk_type'] = $_POST['ApkTypeSelectId']; //APP应用类型
        //登陆及用户名密码判定
        if ($_POST['is_login']) {
            //需要登录
            if ($_POST['account_type_value'] == 1) {
                //单一账号登陆
                $a[0]['username'] = trim($_POST['user_name']);
                $a[0]['password'] = trim($_POST['user_pwd']);
                $acount = json_encode($a);
                $data['account_type'] = 1;
            } else {
                //多账号登陆
                $acount = $_POST['userListJson'];
                $data['account_type'] = 0;
            }
            $data['is_login'] = 1;
            $data['account'] = $acount;
        } else {
            //否则视为无需登录
            $data['is_login'] = 0;
        }

        return $data;
    }

    public function uploadApk() {
        //保存APK
        //log::write(print_r($_FILES, TRUE), LOG::INFO);
        $data['code'] = "1";
        $data['data'] = "您的网络有问题，请稍后重试！";

        if (!empty($_FILES['appfile']['name']) && !empty($_FILES['appfile']['size'])) {
            $attach_type = 'app_test_upload';
            $logo_options['save_to_db'] = FALSE;
            $app = X('Xattach')->upload($attach_type, $logo_options);
            if ($app['status']) {
                $app = $app['info'][0];
                $app['apk_url'] = UPLOAD_URL . '/' . $app['savepath'] . $app['savename'];
                $data['code'] = "0";
                $data['data'] = $app;
            } else {
                $data['code'] = "2";
                $data['data'] = $app['info'];
            }
        }

        //log::write(print_r(json_encode($data), TRUE), LOG::INFO);
        echo json_encode($data);
    }

    //返回账户密码字符串，账户之间用','分隔，账户密码之间用'|'分隔
    public function uploadUserList() {
        $data['code'] = "1";
        $data['data'] = "上传失败";
        if (!empty($_FILES['userList']['name'])) {
            $fp = fopen($_FILES['userList']['tmp_name'], 'r');
            $i = 0;
            while (!feof($fp)) {
                $row = trim(fgets($fp));
                if (!empty($row)) {
                    $one = explode('|', $row);
                    $a['username'] = trim($one[0]);
                    $a['password'] = trim($one[1]);
                    $content[] = $a;
                    $i++;
                }
            }
            fclose($fp);
            if ($i > 0) {
                $data['code'] = "0";
                $data['data'] = $content;
                $data['name'] = $_FILES['userList']['name'];
            } else {
                $data['code'] = "2";
                $data['data'] = "账号信息为空或格式不合法！";
            }
        }

        //log::write(print_r(json_encode($data), TRUE), LOG::INFO);
        echo json_encode($data);
    }

    function create_uuid($prefix = "") {    //可以指定前缀
        $str = md5(uniqid(mt_rand(), true));
        $uuid = substr($str, 0, 8) . '-';
        $uuid .= substr($str, 8, 4) . '-';
        $uuid .= substr($str, 12, 4) . '-';
        $uuid .= substr($str, 16, 4) . '-';
        $uuid .= substr($str, 20, 12);
        return $prefix . $uuid;
    }

    //获取已上传APK列表
    function getApkList() {
        $model = M('attach');
        $map['userId'] = $this->mid;
        $map['attach_type'] = 'app_test_upload';
        $map['flag'] = 1;
        $list = $model->where($map)->field('id,name')->group('name')->findAll();
        $this->assign('uploadApkList', $list);
    }

    //根据条件，查询可选的自选终端
    function getPhoneList() {
        $map = array();
        if (!empty($_POST['edition1'])) {
            $map['os'] = array('in', $_POST['edition1']);
        }
        if (!empty($_POST['edition2'])) {
            $map['resolution'] = array('in', $_POST['edition2']);
        }
        if (!empty($_POST['edition3'])) {
            $map['model'] = array('in', $_POST['edition3']);
        }

        $map['package_type'] = AppTestVars::COMP_OPT_PACKAGE;
        $map['devState'] = 1; //在线设备
        $map['isEnabled'] = 1; //启用设备
        $model = M('app_client');
        $data = $model->where($map)->field('id,name')->select();
        //log::write($model->getLastSql(), LOG::SQL);
        $a['code'] = 0;
        $a['data'] = empty($data) ? array() : $data;
        echo json_encode($a);
    }

    public function getFile($url, $save_dir = '', $filename = '', $type = 0) {
        if (trim($url) == '') {
            return array('file_name' => '', 'save_path' => '', 'error' => 1, 'msg' => '下载链接错误');
        }
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
            return array('file_name' => '', 'save_path' => '', 'error' => 5, 'msg' => '保存路径创建失败');
        }
        //获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            $timeout = 10;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //把CRUL获取的内容赋值到变量
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); //服务器连接响应前的等待超时时间
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();
        }
        //文件大小
        $fp2 = @fopen($save_dir . $filename, 'w');
        fwrite($fp2, $content);
        fclose($fp2);
        unset($content, $url);
        return array('file_name' => $filename, 'save_path' => $save_dir . $filename, 'error' => 0);
    }

//    //更新个人信息
//    public function _initUpgradeInfo() {
//        list($editType, $validFunc, $userType) = $this->getInfo();
//
//        if ($editType) {
//            $validateFunc = 'set' . ucfirst($validFunc) . 'ValidateField';
//            self::$FORM_NAMESPACE = new UsersFormNamespace();
//            self::$FORM_NAMESPACE->$validateFunc();
//            $this->assign('workClassify', UserInfoVars::getWorkClassify());
//            $this->assign('workStation', UserInfoVars::getWorkStation());
//            $this->assign('companySize', UserInfoVars::getCompanySize());
//
//            if (self::$FORM_NAMESPACE->isPostBack() && self::$FORM_NAMESPACE->validate()) {
//                $extraData = array(
//                    'userType' => $userType
//                );
//                $this->saveUpgradeInfo($extraData);
//                exit;
//            }
//        }
//        $this->assign('editType', $editType);
//    }
//
//    //获取需完善资料
//    function getInfo() {
//        $upgrateUserConfig = AppTestVars::$UPGRADE_USER_CONFIG;
//
//        $editType = null;
//        $validFunc = '';
//        $userType = 0;
//        foreach ($upgrateUserConfig as $key => $userConfigArr) {
//            foreach ($userConfigArr['fields'] as $k => $field) {
//                if (empty($this->user[$field])) {
//                    if ($field == 'work_station' && $this->user['user_type'] == 2) {
//                        break;
//                    }
//                    $editType = $key;
//                    $validFunc = $userConfigArr['validFunc'];
//                    $userType = $userConfigArr['userType'];
//                    break;
//                }
//            }
//            if ($editType) {
//                break;
//            }
//        }
//        return array($editType, $validFunc, $userType);
//    }
//
//    private function saveUpgradeInfo($extraData = array()) {
//        $data['uid'] = $this->uid;
//        if ($this->user['user_type'] <= $extraData['userType']) {
//            $data['is_audit'] = $this->user['user_type'] == UserInfoVars::USER_NORMAL ? UserInfoVars::YES_AUDIT : UserInfoVars::IS_AUDITING; // 用户升级需要审核 
//            $data['user_type'] = $extraData['userType']; // 默认0
//            if ($data['user_type'] == UserInfoVars::USER_COM) {
//                $this->savePic();
//            }
//        }
//        if (isset($_POST['phone'])) {
//            $data['phone'] = $_POST['phone'];
//        }
//        if (isset($_POST['company_name'])) {
//            $data['company_name'] = t($_POST['company_name']);
//        }
//        if (isset($_POST['company_size'])) {
//            $data['company_size'] = $_POST['company_size'];
//        }
//        if (isset($_POST['work_classify'])) {
//            $data['work_classify'] = $_POST['work_classify'];
//        }
//        if (isset($_POST['work_station'])) {
//            $data['work_station'] = $_POST['work_station'];
//        }
//        if (isset($_POST['attach_id'])) {
//            $data['licence_img'] = $_POST['pic_url'];
//            $data['attach_id'] = $_POST['attach_id'];
//        }
//        if (isset($_POST['qq'])) {
//            $data['qq'] = $_POST['qq'];
//            $flag = 1;
//        }
//        if (isset($_POST['weixin'])) {
//            $data['weixin'] = $_POST['weixin'];
//            $flag = 1;
//        }
//        if (isset($_POST['weibo'])) {
//            $data['weibo'] = $_POST['weibo'];
//            $flag = 1;
//        }
//        $data['mtime'] = time();
//
//        //完善QQ/微信/微博，可获得网站20分测试积分的奖励
//        if ($flag) {
//            if ($this->user['is_init'] != 1) {
//                // 首次完善个人资料 +积分
//                X('Credit')->setUserCredit($this->uid, 'init_userinfo');
//                $data['is_init'] = 1;
//            }
//        }
//
//        if (!($res = D('User', 'home')->save($data))) {
//            
//        }
//        // 更新缓存
//        D('User', 'home')->resetUserInfoCache($this->uid);
//
//        echo 1; //更新成功
//    }
//
//    public function savePic() { //保存LOGO
//        if (!empty($_FILES['pic_url']['name'])) {
//            $attach_type = 'edit_userinfo_pic_url';
//            $logo_options['save_to_db'] = true;
//            $logo = X('Xattach')->upload($attach_type, $logo_options);
//            if ($logo['status']) {
//                $logofile = UPLOAD_URL . '/' . $logo['info'][0]['savepath'] . $logo['info'][0]['savename'];
//            }
//            $_POST['pic_url'] = $logofile;
//            $_POST['attach_id'] = $logo['info'][0]['id'];
//        }
//    }

}
