<?php
/**
 * 购买
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Mobile\Controller;
use Mobile\Controller\MobileMemberController;
use Common\Lib\Language;
use Common\Lib\Email;
use Common\Lib\Log;
use Common\Lib\Model;



class MemberBuyController extends MobileMemberController {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 购物车、直接购买第一步:选择收获地址和配置方式
     */
    public function buy_step1() {
    	
    	$merge_store_product = $_POST['merge_store_product'];
    	
        $cart_id = explode(',', $_POST['cart_id']);

        $logic_buy = logic('buy');

        //得到会员等级
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if(!$member_info['is_buy']) output_error('您没有商品购买的权限,如有疑问请联系客服人员');

        if ($member_info){
            $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
            $member_discount = $member_gradeinfo['orderdiscount'];
            $member_level = $member_gradeinfo['level'];
        } else {
            $member_discount = $member_level = 0;
        }

        //得到购买数据
        $result = $logic_buy->buyStep1($cart_id, $_POST['ifcart'], $this->member_info['member_id'], $this->member_info['store_id'],null,$member_discount,$member_level);
        if(!$result['state']) {
            output_error($result['msg']);
        } else {
            $result = $result['data'];
        }
        
        if (intval($_POST['address_id']) > 0) {
        	Model('address')->editAddress(array("is_default" => 0), array('1' => 1));
        	Model('address')->editAddress(array("is_default" => 1), array('address_id' => intval($_POST['address_id'])));
            $result['address_info'] = Model('address')->getDefaultAddressInfo(array('address_id'=>intval($_POST['address_id']),'member_id'=>$this->member_info['member_id']));
        }
 
        if ($result['address_info']) {
//         	var_dump($result['address_info']);
            $data_area = $logic_buy->changeAddr($result['freight_list'], $result['address_info']['city_id'], $result['address_info']['area_id'], $this->member_info['member_id']);
            if(!empty($data_area) && $data_area['state'] == 'success' ) {
                if (is_array($data_area['content'])) {
                    foreach ($data_area['content'] as $store_id => $value) {
                        $data_area['content'][$store_id] = daPriceFormat($value);
                    }
                }
            } else {
                output_error('地区请求失败');
            }
        }

        //整理数据
        $store_cart_list = array();
        $store_total_list = $result['store_goods_total_1'];
        foreach ($result['store_cart_list'] as $key => $value) {
            $store_cart_list[$key]['goods_list'] = $value;
            $store_cart_list[$key]['store_goods_total'] = $result['store_goods_total'][$key];

            $store_cart_list[$key]['store_mansong_rule_list'] = $result['store_mansong_rule_list'][$key];

            if (is_array($result['store_voucher_list'][$key]) && count($result['store_voucher_list'][$key]) > 0) {
                reset($result['store_voucher_list'][$key]);
                $store_cart_list[$key]['store_voucher_info'] = current($result['store_voucher_list'][$key]);
                $store_cart_list[$key]['store_voucher_info']['voucher_price'] = daPriceFormat($store_cart_list[$key]['store_voucher_info']['voucher_price']);
                $store_total_list[$key] -= $store_cart_list[$key]['store_voucher_info']['voucher_price'];
            } else {
                $store_cart_list[$key]['store_voucher_info'] = array();
            }

            $store_cart_list[$key]['store_voucher_list'] = $result['store_voucher_list'][$key];
            if(!empty($result['cancel_calc_sid_list'][$key])) {
                $store_cart_list[$key]['freight'] = '0';
                $store_cart_list[$key]['freight_message'] = $result['cancel_calc_sid_list'][$key]['desc'];
            } else {
                $store_cart_list[$key]['freight'] = '1';
            }
            $store_cart_list[$key]['store_name'] = $value[0]['store_name'];
        }

        $buy_list = array();
        $buy_list['store_cart_list'] = $store_cart_list;
        $buy_list['freight_hash'] = $result['freight_list'];
        $buy_list['address_info'] = $result['address_info'];
        $buy_list['ifshow_offpay'] = $result['ifshow_offpay'];
        $buy_list['vat_hash'] = $result['vat_hash'];
        $buy_list['inv_info'] = $result['inv_info'];
        $buy_list['available_predeposit'] = $result['available_predeposit'];
        $buy_list['available_rc_balance'] = $result['available_rc_balance'];
        if (is_array($result['rpt_list']) && !empty($result['rpt_list'])) {
            foreach ($result['rpt_list'] as $k => $v) {
                unset($result['rpt_list'][$k]['rpacket_id']);
                unset($result['rpt_list'][$k]['rpacket_end_date']);
                unset($result['rpt_list'][$k]['rpacket_owner_id']);
                unset($result['rpt_list'][$k]['rpacket_code']);
            }
        }
        $buy_list['rpt_list'] = $result['rpt_list'] ? $result['rpt_list'] : array();
        $buy_list['zk_list'] = $result['zk_list'];

        if ($data_area['content']) {
            $store_total_list = Logic('buy_1')->reCalcGoodsTotal($store_total_list,$data_area['content'],'freight');
            //返回可用平台红包
            $result['rpt_list'] = Logic('buy_1')->getStoreAvailableRptList($this->member_info['member_id'],array_sum($store_total_list),'rpacket_limit desc');
            reset($result['rpt_list']);
            if (is_array($result['rpt_list']) && count($result['rpt_list']) > 0) {
                $result['rpt_info'] = current($result['rpt_list']);
                unset($result['rpt_info']['rpacket_id']);
                unset($result['rpt_info']['rpacket_end_date']);
                unset($result['rpt_info']['rpacket_owner_id']);
                unset($result['rpt_info']['rpacket_code']);
            }
        }
        $buy_list['order_amount'] = daPriceFormat(array_sum($store_total_list)-$result['rpt_info']['rpacket_price']);
        $buy_list['rpt_info'] = $result['rpt_info'] ? $result['rpt_info'] : array();
        $buy_list['address_api'] = $data_area ? $data_area : '';

        foreach ($store_total_list as $store_id => $value) {
            $store_total_list[$store_id] = daPriceFormat($value);
        }
        $buy_list['store_final_total_list'] = $store_total_list;

        if ($merge_store_product == 1)
        {
        	$full_goods_list = array();
        	
        	$store_cart_list = $buy_list['store_cart_list'];
        	foreach ($store_cart_list as $store_id => $buy_info)
        	{
        		$goods_list = $buy_info['goods_list'];
        		foreach ($goods_list as $goods_item)
        		{
        			$full_goods_list[] = $goods_item;		
        		}
        	}
        	$buy_list['merge_store_product'] = $full_goods_list;
        }
        
        output_data($buy_list);
    }

    /**
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     *
     */
    public function buy_step2() {
        $param = array();
        $param['ifcart'] = $_POST['ifcart'];
        $param['cart_id'] = explode(',', $_POST['cart_id']);
        $param['address_id'] = $_POST['address_id'];
        $param['vat_hash'] = $_POST['vat_hash'];
        $param['offpay_hash'] = $_POST['offpay_hash'];
        $param['offpay_hash_batch'] = $_POST['offpay_hash_batch'];
        $param['pay_name'] = $_POST['pay_name'];
        $param['invoice_id'] = $_POST['invoice_id'];
        $param['rpt'] = $_POST['rpt'];

        //处理代金券
        $voucher = array();
        $post_voucher = explode(',', $_POST['voucher']);
        if(!empty($post_voucher)) {
            foreach ($post_voucher as $value) {
                list($voucher_t_id, $store_id, $voucher_price) = explode('|', $value);
                $voucher[$store_id] = $value;
            }
        }
        $param['voucher'] = $voucher;

        $_POST['pay_message'] = trim($_POST['pay_message'],',');
        $_POST['pay_message'] = explode(',',$_POST['pay_message']);
        $param['pay_message'] = array();
        if (is_array($_POST['pay_message']) && $_POST['pay_message']) {
            foreach ($_POST['pay_message'] as $v) {
                if (strpos($v, '|') !== false) {
                    $v = explode('|', $v);
                    $param['pay_message'][$v[0]] = $v[1];
                }
            }
        }
        $param['pd_pay'] = $_POST['pd_pay'];
        $param['rcb_pay'] = $_POST['rcb_pay'];
        $param['password'] = $_POST['password'];
        $param['fcode'] = $_POST['fcode'];
        $param['order_from'] = 2;
        $logic_buy = logic('buy');

        //得到会员等级
        $model_member = Model('member');
        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if ($member_info){
            $member_gradeinfo = $model_member->getOneMemberGrade(intval($member_info['member_exppoints']));
            $member_discount = $member_gradeinfo['orderdiscount'];
            $member_level = $member_gradeinfo['level'];
        } else {
            $member_discount = $member_level = 0;
        }
        $result = $logic_buy->buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email'],$member_discount,$member_level);
        if(!$result['state']) {
            output_error($result['msg']);
        }
        $order_info = current($result['data']['order_list']);
        output_data(array('pay_sn' => $result['data']['pay_sn'],'payment_code'=>$order_info['payment_code']));
    }

    /**
     * 验证密码
     */
    public function check_password() {
        if(empty($_POST['password'])) {
            output_error('参数错误');
        }

        $model_member = Model('member');

        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        if($member_info['member_paypwd'] == md5($_POST['password'])) {
            output_data('1');
        } else {
            output_error('密码错误');
        }
    }

    /**
     * 更换收货地址
     */
    public function change_address() {
        $logic_buy = Logic('buy');
        if (empty($_POST['city_id'])) {
            $_POST['city_id'] = $_POST['area_id'];
        }
        
        $data = $logic_buy->changeAddr($_POST['freight_hash'], $_POST['city_id'], $_POST['area_id'], $this->member_info['member_id']);
        if(!empty($data) && $data['state'] == 'success' ) {
            output_data($data);
        } else {
            output_error('地址修改失败');
        }
    }

    /**
     * 实物订单支付(新接口)
     */
    public function pay() {
        $pay_sn = $_POST['pay_sn'];
        if (!preg_match('/^\d{18}$/',$pay_sn)){
            output_error('该订单不存在');
        }

        //查询支付单信息
        $model_order= Model('order');
        $pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$this->member_info['member_id']),true);
        if(empty($pay_info)){
            output_error('该订单不存在');
        }
    
        //取子订单列表
        $condition = array();
        $condition['pay_sn'] = $pay_sn;
        $condition['order_state'] = array('in',array(ORDER_STATE_NEW,ORDER_STATE_PAY));
        $order_list = $model_order->getOrderList($condition,'','*','','',array(),true);
        if (empty($order_list)) {
            output_error('未找到需要支付的订单');
        }

        //定义输出数组
        $pay = array();
        //支付提示主信息
        //订单总支付金额(不包含货到付款)
        $pay['pay_amount'] = 0;
        //充值卡支付金额(之前支付中止，余额被锁定)
        $pay['payed_rcb_amount'] = 0;
        //预存款支付金额(之前支付中止，余额被锁定)
        $pay['payed_pd_amount'] = 0;
        //还需在线支付金额(之前支付中止，余额被锁定)
        $pay['pay_diff_amount'] = 0;
        //账户可用金额
        $pay['member_available_pd'] = 0;
        $pay['member_available_rcb'] = 0;

        $logic_order = Logic('order');

        //计算相关支付金额
        foreach ($order_list as $key => $order_info) {
            if (!in_array($order_info['payment_code'],array('offline','chain'))) {
                if ($order_info['order_state'] == ORDER_STATE_NEW) {
                    $pay['payed_rcb_amount'] += $order_info['rcb_amount'];
                    $pay['payed_pd_amount'] += $order_info['pd_amount'];
                    $pay['pay_diff_amount'] += $order_info['order_amount'] - $order_info['rcb_amount'] - $order_info['pd_amount'];
                }
            }
        }
        if ($order_info['chain_id'] && $order_info['payment_code'] == 'chain') {
            $order_list[0]['order_remind'] = '下单成功，请在'.CHAIN_ORDER_PAYPUT_DAY.'日内前往门店提货，逾期订单将自动取消。';
            $flag_chain = 1;
        }

        //如果线上线下支付金额都为0，转到支付成功页
        if (empty($pay['pay_diff_amount'])) {
            output_error('订单重复支付');
        }

        $payment_list = Model('mb_payment')->getMbPaymentOpenList();
        if(!empty($payment_list)) {
            foreach ($payment_list as $k => $value) {
//                 if ($value['payment_code'] == 'wxpay') {
//                     unset($payment_list[$k]);
//                     continue;
//                 }
//                 unset($payment_list[$k]['payment_id']);
//                 unset($payment_list[$k]['payment_config']);
//                 unset($payment_list[$k]['payment_state']);
//                 unset($payment_list[$k]['payment_state_text']);
				
            	   $payment_config = unserialize($payment_list[$k]['payment_config']);
            	   foreach ($payment_config as $key => $value)
            	   {
            	   	  if (strstr($key, "appsecret") || strstr($key, "partnerkey") ||
            	   	  		(strstr($key, "Secret")) )
            	   	  {
            	   	      unset($payment_config[$key]);
            	   	  }	
            	   }
            	   $payment_list[$k]['payment_config'] = $payment_config;
            }
        }
        
        //显示预存款、支付密码、充值卡
        $pay['member_available_pd'] = $this->member_info['available_predeposit'];
        $pay['member_available_rcb'] = $this->member_info['available_rc_balance'];
        $pay['member_paypwd'] = $this->member_info['member_paypwd'] ? true : false;
        
        $pay['pay_sn'] = $pay_sn;
        $pay['payed_amount'] = daPriceFormat($pay['payed_rcb_amount']+$pay['payed_pd_amount']);
        unset($pay['payed_pd_amount']);unset($pay['payed_rcb_amount']);
        $pay['pay_amount'] = daPriceFormat($pay['pay_diff_amount']);
        unset($pay['pay_diff_amount']);
        $pay['member_available_pd'] = daPriceFormat($pay['member_available_pd']);
        $pay['member_available_rcb'] = daPriceFormat($pay['member_available_rcb']);
        $pay['payment_list'] = $payment_list ? array_values($payment_list) : array();
        $pay['payment_hash'] = Model('mb_payment')->hashPaymentList(array_values($payment_list));
        
        $temp_payment_list = array();
        $payment_list = $pay['payment_list'];
        foreach ($payment_list as $payment_item)
        {
        	unset($payment_item['payment_config']);
        	$temp_payment_list[] = $payment_item;
        }
        $pay['payment_list'] = $temp_payment_list;
        
        $temp_payment_hash = array();
        $payment_hash = $pay['payment_hash'];
        foreach ($payment_hash as $key => $val)
        {
        	unset($val['payment_config']);
        	$temp_payment_hash[$key] = $val;
        }
        $pay['payment_hash'] = $temp_payment_hash;
        
        output_data(array('pay_info'=>$pay));
    }

    /**
     * AJAX验证支付密码
     */
    public function check_pd_pwd(){
        if (empty($_POST['password'])) {
            output_error('支付密码格式不正确');
        }
        $buyer_info = Model('member')->getMemberInfoByID($this->member_info['member_id'],'member_paypwd');
        if ($buyer_info['member_paypwd'] != '') {
            if ($buyer_info['member_paypwd'] === md5($_POST['password'])) {
                output_data('1');
            }
        }
        output_error('支付密码验证失败');
    }

    /**
     * F码验证
     */
    public function check_fcode() {
        $goods_id = intval($_POST['goods_id']);
        if ($goods_id <= 0) {
            output_error('商品ID格式不正确');
        }
        if ($_POST['fcode'] == '') {
            output_error('F码格式不正确');
        }
        $result = logic('buy')->checkFcode($goods_id, $_POST['fcode']);
        if ($result['state']) {
            output_data('1');
        } else {
            output_error('F码验证抢购');
        }
    }
    
    
    /*微信支付获取预支付订单*/
    public function wxpay_native_prepay()
    {
    	$params = array();
    	
    	$pay_sn = $_GET['pay_sn'];
    	$payorder_info = $this->_get_wx_pay_info($pay_sn);
    	if ($payorder_info['error'] == 0)
    	{
    		$payorder_info = $payorder_info['data'];
    	}
    	else
    	{
    		$payorder_info = array();
    	}
    	
    	if (empty($payorder_info)) 
    	{
    		echo json_encode($params);
    		exit;
    	}
    	
    	$payment_conf = Model("mb_payment")->getPaymentConf("wxpay");
    	$appid = $payment_conf['payment_config']['wxpay_appid'];
    	$mch_id =  $payment_conf['payment_config']['wxpay_partnerid'];
    	$sign_key = $payment_conf['payment_config']['wxpay_partnerkey'];
    	
    	$nonce_str = genSecKey(30);
    	$notify_url = BASE_SITE_URL."/Api/payment/mobile/wxpay/notify_url.php";	//通知URL;
    	$client_ip = get_client_ip();
    	
    	$pay_amount = $payorder_info['pay_amount'] * 100;
		$params = array (
				'appid' => $appid,
				'mch_id' => $mch_id,
				'nonce_str' => $nonce_str,
				'body' => C ( 'site_name' ),
				'detail' => C ( 'site_name' ) . "_" . $pay_sn,
				'attach' => "",
				'out_trade_no' => $pay_sn,
				'fee_type' => "CNY",
				'total_fee' => $pay_amount, // last*100
				'spbill_create_ip' => $client_ip,
				'notify_url' => $notify_url,
				'trade_type' => "APP" 
		);
  
		$params ['sign'] = strtoupper(buildSimpleSign($params, $sign_key));
    	
    	echo json_encode($params);
    	exit;
    }
    
    //获取微信支付参数
    public function wxpay_native_pay()
    {
    	$params = array();
    	$pay_sn = $_GET['pay_sn'];
    	$prepay_id = $_GET['prepay_id'];
    	
    	$payorder_info = $this->_get_wx_pay_info($pay_sn);
    	if ($payorder_info['error'] == 0)
    	{
    		$payorder_info = $payorder_info['data'];
    	}
    	else
    	{
    		$payorder_info = array();
    	}
    	
    	if (empty($payorder_info))
    	{
    		echo json_encode($params);
    		exit;
    	}
    	 
    	$payment_conf = Model("mb_payment")->getPaymentConf("wxpay");
    	$appid = $payment_conf['payment_config']['wxpay_appid'];
    	$mch_id =  $payment_conf['payment_config']['wxpay_partnerid'];
    	$sign_key = $payment_conf['payment_config']['wxpay_partnerkey'];
    	 
    	$nonce_str = genSecKey(30);
    	$notify_url = BASE_SITE_URL."/Api/payment/mobile/wxpay/notify_url.php";	//通知URL;
    	$client_ip = get_client_ip();
    	
    	$params = array(
    		'appid' => $appid,
    		'partnerid' => $mch_id,
    		'prepayid' => $prepay_id,
    		'package' => "Sign=WXPay",
    		'noncestr' => $nonce_str,
    		'timestamp'  => time(),
    		 
    	);
    	$params['sign'] = strtoupper(buildSimpleSign($params, $signKey));
    	echo json_encode($params);
    	exit;
    }
    
    /*获取支付宝支付参数*/
    public function get_alipay_info()
    {
    	$params = array();
    	
    	$pay_sn = $_GET['pay_sn'];
    	$payorder_info = $this->_get_wx_pay_info($pay_sn);
    	if ($payorder_info['error'] == 0)
    	{
    		$payorder_info = $payorder_info['data'];
    	}
    	else
    	{
    		$payorder_info = array();
    	}
    	
    	if (empty($payorder_info))
    	{
    		echo json_encode($params);
    		exit;
    	}
    	
    	$payment_conf = Model("mb_payment")->getPaymentConf("alipay_native");
    	$alipay_account = $payment_conf['payment_config']['alipay_account'];
    	$partner_id =  $payment_conf['payment_config']['alipay_partner'];
    	$rsa_public = $payment_conf['payment_config']['rsa_public'];
    	$rsa_private = $payment_conf['payment_config']['rsa_private'];
    	
    	$subject = C ( 'site_name' ) . "-支付";
    	$body = C ( 'site_name' ) . "_" . $pay_sn;
    	$fee = $payorder_info['pay_amount'] * 100;;
    	$num = $pay_sn;	
    	$partner = $partner_id;
    	$seller = $alipay_account;
    	$rsaPrivate = $rsa_private;
    	$rsaPublic = $rsa_public;
    	$notifyUrl = BASE_SITE_URL."/Api/payment/mobile/alipay_native/notify_url.php";	//通知URL;
    
    	$ret = array("subject" => $subject, 
    			"body" => $body,
    			"fee" => $fee,
    			"num" => $num,
    			"partner" => $partner,
    			"seller" => $seller,
    			"rsa_public" => $rsaPublic,
    			"rsa_private" => $rsaPrivate,
    			"notify_url" => $notifyUrl,
    	); 
    	//TODO 拼接字符串 供客户端支付宝支付使用
    	$ret['info'] = '';
    	echo json_encode($ret);
    	exit;
    }
    
    
    //获取订单信息
    private function _get_wx_pay_info($pay_sn)
    {
    	$ret = array("error" => 0, "data" => "", "errmsg" => "");
    	//查询支付单信息
    	$model_order= Model('order');
    	$pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$this->member_info['member_id']),true);
    	if(empty($pay_info)){
    		$ret['error'] = -1;
    		$ret['errmsg'] = '该订单不存在';
    		return $ret;
    	}
    	 
    	//取子订单列表
    	$condition = array();
    	$condition['pay_sn'] = $pay_sn;
    	$condition['order_state'] = array('in',array(ORDER_STATE_NEW,ORDER_STATE_PAY));
    	$order_list = $model_order->getOrderList($condition,'','*','','',array(),true);
    	if (empty($order_list)) {
    		$ret['error'] = -2;
    		$ret['errmsg'] = '未找到需要支付的订单';
    		return $ret;
    	}
    	 
    	//定义输出数组
    	$pay = array();
    	//支付提示主信息
    	//订单总支付金额(不包含货到付款)
    	$pay['pay_amount'] = 0;
    	//充值卡支付金额(之前支付中止，余额被锁定)
    	$pay['payed_rcb_amount'] = 0;
    	//预存款支付金额(之前支付中止，余额被锁定)
    	$pay['payed_pd_amount'] = 0;
    	//还需在线支付金额(之前支付中止，余额被锁定)
    	$pay['pay_diff_amount'] = 0;
    	//账户可用金额
    	$pay['member_available_pd'] = 0;
    	$pay['member_available_rcb'] = 0;
    	 
    	$logic_order = Logic('order');
    	 
    	//计算相关支付金额
    	foreach ($order_list as $key => $order_info) {
    		if (!in_array($order_info['payment_code'],array('offline','chain'))) {
    			if ($order_info['order_state'] == ORDER_STATE_NEW) {
    				$pay['payed_rcb_amount'] += $order_info['rcb_amount'];
    				$pay['payed_pd_amount'] += $order_info['pd_amount'];
    				$pay['pay_diff_amount'] += $order_info['order_amount'] - $order_info['rcb_amount'] - $order_info['pd_amount'];
    			}
    		}
    	}
    	if ($order_info['chain_id'] && $order_info['payment_code'] == 'chain') {
    		$order_list[0]['order_remind'] = '下单成功，请在'.CHAIN_ORDER_PAYPUT_DAY.'日内前往门店提货，逾期订单将自动取消。';
    		$flag_chain = 1;
    	}
    	 
    	//如果线上线下支付金额都为0，转到支付成功页
    	if (empty($pay['pay_diff_amount'])) {
    		$ret['error'] = -3;
    		$ret['errmsg'] = '订单重复支付';
    		return $ret;
    	}
    
    	//显示预存款、支付密码、充值卡
    	$pay['member_available_pd'] = $this->member_info['available_predeposit'];
    	$pay['member_available_rcb'] = $this->member_info['available_rc_balance'];
    	$pay['member_paypwd'] = $this->member_info['member_paypwd'] ? true : false;
    	$pay['pay_sn'] = $pay_sn;
    	$pay['payed_amount'] = daPriceFormat($pay['payed_rcb_amount']+$pay['payed_pd_amount']);
    	unset($pay['payed_pd_amount']);unset($pay['payed_rcb_amount']);
    	$pay['pay_amount'] = daPriceFormat($pay['pay_diff_amount']);
    	unset($pay['pay_diff_amount']);
    	$pay['member_available_pd'] = daPriceFormat($pay['member_available_pd']);
    	$pay['member_available_rcb'] = daPriceFormat($pay['member_available_rcb']);
    	 
    	$ret['error'] = 0;
    	$ret['errmsg'] = '成功';
    	$ret['data'] = $pay;
    	return $ret;
    }
}
