<?php
/**
 * cms首页
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Cms\Controller;


class IndexController extends CMSHomeController {

    public function __construct() {
        parent::__construct();
        $this->assign('index_sign','index');
    }
    public function index(){
        $this->render('index');
    }
}
