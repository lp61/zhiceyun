<?php

/**
 * Description of taskCronAction
 *
 * @author vini
 */
class sendTask {

    //业务接口地址
    protected $upload_url = 'http://42.96.171.6:8008/zhiceyun/soft/upload.php'; //apk上传接口
    protected $taskCheck_url = 'http://10.1.10.100/management/rest/uri/detectionModel'; //任务下发检测接口地址
    protected $taskSend_url = 'http://10.1.10.100/management/rest/uri/createPlan'; //任务下发接口地址
    protected $checkProgress_url = 'http://10.1.10.100/management/rest/uri/planProgress'; //查看任务进度接口地址
    //计划任务日志路径
    protected $log_path = '';
    //下发测试默认配置
    protected $runType = 2;
    protected $runTime = 10;
    protected $runNum = 100;
    protected $errCode;
    protected $errMsg;

    /*
     * TASK-1：队列任务下发 (按包类型下发)
     * 每5分钟 执行一次
     * 执行命令：cron_index.php task\sendTask
     * +--------------------------------------------------------
     * 1. 每种包类型任务下发各自队列中的第一条
     * 2. 下发排序依据（优先级）：队列置顶标识（send_order从高到低）->下发失败次数（send_num从小到大）->任务提交时间（ctime正序）
     * 
     */

    public function send($_one) {
        $data = array();
        $data['packageType'] = $_one['package_type']; //下发接口检测参数 1.测试包类型
        $data['businessID'] = $_one['businessID']; //下发接口检测参数 2.测试业务ID
        $data['sourceID'] = $_one['sourceID']; //下发接口检测参数 3.任务源ID
        $data['deviceType'] = $_one['device_type']; //下发接口检测参数 4.测试终端型号
        $mininum = 1; //下发任务最小终端数限制

        LogWrite('senTask', "check start...", json_encode($data));
        $check_result = $this->request_post($this->taskCheck_url, json_encode($data)); //STEP-1 任务检测接口
        if ($check_result['status'] == 1 && $check_result['deviceStatus'] == 1) {
            //status=1检测执行成功；deviceStatus=1该包下终端全空闲
            if ($data['deviceType'] == '' && $check_result['deviceNum'] < $mininum) {
                //在线空闲的可用设备数量不满足最小下发要求
                $this->errCode = 1;
                $this->errMsg = '无可用测试包';
                return FALSE;
            }
            $data['uuid'] = $_one['uuid'] . "_" . $_one['retry']; //任务下发接口参数 5.任务UUID
            $data['runType'] = $this->runType; //任务下发接口参数 6.测试类型
            $data['runTime'] = $this->runTime; //任务下发接口参数 7.测试时间
            $data['runNum'] = $this->runNum; //任务下发接口参数 8.测试数量
            $data['apkurl'] = $_one['apkurl']; //任务下发接口参数 9.apk下载地址
            $data['isShared'] = $_one['account_type']; //任务下发接口参数 10.账号是否共享
            $data['accounts'] = json_decode($_one['account'], TRUE); //任务下发接口参数 11.账号信息

            LogWrite('senTask', "task sending...", json_encode($data));
            $send_result = $this->request_post($this->taskSend_url, json_encode($data)); //STEP-2 任务下发接口
            LogWrite('senTask', "end sending.", json_encode($send_result));
            if ($send_result['status'] == 1) {
                return $send_result;
            } elseif ($send_result === FALSE) {
                $this->errCode = 2;
                $this->errMsg = '网络错误';
                return FALSE;
            } else {
                //其它异常：任务下发前检测通过，但任务下发失败，不累计下发失败次数，再次下发时队列优先级不变
                $this->errCode = 3;
                $this->errMsg = '系统异常';
                return FALSE;
            }
        } else {
            //任务下发前检测失败或包忙碌：下发失败（重发）
            $this->errCode = 1;
            $this->errMsg = '测试包忙碌';
            return FALSE;
        }
    }

    /*
     * 查询任务进度接口
     * +-----------------------------------------------------------------
     * @param $uuid 任务ID 348cd2d4-86e4-97a1-2e80-8c4ca157a496
     * +-----------------------------------------------------------------
     * @return {"message":"获取任务进度成功","progress":"60.00%","status":1}
     */

    public function checkProgress($uuid = '') {
        if (!empty($uuid)) {
            $this->errCode = 1;
            $this->errMsg = '非法的UUID';
            return FALSE;
        }
        $data['uuid'] = $uuid;
        $check_result = $this->request_post($this->checkProgress_url, json_encode($data)); //任务下发接口请求
        if ($check_result['status'] == 1) {
            //获取进度成功
            return $check_result['progress']; //string类型
        } else {
            //获取进度失败
            $this->errCode = 2;
            $this->errMsg = $check_result['message'];
            return FALSE;
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 设置超时限制防止死循环
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

    public function getError() {
        $data['code'] = $this->errCode;
        $data['msg'] = $this->errMsg;
        return $data;
    }

    public function getErrorMsg() {
        return $this->errMsg;
    }

}
