<?php
/**
 * 商家运费模板
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Mobile\Controller;
use Mobile\Controller\MobileSellerController;
use Common\Lib\Language;
use Common\Lib\Model;


class SellerTransportController extends MobileSellerController{

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->transport_list();
    }

    /**
     * 返回商家店铺商品分类列表
     */
    public function transport_list() {
        $model_transport = Model('transport');
        $transport_list = $model_transport->getTransportList(array('store_id'=>$this->store_info['store_id']));
        output_data(array('transport_list' => $transport_list));
    }
}
