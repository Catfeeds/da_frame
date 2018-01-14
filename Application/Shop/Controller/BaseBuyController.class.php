<?php
/********************************** 购买流程父类 **********************************************/

namespace Shop\Controller;
use Shop\Controller\BaseController;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\Model;


class BaseBuyController extends BaseController {
	protected $member_info = array();   // 会员信息

	public function __construct(){
		parent::__construct();
		
		Language::read('common,home_layout');
		//输出会员信息
		$this->member_info = $this->getMemberAndGradeInfo(true);
		$this->assign('member_info', $this->member_info);

		$this->setDir('buy');
		$this->setLayout('buy_layout');
		if ($_GET['column'] && strtoupper(CHARSET) == 'GBK'){
			$_GET = Language::getGBK($_GET);
		}
		if(!C('site_status')) halt(C('closed_reason'));
		//获取导航
		$this->assign('nav_list', rkcache('nav',true));

		$this->assign('contract_list',Model('contract')->getContractItemByCache());
	}
}

