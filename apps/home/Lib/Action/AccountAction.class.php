<?php

/**
 * 用户管理中心
 * @author Nonant
 *
 */
import('@.Form.UsersFormNamespace');
import('@.Form.AppEmailSendVars');
import('@.Form.AppTestVars');

class AccountAction extends Action {

    var $pUser;

    function _initialize() {
        if (!$this->mid) {
            redirect(U('home/Index/index'));
        }

        $this->pUser = D('UserProfile');
        $this->pUser->uid = $this->mid;

        $menu[] = array('act' => 'index', 'name' => L('personal_profile'));
        $menu[] = array('act' => 'avatar', 'name' => L('face_setting'));
        $menu[] = array('act' => 'security', 'name' => L('account_safe'));
        // $menu[] = array( 'act' => 'bind',       'name' => L('outer_bind') );
        $menu[] = array('act' => 'invite', 'name' => L('invite'));
        $menu[] = array('act' => 'credit', 'name' => L('integral_rule'));
        $menu[] = array('act' => 'feedback', 'name' => L('user_feedback'));

        $this->assign('accountmenu', $menu);
    }

    // 有不存在的ACTION操作的时候执行
    protected function _empty() {
        if (empty($_POST)) {
            $this->display('addons');
        }
    }

    //用户中心-个人资料
    function index() {
        $userType = $_REQUEST['utype'] ? $_REQUEST['utype'] : 0;
        $userType = max($this->user['user_type'], $userType);
        if (!array_key_exists($userType, UserInfoVars::$USER_TYPE_CONFIG)) {
            $userType = UserInfoVars::USER_NORMAL;
        }
        // dump($this->user);
        $validString = UserInfoVars::$USER_TYPE_CONFIG[$userType]['validate'];
        $validateFunc = 'set' . ucfirst($validString) . 'UserValidateField';

        self::$FORM_NAMESPACE = new UsersFormNamespace(true);
        self::$FORM_NAMESPACE->$validateFunc();
        $this->assign('utype', $userType);
        //如果为填写过手机号，则显示手机号和验证码字段；如果已有手机号，则默认手机号置灰，不显示验证码（点击更换后显示）
        $this->assign('phoneShow', empty($this->user['phone']) ? 1 : 0);
        $this->assign('workClassify', UserInfoVars::getWorkClassify());
        $this->assign('workStation', UserInfoVars::getWorkStation());
        $this->assign('companySize', UserInfoVars::getCompanySize());
        $this->assign('userTypeConfig', UserInfoVars::$USER_TYPE_CONFIG);

        if (self::$FORM_NAMESPACE->isPostBack() && self::$FORM_NAMESPACE->validate()) {
            $flag = 0;
            if (!$this->uid) {
                $this->error(L('post_arg_error'));
            }
            $data['uid'] = $this->uid;
            if (isset($_POST['utype']) && $this->user['user_type'] <= $_POST['utype']) {
                $data['is_audit'] = $this->user['user_type'] == UserInfoVars::USER_NORMAL ? UserInfoVars::YES_AUDIT : UserInfoVars::IS_AUDITING; // 用户升级需要审核 
                $data['user_type'] = $_POST['utype']; // 默认0
                if ($data['user_type'] == UserInfoVars::USER_COM) {
                    $this->savePic();
                }
                //用户等级提升，发送微信通知
                if ($this->user['user_type'] < $_POST['utype']) {
                    $map['uid'] = $this->uid;
                    $map['status'] = 1;
                    $touser = M('wx_user')->where($map)->getField('openID');
                    if ($touser) {
                        $toName = UserInfoVars::$USER_TYPE_CONFIG[$_POST['utype']]['name'];
                        $fromName = UserInfoVars::$USER_TYPE_CONFIG[$this->user['user_type']]['name'];
                        $sendInfo['first'] = '亲爱的开发者，恭喜你成功升级为' . $toName;
                        $sendInfo['key1'] = $fromName;
                        $sendInfo['key2'] = $toName;
                        $sendInfo['key3'] = date("Y年m月d日 H:i");
                        service('Weixin')->send_notice($touser, 2, $sendInfo);
                    }
                }
            }
            //个性信息
            if (isset($_POST['uname'])) {
                $data['uname'] = t($_POST['uname']);
            }
            if (isset($_POST['phone'])) {
                $data['phone'] = $_POST['phone'];
            }
            if (isset($_POST['company_name'])) {
                $data['company_name'] = t($_POST['company_name']);
            }
            if (isset($_POST['company_size'])) {
                $data['company_size'] = $_POST['company_size'];
            }
            if (isset($_POST['work_classify'])) {
                $data['work_classify'] = $_POST['work_classify'];
            }
            if (isset($_POST['work_station'])) {
                $data['work_station'] = $_POST['work_station'];
            }
            if (isset($_POST['attach_id'])) {
                $data['licence_img'] = $_POST['pic_url'];
                $data['attach_id'] = $_POST['attach_id'];
            }
            if (!empty($_POST['qq'])) {
                $data['qq'] = $_POST['qq'];
                $flag = 1;
            }
            if (!empty($_POST['weixin'])) {
                $data['weixin'] = $_POST['weixin'];
                $flag = 1;
            }
            if (!empty($_POST['weibo'])) {
                $data['weibo'] = $_POST['weibo'];
                $flag = 1;
            }
            $data['mtime'] = time();

            //完善QQ/微信/微博，获取积分奖励
            if ($flag) {
                if ($this->user['is_init'] != 1) {
                    // 首次完善个人资料 +积分
                    X('Credit')->setUserCredit($this->uid, 'init_userinfo');
                    $data['is_init'] = 1;
                }
            }

            $this->assign('waitSecond', 3);
            if (!($res = D('User', 'home')->save($data))) {
                $this->error(L('personal_profile') . L('save_error'));
            }
            // 更新缓存
            D('User', 'home')->resetUserInfoCache($this->uid);
            $this->success(L('personal_profile') . L('save_success'));
        }

        $this->setTitle(L('personal_profile'));
        $this->display();
    }

    //保存图片
    public function savePic() {
        if (!empty($_FILES['pic_url']['name'])) {
            $attach_type = 'edit_userinfo_pic_url';
            $logo_options['save_to_db'] = true;
            $logo = X('Xattach')->upload($attach_type, $logo_options);
            if ($logo['status']) {
                $logofile = UPLOAD_URL . '/' . $logo['info'][0]['savepath'] . $logo['info'][0]['savename'];
            }
            $_POST['pic_url'] = $logofile;
            $_POST['attach_id'] = $logo['info'][0]['id'];
        }
    }

    //更新资料
    function update() {
        D('User', 'home')->resetUserInfoCache($this->uid);
        $nickname = $_REQUEST['nickname'];

        //检查禁止注册的用户昵称
        $audit = model('Xdata')->lget('audit');
        if ($audit['banuid'] == 1) {
            $bannedunames = $audit['bannedunames'];
            if (!empty($bannedunames)) {
                $bannedunames = explode('|', $bannedunames);
                if (in_array($nickname, $bannedunames)) {
                    exit(json_encode(array('message' => '这个昵称禁止注册', 'boolen' => 0)));
                }
            }
        }

        exit(json_encode($this->pUser->upDate(t($_REQUEST['dotype']))));
    }

    //邀请
    public function invite() {
        vendor('libs.MyInviteCode', null, '.class.php');
        $inviteCode = MyInviteCode::num2code($this->mid);
        $inviteUrl = U('home/Public/register', array('inviteCode' => $inviteCode));
        if ($_POST) {
            $emails = $_POST['emails'];
            $uname = $_POST['uname'];
            if (empty($emails)) {
                $this->error(L('email_format_error'));
            }
            $emailsArr = explode(',', $emails);
            $k = 0;
            foreach ($emailsArr as $key => $email) {
                if (empty($email)) {
                    $k++;
                    continue;
                }
                if (!preg_match('/^\w+((-|\.)\w+)*@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/', $email)) {
                    $this->error(L('email_format_error'));
                }
            }
            if ($k == count($emailsArr)) {
                $this->error(L('email_format_error'));
            }

            foreach ($emailsArr as $key => $email) {
                if (empty($email)) {
                    $k++;
                    continue;
                }
                if (preg_match('/^\w+((-|\.)\w+)*@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/', $email)) {
                    $this->sendInviteEmail($email, $inviteUrl, $uname);
                }
            }

            $this->assign('waitSecond', 3);
            $this->assign('jumpUrl', U('home/Account/invite'));
            $this->success(L('save_success') . ',' . L('email_invite_later'));
        } else {
            $this->assign('inviteUrl', $inviteUrl);
            $this->setTitle(L('invite'));
            $this->display();
        }
    }

    private function sendInviteEmail($email, $inviteUrl, $uname = '') {
        $status = AppEmailSendVars::IS_WAITING;
        if (empty($email) || empty($inviteUrl)) {
            $status = AppEmailSendVars::NO_AVAIL_RECORD;
        }
        $uname = !empty($uname) ? $uname : $this->user['email'];
        $subject = "您的好友邀请您注册智测云帐号";
        $body = <<<EOF
<html>
<body>
    <h3>您好!您的好友{$uname}邀请您注册智测云。</h3>
    <p>请点击以下链接注册帐号：</p>
    <p><a href="{$inviteUrl}">{$inviteUrl}</a></p>
    <p>如果以上链接无法点击，请将上面的地址复制到您的浏览器（如IE）的地址栏。</p>
    <p>如果你错误地收到了此电子邮件，你无需执行任何操作。</p>
    <p><a href="http://www.smarterapps.cn" target="_blank">智测云</a></p>
<style type="text/css">
body{font-size:14px;font-family:arial,verdana,sans-serif;line-height:1.666;padding:0;margin:0;overflow:auto;white-space:normal;word-wrap:break-word;min-height:100px}
td, input, button, select, body{font-family:Helvetica, 'Microsoft Yahei', verdana}
pre {white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word;width:95%}
th,td{font-family:arial,verdana,sans-serif;line-height:1.666}
img{ border:0}
header,footer,section,aside,article,nav,hgroup,figure,figcaption{display:block}
</style>
</body>
</html>
EOF;

        $send_email_id = model('AppEmailSend')->addSendRecord($email, $subject, $body, AppEmailSendVars::REGISTER_INVITE, $status, AppEmailSendVars::SEND_BY_QUEUE);
    }

    public function doInvite() {
        $_POST['email'] = t($_POST['email']);
        if (!isValidEmail($_POST['email'])) {
            echo -1; //错误的Email格式
            return;
        }

        $map['email'] = $_POST['email'];
        $map['is_active'] = 1;
        if ($user = M('user')->where($map)->find()) {
            echo $user['id']; //被邀请人已存在
            return;
        }
        unset($map);

        //添加验证数据 之1
        $validation = service('Validation')->addValidation($this->mid, $_POST['email'], U('home/Public/inviteRegister'), 'test_invite');
        if (!$validation) {
            echo 0;
            return;
        }

        //发送邀请邮件
        global $ts;
        $data['title'] = array(
            'actor_name' => $ts['user']['uname'],
            'site_name' => $ts['site']['site_name'],
        );
        $data['body'] = array(
            'email' => $_POST['email'],
            'actor' => '<a href="' . U('home/Space/index', array('uid' => $ts['user']['uid'])) . '" target="_blank">' . $ts['user']['uname'] . '</a>',
            'site' => '<a href="' . U('home') . '" target="_blank">' . $ts['site']['site_name'] . '</a>',
        );
        $tpl_record = model('Template')->parseTemplate('invite_register', $data);
        unset($data);

        if ($tpl_record) {
            //echo -2; //邀请成功
            //添加验证数据 之2
            $map['target_url'] = $validation;
            M('validation')->where($map)->setField('data', serialize(array('tpl_record_id' => $tpl_record)));
            echo $validation;
        } else {
            echo 0;
        }
    }

    //邀请已存在的用户
    public function inviteExisted() {
        $this->assign('uid', intval($_GET['uid']));
        $this->display();
    }

    //删除资料
    function delprofile() {
        // 更新缓存
        D('User', 'home')->resetUserInfoCache($this->uid);
        $intId = intval($_REQUEST['id']);
        $pUserProfile = D('UserProfile');
        echo $pUserProfile->delprofile($intId, $this->mid);
    }

    //账户管理-密码修改
    public function security() {
        $validateFunc = 'setUserEditPwdValidateField';

        self::$FORM_NAMESPACE = new UsersFormNamespace(true);
        self::$FORM_NAMESPACE->$validateFunc();

        if (self::$FORM_NAMESPACE->isPostBack() && self::$FORM_NAMESPACE->validate()) {
            $this->doModifyPassword();
        }
        $this->setTitle(L('setting') . ' - ' . L('account_safe'));

        $this->display();
    }

    //修改密码
    public function doModifyPassword() {
        $dao = M('user');
        $map['uid'] = $this->mid;
        $map['password'] = md5($_POST['oldpassword']);
        // 更新缓存
        D('User', 'home')->resetUserInfoCache($this->uid);
        if ($dao->where($map)->find()) {
            include_once(SITE_PATH . '/api/uc_client/uc_sync.php');
            if (UC_SYNC) {
                $ucenter_user_ref = ts_get_ucenter_user_ref($this->mid);
                $uc_res = uc_user_edit($ucenter_user_ref['uc_username'], $_POST['oldpassword'], $_POST['password'], '');
                if ($uc_res == -8) {
                    $this->error(L('userprotected_no_right'));
                }
            }
            //$_POST['password']    = md5($_POST['password']);
            if ($dao->where($map)->setField('password', md5($_POST['password']))) {
                $this->success(L('save_success'));
            } else {
                $this->error(L('save_error'));
            }
        } else {
            $this->error(L('oldpassword_wrong'));
        }
    }

    /**
     * [checkOldPwd 检测原始密码]
     * @return [type] [description]
     */
    public function checkOldPwd() {
        if ($this->user['password'] == md5($_POST['oldpassword'])) {
            echo 'true';
        } else {
            echo '密码不对哦~~~';
        }
    }

    // 用户反馈
    public function feedback() {
        $validateFunc = 'setUserFeedbackValidateField';
        self::$FORM_NAMESPACE = new UsersFormNamespace(true);
        self::$FORM_NAMESPACE->$validateFunc();

        if (self::$FORM_NAMESPACE->isPostBack() && self::$FORM_NAMESPACE->validate()) {
            $this->doSaveFeedback();
            $this->assign('waitSecond', 3);
            $this->assign('jumpUrl', U('home/Account/feedbackList', array('type' => 1)));
            $this->success('反馈提交成功，感谢您的支持');
        }

        $this->assign('appTestConfig', AppTestVars::getAppTestConfig(-1));
        $this->setTitle(L('user_feedback'));
        $this->display();
    }

    //反馈列表
    public function feedbackList() {
        $map['uid'] = $this->uid;
        if ($_GET['type'] == 2) { //已回复
            $map['is_reply'] = 1;
        }
        $listData = M('user_feedback')->where($map)->order('ctime DESC')->findPage(15);

        $this->assign('type', $_GET['type']);
        $this->assign($listData);
        $this->setTitle(L('user_feedback'));
        $this->display();
    }

    //反馈保存
    private function doSaveFeedback() {
        $data['title'] = $_POST['title'];
        $data['type'] = $_POST['type'];
        $data['content'] = $_POST['content'];

        $time = time();
        if ($_POST['id']) {
            $data['id'] = $_POST['id'];
        } else {
            $data['ctime'] = $time;
            $data['uid'] = $this->uid;
        }

        $data['mtime'] = $time;
        M('user_feedback')->save($data) || M('user_feedback')->add($data);
    }

    //反馈回复
    public function replyFeedback() {
        import('@.Form.AppTestVars');
        $id = $_GET['id'];
        $fmap['id'] = $id;
        $fmap['isdel'] = 0;
        $feedback = M('user_feedback')->where($fmap)->find();

        $map['fid'] = $id;
        $map['isdel'] = 0;
        $listData = M('user_feedback_reply')->where($map)->order('ctime DESC')->findPage();
        $this->assign($listData);
        $this->assign('appTestConfig', AppTestVars::getAppTestConfig());
        $this->assign('feedback', $feedback);
        $this->display();
    }

    //反馈详情
    public function feedbackInfo() {
        $id = $_POST['id'];
        $map['fid'] = $id;
        $map['isdel'] = 0;
        $info = M('user_feedback_reply')->where($map)->order('ctime DESC')->find();
        echo json_encode($info);
    }

    //测试积分中心
    public function recharge() {
        if ($_REQUEST['type'] == 1) {
            //用户支付成功后，登录用户的缓存将修改
            D('User', 'home')->resetUserInfoCache($this->mid);
        }
        $map['uid'] = $this->mid;
        $score = M('credit_user')->where($map)->getField('score');
        $this->assign('score', $score);

        $listData = M('credit_user_log')->where($map)->order('ctime desc')->findPage(8);
        $this->assign($listData);
        $this->assign('bill_amount_max', $this->user['pay_amount'] - $this->user['bill_amount']); //可开发票的最大金额
        $this->display();
    }

    //开发票申请
    public function bill() {
        $max = $this->user['pay_amount'] - $this->user['bill_amount'];
        if (empty($_POST['title']) || empty($_POST['amount']) || $_POST['amount'] < 0) {
            $return['code'] = 1;
            $return['msg'] = "发票抬头或金额不能为空！";
            echo json_encode($return);
        }
        if ($_POST['amount'] > $max) {
            $return['code'] = 1;
            $return['msg'] = "发票金额超出当前可开具的最大金额！";
            echo json_encode($return);
        }
        $data['uid'] = $this->mid;
        $data['type'] = 1; //发票类型 1-普通发票（纸质）
        $data['title'] = $_POST['title'];
        $data['amount'] = $_POST['amount'];
        $data['ctime'] = date("Y-m-d H:i:s");
        $data['status'] = 0;

        $model = M('Bill');
        if ($model->add($data)) {
            $return['code'] = 0;
            $return['msg'] = "发票申请成功！";
            M('User')->setInc('bill_amount', "uid='" . $this->mid . "'", $data['amount']);
            D('User', 'home')->resetUserInfoCache($this->mid); //更新用户缓存
        } else {
            $return['code'] = 1;
            $return['msg'] = "操作失败，请稍后重试！";
        }
        echo json_encode($return);
    }

}
