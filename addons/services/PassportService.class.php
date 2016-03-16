<?php
/**
 * 通行证服务
 *
 * @author daniel <desheng.young@gmail.com>
 */
class PassportService extends Service
{
    var $_error;

    const PREFIX_VALUE = 'zhiceyun';
    /**
     * 获取错误信息
     *
     * @return string 返回具体的错误信息
     */

    public function getLastError(){
        return $this->_error;
    }
    /**
     * 验证用户是否已登录
     *
     * 按照session -> cookie的顺序检查是否登录
     *
     * @return boolean 登录成功是返回true, 否则返回false
     */
    public function isLogged()
    {
        // 验证本地系统登录
        if (intval($_SESSION['mid']) > 0)
            return true;
        else if ($uid = $this->getCookieUid())
            return $this->loginLocal($uid);
        else
            return false;
    }

    /**
     * 根据标示符(email或uid)和未加密的密码获取本地用户 (密码为null时不参与验证)
     *
     * @param string         $identifier 标示符内容 (为数字时:标示符类型为uid, 其他:标示符类型为email)
     * @param string|boolean $password   未加密的密码
     * @return array|boolean 成功获取用户数据时返回用户信息数组, 否则返回false
     */
    public function getLocalUser($identifier, $password = null)
    {
        if (empty($identifier))
            return false;

        if($this->isValidEmail($identifier)){
            $identifier_type = 'email';
        }elseif(is_numeric($identifier) && is_int($identifier)){
            $identifier_type = 'uid';
        }else{
            $identifier_type = 'uname';
        }
        $user = D('User', 'home')->getUserByIdentifier($identifier, $identifier_type);
        if (!$user) {
            $this->_error = '帐号不存在';
            return false;
        }else if ($password && md5($password) != $user['password']){
            $this->_error = '帐号或密码错误';
            return false;
        }else if ($this->checkBannedUser($user['uid'])){        
            $this->_error = '帐号不存在或已被禁用';
            return false;
        // }else if ($user['is_active']==0){
        //  $this->_error = '用户未激活';
        //  return false;
        }else{
            return $user;
        }
    }

    /**
     * 使用本地帐号登录 (密码为null时不参与验证)
     *
     * @param string         $email
     * @param string|boolean $password
     * @return boolean
     */
    public function loginLocal($identifier, $password = null, $is_remember_me = false)
    {
        $user = $this->getLocalUser($identifier, $password);
        return $user ? $this->registerLogin($user, $is_remember_me,$password) : (UC_SYNC?$this->ucLogin($identifier,$password):false);
    }

    /**
     * 注册用户的登录状态 (即: 注册cookie + 注册session + 记录登录信息)
     *
     * @param array   $user
     * @param boolean $is_remeber_me
     */
    public function registerLogin(array $user, $is_remeber_me = false,$password = null)
    {
        if (empty($user))
            return false;

        if(UC_SYNC && isset($password)) {
            $result = $this->ucLogin($user['uname'],$password);
            if(!$result['user']){
                return false;
            }
        }else{
            $result['user'] = $user;
        }
        
        $this->_recordLogin($user['uid'], $user['uname'], $is_remeber_me, $user['user_type']);

        return $result;
    }

    /**
     * 设置登录状态、记录登录日志
     */
    private function _recordLogin($uid, $uname, $is_remeber_me = false, $user_type=0){
        $_SESSION['mid']    = $uid;
        $_SESSION['uname']  = $uname;

        if (!$this->getCookieUid()) {
            $expire = $is_remeber_me ? (3600*24*365) : (3600*1);
            cookie(self::PREFIX_VALUE.'LOGGED_USER', jiami(self::PREFIX_VALUE.".{$uid}"), $expire);
        }

        $this->loginCredit($uid, $user_type);
    }
    /**
     * [loginCredit 登录日志和登陆积分处理]
     * @param  [type]  $uid       [用户id]
     * @param  integer $user_type [用户类型]
     * @return [type]             [description]
     */
    public function loginCredit($uid, $user_type=0) {
        $stime = strtotime(date('Y-m-d', time()));
        $time  = $stime - 4*24*3600;
        $limit = 5;
        $model = M('login_record');

        $retData = $model->where("{$time}<=ctime and ctime<{$stime} and uid={$uid} and is_first=1 and is_add_credit=0")->limit($limit)->order('ctime desc')->select();
        // 连续5日登录
        $status1 = false;
        if (count($retData) == $limit-1) {
            X('Credit')->setUserCredit($uid, 'user_fiveday_login_'.$user_type);
            $model->where("{$time}<=ctime and uid={$uid} and is_first=1 and is_add_credit=0")
                            ->limit($limit)->order('ctime desc')->setField('is_add_credit', 1);
            $status1 = true;
        }

        $status = $model->where("{$stime}<=ctime and uid={$uid} and is_first=1")->find() ? false : true;
        if ($status && !$status1) {
            //X('Credit')->setUserCredit($uid, 'user_login_'.$user_type); //每日首次登陆奖励积分
        }

        $this->recordLogin($uid, $status, $status1);
    }

    /**
     * 注销本地登录
     */
    public function logoutLocal()
    {
        // 注销session
        unset($_SESSION['mid']);
        unset($_SESSION['uname']);
        unset($_SESSION['userInfo']);

        // 注销cookie
        cookie(self::PREFIX_VALUE.'LOGGED_USER', NULL);

        // 注销管理员
        unset($_SESSION[self::PREFIX_VALUE.'Admin']);
    }

    /**
     * 获取cookie中记录的用户ID
     */
    public function getCookieUid() {

        static $cookie_uid = null;
        if (isset($cookie_uid))
            return $cookie_uid;

        $cookie = t(cookie(self::PREFIX_VALUE.'LOGGED_USER'));
        $cookie = explode('.', jiemi($cookie));
        $cookie_uid = ($cookie[0] !== self::PREFIX_VALUE.'') ? false : $cookie[1];
        
        if($this->checkBannedUser($cookie_uid))
            return false;

        return $cookie_uid;
    }

    /**
     * 检查是否登录后台
     */
    public function isLoggedAdmin() {
        return $_SESSION[self::PREFIX_VALUE.'Admin'] == '1';
    }

    /**
     * 登录后台
     *
     * @param int    $uid      用户ID,不能和email同时为空
     * @param string $email    用户Email,不能和用户ID同时为空
     * @param string $password 未加密的密码,不能为空
     * @return boolean
     */
    public function loginAdmin($identifier, $password) {
        if (empty($identifier) || empty($password))
            return false;

        if (!($user = $this->getLocalUser($identifier, $password))) {
            unset($_SESSION[self::PREFIX_VALUE.'Admin']);
            return false;
        }

        // 检查是否拥有admin/Index/index权限
        if ( service('SystemPopedom')->hasPopedom($user['uid'], 'admin/Index/index', false) ) {
            $_SESSION[self::PREFIX_VALUE.'Admin']  = 1;
            $_SESSION['mid']            = $user['uid'];
            $_SESSION['uname']          = $user['uname'];

            //登录记录
            $this->recordLogin($user['uid']);
            return true;
        } else {
            unset($_SESSION[self::PREFIX_VALUE.'Admin']);
            return false;
        }
    }

    /**
     * uc登录或者注册。返回数组
     * $result['user'] 用户信息。用于ts系统使用
     * $result['login'] 同步登录是否成功
     * @param unknown_type $username
     * @param unknown_type $password
     */
    public function ucLogin($username,$password) {
        if(isValidEmail($username)){
            $user = service('Passport')->getLocalUser($username,$password);
            if(UC_SYNC && $user['uid']){
                $uc_user_ref = ts_get_ucenter_user_ref($user['uid']);
                if($uc_user_ref['uc_uid']){
                    $uc_user = uc_user_login($uc_user_ref['uc_uid'],$password,1);
                    if($uc_user[0] == -1 || $uc_user[0] == -2)$uc_user_ref = array();
                }else if($user['uname']){
                    $res_checkname = uc_user_checkname($user['uname']);
                    if($res_checkname>=-3 && $res_checkname<=-1){
                        $error_param = L('username');
                    }
                    $res_checkemail = uc_user_checkemail($username);
                    if($res_checkemail>=-6 && $res_checkemail<= -4){
                        $error_param = $error_param?$error_param.L('and_email'):'Email';
                    }
                    if($error_param){
                        $message_data['title']   = L('sync_ucenter').$error_param.L('sign_in_failed');
                        $message_data['content'] = L('you_of_site').$error_param.L('ucenter_sign_in_failed').$error_param.L('ucenter_clash').U('home/Account/security').L('ucenter_reset').$error_param.'。';
                        $message_data['to'] = $user['uid'];
                        model('Message')->postMessage($message_data, M('user')->getField('uid','admin_level=1'));
                    }else{
                        $uc_uid = uc_user_register($user['uname'],$password,$username);
                        ts_add_ucenter_user_ref($user['uid'],$uc_uid,$user['uname']);
                        $uc_user[0] = $uc_uid;
                    }
                }
            }
        }else{
            if(UC_SYNC){
                $uc_user = uc_user_login($username,$password);
                if($uc_user[0]>0){
                    $uc_user_ref = ts_get_ucenter_user_ref('',$uc_user[0]);
                    if(!$uc_user_ref){
                        // 注册
                        if($this->isValidEmail($uc_user['3']) && $this->isEmailAvailable($uc_user['3'])){
                            $user['email'] = $uc_user['3'];
                        }else{
                            $message_data['title']   = L('ucenter_sync_email_clash');
                            $message_data['content'] = L('ucenter_email_used').U('home/Account/bind').L('ucenter_reset_email');
                        }
                        if ( isLegalUsername($uc_user['1']) && !M('user')->where("uname='{$uc_user['1']}'")->count())
                            $user['uname'] = $uc_user['1'];
                        $user['password']  = md5($uc_user['2']);
                        $user['ctime']     = time();
                        $user['is_active'] = 1;
                        $user['uid'] = M('user')->add($user);
                        if ($user['uid']){
                            $reg_from_ucenter = 1;
                            ts_add_ucenter_user_ref($user['uid'],$uc_user['0'],$uc_user['1']);

                            // 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
                            $userlog = array(
                                'uid'       => $user['uid'],
                                'action'    => 'add',
                                'type'      => '0',
                                'dateline'  => time(),
                            );
                            M('myop_userlog')->add($userlog);

                            if(isset($message_data) && !empty($message_data)){
                                $message_data['to'] = $user['uid'];
                                model('Message')->postMessage($message_data,  M('user')->getField('uid','admin_level=1'));
                                //登录到系统
                                $this->_recordLogin($user['uid'],$user['uname']);
                            }
                        }else{
                            $user = array();
                        }
                    }else{
                        if($username != $uc_user_ref['uc_username']){
                            ts_update_ucenter_user_ref('',$uc_user_ref['uc_uid'],$username);
                        }
                        $user = M('user')->where("uid={$uc_user_ref['uid']}")->find();
                        if(!$user['uname']){
                            M('user')->where("uid={$uc_user_ref['uid']}")->save(array('uname',$uc_user_ref['uc_username']));
                        }
                        if(md5($password) != $user['password']){
                            M('user')->where("uid={$uc_user_ref['uid']}")->setField('password', md5($password));
                        }
                        //登录到系统
                        $this->_recordLogin($uc_user_ref['uid'],$uc_user_ref['uc_username']);
                    }
                }
            }else{
                $uc_user_ref = ts_get_ucenter_user_ref('','',$username);
                if($uc_user_ref['uid']){
                    $user = service('Passport')->getLocalUser($uc_user_ref['uid'],$password);
                }
            }
        }

        $result['login'] = '';
        if($user){
            $result['login'] = ( (UC_SYNC && $uc_user[0])?uc_user_synlogin($uc_user[0]):'' );
        }
        $result['user'] = $user;
        $result['reg_from_ucenter'] = $reg_from_ucenter;
        return $result;
    }

    /**
     * 记录登录信息
     *
     * @param int $uid 用户ID
     */
    public function recordLogin($uid, $status=false, $status1=false) {
        $data['uid']      = $uid;
        $data['ip']       = get_client_ip();
        $data['place']    = convert_ip($data['ip']);
        $data['ctime']    = time();
        $data['is_first'] = $status ? 1 : 0;
        $data['is_add_credit'] = $status1 ? 1 : 0;
        M('login_record')->add($data);
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

        $res = M('user')->where('`email`="'.$email.'"')->find();
        if(UC_SYNC){
            $uc_res = uc_user_checkemail($email);
            if($uc_res == -5 || $uc_res == -6){
                $res = true;
            }
        }

        if ( !$res ) {
            if ($return_type === 'ajax') echo 'success';
            else return true;
        }else {
            if ($return_type === 'ajax') echo L('email_used');
            else return false;
        }
    }

    //验证用户是否被禁用
    public function checkBannedUser($uid) {
        if(!$uid) return false;
        //检查用户是否存在数据库中
        $user = D('User')->where('uid='.intval($uid))->find();
        if(!$user) {
            $this->_error = '用户不存在或获取信息失败';
            return true;
        }
        //检查禁止登录的UID
        $audit = model('Xdata')->lget('audit');
        if($audit['banuid']==1){
            $banned_uids = $audit['banneduids'];
            if(!empty($banned_uids)){
                $banned_uids = explode('|',$banned_uids);
                $admin_uids = explode(',',C('ADMIN_UID'));
                //登录用户在禁用列表，并且不在默认管理员列表中
                if($uid>0 && in_array($uid,$banned_uids) && !in_array($uid,$admin_uids)){
                    $this->_error = '该用户已被禁用，禁止登录系统。';
                    return true;
                }
            }
        }
        return false;
    }


    /* 后台管理相关方法 */

    // 运行服务，系统服务自动运行
    public function run() {
        return;
    }
}