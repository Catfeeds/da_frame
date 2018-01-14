<?php
/**
 * 物流自提服务站首页
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Delivery\Controller;
use Delivery\Controller\BaseDeliveryController;
use Common\Lib\Language;
use Common\Lib\Log;


class IndexController extends BaseDeliveryController {
    public function __construct(){
        parent::__construct();
        @header("location: {$GLOBALS['_PAGE_URL']}&c=Login");die;
    }
}
