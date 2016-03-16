<?php

/**
 * Description of TestRecordAction
 *
 * @author vini
 */
class TestRecordAction extends Action {

    //put your code here
    public function record() {
        if (!$this->mid) {
            redirect(U('wap/Public/login'));
        }
        $uid = $this->mid;
        $model = new Model();
        $sql = "SELECT a.task_status,a.apk_name,a.package_type,a.businessID,c.star,a.device_num,b.install_failed_num,b.run_failed_num,(b.optimize_num+b.success_num) as success_num FROM "
                . "(ts_app_test_task a  LEFT JOIN ts_apptest_result_network c ON a.uuid=c.uuid ) LEFT JOIN ts_apptest_result_comp b ON a.uuid=b.uuid WHERE  a.user_id='$uid' AND status=1 ORDER BY a.ctime DESC";
        $mo = $model->query($sql);
        $this->assign('mo', $mo);
        $this->assign('title', '测试记录');
        $this->display();
    }

    public function wxrecord() {
        $uid = $this->oauth2();
        $model = new Model();
        $sql = "SELECT a.task_status,a.apk_name,a.package_type,a.businessID,c.star,a.device_num,b.install_failed_num,b.run_failed_num,(b.optimize_num+b.success_num) as success_num FROM "
                . "(ts_app_test_task a  LEFT JOIN ts_apptest_result_network c ON a.uuid=c.uuid ) LEFT JOIN ts_apptest_result_comp b ON a.uuid=b.uuid WHERE   a.user_id='$uid' AND status=1 ORDER BY a.ctime DESC";
        $mo = $model->query($sql);
        $this->assign('mo', $mo);
        $this->assign('title', '测试记录');
        $this->display('record');
    }

    /*
     * oauth2认证
     * +==================================
     * 若此微信用户已绑定，则返回绑定用户ID；否则跳转至绑定页面
     */

    public function oauth2() {
        $appid = 'wx853e30fa671758e0';
        $appsecret = 'fd0ce8cc6de36ef771e2fc5f9b260876';
        if (!isset($_GET['state'])) {
            //如果不是授权回调请求，则进行授权
            $redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . urlencode($redirect_url) . '&response_type=code&scope=snsapi_base&state=1#wechat_redirect');
            exit;
        } else { //授权回调处理
            if (isset($_GET['code'])) { //同意授权，获取用户微信基本信息
                //STEP-1 获取ACCESS_TOKEN
                $code = $_GET['code'];
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appid . "&secret=" . $appsecret . "&code=" . $code . "&grant_type=authorization_code";
                $re = file_get_contents($url);
                $arr = json_decode($re, TRUE);
                //STEP-2 获取用户微信基本信息
                if (isset($arr['access_token'])) {
                    $openid = $arr['openid'];
                    $model = M('wx_user');
                    $row = $model->where("openID='$openid'")->find();
                    if (!$row) {
                        $_SESSION['wx_openID'] = $openid;
                        $this->display('binding'); //跳转绑定页
                        exit;
                    } else {
                        return $row['uid'];
                    }
                }
            }
            exit("未授权！");
        }
    }

    //用户绑定
    public function bind() {
        if (empty($_POST['username']) || empty($_POST['pwd'])) {
            $this->assign('waitSecond', 2);
            $this->error("用户名和密码不能为空！");
        }
        $map['email'] = $_POST['username'];
        $map['password'] = md5($_POST['pwd']);
        $User = M('user');
        $row = $User->where($map)->find();
        if (empty($row)) {
            $this->error("用户名或密码错误！");
        }
        if (empty($_SESSION['wx_openID'])) {
            redirect(U('wap/Index/index')); //跳转到移动端首页
        }

        $model = M('wx_user');
        if ($model->where("uid=" . $row['uid'])->find()) {
            $this->error("该账号已绑定,请更换其它账号！");
        } else {
            $data['uid'] = $row['uid'];
            $data['openID'] = $_SESSION['wx_openID'];
            $data['ctime'] = date("Y-m-d H:i:s");
            $data['status'] = 1;
            $model->add($data);
            X('Credit')->setUserCredit($data['uid'], 'attention_wx');
        }
        redirect(U('wap/TestRecord/wxrecord')); //跳转到测试记录
    }

}
