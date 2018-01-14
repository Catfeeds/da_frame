<?php
/**
 * 店铺卖家注销
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Log;


class SellerLogoutController extends BaseSellerController {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->logout();
    }

    public function logout() {
        $this->recordSellerLog('注销成功');
        // 清除店铺消息数量缓存
        setDaCookie('storemsgnewnum'.$_SESSION['seller_id'],0,-3600);
        session_destroy();
        redirect($GLOBALS['_PAGE_URL'] . '&c=SellerLogin');
    }

}
