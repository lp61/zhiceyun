<?php
require_once SITE_PATH . '/addons/plugins/login/douban/config.php';
require_once SITE_PATH . '/addons/plugins/login/douban/DoubanAPI2.php';
require_once SITE_PATH . '/addons/plugins/login/douban/DoubanOauth2.php';

date_default_timezone_set('Asia/Chongqing');

class douban {
	
	private $_authorize_url;
	private $_douban_key;
	private $_douban_secret;
	
	public function __construct() {
		$this->_douban_key		= DOUBAN_KEY;
		$this->_douban_secret	= DOUBAN_SECRET;
	}
	
	public function getUrl( $call_back = null, $needSkip ,$uid ,$state=0) {
		if ( empty($this->_douban_key) || empty($this->_douban_secret) )
			return false;
			
		if (is_null($call_back)) {
			if ( $needSkip == 1 ) {
				$call_back = U( 'home/Public/doubanCallback', array('skip' => 1, 'newid' => $uid) );
			}
			else {
				$call_back = U( 'home/Public/doubanCallback' );
			}
		}
		
		$_SESSION['DOUBAN_CALLBACK_URL'] = rawurlencode( $call_back );
		
		$douban = new DoubanOauth2( $this->_douban_key, $this->_douban_secret, rawurlencode( $call_back) );
		$this->_authorize_url = $douban->authorizationUrl() . '&state=' . $state;
		return $this->_authorize_url;
	}
	
	public function checkUser() {
		if ( empty($this->_douban_key) || empty($this->_douban_secret) )
			return false;
		
		$code = t( $_REQUEST['code'] );
		if ( !$code ) 
			return false;
		
		$call_back = $_SESSION['DOUBAN_CALLBACK_URL'];
		
		$douban = new DoubanOauth2( $this->_douban_key, $this->_douban_secret, $call_back );
		$douban->authorizationCode = $code;
		$douban_token = $douban->access();
		
		if ( $douban_token && $douban_token->access_token ) {
			$_SESSION['douban']["access_token"] = $douban_token->access_token;
			$_SESSION['douban']["access_token_secret"] = $douban_token->refresh_token;
			//保存豆瓣的用户ID，后面查询个人信息及好友信息时要用到
			$_SESSION['douban']["douban_uid"] = $douban_token->douban_user_id;
			$_SESSION['open_platform_type'] = 'douban';
			
			return true;
		}
		else {
			return false;
		}
	}
	
	// 用户资料
	public function userInfo() {
		if ( empty($this->_douban_key) || empty($this->_douban_secret) || empty($_SESSION['douban']['access_token']) || empty($_SESSION['douban']['douban_uid']) )
			return false;
		
		$douban = new DoubanOauth2( $this->_douban_key, $this->_douban_secret, null );
		
		$api = new DoubanAPI2();
		$api->userMe( $_SESSION['douban']['access_token'] );
		$res = $douban->send($api);
		
		$userInfo['id']			= $res->id;
		$userInfo['uname']		= $res->name;
		list( $host, $prefix, $avatar ) = explode( '/', str_replace('http://', '', $res->avatar) );
		if ( $avatar != 'user_normal.jpg' ) {
			$avatar = str_replace( 'u', 'ul', $avatar );
		}
		$userInfo['userface'] = "http://{$host}/{$prefix}/{$avatar}";
		$userInfo['location'] 	= $res->loc_name;
		
		return $userInfo;
	}
	
	/**
	 * 获取用户好友列表
	 * @return boolean|array
	 */
	public function get_fanslist() {
		if ( empty($this->_douban_key) || empty($this->_douban_secret) )
			return false;
		
		$douban = new DoubanOauth2( $this->_douban_key, $this->_douban_secret, null );
		$api = new DoubanAPI2();
		$api->getFriends( $_SESSION['douban']["douban_uid"], $_SESSION['douban']['access_token'] );
		$res = $douban->send($api);
		
		if ( $res ) {
			foreach ( $res->users as $key => $value ) {
				$userInfo[$key]['id']			= $value->id;
				$userInfo[$key]['uname']		= $value->name;
				$userInfo[$key]['userface']	= str_replace('u', 'ul', $value->avatar);
				$userinfo[$key]['location'] 	= $value->loc_name;
			}
			return $userinfo;
		}
		else {
			return false;
		}
	}
	
	/**
	 * 发布一个新的“我说”
	 * @param string $content
	 * @param array $opt
	 */
	public function upload($content, $pic, $opt) {
		//请参考地址：http://developers.douban.com/wiki/?title=shuo_v2
		$oauth_token = $opt['oauth_token'];
		
		$douban = new DoubanOauth2( $this->_douban_key, $this->_douban_secret, null );
		$api = new DoubanAPI2();
		$api->newStutas( $oauth_token );
		
		if($pic){
			$pic = getImgRealPath( $pic );
			$info = @getimagesize( $pic );
			$pic = '@' . $pic . ';type=' .  $info['mime'];
		}
		
		$data = array(
			'source' 			=> $this->_douban_key,
			'text' 				=> $content.'122',
			'image' 			=> $pic, //当有image时推荐url就不能使用
// 			'rec_title' 		=> $opt['title'],
// 			'rec_url'			=> $opt['url'],
// 			'rec_desc'		=> $opt['desc'],
		);
		
		$data  = $douban->send( $api, $data );
		
		if($data->code == 1000 || $data->id > 0){
			return true;
		}
		else{
			return false;
		}

	}
}