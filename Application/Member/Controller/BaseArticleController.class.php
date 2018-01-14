<?php
namespace Member\Controller;
use Member\Controller\BaseController;
use Common\Lib\Language;
use Common\Lib\Cache;


class BaseArticleController extends BaseController {
	/**
	 * 构造函数
	 */
	public function __construct(){
		parent::init_view();

		/**
		 * 读取通用、布局的语言包
		 */
		Language::read('common,core_lang_index');
		/**
		 * 设置布局文件内容
		*/
		$this->setLayout('article_layout');

		/**
		 * 获取导航
		*/
		$this->assign('nav_list', rkcache('nav',true));

		/**
		 *  输出头部的公用信息
		*/
		$this->showLayout();

		/**
		 * 文章
		*/
		$this->init_article();
	}

}
