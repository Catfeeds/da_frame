<?php
namespace Shop\Controller;
use Shop\Controller\BaseStoreController;
use Common\Lib\Language;


class BaseChainController extends BaseStoreController {

	public function __construct(){

		Language::read('common,store_layout');

		if(!C('site_status')) halt(C('closed_reason'));

		$this->setDir('store');
		$this->setLayout('home_layout');

		//输出头部的公用信息
		$this->showLayout();
	}

}