<?php
/**
 * 前台control父类
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Chat\Controller;
use Think\Controller;
use Common\Lib\Language;



class BaseController extends Controller {
	public function __construct(){
		parent::__construct();
		Language::read('common');
	}
}
