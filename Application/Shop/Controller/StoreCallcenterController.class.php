<?php
/**
 * 客服中心
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


class StoreCallcenterController extends BaseSellerController {
    public function __construct() {
        parent::__construct();
        Language::read('member_store_index');
    }
    public function index(){
        $model_store = Model('store');
        $store_info = $model_store->getStoreInfo(array('store_id' => $_SESSION['store_id']));
        $this->assign('storeinfo', $store_info);
        $this->profile_menu('store_callcenter');
        $model_seller = Model('seller');
        $seller_list = $model_seller->getSellerList(array('store_id' => $store_info['store_id']), '', 'seller_id asc');//账号列表
        $this->assign('seller_list', $seller_list);
        $this->render('store_callcenter');
    }
    /**
     * 保存
     */
    public function save(){
        if(chksubmit()){
            $update = array();
            $i=0;
            if(is_array($_POST['pre']) && !empty($_POST['pre'])){
                foreach($_POST['pre'] as $val){
                    if(empty($val['name']) || empty($val['type']) || empty($val['num'])) continue;
                    $update['store_presales'][$i]['name']   = $val['name'];
                    $update['store_presales'][$i]['type']   = intval($val['type']);
                    $update['store_presales'][$i]['num']    = $val['num'];
                    $i++;
                }
                $update['store_presales'] = serialize($update['store_presales']);
            }else{
                $update['store_presales'] = serialize(null);
            }

            $i=0;
            if(is_array($_POST['after']) && !empty($_POST['after'])){
                foreach($_POST['after'] as $val){
                    if(empty($val['name']) || empty($val['type']) || empty($val['num'])) continue;
                    $update['store_aftersales'][$i]['name'] = $val['name'];
                    $update['store_aftersales'][$i]['type'] = intval($val['type']);
                    $update['store_aftersales'][$i]['num']  = $val['num'];
                    $i++;
                }
                $update['store_aftersales'] = serialize($update['store_aftersales']);
            }else{
                $update['store_aftersales'] = serialize(null);
            }

            $update['store_workingtime'] = $_POST['working_time'];
            $where = array();
            $where['store_id']  = $_SESSION['store_id'];
            Model('store')->editStore($update,$where);
            showDialog(Language::get('spd_common_save_succ'), $GLOBALS['_PAGE_URL'] . '&c=StoreCallcenter', 'succ');
        }
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key) {
        $menu_array = array(
            1=>array('menu_key'=>'store_callcenter','menu_name'=>Language::get('spd_member_path_store_callcenter'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=StoreCallcenter'),
        );
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}
