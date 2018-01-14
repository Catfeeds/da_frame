<?php
namespace Chain\Controller;
use Think\Controller;
use Common\Lib\Language;
use Common\Lib\Cache;



class BaseChainController extends Controller {
	/**
	 * 构造函数
	 */
	public function __construct(){
		parent::__construct();
		/**
		 * 读取通用、布局的语言包
		 */
		Language::read('common');
		/**
		 * 设置布局文件内容
		*/
		$this->setLayout('chain_layout');
		/**
		 * SEO
		*/
		$this->SEO();
		/**
		 * 获取导航
		*/
		$this->assign('nav_list', rkcache('nav',true));
	}
	/**
	 * SEO
	 */
	protected function SEO() {
		$this->assign('html_title','门店系统      ' . C('site_name') . '');
		$this->assign('seo_keywords','');
		$this->assign('seo_description','');
	}
}