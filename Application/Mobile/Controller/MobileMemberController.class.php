<?php
namespace Mobile\Controller;
use Mobile\Controller\MobileController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;


class MobileMemberController extends MobileController{

	protected $member_info = array();

	public function __construct() {
		parent::__construct();
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($agent, "MicroMessenger") && $_GET["c"]=='auto') {
			$this->appId = C('app_weixin_appid');
			$this->appSecret = C('app_weixin_secret');
		}else{
			$model_mb_user_token = Model('mb_user_token');
			$key = $_POST['key'];
			if(empty($key)) {
				$key = $_GET['key'];
			}
			$mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
			if(empty($mb_user_token_info)) {
				output_error('请登录', array('login' => '0'));
			}

			$model_member = Model('member');
			$this->member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);

			if(empty($this->member_info)) {
				output_error('请登录', array('login' => '0'));
			} else {
				$this->member_info['client_type'] = $mb_user_token_info['client_type'];
				$this->member_info['openid'] = $mb_user_token_info['openid'];
				$this->member_info['token'] = $mb_user_token_info['token'];
				$level_name = $model_member->getOneMemberGrade($mb_user_token_info['member_id']);
				$this->member_info['level_name'] = $level_name['level_name'];
				//读取卖家信息
				$seller_info = Model('seller')->getSellerInfo(array('member_id'=>$this->member_info['member_id']));
				$this->member_info['store_id'] = $seller_info['store_id'];
			}
		}
	}

	public function getOpenId()
	{
		return $this->member_info['openid'];
	}

	public function setOpenId($openId)
	{
		$this->member_info['openid'] = $openId;
		Model('mb_user_token')->updateMemberOpenId($this->member_info['token'], $openId);
	}
}