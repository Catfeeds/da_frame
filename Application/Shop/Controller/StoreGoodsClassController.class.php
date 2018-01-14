<?php
/**
 * 会员中心——我是卖家
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


class StoreGoodsClassController extends BaseSellerController {
    public function __construct() {
        parent::__construct();
        Language::read('member_store_index');
    }
    /**
     * 卖家商品分类
     *
     * @param
     * @return
     */
    public function index() {
        $model_class    = Model('store_goods_class');

        if($_GET['type'] == 'ok') {
            if(intval($_GET['class_id']) != 0) {
                $class_info = $model_class->getStoreGoodsClassInfo(array('stc_id'=>intval($_GET['class_id'])));
                $this->assign('class_info',$class_info);
            }
            if(intval($_GET['top_class_id']) != 0) {
                $this->assign('class_info',array('stc_parent_id'=>intval($_GET['top_class_id'])));
            }
            $goods_class        = $model_class->getStoreGoodsClassList(array('store_id'=>$_SESSION['store_id'],'stc_parent_id'=>0));
            $this->assign('goods_class',$goods_class);
            $this->render('store_goods_class.add','null_layout');
        } else {
            $goods_class        = $model_class->getTreeClassList(array('store_id'=>$_SESSION['store_id']),2);
            $str    = '';
            if(is_array($goods_class) and count($goods_class)>0) {
                foreach ($goods_class as $key => $val) {
                    $row[$val['stc_id']]    = $key + 1;
                    $str .= intval($row[$val['stc_parent_id']]).",";
                }
                $str = substr($str,0,-1);
            } else {
                $str = '0';
            }
            $this->assign('map',$str);
            $this->assign('class_num',count($goods_class)-1);
            $this->assign('goods_class',$goods_class);

            self::profile_menu('store_goods_class','store_goods_class');
            $this->render('store_goods_class.list');
        }
    }
    /**
     * 卖家商品分类保存
     *
     * @param
     * @return
     */
    public function goods_class_save() {
        $model_class    = Model('store_goods_class');
        if(isset($_POST['stc_id'])) {
            $stc_id = intval($_POST['stc_id']);
            if ($stc_id <= 0) {
                showDialog(L('wrong_argument'));
            }
            $class_array    = array();
            if($_POST['stc_name'] != ''){
                $class_array['stc_name']     = $_POST['stc_name'];
            }
            if($_POST['stc_parent_id'] != ''){
                $class_array['stc_parent_id']= $_POST['stc_parent_id'];
            }
            if($_POST['stc_state'] != ''){
                $class_array['stc_state']    = $_POST['stc_state'];
            }
            if($_POST['stc_sort'] != ''){
                $class_array['stc_sort']     = $_POST['stc_sort'];
            }
            $where = array();
            $where['store_id'] = $_SESSION['store_id'];
            $where['stc_id'] = intval($_POST['stc_id']);
            $state = $model_class->editStoreGoodsClass($class_array, $where);
            if($state) {
                showDialog(Language::get('spd_common_save_succ'),$GLOBALS['_PAGE_URL'] . '&c=StoreGoodsClass&a=index','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
            } else {
                showDialog(Language::get('spd_common_save_fail'));
            }
        } else {
            $class_array        = array();
            $class_array['stc_name']      = $_POST['stc_name'];
            $class_array['stc_parent_id'] = $_POST['stc_parent_id'];
            $class_array['stc_state']     = $_POST['stc_state'];
            $class_array['store_id']      = $_SESSION['store_id'];
            $class_array['stc_sort']      = $_POST['stc_sort'];
            $state = $model_class->addStoreGoodsClass($class_array);
            if($state) {
                showDialog(Language::get('spd_common_save_succ'),$GLOBALS['_PAGE_URL'] . '&c=StoreGoodsClass&a=index','succ',empty($_GET['inajax']) ?'':'CUR_DIALOG.close();');
            } else {
                showDialog(Language::get('spd_common_save_fail'));
            }
        }
    }
    /**
     * 卖家商品分类删除
     *
     * @param
     * @return
     */
    public function drop_goods_class() {
        $model_class    = Model('store_goods_class');
        $stcid_array = explode(',', $_GET['class_id']);
        foreach ($stcid_array as $key => $val) {
            if (!is_numeric($val)) unset($stcid_array[$key]);
        }
        $where = array();
        $where['stc_id'] = array('in', $stcid_array);
        $where['store_id'] = $_SESSION['store_id'];
        $drop_state = $model_class->delStoreGoodsClass($where);
        if ($drop_state){
            showDialog(Language::get('spd_common_del_succ'),$GLOBALS['_PAGE_URL'] . '&c=StoreGoodsClass&a=store_goods_class','succ');
        }else{
            showDialog(Language::get('spd_common_del_fail'));
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
        Language::read('member_layout');
        $menu_array     = array();
        switch ($menu_type) {
            case 'store_goods_class':
                $menu_array = array(
                1=>array('menu_key'=>'store_goods_class','menu_name'=>'店铺分类',   'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=StoreGoodsClass&a=store_goods_class'));
                break;
        }
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}
