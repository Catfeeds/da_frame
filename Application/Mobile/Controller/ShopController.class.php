<?php
/**
 * 所有店铺街
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Mobile\Controller;
use Mobile\Controller\MobileHomeController;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Page;


class ShopController extends MobileHomeController {

    public function __construct(){
        parent::__construct();
    }

    /*
     * 首页显示
     */
    public function index(){
        $this->_get_Own_Store_List();

    }


    private  function  _get_Own_Store_List(){
		$model_store = Model('store');
        //查询条件
        $condition = array();
		$keyword = trim($_GET['keyword']);
		if ($keyword != ''){
				$condition['store_name'] = array('like','%'.$keyword.'%');
			}
		if($_GET['area_info']!='undefined' && $_GET['area_info']!='null' ){
					$condition['area_info'] = array('like','%'.$_GET['area_info'].'%');
			}
        if(!empty($_GET['sc_id']) && intval($_GET['sc_id']) > 0) {
            $condition['sc_id'] = $_GET['sc_id'];
        } elseif (!empty($_GET['keyword'])) {
            $condition['store_name'] = array('like', '%' . $_GET['keyword'] . '%');
        }
        //所需字段
        $fields = "*";
        //排序方式
        $order = $this->_store_list_order($_GET['key'], $_GET['order']);
        $store_list = $model_store->where($condition)->order($order)->page(10)->select();
        $page_count = $model_store->gettotalpage();
        $own_store_list = $store_list;
        $simply_store_list = array();

        foreach ($own_store_list as $key => $value) {

            $simply_store_list[$key]['store_id'] = $own_store_list[$key]['store_id'];
            $simply_store_list[$key]['store_name'] = $own_store_list[$key]['store_name'];
			$simply_store_list[$key]['store_collect'] = $own_store_list[$key]['store_collect'];
            $simply_store_list[$key]['store_address'] = $own_store_list[$key]['store_address'];
            $simply_store_list[$key]['store_area_info'] = $own_store_list[$key]['area_info'];
			$simply_store_list[$key]['store_avatar'] = $own_store_list[$key]['store_avatar'];
			$simply_store_list[$key]['goods_count'] = $own_store_list[$key]['goods_count'];
            $simply_store_list[$key]['store_avatar_url'] = UPLOAD_SITE_URL.'/'.ATTACH_COMMON.DS.C('default_store_avatar');
			if($own_store_list[$key]['store_avatar']){
				$simply_store_list[$key]['store_avatar_url'] = UPLOAD_SITE_URL.'/shop/store/'.$own_store_list[$key]['store_avatar'];
			}
			

        }
		
		 output_data(array('store_list' => $simply_store_list), mobile_page($page_count));
       
    }

	 /**
     * 商品列表排序方式
     */
    private function _store_list_order($key, $order) {
        $result = 'store_id desc';
        if (!empty($key)) {

            $sequence = 'desc';
            if($order == 1) {
                $sequence = 'asc';
            }

            switch ($key) {
                //销量
                case '1' :
                    $result = 'store_id' . ' ' . $sequence;
                    break;
                //浏览量
                case '2' :
                    $result = 'store_name' . ' ' . $sequence;
                    break;
                //价格
                case '3' :
                    $result = 'store_name' . ' ' . $sequence;
                    break;
            }
        }
        return $result;
    }

	
	
	
	
	
	
	
	
	
}