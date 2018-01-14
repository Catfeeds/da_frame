<?php
/**
 * 卖家账号日志
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
use Common\Lib\Model;
use Common\Lib\Page;

class SellerLogController extends BaseSellerController {
    public function __construct() {
        parent::__construct();
    }

    public function log_list() {
        $model_seller_log = Model('seller_log');
        $condition = array();
        $condition['log_store_id'] = $_SESSION['store_id'];
        if(!empty($_GET['seller_name'])) {
            $condition['log_seller_name'] = array('like', '%'.$_GET['seller_name'].'%');
        }
        if(!empty($_GET['log_content'])) {
            $condition['log_content'] = array('like', '%'.$_GET['log_content'].'%');
        }
        $condition['log_time'] = array('time', array(strtotime($_GET['add_time_from']), strtotime($_GET['add_time_to'])));
        $log_list = $model_seller_log->getSellerLogList($condition, 10, 'log_id desc');
        $this->assign('log_list', $log_list);
        $this->assign('show_page', $model_seller_log->showpage(2));

        $this->profile_menu('log_list');
        $this->render('seller_log.list');
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key = '') {
        $menu_array = array();
        $menu_array[] = array(
            'menu_key' => 'log_list',
            'menu_name' => '日志列表',
            'menu_url' => urlShop('seller_log', 'log_list')
        );
        $this->assign('member_menu', $menu_array);
        $this->assign('menu_key', $menu_key);
    }

}
