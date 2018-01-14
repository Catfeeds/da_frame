<?php
namespace Mobile\Controller;

use Think\Controller;

class WxbaseController extends MobileController
{

	private $appId;
	private $appKey;
	
	public function __construct()
	{
		parent::__construct();
		$config = $this->_get_wxapp_config();
		$this->appId = $config["app_wxapp_appid"];
		$this->appKey = $config["app_wxapp_appkey"];
	}


	public function wxapp_jscode2session($code)
	{
		$ret = array("openid" => "", "unionid" => "", );
		$args = array("appid" => $this->appId,
				"secret" => $this->appKey,
				"js_code" => $code,
				"grant_type" => "authorization_code",
		);

		$url = "https://api.weixin.qq.com/sns/jscode2session";
		$result = callOnce($url, $args);
		$result = json_decode($result, true);
		//var_dump($result);
		if (isset($result['openid'])) 
		{
			$ret = array("openid" => $result['openid'],
					"unionid" => $result['unionid'],
					"appId" => $this->appId,
			);
		}
		return $ret;
	}
	
	private function _get_wxapp_config()
	{
		$setting_mod = Model("setting");
		$appid = $setting_mod->getRowSetting("app_wxapp_appid");
		$appkey = $setting_mod->getRowSetting("app_wxapp_appkey");
		$ret = array(
				"app_wxapp_appid" => $appid['value'],
				"app_wxapp_appkey" => $appkey['value'],
		);
		return $ret;
	}
	
}