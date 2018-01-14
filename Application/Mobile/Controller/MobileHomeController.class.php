<?php
namespace Mobile\Controller;
use Mobile\Controller\MobileController;
use Common\Lib\Language;
use Common\Lib\Model;


class MobileHomeController extends MobileController{
	public function __construct() {
		parent::__construct();
	}

	protected function getMemberIdIfExists()
	{
		$key = $_POST['key'];
		if (empty($key)) {
			$key = $_GET['key'];
		}

		$model_mb_user_token = Model('mb_user_token');
		$mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
		if (empty($mb_user_token_info)) {
			return 0;
		}

		return $mb_user_token_info['member_id'];
	}
}
