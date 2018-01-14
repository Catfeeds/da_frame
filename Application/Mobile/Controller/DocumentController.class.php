<?php
/**
 * 前台品牌分类
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Mobile\Controller;
use Mobile\Controller\DocumentController;
use Common\Lib\Language;
use Common\Lib\Model;


class DocumentController extends MobileHomeController {
    public function __construct() {
        parent::__construct();
    }

    public function agreement() {
        $doc = Model('document')->getOneByCode('agreement');
        output_data($doc);
    }
}
