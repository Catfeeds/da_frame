<?php
/********************************** 前台control父类 **********************************************/
namespace Mobile\Controller;
use Think\Controller;
use Common\Lib\Language;
use Common\Lib\Chat;
use Common\Lib\Page;



class MobileController extends Controller {

	//客户端类型
	protected $client_type_array = array('android', 'wap', 'wechat', 'ios', 'windows');
	//列表默认分页数
	protected $page = 5;


	protected  $wx_unionid = '';
	protected $wx_openid = '';
	
	public function __construct() {
		parent::__construct();
		Language::read('mobile');

		//分页数处理
		$page = intval($_GET['page']);
		if($page > 0) {
			$this->page = $page;
		}
		
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);

		if (strpos($agent, "micromessenger") || strstr($agent, "wechat")) {
			$this->_wxapp_register();
		}
	}
	
	
	public function _wxapp_register()
	{
		$http_headers = $_SERVER;
		$temp_headers = array();
		foreach ($http_headers as $key => $val)
		{
			$temp_headers[strtolower($key)] = $val;
		}
		$http_headers = $temp_headers;

		$user_info = $http_headers['http_shopdauserinfo'];
		$user_info_arr = json_decode($user_info, true);

		$wx_unionid = $user_info_arr['unionid'];
		$wx_openid = $user_info_arr['openid'];
		
		$this->wx_unionid = $wx_unionid;
		$this->wx_openid = $wx_openid;

		//var_dump($http_headers);
		
		if (!empty($wx_unionid))
		{
			$client = 'weixin_app';
			$logic_connect_api = Logic('connect_api');
			$state_data = $logic_connect_api->wxRegister($user_info_arr, $client);
			$GLOBALS['mobile_token'] = $state_data['token'];
			$_POST['key'] = $GLOBALS['mobile_token'];
		}
	}

}