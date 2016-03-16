<?php
header("Content-type:text/html; charset=UTF-8");
date_default_timezone_set('Asia/Chongqing');

require_once SITE_PATH . '/addons/plugins/Login/lib/qq/config.login.php';

class qqnumber {
    private $_qq_key;
    private $_qq_secret;
    private $_authorize_url;
    
    /**
     * qqnumber.class.php 构造函数
     */
    public function __construct($isAudit = NULL) {
        if ($isAudit) {
            $this->_audit = true;
            $this->_qq_key      = '101171106';
            $this->_qq_secret   = 'b441ce0d9bcd26810821a8251357b156';
        }
        else {
            $this->_qq_key      = QQ_AKEY;
            $this->_qq_secret   = QQ_SKEY;
        }
    }
    
    /**
     * 获取腾讯QQ验证登录地址
     * @return string 验证地址
     */
    function getUrl( $call_back = null, $needSkip, $uid, $isPick ) {
        $state = md5(date("YmdHis" . get_client_ip()));
        $_SESSION['qqnumber']['state'] = $state;
        
        if ( empty($this->_qq_key) || empty($this->_qq_secret) )
            return false;
        
        if (is_null($call_back)) {
            if ( $isPick ) {
                $call_back = U( 'home/Public/PickCallback', array( 'type' => 'qqnumber' ) );
            }
            else {
                if ($needSkip == 1) {
                    $call_back = U( 'home/Public/qqnumberCallback', array( 'skip' => 1, 'newid' => $uid ) );
                }
                else {
                    $call_back = U('home/Public/qqnumberCallback');
                }
            }
        }

        // qq验证用
        if ($this->_audit) {
            $call_back = U('home/Public/qqnumberCallback', array('audit' => 1));
        }

        if (C('TEST_OTHERS_SITE')) {
            $call_back = 'http://new.smarterapps.cn?app=home&mod=Public&act=qqnumberCallback';
        }
        $call_back = 'http://www.smarterapps.cn?app=home&mod=Public&act=qqnumberCallback';
        $scope = 'get_user_info,add_share,add_t,add_pic_t,get_info,get_fanslist';
        
        $this->_authorize_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id="
                //. $this->_qq_key . "&redirect_uri=" . rawurlencode($call_back)
                . $this->_qq_key . "&redirect_uri=" . rawurlencode($call_back)
                . "&state=" . $state
                . "&scope=" . $scope;

        $_SESSION['qqnumber']['callback'] = $call_back;
        return $this->_authorize_url;
    }
    
    /**
     * 获取access_token
     * @return string|boolean
     */
    function checkUser() {
        if ( empty($this->_qq_key) || empty($this->_qq_secret) )
            return false;
        
        $code     = $_REQUEST['code'];
        $state    = $_SESSION['qqnumber']['state'];
        $callback = $_SESSION['qqnumber']['callback'];
        
        $fields = array(
                "grant_type"    =>    "authorization_code",
                "client_id"     =>    $this->_qq_key,
                "client_secret" =>    $this->_qq_secret,
                "code"          =>    $code,
                "state"         =>    $state,
                "redirect_uri"  =>    $callback,
        );

        unset( $_SESSION['qqnumber']['code'] );
        unset( $_SESSION['qqnumber']['state'] );
        unset( $_SESSION['qqnumber']['callback'] );
        
        $url = 'https://graph.qq.com/oauth2.0/token';
        
        $content = $this->get($url, $fields);
        if ($content !== false) {
            $temp = explode("&", $content);
            $param = array();
            
            foreach($temp as $val){
                $temp2 = explode("=", $val);
                $param[$temp2[0]] = $temp2[1];
            }
            $_SESSION['qqnumber']['access_token'] = $param["access_token"];
            
            $url = "https://graph.qq.com/oauth2.0/me";
            $fields = array(
                    "access_token"    => $param["access_token"]
            );
            
            $content = $this->get($url, $fields);
            if( $content !== FALSE ) {
                $temp = array();
                preg_match( '/callback\(\s+(.*?)\s+\)/i', $content, $temp );
                $result = json_decode( $temp[1], true );
                //到此获取到openid，整个请求过程结束
                $_SESSION['qqnumber']["openid"] = $result["openid"];
                $_SESSION['open_platform_type'] = 'qqnumber';
            }
        }
    }
    
    /**
     * 获取用户信息
     */
    function userinfo() {
        if ( !$_SESSION['qqnumber']['openid'] || !$_SESSION['qqnumber']['access_token'] )
            return false;

        $user['id']       = $_SESSION['qqnumber']['openid'];
        return $user;
        
        $url = "https://graph.qq.com/user/get_user_info";
        
        $param = array(
                "access_token"       => $_SESSION['qqnumber']['access_token'],
                "oauth_consumer_key" => $this->_qq_key,
                "openid"             => $_SESSION['qqnumber']['openid'],
                "format"             => "json"
        );
        
        $content = $this->get( $url, $param );
        //$content = iconv("GB2312", "UTF-8//IGNORE", $content);
        //if (get_magic_quotes_runtime()) $content = stripslashes($content);
        //$content = mb_convert_encoding(urlencode($content), 'UTF-8');
        
        if ( $content !== false ) {
            
            $me = json_decode( $content, true );
            
            if ($me['ret'] == '0') {
                $user['id']       = $_SESSION['qqnumber']['openid'];
                $user['uname']    = isset( $me['nickname'] ) ? trim($me['nickname']) : null;
                
                $user['userface'] = isset( $me['figureurl_2'] ) ? $me['figureurl_2'] : '';
                $user['sex']      = ($me['gender'] == '男') ? 1 : 0;
                
                return $user;
            }
            else {
                return false;
            }
        }
        else
            return false;
    }
    
    function getSimpleUserinfo() {
        if ( !$_SESSION['qqnumber']['openid'] || !$_SESSION['qqnumber']['access_token'] )
            return false;
        
        $url = "https://graph.qq.com/user/get_user_info";
        
        $param = array(
                "access_token"              => $_SESSION['qqnumber']['access_token'],
                "oauth_consumer_key"    => $this->_qq_key,
                "openid"                        => $_SESSION['qqnumber']['openid'],
                "format"                        => "json"
        );
        
        $content = $this->get( $url, $param );
        
        if ( $content !== false ) {
            $me = json_decode( $content, true );
            if ($me['ret'] == '0') {
                $user['id']       = $_SESSION['qqnumber']['openid'];
                $user['uname']    = isset( $me['nickname'] ) ? (urlencode($me['nickname'])) : null;
                $user['userface'] = isset( $me['figureurl_2'] ) ? $me['figureurl_2'] : '';
                $user['sex']      = isset($me['gender']) ? ( ($me['gender']) == '男' ? 1 : 0 ) : 1;
        
                return $user;
            }
            else {
                return false;
            }
        }
        else
            return false;
    }
    
    public function get_fanslist() {
        if ( !$_SESSION['qqnumber']['openid'] || !$_SESSION['qqnumber']['access_token'] )
            return false;
        
        $url = "https://graph.qq.com/relation/get_fanslist";
        
        $param = array(
                "access_token"       => $_SESSION['qqnumber']['access_token'],
                "oauth_consumer_key" => $this->_qq_key,
                "openid"             => $_SESSION['qqnumber']['openid'],
                "format"             => "json",
                "reqnum"             => 30,
                "startindex"         => 0,
                "mode"               => 1,
        );
        $content = $this->get( $url, $param );
        if ( $content !== false ) {
                
            $fanslist = json_decode( $content, true );
            if ($fanslist['msg'] == 'ok') {
                foreach ($fanslist['data']['info'] as $key => $value) {
                    $user[$key]['id']    = isset( $value['openid'] ) ? $value['openid'] : null;
                    $user[$key]['uname'] = isset( $value['nick'] ) ? $value['nick'] : null;
                    
                    $locationInfo = explode(' ', $value['location']);
                    if ( !empty($locationInfo) ) {
                        $user[$key]['province'] = isset( $locationInfo[1] ) ? $locationInfo[1] : '';
                        $user[$key]['city']     = isset( $locationInfo[2] ) ? $locationInfo[2] : '';
                        $user[$key]['location'] = isset( $value['location'] ) ? $value['location'] : '';
                    }
                    else {
                        $user[$key]['province'] = '';
                        $user[$key]['city']     = '';
                        $user[$key]['location'] = '';
                    }
                    $user[$key]['userface'] = isset( $value['head'] ) ? $value['head'] : '';
                    $user[$key]['sex']      = ($value['sex'] !== '') ? $value['sex'] : 1;
                }
                return $user;
            }
            else {
                return false;
            }
        }
        else
            return false;
    }
    
    /**
     * 添加一条新的腾讯微博
     * @param string $text  微博文字
     * @param string $pic  微博图片
     * @param array $opt  配置项 包含openid,access_token等
     */
    function upload( $text, $pic, $opt ) {
        
        $param = array(
            "access_token"       => $opt['oauth_token'],//$_SESSION["access_token"],
            "oauth_consumer_key" => $this->_qq_key,
            "openid"             => $opt['type_uid'],
            "format"             => "json",
            "title"              => (get_magic_quotes_runtime() ? stripslashes( mb_substr($text, 0, 36, 'UTF-8') ) : mb_substr($text, 0, 36, 'UTF-8')),
            "url"                => (get_magic_quotes_runtime() ? stripslashes($opt["url"]) : $opt["url"]),
            "comment"            => '', //(get_magic_quotes_runtime()?stripslashes($_POST["comment"]):$_POST["comment"]),
            "summary"            => (get_magic_quotes_runtime() ? stripslashes($text) : $text),
            "images"             => (get_magic_quotes_runtime() ? stripslashes($pic) : $pic),
            "source"             => '1',
            "type"               => '4',
            "site"               => '博图·汇', //(get_magic_quotes_runtime()?stripslashes($_POST["site"]):$_POST["site"])
        );
        
        $url = "https://graph.qq.com/share/add_share";
        $content = $this->upload_curl( $url, $param );
        
        if( $content !== FALSE ) {
            $data = json_decode( $content, true );
            
            $errcode = array('10','13','75' );
            
            if( (in_array($data['errcode'], $errcode) && $data['ret'] == 4) || $data['ret'] == 2 || $data['ret'] == 3046){
                return true;
            }
            else if ( $data['errcode'] != 0 ){
                return false;
            }

            return true;

        }
    }
    
    /**
     * 发送GET请求
     * @param string $sUrl 请求地址
     * @param array $aGetParam  请求参数
     * @return string|boolean 返回结果
     */
    function get($sUrl,$aGetParam){
        $oCurl = curl_init();
        if( stripos($sUrl, "https://" ) !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        
        $aGet = array();
        foreach($aGetParam as $key=>$val){
            $aGet[] = $key."=".urlencode($val);
        }
        //curl_setopt($oCurl, CURLOPT_HTTPHEADER, "charset=UTF-8");
        curl_setopt($oCurl, CURLOPT_URL, $sUrl."?".join("&",$aGet));
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);

        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return FALSE;
        }
    }
    
    function upload_curl($url, $param, $fileParam){
        //防止请求超时
        set_time_limit(0);
        $curl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $postField = array();
        foreach($param as $key=>$val){
            if(preg_match("/^@/i",$val)>0){
                $postField[$key] = " ".$val;
            }else{
                $postField[$key]= $val;
            }
        }
        foreach($fileParam as $key=>$val){
            $postField[$key] = "@".$val; //此处对应的是文件的绝对地址
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postField);
        
        $content = curl_exec($curl);
        
        $status = curl_getinfo($curl);
        
        curl_close($curl);
        
        if(intval($status["http_code"])==200){
            return $content;
        }else{
            return FALSE;
        }
    }
}