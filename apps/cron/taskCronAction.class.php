<?php

/**
 * Description of taskCronAction
 *
 * @author vini
 */
class taskCronAction {

    //接口检测参数
    protected $remote_server1 = '114.242.192.204';
    protected $remote_server2 = '114.242.192.205';
    protected $port = '80';
    protected $timeout = 5;
    //业务接口地址
//    protected $upload_url = 'http://42.96.171.6:8008/zhiceyun/soft/upload.php'; //apk上传接口
    protected $upload_url = 'http://localhost/zhiceyun/soft/upload.php'; //apk上传接口
//    protected $url = 'http://42.96.171.6:8008/zhiceyun/sync/transpond/index.php'; //业务转发服务器接口地址
    protected $url = 'http://localhost/zhiceyun/sync/transpond/index.php'; //业务转发服务器接口地址
//    protected $taskCheck_url = 'http://10.1.10.100/management/rest/uri/detectionModel'; //任务下发检测接口地址
//    protected $taskSend_url = 'http://10.1.10.100/management/rest/uri/createPlan'; //任务下发接口地址
//    protected $checkProgress_url = 'http://10.1.10.100/management/rest/uri/planProgress'; //查看任务进度接口地址
    protected $taskCheck_url = 'http://10.1.10.15:8080/management/rest/uri/detectionModel'; //任务下发检测接口地址
    protected $taskSend_url = 'http://10.1.10.15:8080/management/rest/uri/createPlan'; //任务下发接口地址
    protected $checkProgress_url = 'http://10.1.10.15:8080/management/rest/uri/planProgress'; //查看任务进度接口地址
    //计划任务日志路径
    protected $log_path = '';
    //下发测试默认配置
    protected $runType = 2;
    protected $runTime = 10;
    protected $runNum = 100;

    public function __construct() {
        $this->log_path = SITE_PATH . '/logs/' . date("Ym") . "/" . date("d") . "/"; //日志文件路径;
        $this->CreateAllDir($this->log_path);
    }

    /*
     * TASK-1：队列任务下发 (按包类型下发)
     * 每5分钟 执行一次
     * 执行命令：cron_index.php task\sendTask
     * +--------------------------------------------------------
     * 1. 每种包类型任务下发各自队列中的第一条
     * 2. 下发排序依据（优先级）：队列置顶标识（send_order从高到低）->下发失败次数（send_num从小到大）->任务提交时间（ctime正序）
     * 
     */

    public function sendTask() {
        $this->log_path = $this->log_path . 'cron_sendTask.log';

        //STEP-1 从队列中按优先级取出每个包中队首的任务
        $packages = array(1, 2, 3, 4, 5, 8, 9, 10, 11);  //1-8 快速测试包->专机测试包 添加9、10、11等清华测试包
        $map['send_status'] = array('between', '1,4'); //未下发的任务 -1-apk转存中 0-下发成功 1-排队中 2-下发失败 3-排队中（重测任务） 4-网络错误 5-apk文件异常
        $map['task_status'] = array('lt', 2); //0-未开始 1-排队中 2-测试中（重测）的任务 3-测试失败
        $map['status'] = 1; //提交成功
        $model = M('app_test_task');
        foreach ($packages as $value) {
            $map['package_type'] = $value;
            $row = $model->where($map)->order('send_order desc,send_num asc,ctime asc')->find();
            if (!empty($row)) {
                //Log::write($model->getLastSql(), LOG::INFO, 3, $this->log_path);
                $list[] = $row;
            }
        }
        if (empty($list)) {
            return; //没有可下发任务或远程服务器连接失败，直接返回
        }
        Log::write(json_encode($list), LOG::INFO, 3, $this->log_path);

        //STEP-2 下发任务
        $time = time();
        $model_package = M('app_test_package');
        foreach ($list as $_one) {
            $where['package_type'] = $_one['package_type'];

            $data = array();
            $id = $_one['id'];
            $data['packageType'] = $_one['package_type']; //下发接口检测参数 1.测试包类型
            $data['businessID'] = $_one['businessID']; //下发接口检测参数 2.测试业务ID
            $data['sourceID'] = $_one['sourceID']; //下发接口检测参数 3.任务源ID
            $data['deviceType'] = $_one['retry'] > 0 ? $_one['retry_device_type'] : $_one['device_type']; //下发接口检测参数 4.测试终端型号
            $_one['deviceNum'] = $data['deviceType'] == '' ? 0 : $model_package->where($where)->getField('mininum'); //下发任务最小终端数限制

            $post['url'] = $this->taskCheck_url; //STEP-1 任务检测接口请求地址
            $post['data'] = $data;
            Log::write($_one['uuid'] . "  " . $_one['package_type'] . " check start...", LOG::INFO, 3, $this->log_path);
            $check_result = $this->request_post($this->url, json_encode($post));
            if ($check_result['status'] == 1 && $check_result['deviceStatus'] == 1) { //status=1检测执行成功；deviceStatus=1该包下终端全空闲
                if ($data['deviceType'] == '' && $check_result['deviceNum'] < $_one['deviceNum']) {
                    //3-非选定终端的任务，在线空闲的可用设备数量不满足最小下发要求
                    $model->where("id='$id'")->setField('send_status', 2); //=====此类下发失败会造成任务阻塞，需要人工解决=====\
                    $model_package->where($where)->setField('status', 3); //更新通道状态
                    Log::write($_one['deviceNum'] . " check failed: device not enough! " . json_encode($check_result) . "\r\n", LOG::INFO, 3, $this->log_path);
                    continue;
                }
                $data['uuid'] = $_one['uuid'] . "_" . $_one['retry']; //任务下发接口参数 5.任务UUID
                $data['runType'] = $this->runType; //任务下发接口参数 6.测试类型
                $data['runTime'] = $this->runTime; //任务下发接口参数 7.测试时间
                $data['runNum'] = $this->runNum; //任务下发接口参数 8.测试数量
                $data['apkurl'] = $_one['apkurl']; //任务下发接口参数 9.apk下载地址
                $data['isShared'] = $_one['account_type']; //任务下发接口参数 10.账号是否共享
                $data['accounts'] = json_decode($_one['account'], TRUE); //任务下发接口参数 11.账号信息

                $post['url'] = $this->taskSend_url; //STEP-2 任务下发接口请求地址
                $post['data'] = $data;
                Log::write("task sending...", LOG::INFO, 3, $this->log_path);
                $send_result = $this->request_post($this->url, json_encode($post));
//                $send_result = 1;
                if ($send_result['status'] == 1) {
                    //1-通道正常：任务下发成功
                    $save_data['send_status'] = 0; //更新下发状态：下发成功
                    $save_data['task_status'] = 1; //更新任务状态：测试中
                    $save_data['stime'] = $time; //更新下发时间
                    if ($_one['retry'] == 0) {
                        //任务首次下发，记录实际下发的终端型号到device_type字段
                        $models = $send_result['models'];
//                        $models = "测试 很多很多";
                        $device_type = "";
                        if (!empty($models)) {
                            foreach ($models as $one) {
                                $device_type .= $one . ",";
                            }
                            $device_type = substr($device_type, 0, -1);
                        }
//                        $save_data['device_num'] = $send_result['modelNum']; //任务实际测试终端数量
                        $save_data['device_type'] = $device_type; //任务实际测试终端型号
                        //发送微信测试任务结果通知（任务开始通知）
                        $map1['uid'] = $_one['user_id'];
                        $map1['status'] = 1;
                        $touser = M('wx_user')->where($map1)->getField('openID');
                        if ($touser) {
                            $sendInfo['first'] = '您好，亲爱的开发者，您刚刚提交的测试任务已开始！';
                            $sendInfo['key1'] = $_one['uuid']; //任务ID
                            $sendInfo['key2'] = $_one['apk_name']; //任务名称
                            $sendInfo['key3'] = '已开始'; //状态
//                            service('Weixin')->send_notice($touser, 6, $sendInfo);
                        }
                        log::write($touser . "\t" . print_r($sendInfo, TRUE), LOG::INFO);
                        //创建测试任务分享验证链接
                        service('Validation')->addValidation($_one['user_id'], '', U('home/report/index', array('uuid' => $_one['uuid'])), 'share_report', $_one['uuid']);
                    }
                    $model->where("id='$id'")->save($save_data);
                    $model_package->where($where)->setField('status', 1); //更新通道状态
                    Log::write($_one['uuid'] . " send successful. " . $model->getLastSql() . "\r\n", LOG::INFO, 3, $this->log_path);
                } elseif ($send_result === FALSE) {
                    //4-网络超时：不累计下发失败次数，再次下发时队列优先级不变
                    $model->where("id='$id'")->setField('send_status', 4); //=====此类下发失败，下次下发时需要检测任务进度，如果已开始则不再下发
                    $model_package->where($where)->setField('status', 4); //更新通道状态
                    continue;
                } else {    //任务下发失败
                    //5-其它异常：任务下发前检测通过，但任务下发失败，不累计下发失败次数，再次下发时队列优先级不变
                    $model->where("id='$id'")->setField('send_status', 2); //=====此类下发失败会造成任务阻塞，需要人工解决=====
                    $model_package->where($where)->setField('status', 5); //更新通道状态
                    continue;
                }
            } else {
                //2-任务下发前检测失败或包忙碌：下发失败（重发）
                if ($data['retry'] == 0 && $data['deviceType'] == '') {
                    //第一次下发并且非自定义终端的任务，不累计下发失败次数，再次下发时队列优先级不变
                    $model->where("id='$id'")->setField('send_status', 2); //=====此类下发失败会造成任务阻塞，需要人工解决=====
                } else {
                    //降低优先级：重发任务或者自定义终端任务，累计下发失败次数，再次下发时队列优先级会降低
                    $model->query("UPDATE  ts_app_test_task  SET  `send_status`=2,`send_num`=send_num+1  WHERE  id='$id'"); //下发失败（重发）：检测失败或设备忙碌
                }
                $model_package->where($where)->setField('status', 2); //更新通道状态
                Log::write($_one['uuid'] . " check failed_2. " . $model->getLastSql() . "\r\n", LOG::INFO, 3, $this->log_path);
                continue;
            }
        }
    }

    /*
     * 查询任务进度接口
     * +-----------------------------------------------------------------
     * @param $uuid 任务ID 348cd2d4-86e4-97a1-2e80-8c4ca157a496
     * +-----------------------------------------------------------------
     * @return {"message":"获取任务进度成功","progress":"60.00%","status":1}
     */

    public function checkProgress($uuid = 'feee1025-0e79-cf8b-199a-263878e423e9') {
//        if (!$this->check_connect()) {
//            echo 'remote server connect failed!'; //远程服务器连接失败，直接返回
//            return;
//        }
        $post['url'] = $this->checkProgress_url; //任务下发接口请求地址
        $data['uuid'] = $uuid;
        $post['data'] = $data;
        $check_result = $this->request_post($this->url, json_encode($post));
        if ($check_result['status'] == 1) {
            //echo $check_result['progress'];
            //获取进度成功
            return $check_result['progress']; //string类型
        } else {
            //echo $check_result['message'];
            //获取进度失败
            return FALSE;
        }
    }

    /*
     * TASK-2：
     * 每1分钟 执行一次
     * 执行命令：cron_index.php task\sendAPK
     * +----------------------------------
     * 功能1：apk包同步。通知内网服务器，进行apk下载
     * 功能2：微信消息通知。处理微信通知队列
     */

    function sendApk() {
        include('soft/upload_qh.php');
        //FUN-1
        $map['send_status'] = -1; //未同步APK的任务
        $map['status'] = 1; //提交成功
        $model = M('app_test_task');
        $list = $model->where($map)->field('uuid,attach_id,apk_url,apk_sname')->order('ctime asc')->limit(5)->findAll();
        if (!empty($list)) {
            foreach ($list as $_one) {
                $this->request_post($this->upload_url, $_one, 0); //不接收POST请求返回
            }
        }

        //FUN-2
//        $this->send_wx_notice();
        return; //没有需同步APK包，直接返回
    }

    /*
     * 处理微信通知队列
     */

    function send_wx_notice() {
        $map['status'] = 0; //待发送的微信通知
        $list = M('wx_push')->where($map)->limit(50)->select();
        //log::write(print_r($list, true), LOG::INFO);
        if ($list) {
            foreach ($list as $_one) {
                $touser = $_one['openID'];
                $type = $_one['type'];
                $sendInfo['first'] = $_one['first'];
                $sendInfo['key1'] = $_one['key1'];
                $sendInfo['key2'] = $_one['key2'];
                $sendInfo['key3'] = $_one['key3'];
                $sendInfo['key4'] = $_one['key4'];
                $sendInfo['key5'] = $_one['key5'];
                $sendInfo['remark'] = $_one['remark'];
                $notice_send = service('Weixin')->send_notice($touser, $type, $sendInfo);
                //log::write(print_r($notice_send, true), LOG::INFO);

                if ($notice_send) {
                    M('wx_push')->where("id='" . $_one['id'] . "'")->setField('status', 1); //消息推送成功
                } else {
                    M('wx_push')->where("id='" . $_one['id'] . "'")->setField('status', -1); //消息推送失败
                }
            }
        }
    }

    /**
     * 发送post请求
     * @param string $url
     * @param string $param
     * @param int    $type
     * @return bool|mixed
     */
    function request_post($url = '', $param = '', $type = 1) {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1200); // 设置超时限制防止死循环
        if ($type == 1) {
            $data = curl_exec($ch); //运行curl
            curl_close($ch);
            return $data === FALSE ? FALSE : json_decode($data, TRUE);
        } else {
            curl_exec($ch); //运行curl
            curl_close($ch);
            return TRUE;
        }
    }

    function check_connect() {
        $this->log_path = SITE_PATH . '/logs/connect_error.log';
        if ($this->check_port($this->remote_server1, $this->port, $this->timeout)) {
            return TRUE;
        } else {
            if ($this->check_port($this->remote_server2, $this->port, $this->timeout)) {
                $this->upload_url = str_replace($this->remote_server1, $this->remote_server2, $this->upload_url);
                $this->url = str_replace($this->remote_server1, $this->remote_server2, $this->url);
                //发送邮件通知
                $e_title = $this->remote_server1 . " connect error!";
                $e_content = '内网服务器连接错误，请尽快处理，以免影响正常的测试业务！';
                $this->send_email($e_title, $e_content);
                return TRUE;
            } else {
                //发送邮件通知
                $e_title = $this->remote_server1 . " and " . $this->remote_server2 . " connect error!";
                $e_content = '内网服务器连接错误，请尽快处理，以免影响正常的测试业务！';
                $this->send_email($e_title, $e_content);
                return FALSE;
            }
        }
    }

    function check_port($remote_server, $port, $timeout) {
        $socket = fsockopen($remote_server, $port, $errno, $errstr, $timeout);
        if ($socket) {
            return true;
        } else {
            return false;
        }
    }

    function send_email($e_title, $e_content) {
        //远程服务器端口检测失败
        $result1 = M('app_test_config')->select();
        foreach ($result1 as $va) {
            $config[$va['key']] = $va['value'];
        }
        $email_sent = service('Mail')->send_email($config['notice_emails'], $e_title, $e_content);
        $send_email_id = model('AppEmailSend')->addSendRecord($config['notice_emails'], $e_title, $e_content, 4);
        if ($email_sent) {
            model('AppEmailSend')->setSendStatus($send_email_id);
            $msg = $config['notice_emails'] . ":send email successful!";
        } else {
            model('AppEmailSend')->setSendStatus($send_email_id, -1);
            $msg = $config['notice_emails'] . ":send email failed!";
        }
        $log_content = $e_title . " \t " . $msg;
        Log::write($log_content, LOG::INFO, 3, $this->log_path);
    }

    //创建指定目录
    function CreateAllDir($dir) {
        $dir_array = explode("/", $dir);
        $DirName = "";
        for ($i = 0; $i < count($dir_array); $i++) {
            if ($dir_array[$i] != "") {
                $DirName .= "/" . $dir_array[$i];
                if (!is_dir($DirName)) {
                    mkdir($DirName, 0755);
                }
            }
        }
    }

    /*
     * 一期用户数据同步任务，每日执行
     * +------------------------------------------------
     * 更新用户规则如下：
     * 1. 用户资料的更新，只更新密码，其它不做同步。
     * 2. 在一、二期上都注册的用户，不会拿一期数据去同步二期。
     * 
     * 插入用户规则如下：
     * 1. 重复username的只提取一个（GROUP BY）。
     * 2. username为空的用户过滤（'','NULL'）。
     * 3. name为空（username非邮箱账号）的第三方登陆用户过滤 （'NULL'）。
     * 4. 二期账号已存在的用户过滤。
     * 5. 老用户统一设置初始积分50积分；统一分组至“3-老用户组”。
     * 6. 老用户都是已激活用户，都是未完善资料用户（完善qq/微信/微博会奖励积分）。
     */

    public function userSync() {
        $this->log_path = $this->log_path . 'cron_userSync.log';
        /*
          //数据库初始化
          include_once(SITE_PATH . "/addons/libs/Mysql.class.php");
          $config['mDbHost'] = "smarterapps.mysql.rds.aliyuncs.com";
          $config['mDbUser'] = "smarterapps";
          $config['mDbPassword'] = "qwerasdf";
          $config['mDbPort'] = "3306";
          $config['mDbDatabase'] = "smarterapps";
          $db = new Connect_Database((object) $config);
         * 
         */

        $model = M();
        //SETEP-1 从一期网站向二期网站，更新密码
        $sql1 = "UPDATE ts_user a, ts_user_old b SET a.password=b.password WHERE a.email=b.username AND a.`password`<>b.`password` AND a.status>=0";
        $model->query($sql1);
        //STEP-2 将一期网站的新增用户信息，同步至二期网站的用户表中；并记录下本次同步执行到的记录ID
        $score = 50; //初始积分：50

        $map['key'] = 'max_update_id';
        $max_id = M('app_test_config')->where($map)->getField('value'); //更新记录ID
        $sql2 = "SELECT id,username as email,`name` as uname,`password`,1 as is_active,0 as user_type,IFNULL(mobile,'') as phone,"
            . "IFNULL(companyname,'') as company_name,IFNULL(qq,'') AS qq,UNIX_TIMESTAMP(modifytime) as ctime,`status` "
            . "FROM ts_user_old WHERE id>'$max_id' AND `name`<>'NULL' AND username NOT in ('','NULL') GROUP BY username ORDER BY id DESC";
        Log::write($sql2, LOG::INFO, 3, $this->log_path);
        $rows = $model->query($sql2);
        if (empty($rows)) {
            exit('new user is empty.');
        }

        Log::write("total count:" . count($rows), LOG::INFO, 3, $this->log_path);
        $i = 0;
        $a = array();
        $group_id = 3; //老用户组ID
        $group_name = M('user_group')->where("`user_group_id`='$group_id'")->getField('title');
        $User = M('user');
        foreach ($rows as $_one) {
            if (!$model->query("SELECT uid FROM ts_user WHERE email='" . $_one['email'] . "' LIMIT 1")) {
                unset($_one['id']);
                $b[] = $uid = $User->add($_one);
                Log::write($User->getLastSql(), LOG::INFO, 3, $this->log_path);
                if ($uid) {
                    $model->query("INSERT INTO ts_credit_user SET `uid`='$uid',`score`='$score'"); //设定积分
                    $model->query("INSERT INTO ts_user_group_link SET `user_group_id`='$group_id',`user_group_title`='$group_name',`uid`='$uid'"); //设定用户组
                    $i++;
                }
            } else {
                $a[] = $_one['email'];
            }
        }
        $max_id_new = M('user_old')->max('id'); //新的更新记录ID
        M('app_test_config')->where($map)->setField('value', $max_id_new);

        Log::write("actual insert:$i \r\n " . implode(',', $b) . " \r\n existed:" . implode(',', $a), LOG::INFO, 3, $this->log_path);
    }


}
