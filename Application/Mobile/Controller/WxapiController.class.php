<?php
namespace Mobile\Controller;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\Model;
use Think\Controller;

class WxapiController extends Controller {
	
	private $appId = "";
	private $appKey = "";
	
	public function __construct() {
		parent::__construct();
		$appInfo = $this->getAppInfo();
		$this->appId = $appInfo['appId'];
		$this->appKey = $appInfo['appKey'];
	}
	
	public function wx_session()
	{
		$url = "https://api.weixin.qq.com/sns/jscode2session";
		$wx_app_js_code = $this->getParam("code");
		
		$args = array(
				"appid" => $this->appId,
				"secret" => $this->appKey,
				"js_code" => $wx_app_js_code,
				"grant_type" => "authorization_code",
		);
		$ret = callOnce($url, $args);
		
		echo $ret;
		exit;
	}
	
	public function wx_token()
	{
		$url = "https://api.weixin.qq.com/cgi-bin/token";
	}
	
	public function wx_user_info()
	{
		$url = "https://api.weixin.qq.com/cgi-bin/user/info";
	}
	
	public function getAppInfo()
	{
		$ret = array(
				"appId" => "wx4cb28c65b50138df",
				"appKey" => "252f4878922e779cad6b9d096123364a",
		);
		return $ret;
	}
}