<?php
/**
 * PHP飞信发送类-增强版
 *
 * 本项目基于quanhengzhuang在google code上面的项目php-fetion
 * 基于php-fetion版本1.2.1
 *
 * @author horsley <i@a-li.me>
 * @version 1.1.0
 */
class PHPFetionEx {
	
	/**
	 * 发送者手机号
	 * @var string
	 */
	protected $_mobile;
	
	/**
	 * 飞信密码
	 * @param string
	 */
	protected $_password;
	
	/**
	 * Cookie字符串
	 * @param string
	 */
	protected $_cookie = '';
	
	/**
	 * Session字符串t
	 * @param string
	 */
	protected $_t = '';
	
	/**
	 * 构造函数
	 */
	public function __construct() {		
	}
	
	/**
	 * 析构函数
	 */
	public function __destruct() {
		$this->logout();
	}
	
	/**
	 * 动作成功检测函数
	 *
	 * 在此集中检测各处post结果是否包含成功标记，这里应该经常被测试确保有效
	 *
	 * @param string $returnStr post的返回结果
	 * @param string $checkType 检测类型，为字符串常量
	 * @return boolean
	 */
	protected function _isSucceed($returnStr, $checkType) {
		if($returnStr === '' || $checkType === '') {
			return false;
		}
		switch ($checkType) {
			case 'login':
				// 验证时间 2011.12.07
				return !(strpos($returnStr, '您正在登录中国移动WAP飞信,请稍候') === false);
				break;
			case 'msgToMyself':
				// 验证时间 2011.08.08
				return !(strpos($returnStr, '短信发送成功') === false);
				break;
			case 'timingMsgToMyself':
				// 验证时间 2011.08.08
				return !(strpos($returnStr, '定时短信设置成功') === false);
				break;
			case 'MsgToOthers':
				// 验证时间 2011.08.08
				return !(strpos($returnStr, '发送消息成功') === false);
				break;
			case 'logoutsubmit':
				// 验证时间 2011.12.07
				return !(strpos($returnStr, '您已经成功退出WAP飞信') === false);
				break;
		}
	}
	
	/**
	 * 设置当前用户	 
	 * @param string $mobile 手机号(登录者)
	 * @param string $password 飞信密码
	 */
	public function setUser($mobile, $password) {
		if($mobile === '' || $password === '') {
			return false;
		}
		$this->_mobile = $mobile;
		$this->_password = $password;
	}
	/**
	 * 登录飞信
	 *
	 * 登录方式有1:在线 2:忙碌 3:离开 4:隐身   默认为隐身
	 * 推荐使用隐身方式登陆避免使用本类过程中有人向你发送消息而你并没做处理
	 *
	 * @param int $loginStatus 登录方式
	 * @return string
	 */
	public function login($loginStatus = 4) {
		$uri = '/im/login/inputpasssubmit1.action';
		$data = 'm='.$this->_mobile.'&pass='.urlencode($this->_password).'&loginstatus='.$loginStatus;
		
		$result = $this->_postWithCookie($uri, $data);
		
		// 解析Cookie
		preg_match_all('/.*?\r\nSet-Cookie: (.*?);.*?/si', $result, $matches);
		if(isset($matches[1])) {
			$this->_cookie = implode('; ', $matches[1]);
		}

		return $this->_isSucceed($result, 'login');
	}

	/**
	 * 简单地向指定的手机号发送飞信
	 *
	 * 要使用定时短信或者强制短信请使用专用版本函数
	 * @param string $mobile 手机号(接收者)
	 * @param string $message 短信内容
	 * @return boolean
	 */
	public function send($mobile, $message) {
		if($message === '') {
			return false;
		}

		// 判断是给自己发还是给好友发
		if($mobile == $this->_mobile) {
			return $this->sendToMyself($message);
		} else {
			$uid = $this->_getUid($mobile);
			return $uid === '' ? false : $this->sendToOthers($uid, $message);
		}
	}

	/**
	 * 获取飞信ID
	 * @param string $mobile 手机号
	 * @return string
	 */
	protected function _getUid($mobile) {
		$uri = '/im/index/searchOtherInfoList.action';
		$data = 'searchText='.$mobile;
		
		$result = $this->_postWithCookie($uri, $data);
		
		// 匹配
		preg_match('/toinputMsg\.action\?touserid=(\d+)/si', $result, $matches);
		
		return isset($matches[1]) ? $matches[1] : '';
	}
	
	/**
	 * 向好友发送飞信
	 * @param string $uid 飞信ID，注意，不是手机号码也不是飞信号，通过getUid获取
	 * @param string $message 短信内容
	 * @param boolean $isForceSMS 是否强制发送手机短信，可空，默认为假
	 * @return boolean
	 */
	public function sendToOthers($uid, $message, $isForceSMS = false) {
		if($isForceSMS) {
			$uri = '/im/chat/sendShortMsg.action?touserid='.$uid;
		} else {
			$uri = '/im/chat/sendMsg.action?touserid='.$uid;
		}
		
		$data = 'msg='.urlencode($message);
		
		$result = $this->_postWithCookie($uri, $data);
		
		return $this->_isSucceed($result, 'MsgToOthers');
	}
	
	/**
	 * 给自己发飞信
	 *
	 * 可以指定时间定时发送，时间格式为12位字符串yyyymmddhhii，至少要为当前时间的十分钟以后，否则会失败
	 *
	 * @param string $message
	 * @param string $timing 发送时间，默认为立即发送
	 * @return boolean
	 */
	public function sendToMyself($message, $timing = '') {
		if($timing === '') {
			$uri = '/im/user/sendMsgToMyselfs.action';
			$result = $this->_postWithCookie($uri, 'msg='.urlencode($message));
		
			return $this->_isSucceed($result, 'msgToMyself');
		} else {
			if(strlen($timing) != 12) {
				return false;
			}
			$uri = '/im/user/sendTimingMsgToMyselfs.action';
			$result = $this->_postWithCookie($uri, 'msg='.urlencode($message).'&timing'.$timing);
			
			return $this->_isSucceed($result, 'timingMsgToMyself');
		}
		
	}
	
	/**
	 * 退出飞信
	 * @return string
	 */
	public function logout() {
		$uri = '/im/index/logoutsubmit.action';
		$result = $this->_postWithCookie($uri, '');
		
		return $this->_isSucceed($result, 'logoutsubmit');
	}
	
	/**
	 * 携带Cookie向f.10086.cn发送POST请求
	 * @param string $uri
	 * @param string $data
	 */
	protected function _postWithCookie($uri, $data) {
		$fp = fsockopen('f.10086.cn', 80);
		fputs($fp, "POST $uri HTTP/1.1\r\n");
		fputs($fp, "Host: f.10086.cn\r\n");
		fputs($fp, "Cookie: {$this->_cookie}\r\n");
		fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
		fputs($fp, "Content-Length: ".strlen($data)."\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $data);

		$result = '';
		while(!feof($fp)) {
			$result .= fgets($fp);
		}

		fclose($fp);

		return $result;
	}
	
	/**
	 * 截取字符串的部分
	 *
	 * 应该要保证左右标记是唯一的，如果不是，返回的将是第一个左标记和左标记之后第一个右标记之间的内容
	 * @param string $haystack
	 * @param string $leftSign
	 * @param string $rightSign
	 * @return string
	 */
	protected function _getStrMid($haystack, $leftSign, $rightSign) {
		$tmpStrArr = explode($leftSign, $haystack, 2);
		$tmpStrArr = explode($rightSign, $tmpStrArr[1], 2);
		return $tmpStrArr[0];
	}

}
