<?php
	header("Content-type:text/html; charset=UTF-8") ;
	date_default_timezone_set('Asia/Chongqing') ;

	require_once SITE_PATH . '/addons/plugins/login/taobao/config.php';

	class taobao {
		private $_authorize_url;
		private $_taobao_key;
		private $_taobao_secret;
		
		/**
		 * taobao.class.php 构造函数
		 */
		public function __construct() {
			$this->_taobao_key		= TAOBAO_KEY;
			$this->_taobao_secret	= TAOBAO_SECRET;
		}
		
		/**
		 * 生成请求地址
		 * @param string $call_back 网站接收结果的回调地址
		 */
		public function getUrl($call_back = null, $needSkip, $uid, $isPick) {
			if (empty($this->_taobao_key) || empty($this->_taobao_secret))
				return false;
			if (is_null($call_back)) {
				if ($isPick) {
					$call_back = U('home/Public/PickCallback', array('type' => 'taobao'));
				}
				else {
					if ($needSkip == 1) {
						$call_back = U('home/Public/taobaoCallback', array('skip' => 1, 'newid' => $uid));
					}
					else {
						$call_back = U('home/Public/taobaoCallback');
					}
				}
				
			}
			$_SESSION['TAOBAO_CALLBACK_URL'] = rawurlencode($call_back);
			//https://oauth.taobao.com/authorize
			$this->_authorize_url = 'https://oauth.taobao.com/authorize' . '?response_type=code' . 
					'&client_id=' . $this->_taobao_key . 
					'&redirect_uri=' . rawurlencode($call_back);
			return $this->_authorize_url;
		}
		
		/**
		 * 根据授权码获取令牌
		 * @param string $code 淘宝授权码
		 * @return string 从淘宝获取到的令牌
		 */
		public function checkUser() {
			if (empty($this->_taobao_key) || empty($this->_taobao_secret))
				return false;	
			
			$code = $_REQUEST['code'];
			
			$postfields = array(
					'grant_type'     => 'authorization_code',
					'client_id' 		  => $this->_taobao_key,
					'client_secret'   => $this->_taobao_secret,
					'code'          	  => $code,
					'redirect_uri'    => $_SESSION['TAOBAO_CALLBACK_URL'],
			);
			
			$url = 'https://oauth.taobao.com/token';
			$text = $this->curl($url, $postfields);
			
			$token = json_decode($text);
			// $access_token = $token->access_token;
			$_SESSION['taobao']['taobao_user_id'] = $token->taobao_user_id; // 淘宝用户ID
			$_SESSION['taobao']['access_token']   = $token->access_token;
			$_SESSION['taobao']['refresh_token']  = $token->refresh_token;
			$_SESSION['open_platform_type']       = 'taobao';
			
			return true;
		}
		
		/**
		 * 获取淘帐户用户信息
		 */
		public function userinfo() {
			include_once(SITE_PATH . '/addons/libs/goods/taobao/Taoapi.php');
			if (!$tokeninfo = $_SESSION['taobao']['access_token']) 
				return false;
			
			//https请求获取用户信息
			$url = "https://eco.taobao.com/router/rest?access_token={$tokeninfo}&method=taobao.user.buyer.get&v=2.0&fields=user_id,nick,sex,avatar&format=json";
			$response = $this->curl($url);
			if ($response && $userinfo = json_decode($response, true)) {
				$me               = $userinfo['user_buyer_get_response']['user'];
				$user['id']       = isset($me['user_id']) ? $me['user_id'] : $_SESSION['taobao']['taobao_user_id'];
				$user['uname']    = isset($me['nick']) ? $me['nick'] : null;
				$user['province'] = isset($me['location']) ? $me['location']['state'] : '';
				$user['city']     = isset($me['location']) ? $me['location']['state'] : '';
				$user['location'] = isset($me['location']) ? $me['location']['address'] : '';
				$user['userface'] = isset($me['avatar']) ? $me['avatar'] : '';
				$user['sex']      = ($me['sex']=='m') ? 1 : 0;
				
				return $user;
			}
			else {
				return false;
			}
		}
		
		/**
		 * 
		 * @param string $url 请求地址
		 * @param array $postFields 发送数据
		 * @throws Exception
		 * @return json 淘宝返回结果
		 */
		function curl($url, $postFields = null)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FAILONERROR, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
			$postBodyString = "";
			if (is_array($postFields) && 0 < count($postFields))
			{
				foreach ($postFields as $k => $v)
				{
					$postBodyString .= "$k=" . urlencode($v) . "&";
				}
				unset($k, $v);
			}
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
			$reponse = curl_exec($ch);
			if (curl_errno($ch)){
				throw new Exception(curl_error($ch),0);
			}
			else{
				$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if (200 !== $httpStatusCode){
					throw new Exception($reponse,$httpStatusCode);
				}
			}
			curl_close($ch);
			return $reponse;
		}
	}