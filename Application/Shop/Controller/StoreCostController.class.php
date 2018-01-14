<?php
/**
 * 商户消费日志
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
use Common\Lib\Model;
use Common\Lib\Page;

class StoreCostController extends BaseSellerController {
    public function __construct() {
        parent::__construct();
    }

    public function cost_list() {
        $model_store_cost = Model('store_cost');
        $condition = array();
        $condition['cost_store_id'] = $_SESSION['store_id'];
        if(!empty($_GET['cost_remark'])) {
            $condition['cost_remark'] = array('like', '%'.$_GET['cost_remark'].'%');
        }
        $condition['cost_time'] = array('time', array(strtotime($_GET['add_time_from']), strtotime($_GET['add_time_to'])));
        $cost_list = $model_store_cost->getStoreCostList($condition, 10, 'cost_id desc');
        $this->assign('cost_list', $cost_list);
        $this->assign('show_page', $model_store_cost->showpage(2));

        $this->profile_menu('cost_list');
        $this->render('store_cost.list');
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
            'menu_key' => 'cost_list',
            'menu_name' => '消费列表',
            'menu_url' => urlShop('store_cost', 'cost_list')
        );
        $this->assign('member_menu', $menu_array);
        $this->assign('menu_key', $menu_key);
    }

}
