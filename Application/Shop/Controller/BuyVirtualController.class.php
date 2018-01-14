<?php
/**
 * 虚拟商品购买
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;

class BuyVirtualController extends BaseBuyController {

    public function __construct() {
        parent::__construct();
        Language::read('home_cart_index');
        if (!$_SESSION['member_id']){
            redirect(urlLogin('login', 'index', array('ref_url' => request_uri())));
        }
        //验证该会员是否禁止购买
        if(!$_SESSION['is_buy']){
            showMessage(Language::get('cart_buy_noallow'),'','html','error');
        }
        $this->assign('hidden_rtoolbar_cart', 1);
    }

    /**
     * 虚拟商品购买第一步
     */
    public function buy_step1() {
        $logic_buy_virtual = Logic('buy_virtual');
        $result = $logic_buy_virtual->getBuyStep1Data($_GET['goods_id'], $_GET['quantity'], $_SESSION['member_id']);
        if (!$result['state']) {
            showMessage($result['msg'], '', 'html', 'error');
        }
        //标识购买流程执行步骤
        $this->assign('buy_step','step1');

        $this->assign('goods_info',$result['data']['goods_info']);
        $this->assign('store_info',$result['data']['store_info']);

        $this->render('buy_virtual_step1');
    }

    /**
     * 虚拟商品购买第二步
     */
    public function buy_step2() {
        $logic_buy_virtual = Logic('buy_virtual');
        $result = $logic_buy_virtual->getBuyStep2Data($_POST['goods_id'], $_POST['quantity'], $_SESSION['member_id']);
        if (!$result['state']) {
            showMessage($result['msg'], '', 'html', 'error');
        }

        //处理会员信息
        $member_info = array_merge($this->member_info,$result['data']['member_info']);

        //标识购买流程执行步骤
        $this->assign('buy_step','step2');
        $this->assign('goods_info',$result['data']['goods_info']);
        $this->assign('store_info',$result['data']['store_info']);
        $this->assign('member_info',$member_info);
        $this->render('buy_virtual_step2');
    }

    /**
     * 虚拟商品购买第三步
     */
    public function buy_step3() {
        $logic_buy_virtual = Logic('buy_virtual');
        $_POST['order_from'] = 1;
        $result = $logic_buy_virtual->buyStep3($_POST,$_SESSION['member_id']);
        if (!$result['state']) {
            showMessage($result['msg'], "{$GLOBALS['_PAGE_URL']}", 'html', 'error');
        }
        //转向到商城支付页面
		$url = url('buy_virtual', 'pay', array('order_id' => $result['data']['order_id']));
        redirect($url);
    }

    /**
     * 下单时支付页面
     */
    public function pay() {
        $order_id   = intval($_GET['order_id']);
        if ($order_id <= 0){
            showMessage('该订单不存在',$GLOBALS['_PAGE_URL'] . '&c=MemberVrOrder','html','error');
        }

        $model_vr_order = Model('vr_order');
        //取订单信息
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $_SESSION['member_id'];
        $order_info = $model_vr_order->getOrderInfo($condition,'*',true);
        if (empty($order_info) || !in_array($order_info['order_state'],array(ORDER_STATE_NEW,ORDER_STATE_PAY))) {
            showMessage('未找到需要支付的订单',"{$GLOBALS['_PAGE_URL']}&c=MemberOrder",'html','error');
        }

        //定义输出数组
        $pay = array();
        //订单总支付金额
        $pay['pay_amount_online'] = 0;
        //充值卡支付金额(之前支付中止，余额被锁定)
        $pay['payd_rcb_amount'] = 0;
        //预存款支付金额(之前支付中止，余额被锁定)
        $pay['payd_pd_amount'] = 0;
        //还需在线支付金额(之前支付中止，余额被锁定)
        $pay['payd_diff_amount'] = 0;
        //账户可用金额
        $pay['member_pd'] = 0;
        $pay['member_rcb'] = 0;

        $pay['pay_amount_online'] = floatval($order_info['order_amount']);
        $pay['payd_rcb_amount'] = floatval($order_info['rcb_amount']);
        $pay['payd_pd_amount'] = floatval($order_info['pd_amount']);
        $pay['payd_diff_amount'] = $order_info['order_amount'] - $order_info['rcb_amount'] - $order_info['pd_amount'];

        $this->assign('order_info',$order_info);

        //如果所需支付金额为0，转到支付成功页
        if ($pay['payd_diff_amount'] == 0) {
            redirect($GLOBALS['_PAGE_URL'] . '&c=BuyVirtual&a=pay_ok&order_sn='.$order_info['order_sn'].'&order_id='.$order_info['order_id'].'&order_amount='.daPriceFormat($order_info['order_amount']));
        }

        //是否显示站内余额操作(如果以前没有使用站内余额支付过且非货到付款)
        $pay['if_show_pdrcb_select'] = ($pay['payd_rcb_amount'] == 0 && $pay['payd_pd_amount'] == 0);
        
        //显示支付接口列表
        $model_payment = Model('payment');
        $condition = array();
        $payment_list = $model_payment->getPaymentOpenList($condition);
        if (!empty($payment_list)) {
            unset($payment_list['predeposit']);
            unset($payment_list['offline']);
        }
        if (empty($payment_list)) {
            showMessage('暂未找到合适的支付方式',$GLOBALS['_PAGE_URL'] . '&c=MemberVrOrder','html','error');
        }
        $this->assign('payment_list',$payment_list);

        if ($pay['if_show_pdrcb_select']) {
            //显示预存款、支付密码、充值卡
            $available_predeposit = $available_rc_balance = 0;
            $buyer_info = Model('member')->getMemberInfoByID($_SESSION['member_id']);
            if (floatval($buyer_info['available_predeposit']) > 0) {
                $pay['member_pd'] = $buyer_info['available_predeposit'];
            }
            if (floatval($buyer_info['available_rc_balance']) > 0) {
                $pay['member_rcb'] = $buyer_info['available_rc_balance'];
            }
            $pay['member_paypwd'] = $buyer_info['member_paypwd'] ? true : false;
        }
        //标识购买流程执行步骤
        $this->assign('buy_step','step3');
        
        $this->assign('pay',$pay);

        $this->render('buy_virtual_step3');
    }

    /**
     * 支付成功页面
     */
    public function pay_ok() {
        $order_sn   = $_GET['order_sn'];
        if (!preg_match('/^\d{18}$/',$order_sn)){
            showMessage('该订单不存在',$GLOBALS['_PAGE_URL'] . '&c=MemberVrOrder','html','error');
        }
        $this->render('buy_virtual_step4');
    }
}
