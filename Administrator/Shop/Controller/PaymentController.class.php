<?php
/**
 * 支付方式
 *
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Model;


class PaymentController extends SystemController {
    public function __construct(){
        parent::__construct();
        Language::read('payment', 'Home');
    }

    /**
     * 支付方式
     */
    public function index(){
        $model_payment = Model('payment');
        $payment_list = $model_payment->getPaymentList(array('payment_code'=>array('neq','predeposit')));
        $this->assign('payment_list',$payment_list);
		$this->setDirquna('shop');
        $this->render('payment.list');
    }

    /**
     * 编辑
     */
    public function edit(){

        $model_payment = Model('payment');
        if (chksubmit()){
            $payment_id = intval($_POST["payment_id"]);
            $data = array();
            $data['payment_state'] = intval($_POST["payment_state"]);

            $payment_config = '';
            $config_array = explode(',',$_POST["config_name"]);//配置参数
            if(is_array($config_array) && !empty($config_array)) {
                $config_info = array();
                foreach ($config_array as $k) {
                    $config_info[$k] = trim($_POST[$k]);
                }

                $payment_config = serialize($config_info);
            }
            $data['payment_config'] = $payment_config;//支付接口配置信息
            $model_payment->editPayment($data,array('payment_id'=>$payment_id));
            showMessage(Language::get('spd_common_save_succ'),$GLOBALS['_PAGE_URL'] . '&c=Payment&a=index');
        }

        $payment_id = intval($_GET["payment_id"]);
        $payment = $model_payment->getPaymentInfo(array('payment_id'=>$payment_id));
 
        if ($payment['payment_config'] != ''){
        	$config_array = unserialize($payment['payment_config']);

            $this->assign('config_array', $config_array);
        }
        $this->assign('payment',$payment);
		$this->setDirquna('shop');
        $this->render('payment.edit');
    }
}
