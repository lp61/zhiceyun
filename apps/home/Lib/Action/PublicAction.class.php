<?php
import('@.Form.UsersFormNamespace');
import('@.Form.AppEmailSendVars');

class PublicAction extends Action{
    const RESET_TIME = 3600; // 激活时间一小时有效
    
    //后台登陆
    public function adminlogin() {
        if ($_SERVER['HTTP_HOST'] == C('HOME_DOMAIN')) {
            $url = str_replace(C('HOME_DOMAIN'), C('ADMIN_DOMAIN'), U('home/Public/adminlogin'));
            redirect($url);
        }
        if ( service('Passport')->isLoggedAdmin() ) {
            redirect(U('admin/Index/index'));
        }

        $this->display();
    }

    //后台登陆操作
    public function doAdminLogin() {
        // 检查验证码
        if ( md5(strtoupper($_POST['verify'])) != $_SESSION['verify'] ) {
            $this->error(L('error_security_code'));
        }

        // 数据检查
        if ( empty($_POST['password']) ) {
            $this->error(L('password_notnull'));
        }

        // 检查帐号/密码
        $is_logged = false;
        if (isset($_POST['email'])) {
            $is_logged = service('Passport')->loginAdmin($_POST['email'], $_POST['password']);
        }else if ( $this->mid > 0 ) {
            $is_logged = service('Passport')->loginAdmin($this->mid, $_POST['password']);
        }else {
            $this->error(L('parameter_error'));
        }

        // 提示消息不显示头部
        $this->assign('isAdmin','1');

        if ($is_logged) {
            $this->assign('jumpUrl', U('admin/Index/index'));
            $this->success(L('login_success'));
        }else {
            $this->assign('jumpUrl', U('home/Public/adminlogin'));
            $this->error(service('Passport')->getLastError());
        }
    }
    
    // admin登陆检查验证码是否可用
    public function isVerifyAvailableLogin($verify = null) {
        $return_type = empty($verify) ? 'ajax' : 'return';
        $verify = empty($verify) ? $_POST['verify'] : $verify;
        //验证码不可用
        if(empty($_POST['verify']) && empty($verify)){
            echo 'no';
        }else{
            //验证码可用
            if( md5(strtoupper($verify)) == $_SESSION['verify']) {
                echo 'success';
            } else{
                echo 'false';
            }
        }
    }

    /**
     * [login 前端登录]
     * @return [type] [description]
     */
    public function login() {
        if (service('Passport')->isLogged()) {
            if(isMobile()) {
                log::write("3-" . isWeixin(), LOG::INFO);
                if (isWeixin()) { //微信浏览器 对登陆用户进行绑定
                    //$refer_url = U('wap/Index/oauth2');
                } else {
                    //$refer_url = U('wap/Index/index');
                }
                $refer_url = U('wap/Index/index');
            }else{
                $refer_url = U('home/User/index');
            }
            redirect($refer_url);
            
        }

        unset($_SESSION['sina'], $_SESSION['key'], $_SESSION['douban'], $_SESSION['qq'],$_SESSION['open_platform_type']);

        self::$FORM_NAMESPACE = new UsersFormNamespace();
        self::$FORM_NAMESPACE->setLoginValidateField();

        $isFormSubmit = 0; // 是否表单提交

        if (self::$FORM_NAMESPACE->isPostBack() && self::$FORM_NAMESPACE->validate()) {
            $isFormSubmit = 1;
            $this->doLogin();
            //redirect( $refer_url );
        }else{
            $data['email'] = t($_REQUEST['email']);
            $data['uname'] = t($_REQUEST['uname']);
            $data['uid']   = t($_REQUEST['uid']);
            $this->setTitle(L('login'));
            $this->display();
        }
    }

    /**
     * [doLogin 登陆验证]
     * @return [type] [description]
     */
    public function doLogin() {
        $username = $_POST['email'];
        $password = $_POST['password'];
        if(!$username){
            $this->error('请输入帐号');
        }
        if(!$password){
            $this->error(L('please_input_password'));
        }

        $result    = service('Passport')->loginLocal($username, $password, intval($_POST['remember']));
        $lastError = service('Passport')->getLastError(); 
        //检查是否激活
        if (!$result && $lastError =='用户未激活') {
            // $this->assign('jumpUrl',U('home/public/login'));
            // $this->error('该用户尚未激活，请更换帐号或激活帐号！');
            // exit;
        }
        if ($result) { //登陆成功后跳转
            if (UC_SYNC && $result['reg_from_ucenter']) {
                //从UCenter导入ThinkSNS，跳转至帐号修改页
                $refer_url = U('home/Public/userinfo');
            } elseif ($_SESSION['refer_url'] != '') {
                //跳转至登录前输入的url
                $refer_url = $_SESSION['refer_url'];
                unset($_SESSION['refer_url']);
            } elseif(isMobile()) {
                log::write("3-" . isWeixin(), LOG::INFO);
                if (isWeixin()) { //微信浏览器 对登陆用户进行绑定
                    //$refer_url = U('wap/Index/oauth2');
                } else {
                    //$refer_url = U('wap/Index/index');
                }
                $refer_url = U('wap/Index/index');
            } else {
                $refer_url = U('home/User/index');
            }
            $this->assign('jumpUrl', $refer_url);
            $this->assign('waitSecond', 0);
            $this->success();
        } else {
            $this->error($lastError);
        }
    }

    //快速登陆
    public function doAjaxLogin(){
        // 检查验证码
        $opt_verify = $this->_isVerifyOn('login');
        if ($opt_verify && (md5(strtoupper($_POST['verify']))!=$_SESSION['verify'])) {
            $return['message']  =   L('error_security_code');
            $return['status']   =   0;
            exit(json_encode($return));
        }

        $username = $_POST['email'];
        $password = $_POST['password'];

        Addons::hook('public_before_doajaxlogin',$_POST);

        if(!$password){
            $return['message']  =   L('password_notnull');
            $return['status']   =   0;
            exit(json_encode($return));
        }

        $result = service('Passport')->loginLocal($username,$password, intval($_POST['remember']) === 1);
        if($result){
            $return['message']  =   L('login_success');
            $return['status']   =   1;
            if(UC_SYNC && $uc_user[0])
                $return['callback'] =   uc_user_synlogin($uc_user[0]);
        }else{
            $error_message = service('Passport')->getLastError();
            $return['message']  =   $error_message;
            $return['status']   =   0;
        }
        
        Addons::hook('public_after_doajaxlogin',$result);

        exit(json_encode($return));
    }

        //第三方登录页面显示
    function tryOtherLogin(){
        if (!in_array($_GET['type'], array('sina', 'douban', 'qq', 'taobao', 'qqnumber', 'renren'))) {
            $this->error('参数错误');
        }
        
        $needSkip = $_GET['skip'];
        $uid = $_GET['new'];
        
        include_once(SITE_PATH . "/addons/plugins/Login/lib/{$_GET['type']}.class.php");
        // 通过QQ审核验证使用
        $isAudit = intval($_GET['audit']);

        $platform = new $_GET['type']($isAudit);

        redirect($platform->getUrl(null, $needSkip, $uid));
    }
    
    //采集页面第三方登录
    function PickOtherLogin() {
        if (!in_array($_GET['type'], array('sina', 'douban', 'qq', 'taobao', 'qqnumber', 'renren'))) {
            $this->error('参数错误');
        }
        
        include_once(SITE_PATH . "/addons/plugins/Login/lib/{$_GET['type']}.class.php");
        $platform = new $_GET['type']();
        redirect($platform->getUrl(null, $needSkip, $uid, true));
    }
    
    function  PickCallback() {
        if ($_GET['error_code']) {
            redirect(U('home/Public/picklogin'));
        }
        if (!in_array($_GET['type'], array('sina', 'douban', 'qq', 'taobao', 'qqnumber', 'renren'))) {
            $this->error('参数错误');
        }
        
        include_once(SITE_PATH . "/addons/plugins/Login/lib/{$_GET['type']}.class.php");
        $platform = new $_GET['type']();
        $platform->checkUser();
        redirect(U('home/Public/otherlogin', array('type' => $_GET['type'], 'pick' => '1')));
    }
    
    // 腾讯回调地址
    public function qqcallback() {
        include_once(SITE_PATH . '/addons/plugins/Login/lib/qq.class.php');
        $qq = new qq();
        $qq->checkUser();
        if ($_GET['skip'] == 1) {
            redirect(U('home/Public/otherlogin', array('type' => 'qq', 'new' => $_GET['newid'], 'skip' => 1)));
        }
        redirect(U('home/Public/otherlogin', array('type' => 'qq')));
    }
    
    /**
     * 淘宝回调地址
     */
    public function taobaocallback() {
        include_once(SITE_PATH . '/addons/plugins/Login/lib/taobao.class.php');
        $taobao = new taobao();
        $taobao->checkUser();
        if ($_GET['skip'] == 1) {
            redirect(U('home/Public/otherlogin', array('type' => 'taobao', 'new' => $_GET['newid'], 'skip' => 1)));
        }
        redirect(U('home/Public/otherlogin', array('type' => 'taobao')));
    }
    
    /**
     * QQ回调函数
     */
    public function qqnumbercallback() {
        include_once(SITE_PATH . '/addons/plugins/Login/lib/qqnumber.class.php');
        $qqnum = new qqnumber(intval($_GET['audit']));
        $qqnum->checkUser();
        if ($_GET['skip'] == 1) {
            redirect(U('home/Public/otherlogin', array('type' => 'qqnumber', 'new' => $_GET['newid'], 'skip' => 1)));
        }
        redirect(U('home/Public/otherlogin', array('type' => 'qqnumber', 'audit' => intval($_GET['audit']))));
    }
    
    /**
     * 人人回调函数
     */
    public function renrencallback() {
        include_once(SITE_PATH . '/addons/plugins/Login/lib/renren.class.php');
        $renren = new renren();
        $renren->checkUser();
        if ($_GET['skip'] == 1) {
            redirect(U('home/Public/otherlogin', array('type' => 'renren', 'new' => $_GET['newid'], 'skip' => 1)));
        }
        redirect(U('home/Public/otherlogin', array('type' => 'renren')));
    }

    //第三方账号与系统用户绑定函数
    public function bindaccount() {
        if (!in_array($_REQUEST['type'], array('sina', 'douban', 'qq', 'taobao', 'qqnumber', 'renren'))) {
            $this->error('参数错误');
        }

        $psd  = ($_REQUEST['password']) ? $_REQUEST['password'] : true;
        $type = $_REQUEST['type'];

        if ($user = service('Passport')->getLocalUser($_POST['email'], $psd, true)) {
            $res = service('Passport')->getLocalUser($_POST['email'], $psd, false);
            if(!$res){
                $this->error('邮箱已经存在，请修改邮箱，或者输入该用户的正确密码');
            }

            include_once(SITE_PATH."/addons/plugins/Login/lib/{$type}.class.php");
            $platform = new $type();
            $userinfo = $platform->userInfo();

            // 检查是否成功获取用户信息
            if (empty($userinfo['id'])) {
                $this->assign('jumpUrl', SITE_URL);
                $this->error('获取用户信息失败');
            }
            /**
             * @todo 此处需重构
             */
            // 检查是否已加入本站
            $map['type_uid'] = $userinfo['id'];
            $map['type']     = $type;
            if (($local_uid = M('login')->where($map)->getField('uid')) && (M('user')->where('uid='.$local_uid)->find())) {
                $this->assign('jumpUrl', SITE_URL);
                $this->success('您已经加入本站');
            }
            
            $syncdata['uid']      = $user['uid'];
            $syncdata['type_uid'] = $userinfo['id'];
            $syncdata['type']     = $type;
            if ($type == 'sina') {
                $syncdata['oauth_token']        = $_SESSION[$type]['access_token'];
                $syncdata['oauth_token_secret'] = $_SESSION[$type]['access_token_secret'];
            }
            elseif ($type == 'taobao') {
                $syncdata['oauth_token'] = $_SESSION[$type]['access_token'];
                //由于淘宝返回的数据中不包含令牌密钥，所以保存刷新令牌至密钥字段;
                //$syncdata['oauth_token_secret'] = $_SESSION[$type]['access_token']['refresh_token'];
                $syncdata['oauth_token_secret'] = $_SESSION[$type]['refresh_token'];
            }
            elseif ($type == 'renren') {
                $syncdata['oauth_token'] = $_SESSION[$type]['access_token'];
                $syncdata['oauth_token_secret'] = '';
            }
            elseif ($type == 'qqnumber') {
                $syncdata['oauth_token'] = $_SESSION[$type]['access_token'];
                //保存openid至oauth_token_secret
                $syncdata['oauth_token_secret'] = '';//$_SESSION[$type]['openid'];
            }
            elseif ($type == 'douban') {
                $syncdata['oauth_token'] = $_SESSION[$type]['access_token'];
                $syncdata['oauth_token_secret'] = $_SESSION[$type]['access_token_secret'];
            }
            else {
                //if ($info['oauth_token'] == '') {
                    $syncdata['oauth_token']        = $_SESSION[$type]['access_token']['oauth_token'];
                    $syncdata['oauth_token_secret'] = $_SESSION[$type]['access_token']['oauth_token_secret'];
                //}
            }
            
            if (M('login')->add($syncdata)) {                
                service('Passport')->registerLogin($user);
                if ($_GET['pick'] == '1') {
                    if ($_SESSION['pick_refer_url']) {
                        redirect($_SESSION['pick_refer_url']);
                    }
                    else {
                        redirect(U('home/Account/index',array('uid'=>$this->mid)));
                    }
                }
                else {
                    redirect(U('home/Account/index' ,array('uid'=>$user['uid'])));

                }
                //redirect(U('home/Public/lovepic', array('type' => $type, 'new' => $user['uid'])));
                redirect(U('home/Account/index' ,array('uid'=>$user['uid'])));
            }
            else {
                $this->assign('jumpUrl', SITE_URL);
                $this->error('绑定失败');
            }
        }
        else {
            //修改业务逻辑当未找到本地帐户时，则进行initotherlogin逻辑处理，在本站新建立用户
            $this->initotherlogin();
        }
    }

    //新浪回调函数
    public function callback(){
        include_once( SITE_PATH.'/addons/plugins/Login/lib/sina.class.php' );
        $sina = new sina();
        $sina->checkUser();
        redirect(U('home/Public/otherlogin'));
    }

    public function doubanCallback() {
        if ( !isset($_GET['oauth_token']) ) {
            $this->error('Error: No oauth_token detected.');
            exit;
        }
        require_once SITE_PATH . '/addons/plugins/Login/lib/douban.class.php';
        $douban = new douban();
        if ( $douban->checkUser($_GET['oauth_token']) ) {
            redirect(U('home/Public/otherlogin'));
        }else {
            $this->assign('jumpUrl', SITE_URL);
            $this->error(L('checking_failed'));
        }
    }

    /**
     * [logout 退出登陆]
     * @return [type] [description]
     */
    public function logout() {
        service('Passport')->logoutLocal();
        
        Addons::hook('public_after_logout');

        //$this->assign('jumpUrl',U('home/Index/index'));
        //$this->assign('waitSecond',3);
        //$this->success(L('exit_success'). ( (UC_SYNC)?uc_user_synlogout():'' ) );
        $this->redirect('home/Index/index');
    }
    /**
     * [logout 管理员退出登陆]
     * @return [type] [description]
     */
    public function logoutAdmin() {
        // 成功消息不显示头部
        $this->assign('isAdmin','1');
        service('Passport')->logoutLocal();
        $this->assign('jumpUrl',U('home/Public/adminlogin'));
        $this->success(L('exit_success'));
    }
    
    /**
     * [register 注册]
     * @return [type] [description]
     */
    public function register() {
        if (service('Passport')->isLogged())
            redirect(U('home/User/index'));

        $userType   = $_REQUEST['utype'] ? $_REQUEST['utype'] : 0;
        
        if (!array_key_exists($userType, UserInfoVars::$USER_TYPE_CONFIG)) {
            $userType = UserInfoVars::USER_NORMAL;
        }
        $validString  = UserInfoVars::$USER_TYPE_CONFIG[$userType]['validate'];
        $validateFunc = 'set'.ucfirst($validString).'UserValidateField';

        self::$FORM_NAMESPACE = new UsersFormNamespace();
        self::$FORM_NAMESPACE->$validateFunc();

        if ($_REQUEST['inviteCode']) {
            $this->assign('inviteCode', $_REQUEST['inviteCode']);
        }

        $this->assign('utype', $userType);
        $this->assign('workClassify', UserInfoVars::getWorkClassify());
        $this->assign('workStation', UserInfoVars::getWorkStation());
        $this->assign('companySize', UserInfoVars::getCompanySize());

        if (self::$FORM_NAMESPACE->isPostBack() && self::$FORM_NAMESPACE->validate()) {
            $extraData = array(
                'user_type' => $userType
            );
            $this->doRegister($extraData);
        } else{
            //验证码
            $opt_verify = $this->_isVerifyOn('register');
            if ( $opt_verify ) {
                $this->assign('register_verify_on', 1);
            }
            $this->setTitle(L('reg'));
            $this->display();
        }
    }
    public function savePic() {
        //保存LOGO
        if(!empty($_FILES['pic_url']['name'])){
            $attach_type                = 'reg_pic_url';
            $logo_options['save_to_db'] = true;
            $logo                       = X('Xattach')->upload($attach_type, $logo_options);
            if($logo['status']){
                $logofile = UPLOAD_URL .'/'.$logo['info'][0]['savepath'].$logo['info'][0]['savename'];
            }
            $_POST['pic_url']   = $logofile;
            $_POST['attach_id'] = $logo['info'][0]['id'];
        }
    }
    /**
     * [doRegister 注册保存]
     * @param  array  $extraData [description]
     * @return [type]            [description]
     */
    private function doRegister($extraData=array()) {
        // 注册
        $data['email']       = $_POST['email'];
        $data['password']    = md5($_POST['password']);
        $data['ctime']       = time();
        $data['is_active']   = 0;
        $data['is_init']     = 0;

        if (isset($extraData['user_type'])) {
            $data['is_audit']  = $extraData['user_type'] == UserInfoVars::USER_NORMAL ? UserInfoVars::YES_AUDIT : UserInfoVars::IS_AUDITING; // 非普通用户需要审核 
            $data['user_type'] = $extraData['user_type']; // 默认0

            if ($extraData['user_type'] == UserInfoVars::USER_COM) {
                $this->savePic();
            }
        }
        //个性信息
        if (isset($_POST['uname']) && !empty($_POST['uname'])) {
            $data['uname']  = t($_POST['uname']);
        }else {
            $tt            = explode('@', $data['email']);
            $data['uname'] = $tt[0];
        }
        if (isset($_POST['phone'])) {
            $data['phone']  = $_POST['phone'];
            Service('PhoneCode')->setRecordValid($_POST['phone']);
        }
        if (isset($_POST['company_name'])) {
            $data['company_name']  = t($_POST['company_name']);
        }
        if (isset($_POST['company_size'])) {
            $data['company_size']  = $_POST['company_size'];
        }
//        if (isset($_POST['work_classify'])) {
//            $data['work_classify']  = $_POST['work_classify'];
//        }
        if (isset($_POST['work_station'])) {
            $data['work_station']  = $_POST['work_station'];
        }
        if (isset($_POST['attach_id'])) {
            $data['licence_img'] = $_POST['pic_url'];
            $data['attach_id']   = $_POST['attach_id'];
        }

        if (!($uid = D('User', 'home')->add($data)))
            $this->error(L('reg_filed_retry'));

        // 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
        $user_log = array(
            'uid'       => $uid,
            'action'    => 'add',
            'type'      => '0',
            'dateline'  => time(),
        );
        M('myop_userlog')->add($user_log);
        model('Attach')->setAllAttachUserIdById($_POST['attach_id'], $uid, 'licence_img');

        // 同步至UCenter
        if (UC_SYNC) {
            $uc_uid = uc_user_register($_POST['nickname'],$_POST['password'],$_POST['email']);
            //echo uc_user_synlogin($uc_uid);
            if ($uc_uid > 0)
                $data['uname']     = t($_POST['nickname']);
                ts_add_ucenter_user_ref($uid,$uc_uid,$data['uname']);
        }

        model('AppMessage')->postMessage($uid, UserInfoVars::$MESSAGE_CONFIG['register']);
        // 注册成功 初始积分
        X('Credit')->setUserCredit($uid, 'user_register_'.$data['user_type']);

        if (isset($_POST['inviteCode'])) {
            vendor('libs.MyInviteCode', null, '.class.php');
            $fuid = MyInviteCode::code2num($_POST['inviteCode']);
            //邀请注册增加积分
            if (!empty($fuid)) {
                X('Credit')->setUserCredit($fuid, 'invite_friend_register_'.$data['user_type']); //邀请人积分奖励
                X('Credit')->setUserCredit($uid, 'invite_friend_register_'.$data['user_type']); //被邀请人积分奖励
                model('InviteRecord')->addRecord($fuid, $uid);
            }
        }

        // 置为已登录, 供完善个人资料时使用
        service('Passport')->loginLocal($uid);

        $this->activate();
        //if (!is_numeric(stripos($_POST['HTTP_REFERER'], dirname('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']))) && $register_option != 'invite') {
            //注册完毕，跳回注册页之前
        //    redirect($_POST['HTTP_REFERER']);
        //} else {
            //注册完毕，跳转至帐号修改页
            // redirect(U('home/Index/index'));
        //}
    }

    // 完善个人资料
    public function userinfo() {

        if (!$this->mid)
            redirect(U('home/Public/login'));

        // 已初始化的用户, 不允许在此修改资料
        global $ts;
        if ($this->mid && $ts['user']['is_init'])
            redirect(U('home/User/index'));

        redirect(U('home/Index/index'));
    }

    public function resendEmail() {
        $data = array(
            'code' => 1,
            'msg'  => '发送失败'
        );

        if (!service('Passport')->isLogged()) {
            $data['msg'] = '未注册登录';
            echo json_encode($data);
            exit;
        }
        if ($_SESSION['resendEmailTime'] > time() -60) {
            $data['msg'] = '操作太频繁，60秒后重试！';
            echo json_encode($data);
            exit;
        }
        $this->user = D('user')->getUserByIdentifier($_SESSION['mid']);
        if ($this->user['uid'] && $this->user['email']) {
            $status = $this->sendEmail(intval($this->user['uid']), $this->user['email'], '', 1);
            // $status = 1;
            if ($status) {
                $_SESSION['resendEmailTime'] = time();
                $data = array(
                    'code' => 0,
                    'msg'  => '发送成功，请登录激活邮箱！'
                );
            }else{
                $data['msg'] = '发送失败';
            }
        }else{
            $data['msg'] = '未注册登录';
        }
        echo json_encode($data);
        exit;
        
    }

    private function sendEmail($uid, $email, $invite = '', $is_resend = 0) {
        //设置激活路径
        $activate_url  = service('Validation')->addValidation($uid, '', U('home/Public/doActivate'), 'register_activate', serialize($invite));
        if ($invite) {
            $this->assign('invite', $invite);
        }
        $this->assign('url',$activate_url);

        $subject  = "【智测云】用户账号激活";
        //设置邮件模板
        $body = <<<EOF
<html>
<body>
    <h3>您好!感谢您使用智测云。</h3>
    <p>这是来自智测云的验证邮件，用来验证您的注册邮箱真实有效。</p>
    <p>您注册的邮箱为：</p>
    <p><a href="mailto:{$email}" target="_blank">{$email}</a></p>
    <p>请点击以下链接激活帐号：</p>
    <p><a href="{$activate_url}">{$activate_url}</a></p>
    <p>如果以上链接无法点击，请将上面的地址复制到您的浏览器（如IE）的地址栏激活帐号。</p>
    <p>如果你错误地收到了此电子邮件，你无需执行任何操作。</p>
    <p><a href="http://new.smarterapps.cn" target="_blank">智测云</a></p>
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
        // 发送邮件
        $email_sent    = service('Mail')->send_email($email, $subject, $body);
        $send_email_id = model('AppEmailSend')->addSendRecord($email, $subject, $body, AppEmailSendVars::REGISTER_ACTIVE);
        // 渲染输出
        if ($email_sent) {
            model('AppEmailSend')->setSendStatus($send_email_id);
            $email_info = explode("@", $email);
            switch ($email_info[1]) {
                case "qq.com"    : $email_url = "mail.qq.com";break;
                case "163.com"   : $email_url = "mail.163.com";break;
                case "126.com"   : $email_url = "mail.126.com";break;
                case "gmail.com" : $email_url = "mail.google.com";break;
                default          : $email_url = "mail.".$email_info[1];
            }
        }else {
            model('AppEmailSend')->setSendStatus($send_email_id, AppEmailSendVars::FAIL_SENDING);
        }

        $this->assign("email_url", $email_url);
        return $email_sent;
    }
    //发送激活邮件
    private function activate() {
        $this->user = D('user')->getUserByIdentifier($_SESSION['mid']);
        if (!$_GET['nosend']) {
            $email_sent = $this->sendEmail($this->user['uid'], $this->user['email']);
            if ($is_resend) {
                return $email_sent;
            }
        }

        $this->assign("uid",$this->user['uid']);
        $this->assign('email', $this->user['email']);
        $this->assign('email_sent', $email_sent);
        $this->display('activate');
    }
    /**
     * [doActivate 激活]
     * @return [type] [description]
     */
    public function doActivate() {
        if (service('Passport')->isLogged()) {
            service('Passport')->logoutLocal();
            Addons::hook('public_after_logout');
        }

        $invite = service('Validation')->getValidation();
        if (!$invite) {
            //$this->assign('jumpUrl', U('home/Public/register'));
            //$this->error(L('activation_code_error_retry'));
            $this->assign('success', 0); //邀请码错误或激活链接失效
            $this->display();
            exit();
        }
        
        $uid = $invite['from_uid'];
        $invite['data'] = unserialize($invite['data']);
        //邀请信息
        if($invite['data']){

        }
        $user = M('user')->where("`uid`=$uid")->find();
        if ($user['is_active'] == 1) { //已激活
            // $this->assign('waitSecond', 3);
            // $this->assign('jumpUrl', U('home/Public/login'));
            // $this->success(L('account_activity'));
            $this->assign('success', 1);
            $this->display();
        } else if ($user['is_active'] == 0) {
            //激活帐户
            $res = M('user')->where("`uid`=$uid")->setField('is_active', 1);
            if (!$res) { //激活失败
                $this->error(L('activation_failed'));
            }

            service('Passport')->registerLogin($user);
            // 更新缓存
            D('User', 'home')->resetUserInfoCache($uid);
            // 注销验证有效
            service('Validation')->unsetValidation();

            // $this->assign('waitSecond', 3);
            // $this->assign('jumpUrl', U('home/Account/index'));
            // $this->success(L("activation_success"));
            $this->assign('success', 1);
            $this->display();
        } else {
            //$this->assign('jumpUrl', U('home/Public/register'));
            //$this->error(L('activation_code_error_retry'));
            $this->assign('success', 0); //邀请码错误或激活链接失效
            $this->display();
        }
    }
    /**
     * [sendPassword 找回密码]
     * @return [type] [description]
     */
    public function sendPassword() {
        if (service('Passport')->isLogged())
            redirect(U('home/User/index'));
        self::$FORM_NAMESPACE = new UsersFormNamespace();
        self::$FORM_NAMESPACE->setSendPasswordValidateField();

        $isFormSubmit = 0; // 是否表单提交

        if ($_SESSION['resetInfo']['uid'] && $_SESSION['resetInfo']['email'] && $_SESSION['resetInfo']['time'] >= time()-self::RESET_TIME && $_SESSION['resetInfo']['code']) {
            if (!isset($_GET['tt']) || base64_decode($_GET['tt']) != $_SESSION['resetInfo']['time']) {
                redirect(U('home/Public/validSendCode'));
            }
        } 

        if (self::$FORM_NAMESPACE->isPostBack() && self::$FORM_NAMESPACE->validate()) {
            $this->doSendPassword();
        }else{
            $this->display();
        }
    }
    /**
     * [doSendPassword 找回密码]
     * @return [type] [description]
     */
    private function doSendPassword() {
        $_POST["email"] = t($_POST["email"]);
        if ( !$this->isValidEmail($_POST['email']) )
            $this->error(L('email_format_error'));

        $user = M("user")->where('`email`="' . $_POST['email'] . '"')->find();

        if(!$user) {
            $this->error(L("email_not_reg"));
        }else {
//             $code = base64_encode( $user["uid"] . "." . md5($user["uid"] . '+' . $user["password"]) );
//             $url  = U('home/Public/resetPassword', array('code'=>$code));
//             $body = <<<EOD
// <strong>{$user["email"]}，你好: </strong><br/>

// 您只需通过点击下面的链接重置您的密码: <br/>

// <a href="$url">$url</a><br/>

// 如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

// 如果你错误地收到了此电子邮件，你无需执行任何操作。
// EOD;
            $code = rand_string();
            $body = <<<EOD
<strong>{$user["email"]}，你好: </strong><br/>

重置密码的邮件验证码是: <br/>

{$code}<br/>

如果你错误地收到了此电子邮件，你无需执行任何操作。
EOD;
            global $ts;
            $email_sent    = service('Mail')->send_email($user['email'], L('reset')."{$ts['site']['site_name']}".L('password'), $body);
            $send_email_id = model('AppEmailSend')->addSendRecord($user['email'], L('reset')."{$ts['site']['site_name']}".L('password'), $body, AppEmailSendVars::PASSWORD_RECOVERY);

            if ($email_sent) {
                model('AppEmailSend')->setSendStatus($send_email_id);

                $_SESSION['resetInfo'] = array(
                    'uid'   => $user['uid'],
                    'email' => $user['email'],
                    'time'  => time(),
                    'step'  => 1,
                    'isphone_type'       => 0,
                    'code'  => $code
                );
                // $this->assign('waitSecond', 5);
                // $this->assign('jumpUrl', U('home/Public/validSendCode'));
                // $this->success(L('send_you_mailbox') . $user['email'] . L('notice_accept'));
                redirect( U('home/Public/validSendCode'));
            }else {
                model('AppEmailSend')->setSendStatus($send_email_id, AppEmailSendVars::FAIL_SENDING);
                $this->error(L('email_send_error_retry'));
            }
        }
    }
    public function validSendCode() {
        if (service('Passport')->isLogged())
            redirect(U('home/User/index'));

        if (empty($_SESSION['resetInfo']['email'])||empty($_SESSION['resetInfo']['uid']) || empty($_SESSION['resetInfo']['code'])) {
            redirect(U('home/Public/sendPassword'));
        }
        if ($_POST) {
            if ($_SESSION['resetInfo']['time'] <= time()-self::RESET_TIME) {
                unset($_SESSION['resetInfo']['code']);
                $this->error('验证码已经失效！');
            }elseif ($_POST['code'] != $_SESSION['resetInfo']['code']) {
                $this->error('验证码错误！');
            }else{
                $_SESSION['resetInfo']['step'] = 2;
                $code = base64_encode( $_SESSION['resetInfo']["uid"] . "." . md5($_SESSION['resetInfo']["uid"] . '+' . $_SESSION['resetInfo']["code"]) );
                redirect(U('home/Public/resetPassword', array('code' => $code)));
            }
        }

        $status = $_SESSION['resetInfo']['time'] >= time()-self::RESET_TIME ? false : true;
        $url = U('home/Public/sendPassword', array('tt'=>base64_encode($_SESSION['resetInfo']['time'])));
        $this->assign('status', $status);
        $this->assign('url', $url);
        $this->assign('resetInfo', $_SESSION['resetInfo']);
        $this->display();
    }
    public function doValidSendCode() {
        if ($_POST) {
            if ($_SESSION['resetInfo']['time'] <= time()-self::RESET_TIME) {
                echo '验证码已经失效！';return;
            }elseif ($_POST['code'] != $_SESSION['resetInfo']['code']) {
                echo 0;return;
            }else{
                echo 'true';return;
            }
        }
    }

    public function sendPhonePwd() {
        if (service('Passport')->isLogged())
            redirect(U('home/User/index'));

        self::$FORM_NAMESPACE = new UsersFormNamespace();
        self::$FORM_NAMESPACE->setSendPhonwPwdValidateField();

        $isFormSubmit = 0; // 是否表单提交

        if ($_SESSION['resetInfo']['uid'] && $_SESSION['resetInfo']['phone'] && $_SESSION['resetInfo']['time'] >= time()-self::RESET_TIME && $_SESSION['resetInfo']['code']) {
            if (!isset($_GET['tt']) || base64_decode($_GET['tt']) != $_SESSION['resetInfo']['time']) {
                redirect(U('home/Public/validPhoneCode'));
            }
        }
        $this->assign('isphone_type', 1);
        if (self::$FORM_NAMESPACE->isPostBack() && self::$FORM_NAMESPACE->validate()) {
            
            $user = M("user")->where('`phone`="' . $_POST['phone'] . '"')->find();
            if (empty($user)) {
                $this->error('手机不存在');
            }

            $data = Service('PhoneCode')->sendCode($_POST['phone']);

            $_SESSION['resetInfo'] = array(
                'uid'        => $user['uid'],
                'phone'      => $user['phone'],
                'time'       => time(),
                'isphone_type'       => 1,
                'step'       => 1,
                'code' => $data['message']
            );
            redirect( U('home/Public/validPhoneCode'));
        }else{
            $this->display();
        }
    }

    public function validPhoneCode() {
        if (service('Passport')->isLogged())
            redirect(U('home/User/index'));

        if (empty($_SESSION['resetInfo']['phone'])||empty($_SESSION['resetInfo']['uid']) || empty($_SESSION['resetInfo']['code'])) {
            redirect(U('home/Public/sendPhonePwd'));
        }
        if ($_POST) {
            if ($_SESSION['resetInfo']['time'] <= time()-self::RESET_TIME) {
                unset($_SESSION['resetInfo']['code']);
                $this->error('验证码已经失效！');
            }elseif ($_POST['code'] != $_SESSION['resetInfo']['code']) {
                $this->error('验证码错误！');
            }else{
                $_SESSION['resetInfo']['step'] = 2;
                $code = base64_encode( $_SESSION['resetInfo']["uid"] . "." . md5($_SESSION['resetInfo']["uid"] . '+' . $_SESSION['resetInfo']["code"]) );
                redirect(U('home/Public/resetPassword', array('code' => $code)));
            }
        }

        $status = $_SESSION['resetInfo']['time'] >= time()-self::RESET_TIME ? false : true;
        $url = U('home/Public/sendPhonePwd', array('tt'=>base64_encode($_SESSION['resetInfo']['time'])));
        $this->assign('status', $status);
        $this->assign('isphone_type', 1);
        $this->assign('url', $url);
        $this->assign('resetInfo', $_SESSION['resetInfo']);
        $this->display();
    }
    public function doValidPhoneCode() {
        if ($_POST) {
            if ($_SESSION['resetInfo']['time'] <= time()-self::RESET_TIME) {
                echo '验证码已经失效！';return;
            }elseif ($_POST['code'] != $_SESSION['resetInfo']['code']) {
                echo 0;return;
            }else{
                echo 'true';return;
            }
        }
    }
    /**
     * [resetPassword 重置密码]
     * @return [type] [description]
     */
    public function resetPassword() {
        $code = explode('.', base64_decode($_GET['code']));
        $user = M('user')->where('`uid`=' . $code[0])->find();

        if ( $code[1] == md5($code[0].'+'.$_SESSION['resetInfo']["code"]) ) {
            $_SESSION['resetInfo']['step'] = 3;
            $validateFunc = 'resetPasswordValidateField';

            self::$FORM_NAMESPACE = new UsersFormNamespace();
            self::$FORM_NAMESPACE->$validateFunc();

            if (self::$FORM_NAMESPACE->isPostBack() && self::$FORM_NAMESPACE->validate()) {
                $this->doResetPassword();
            }

            $this->assign('email',$user["email"]);
            $this->assign('isphone_type',$_SESSION['resetInfo']['isphone_type']);
            $this->assign('code', $_GET['code']);
            $this->display();
        }else {
            $this->error(L("link_error"));
        }
    }

    /**
     * [resetPassword 重置密码]
     * @return [type] [description]
     */
    private function doResetPassword() {
        $code = explode('.', base64_decode($_POST['code']));
        $user = M('user')->where('`uid`=' . $code[0])->find();

        if ( $code[1] == md5($code[0] . '+' . $_SESSION['resetInfo']["code"]) ) {
            $user1['password'] = md5($_POST['password']);
            $user1['uid']      = $user['uid'];
            $res = M('user')->save($user1) || $user1['password'] == $user['password'];
            //同步设置UC密码
            include_once(SITE_PATH.'/api/uc_client/uc_sync.php');
            if(UC_SYNC){
                $ucenter_user_ref = ts_get_ucenter_user_ref($code[0]);
                $uc_res = uc_user_edit($ucenter_user_ref['uc_username'],'',$_POST['password'],'',1);
                if($uc_res == -8){
                    $this->error(L('userprotected_no_right'));
                }
            }
            //去掉用户缓存信息
            D('User', 'home')->resetUserInfoCache($code[0]);

            if ($res) {
                unset($_SESSION['resetInfo']);
                $this->assign('waitSecond', 3);
                $this->assign('jumpUrl', U('home/Public/login'));
                $this->success('密码重置成功，请登录！');
            }else {
                $this->error(L('save_error_retry'));
            }
        }else {
            $this->error(L("safety_code_error"));
        }
    }

    //检查Email地址是否合法
    public function isValidEmail($email) {
        if(UC_SYNC){
            $res = uc_user_checkemail($email);
            if($res == -4){
                return false;
            }else{
                return true;
            }
        }else{
            return preg_match("/[_a-zA-Z\d\-\.]+@[_a-zA-Z\d\-]+(\.[_a-zA-Z\d\-]+)+$/i", $email) !== 0;
        }
    }

    //检查Email是否可用
    public function isEmailAvailable($email = null) {
        $return_type = empty($email) ? 'ajax'          : 'return';
        $email       = empty($email) ? $_POST['email'] : $email;

        $sendPasswordSta = $_REQUEST['p'] ? true:false;

        $res = M('user')->where('`email`="'.$email.'"')->find();
        if ($sendPasswordSta) {
            echo $res ? 'true' :'false';
            exit;
        }
        if(UC_SYNC){
            $uc_res = uc_user_checkemail($email);
            if($uc_res == -5 || $uc_res == -6){
                $res = true;
            }
        }

        if ( !$res ) {
            if ($return_type === 'ajax') echo 'true';
            else return true;
        }else {
            if ($return_type === 'ajax') echo L('email_used');
            else return false;
        }
    }
    public function isPhoneAvailable($phone =null) {
        $return_type = empty($phone) ? 'ajax'          : 'return';
        $phone       = empty($phone) ? $_POST['phone'] : $phone;

        $sendPasswordSta = $_REQUEST['p'] ? true:false;

       $res = M('user')->where('`phone`="'.$phone.'"')->find();
        if ($sendPasswordSta) {
            echo $res ? 'true' :'false';
            exit;
        }
        if(UC_SYNC){
            $uc_res = uc_user_checkemail($phone);
            if($uc_res == -5 || $uc_res == -6){
                $res = true;
            }
        }

        if ( !$res ) {
            if ($return_type === 'ajax') echo 'true';
            else return true;
        }else {
            if ($return_type === 'ajax') echo L('email_used');
            else return false;
        }
    }

    public function verify() {
        require_once(SITE_PATH.'/addons/libs/Image.class.php');
        require_once(SITE_PATH.'/addons/libs/String.class.php');
        Image::buildImageVerify();
    }

    //上传图片
    public function uploadpic(){
        if( $_FILES['pic'] ){
            //执行上传操作
            $savePath =  $this->getSaveTempPath();
            $filename = md5( time().'teste' ).'.'.substr($_FILES['pic']['name'],strpos($_FILES['pic']['name'],'.')+1);
            if(@copy($_FILES['pic']['tmp_name'], $savePath.'/'.$filename) || @move_uploaded_file($_FILES['pic']['tmp_name'], $savePath.'/'.$filename))
            {
                $result['boolen']    = 1;
                $result['type_data'] = 'temp/'.$filename;
                $result['picurl']    = __UPLOAD__.'/temp/'.$filename;
            } else {
                $result['boolen']    = 0;
                $result['message']   = L('upload_filed');
            }
        }else{
            $result['boolen']    = 0;
            $result['message']   = L('upload_filed');
        }

        exit( json_encode( $result ) );
    }

    //上传临时文件
    public function getSaveTempPath(){
        $savePath = SITE_PATH.'/data/uploads/temp';
        if( !file_exists( $savePath ) ) mk_dir( $savePath  );
        return $savePath;
    }

    // 地区管理
    public function getArea() {
        echo json_encode(model('Area')->getAreaTree());
    }

    /**  文章  **/
    public function document() {
        $list   = array();
        $detail = array();
        $res    = M('document')->where('`is_active`=1')->order('`display_order` ASC,`document_id` ASC')->findAll();

        // 获取content为url且在页脚显示的文章
        global $ts;
        $ids_has_url = array();
        foreach($ts['footer_document'] as $v)
            if( !empty($v['url']) )
                $ids_has_url[] = $v['document_id'];

        $_GET['id'] = intval($_GET['id']);

        foreach($res as $v) {
            // 不显示content为url且在页脚显示的文章
            if ( in_array($v['document_id'], $ids_has_url) )
                continue ;

            $list[] = array('document_id'=>$v['document_id'], 'title'=>$v['title']);

            // 当指定ID，且该ID存在，且该文章的内容不是url时，显示指定的文章。否则显示第一篇
            if ( $v['document_id'] == $_GET['id'] || empty($detail) ) {
                $v['content'] = htmlspecialchars_decode($v['content']);
                $detail = $v;
            }
        }
        unset($res);

        $this->assign('detail', $detail);
        $this->assign('list', $list);
        $this->display();
    }

    public function toWap() {
        $_SESSION['wap_to_normal'] = '0';
        cookie('wap_to_normal', '0', 3600*24*365);
        U('wap', '', true);
    }

    public function error404() {
        $this->display('404');
    }

    private function _isVerifyOn($type='login'){
        // 检查验证码
        if($type!='login' && $type!='register') return false;
        $opt_verify = $GLOBALS['ts']['site']['site_verify'];
        return in_array($type, $opt_verify);
    }

    public function about() {
        $this->display();
    }

    public function protocol() {
        $this->display();
    }

    public function faq() {
        $result = M('faq')->order('display_order asc')->select();
        $mo['normal'] = $mo['func'] = $mo['set'] = array();
        if (!empty($result)) {
            foreach ($result as $_one) {
                switch ($_one['type']) {
                    case 1: //常见问题
                        $mo['normal'][] = $_one;
                        break;
                    case 2: //功能使用
                        $mo['func'][] = $_one;
                        break;
                    case 3: //账户设置
                        $mo['set'][] = $_one;
                        break;
                }
            }
        }
        $this->assign('mo', $mo);
        $this->display();
    }

    /**
     * [isPhoneCodeAvailable 验证手机确认码]
     * @return boolean [description]
     */
    public function isPhoneCodeAvailable() {
        $data = Service('PhoneCode')->checkSendCode($_POST['phone'], $_POST['phone_code']);
        //var_dump($data);
        echo $data['code'] ? 'false' : 'true';
        exit;
    }
    
    //活动详情页
    public function activity_detail() {
        $this->display();
    }
}