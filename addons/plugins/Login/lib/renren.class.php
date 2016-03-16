<?php 
include_once SITE_PATH . '/addons/plugins/login/renren/RenrenOAuthApiService.class.php';
include_once SITE_PATH . '/addons/plugins/login/renren/RenrenRestApiService.class.php';
//include_once SITE_PATH . '/addons/plugins/login/renren/config.php';

class renren {
	private $_config;
	
	/**
	 * 构造函数
	 */
	public function __construct() {
		include_once SITE_PATH . '/addons/plugins/login/renren/config.php';
		$this->_config = $renren_config;
	}
	
	/**
	 * 获取人人第三方登录认证地址
	 * @param string $callback 网站回调地址
	 */
	function getUrl( $call_back = null, $needSkip, $uid, $isPick ) {
		$state = md5(date("YmdHis" . get_client_ip()));
		$_SESSION['renren']['state'] = $state;
		
		if ( empty($this->_config->APPID) || empty($this->_config->APIKey) || empty($this->_config->SecretKey) )
			return false;
			
		if (is_null($call_back)) {
			if ( $isPick ) {
				$call_back = U( 'home/Public/PickCallback', array( 'type' => 'renren' ) );
			}
			else {
				if ( $needSkip == 1 ) {
					$call_back = U('home/public/renrencallback', array( 'skip' => 1, 'newid' => $uid ) );
				}
				else {
					$call_back = U('home/public/renrencallback');
				}
			}
		}
		
		$_SESSION['RENREN_CALLBACK_URL'] = $call_back;
		$loginUrl = 'https://graph.renren.com/oauth/authorize?' . 
				'client_id=' . $this->_config->APPID . 
				'&response_type=code&scope=' . $this->_config->scope . 
				'&state=' . $_SESSION['renren']['state'] . 
				'&redirect_uri=' . rawurlencode($call_back);
		return $loginUrl;
	}
	
	/**
	 * 获取access_token
	 */
	function checkUser() {
		if ( empty($this->_config->APPID) || empty($this->_config->APIKey) || empty($this->_config->SecretKey) )
			return false;
		
		$code = $_REQUEST['code'];
		$oauthApi = new RenrenOAuthApiService;
		$post_params = array(
				'client_id'=>$this->_config->APIKey,
				'client_secret'=>$this->_config->SecretKey,
				'redirect_uri'=>$_SESSION['RENREN_CALLBACK_URL'],
				'grant_type'=>'authorization_code',
				'code'=>$code
		);
		$token_url='http://graph.renren.com/oauth/token';
		$access_info=$oauthApi->rr_post_curl($token_url,$post_params);
		$access_token=$access_info["access_token"];
		//$expires_in=$access_info["expires_in"];
		//$refresh_token=$access_info["refresh_token"];
		
		$_SESSION['renren']["access_token"] = $access_token;
		$_SESSION['open_platform_type'] = 'renren';
	}
	
	/**
	 * 获取人人登录用户信息
	 */
	function userinfo() {
		$restApi = new RenrenRestApiService;
		$params = array(
				'fields'=>'uid,name,sex,birthday,mainurl,hometown_location,university_history,tinyurl,headurl',
				'access_token'=>$_SESSION['renren']["access_token"]
		);
		
		$res = $restApi->rr_post_curl('users.getInfo', $params);
		
		$user['id']       = $res[0]["uid"];
		$user['uname']    = $res[0]["name"];
		$user['province'] = $res[0]["hometown_location"]['province'];
		$user['city']     = $res[0]["hometown_location"]['city'];
		$user['location'] = '';
		$user['userface'] = $res[0]["mainurl"];
		$user['sex']      = $res[0]['sex'];
		
		if (!isset( $user['id'] ) || empty( $user['id'] ))
			return false;
		
		return $user;
	}
	
	/**
	 * 获取好友列表
	 * @return boolean|unknown
	 */
	function get_fanslist() {
		$restApi = new RenrenRestApiService;
		$params = array(
				'page'				=>'1',
				'count'				=>'30',
				'access_token' 	=>$_SESSION['renren']["access_token"]
		);
		$res = $restApi->rr_post_curl('friends.getFriends', $params);
		foreach ($res as $key => $value) {
			$user[$key]['id'] 			= $value["uid"];
			$user[$key]['uname'] 		= $value["name"];
			$user[$key]['userface'] 	= $value["tinyurl"];
		}
		return $user;
	}

	/**
	 * 发布一个新鲜事
	 * @param string $text
	 * @param string $pic
	 * @param array $opt
	 * @return boolean
	 */
	function upload($text,$pic,$opt){
		$token = $opt['oauth_token']; 
		if (empty($token)) {
			$token = $_SESSION['renren']['access_token'];
		}
		$params = array(
				'name' 				=> '博图·汇分享',//$opt['name'],
				'description'		=> '博图·汇分享',
				'url'					=> $opt['url'],
				'image'				=> $pic,
				'action_name'		=> '',
				'action_link'		=> '',
				'message'			=> $text,
				'access_token'	=> $token
		);
		$rrObj = new RenrenRestApiService;
		$res   = $rrObj->rr_post_curl('feed.publishFeed', $params);

		$error_code = array('10702','20308','10400','10600','10703','303','304');
		if ($res['post_id'] > 0 || in_array($res['error_code'], $error_code))
			return true;
		else 
			return false;
	}
}