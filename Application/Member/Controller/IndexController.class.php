<?php
/**
 * 默认展示页面
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Member\Controller;
use Member\Controller\BaseLoginController;
use Common\Lib\Language;


class IndexController extends BaseLoginController{
    public function __construct() {
		parent::init_view();
        @header("location: " . urlMember('member_information'));
    }
}
