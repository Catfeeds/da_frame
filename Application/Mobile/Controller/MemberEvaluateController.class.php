<?php
/**
 * 会员评价
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Mobile\Controller;
use Mobile\Controller\MobileMemberController;
use Common\Lib\Language;
use Common\Lib\Log;


class MemberEvaluateController extends MobileMemberController {

    public function __construct(){
        parent::__construct();
    }
    
    /**
     * 评论
     */
    public function index() {
        $order_id = intval($_GET['order_id']);
        $return = Logic('member_evaluate')->validation($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        extract($return['data']);
        $store = array();
        $store['store_id'] = $store_info['store_id'];
        $store['store_name'] = $store_info['store_name'];
        $store['is_own_shop'] = $store_info['is_own_shop'];

        output_data(array('store_info' => $store, 'order_goods' => $order_goods));
    }
    
    /**
     * 评论保存
     */
    public function save() {
        $order_id = intval($_POST['order_id']);
        $return = Logic('member_evaluate')->validation($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        extract($return['data']);
        $return = Logic('member_evaluate')->save($_POST, $order_info, $store_info, $order_goods, $this->member_info['member_id'], $this->member_info['member_name']);

        if(!$return['state']) {
            output_data($return['msg']);
        } else {
            output_data('1');
        }
    }
    
    /**
     * 追评
     */
    public function again() {
        $order_id = intval($_GET['order_id']);
        $return = Logic('member_evaluate')->validationAgain($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        extract($return['data']);
        $store = array();
        $store['store_id'] = $store_info['store_id'];
        $store['store_name'] = $store_info['store_name'];
        $store['is_own_shop'] = $store_info['is_own_shop'];
        
        output_data(array('store_info' => $store, 'evaluate_goods' => $evaluate_goods));
    }

    /**
     * 追加评价保存
     */
    public function save_again() {
        $order_id = intval($_POST['order_id']);
        $return = Logic('member_evaluate')->validationAgain($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        extract($return['data']);

        $return = Logic('member_evaluate')->saveAgain($_POST, $order_info, $evaluate_goods);
        if(!$return['state']) {
            output_data($return['msg']);
        } else {
            output_data('1');
        }
    }
    
    /**
     * 虚拟订单评价
     */
    public function vr() {
        $order_id = intval($_GET['order_id']);
        $return = Logic('member_evaluate')->validationVr($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        extract($return['data']);
        output_data(array('order_info' => $order_info));
    }
    
    /**
     * 虚拟订单评价保存
     */
    public function save_vr() {
        $order_id = intval($_POST['order_id']);
        $return = Logic('member_evaluate')->validationVr($order_id, $this->member_info['member_id']);
        if (!$return['state']) {
            output_error($return['msg']);
        }
        extract($return['data']);

        $return = Logic('member_evaluate')->saveVr($_POST, $order_info, $store_info, $this->member_info['member_id'], $this->member_info['member_name']);
        if(!$return['state']) {
            output_data($return['msg']);
        } else {
            output_data('1');
        }
    }
}
