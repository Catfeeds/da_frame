<?php
/********************************** 前台control父类 **********************************************/
namespace Shop\Controller;
use Shop\Controller\BaseController;
use Common\Lib\Language;
use Common\Lib\Log;


class BaseHomeController extends BaseController {

	public function __construct(){
		parent::__construct();
		//输出头部的公用信息
		$this->showLayout();
		//输出会员信息
		$this->getMemberAndGradeInfo(false);

		Language::read('common,home_layout');

		$this->setDir('home');

		$this->setLayout('home_layout');

		if ($_GET['column'] && strtoupper(CHARSET) == 'GBK'){
			$_GET = Language::getGBK($_GET);
		}
		if(!C('site_status')) halt(C('closed_reason'));
		// 自动登录
		$this->auto_login();
	}

}

