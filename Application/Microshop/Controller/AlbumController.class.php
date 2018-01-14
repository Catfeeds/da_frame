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


namespace Microshop\Controller;
use Microshop\Controller\MircroShopController;
use Common\Lib\Language;


class AlbumController extends MircroShopController {

    public function __construct() {
        parent::__construct();
        $this->assign('index_sign','album');
    }

    //首页
    public function index(){
        $this->render('album');
    }
}
