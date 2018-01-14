<?php
/**
 * 店铺地址
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

class StoreMapController extends BaseSellerController {
    public function __construct() {
        parent::__construct();
    }
    /**
     * 店铺地址地图显示
     *
     */
    public function index() {
        $model_store_map = Model('store_map');
        $store_id = $_SESSION['store_id'];
        $condition = array();
        $condition['store_id'] = $store_id;
        $map_list = $model_store_map->getStoreMapList($condition, '', '', 'map_id asc');
        $this->assign('map_list',$map_list);
        self::profile_menu('store_map','index');
        $this->render('store_map.index');
    }
    /**
     * 店铺地址列表显示
     *
     */
    public function lists() {
        $model_store_map = Model('store_map');
        $store_id = $_SESSION['store_id'];
        $condition = array();
        $condition['store_id'] = $store_id;
        $map_list = $model_store_map->getStoreMapList($condition, 10, '', 'map_id asc');
        $this->assign('map_list',$map_list);
        $this->assign('show_page',$model_store_map->showpage());
        self::profile_menu('store_map','list');
        $this->render('store_map.list');
    }
    /**
     * 增加店铺地址
     *
     */
    public function add_map() {
        if (chksubmit()) {
            $model_store = Model('store');
            $store_id = $_SESSION['store_id'];
            $store = $model_store->getStoreInfoByID($store_id);

            $map_array = array();
            $map_array['store_id'] = $store['store_id'];
            $map_array['sc_id'] = $store['sc_id'];
            $map_array['store_name'] = $store['store_name'];
            $map_array['name_info'] = $_POST['name_info'];
            $map_array['address_info'] = $_POST['address_info'];
            $map_array['phone_info'] = $_POST['phone_info'];
            $map_array['bus_info'] = $_POST['bus_info'];
            $map_array['baidu_province'] = $_POST['province'];
            $map_array['baidu_city'] = $_POST['city'];
            $map_array['baidu_district'] = $_POST['district'];
            $map_array['baidu_street'] = $_POST['street'];
            $map_array['baidu_lng'] = $_POST['lng'];
            $map_array['baidu_lat'] = $_POST['lat'];
            $map_array['update_time'] = time();

            $model_store_map = Model('store_map');
            $state = $model_store_map->addStoreMap($map_array);
            if ($state) {
                showDialog(Language::get('spd_common_save_succ'),'reload','succ','CUR_DIALOG.close();');
            } else {
                showDialog(Language::get('spd_common_save_fail'),'reload','error','CUR_DIALOG.close();');
            }
        }
        $this->render('store_map.add','null_layout');
    }
    /**
     * 编辑店铺地址
     *
     */
    public function edit_map() {
        $model_store_map = Model('store_map');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['map_id'] = intval($_GET['map_id']);
        if (chksubmit()) {
            $map_array = array();
            $map_array['name_info'] = $_POST['name_info'];
            $map_array['address_info'] = $_POST['address_info'];
            $map_array['phone_info'] = $_POST['phone_info'];
            $map_array['bus_info'] = $_POST['bus_info'];
            $map_array['update_time'] = time();
            $state = $model_store_map->editStoreMap($condition, $map_array);
            if ($state) {
                showDialog(Language::get('spd_common_save_succ'),'reload','succ','CUR_DIALOG.close();');
            } else {
                showDialog(Language::get('spd_common_save_fail'),'reload','error','CUR_DIALOG.close();');
            }
        }
        $map_list = $model_store_map->getStoreMapList($condition);
        $map = $map_list[0];
        $this->assign('map',$map);
        $this->render('store_map.edit','null_layout');
    }
    /**
     * 更新地址坐标
     *
     */
    public function update_map() {
        $model_store_map = Model('store_map');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['map_id'] = intval($_POST['map_id']);
        $map_array = array();
        $map_array['baidu_province'] = $_POST['province'];
        $map_array['baidu_city'] = $_POST['city'];
        $map_array['baidu_district'] = $_POST['district'];
        $map_array['baidu_street'] = $_POST['street'];
        $map_array['baidu_lng'] = $_POST['lng'];
        $map_array['baidu_lat'] = $_POST['lat'];
        $map_array['update_time'] = time();
        $state = $model_store_map->editStoreMap($condition, $map_array);
        if ($state) {
            echo '1';exit;
        } else {
            echo '0';exit;
        }
    }
    /**
     * 删除店铺地址
     *
     */
    public function del_map() {
        $model_store_map = Model('store_map');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $condition['map_id'] = intval($_GET['map_id']);
        $state = $model_store_map->delStoreMap($condition);
        if ($state) {
            showDialog(L('spd_common_op_succ'), 'reload', 'succ');
        } else {
            showDialog(L('spd_common_op_fail'), 'reload', 'error');
        }
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type,$menu_key='') {
        $menu_array = array();
        switch ($menu_type) {
            case 'store_map':
                $menu_array = array(
                    array('menu_key'=>'index','menu_name'=>'地图显示 ',  'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=StoreMap&a=index'),
                    array('menu_key'=>'list','menu_name'=>'列表显示 ',  'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=StoreMap&a=lists')
                );
                break;
        }
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }

}
