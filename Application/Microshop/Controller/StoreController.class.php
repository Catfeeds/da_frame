<?php
/**
 * 微商城店铺街
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
use Common\Lib\Model;
use Common\Lib\Page;


class StoreController extends MircroShopController{

    public function __construct() {
        parent::__construct();
        $this->assign('index_sign','store');
    }

    public function index(){
        $this->store_list();
    }

    /**
     * 店铺列表
     */
    public function store_list() {
        $model_store = Model('store');
        $model_microshop_store = Model('micro_store');
        $condition = array();
        $store_list = $model_microshop_store->getListWithStoreInfo($condition,30,'microshop_sort asc');
        $this->assign('list',$store_list);
        $this->assign('show_page',$model_store->showpage(2));
        //广告位
        self::get_microshop_adv('store_list');
        $this->assign('html_title',Language::get('spd_microshop_store').'-'.Language::get('spd_microshop').'-'.C('site_name'));
        $this->render('store_list');
    }

    /**
     * 店铺详细页
     */
    public function detail() {
        $store_id = intval($_GET['store_id']);
        if($store_id <= 0) {
            header('location: '.MICROSHOP_SITE_URL);die;
        }
        $model_store = Model('store');
        $model_goods = Model('goods');
        $model_microshop_store = Model('micro_store');

        $store_info = $model_microshop_store->getOneWithStoreInfo(array('microshop_store_id'=>$store_id));
        if(empty($store_info)) {
            header('location: '.MICROSHOP_SITE_URL);
        }

        //点击数加1
        $update = array();
        $update['click_count'] = array('exp','click_count+1');
        $model_microshop_store->modify($update,array('microshop_store_id'=>$store_id));

        $this->assign('detail',$store_info);

        $condition = array();
        $condition['store_id'] = $store_info['shop_store_id'];
        $goods_list = $model_goods->getGoodsListByColorDistinct($condition, 'goods_id,store_id,goods_name,goods_image,goods_price,goods_salenum', 'goods_id asc', 39);
        $this->assign('comment_type','store');
        $this->assign('comment_id',$store_id);
        $this->assign('list',$goods_list);
        $this->assign('show_page',$model_goods->showpage());
        //获得分享app列表
        self::get_share_app_list();
        $this->assign('html_title',$store_info['store_name'].'-'.Language::get('spd_microshop_store').'-'.Language::get('spd_microshop').'-'.C('site_name'));
        $this->render('store_detail');
    }

}
