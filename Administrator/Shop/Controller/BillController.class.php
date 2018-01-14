<?php
/**
 * 结算管理
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
use Common\Lib\Csv;
use Common\Lib\Excel;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;
use Common\Lib\QueueClient;
use Common\Lib\Tpl;


class BillController extends SystemController {
    /**
     * 每次导出订单数量
     * @var int
     */
    const EXPORT_SIZE = 1000;



    public function __construct(){
        parent::__construct();
		    $this->links = array(
        array('url'=> $GLOBALS['_PAGE_URL'] . "&c=Bill&a=index",'lang'=>'spd_manage'),
    );
    }

    /**
     * 结算单列表
     *
     */
    public function index(){
						
		$this->setDirquna('shop');
        $this->render('bill.index');
    }

    /**
     * 某店铺某月订单列表
     *
     */
    public function show_bill(){
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误','','html','error');
        }
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>$ob_id));
        if (!$bill_info){
            showMessage('参数错误','','html','error');
        }

        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $bill_info['ob_store_id'];
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        $end_unixtime = $if_end_date ? $end_unixtime+86400-1 : null;
        if ($if_start_date || $if_end_date) {
            $order_condition['finnshed_time'] = array('between',"{$start_unixtime},{$end_unixtime}");
        } else {
            $order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        if ($_GET['query_type'] == 'refund') {
            $sub_tpl_name = 'bill_order_bill.show.refund_list';
        } elseif ($_GET['query_type'] == 'cost') {
            $sub_tpl_name = 'bill_order_bill.show.cost_list';
        } elseif ($_GET['query_type'] == 'book') {
            $sub_tpl_name = 'bill_order_bill.show.order_book_list';
        } else {
            //订单列表
            $sub_tpl_name = 'bill_order_bill.show.order_list';
        }

        $this->assign('tpl_name',$sub_tpl_name);
        $this->assign('bill_info',$bill_info);
						
		$this->setDirquna('shop');
        $this->render('bill_order_bill.show');
    }

    public function get_bill_info_xml(){
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0 ) {
            exit();
        }
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>$ob_id));
        if (!$bill_info){
            exit();
        }

        $order_condition = array();
        $order_condition['order_state'] = ORDER_STATE_SUCCESS;
        $order_condition['store_id'] = $bill_info['ob_store_id'];
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        $end_unixtime = $if_end_date ? $end_unixtime+86400-1 : null;
        if ($if_start_date || $if_end_date) {
            $order_condition['finnshed_time'] = array('between',"{$start_unixtime},{$end_unixtime}");
        } else {
            $order_condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        if ($_GET['query_type'] == 'refund') {
            //退款订单列表
            $model_refund = Model('refund_return');
            $refund_condition = array();
            $refund_condition['seller_state'] = 2;
            $refund_condition['store_id'] = $bill_info['ob_store_id'];
            $refund_condition['goods_id'] = array('gt',0);
            $refund_condition['admin_time'] = $order_condition['finnshed_time'];
            if ($_POST['query'] != '' && in_array($_POST['qtype'],array('refund_sn','order_sn','buyer_name'))) {
                $refund_condition[$_POST['qtype']] = array('like',"%{$_POST['query']}%");
            }
            $sort_fields = array('refund_amount','commis_amount','refund_type','admin_time','buyer_id','store_id');
            if (in_array($_POST['sortorder'],array('asc','desc')) && in_array($_POST['sortname'],$sort_fields)) {
                $order = $_POST['sortname'].' '.$_POST['sortorder'];
            }
            $refund_list = $model_refund->getRefundReturnList($refund_condition,$_POST['rp'],'refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount','',$order);
            if (is_array($refund_list) && count($refund_list) == 1 && $refund_list[0]['refund_id'] == '') {
                $refund_list = array();
            }
            $data = array();
            $data['now_page'] = $model_refund->shownowpage();
            $data['total_num'] = $model_refund->gettotalnum();
            foreach ($refund_list as $refund_info) {
                $list = array();
                if ($refund_info['refund_type'] == 1) {
                    $list['operation'] = "<a class=\"btn green\" href=\"{$GLOBALS['_PAGE_URL']}&c=Refund&a=view&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
                } else {
                    $list['operation'] = "<a class=\"btn green\" href=\"{$GLOBALS['_PAGE_URL']}&c=Return&a=view&return_id={$refund_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
                }
                $list['refund_sn'] = $refund_info['refund_sn'];
                $list['order_sn'] = $refund_info['order_sn'];
                $list['refund_amount'] = daPriceFormat($refund_info['refund_amount']);
                $list['commis_amount'] = daPriceFormat($refund_info['commis_amount']);
                $list['rpt_amount'] = daPriceFormat($refund_info['rpt_amount']);
                $list['refund_type'] = str_replace(array(1,2), array('退款 ','退货'),$refund_info['refund_type']);
                $list['admin_time'] = date('Y-m-d',$refund_info['admin_time']);
                $list['buyer_name'] = $refund_info['buyer_name'];
                $list['buyer_id'] = $refund_info['buyer_id'];
                $list['store_name'] = $refund_info['store_name'];
                $list['store_id'] = $refund_info['store_id'];
                $data['list'][$refund_info['refund_id']] = $list;
            }
            exit($this->flexigridXML($data));
        } elseif ($_GET['query_type'] == 'cost') {

            //店铺费用
            $model_store_cost = Model('store_cost');
            $cost_condition = array();
            $cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
            $cost_condition['cost_time'] = $order_condition['finnshed_time'];
            $store_cost_list = $model_store_cost->getStoreCostList($cost_condition,$_POST['rp'],'cost_id desc');
            //取得店铺名字
            $store_info = Model('store')->getStoreInfoByID($bill_info['ob_store_id']);
            $data = array();
            $data['now_page'] = $model_store_cost->shownowpage();
            $data['total_num'] = $model_store_cost->gettotalnum();
            foreach ($store_cost_list as $store_cost_info) {
                $list = array();
                $list['store_name'] = $store_info['store_name'];
                $list['cost_remark'] = $store_cost_info['cost_remark'];
                $list['cost_price'] = daPriceFormat($store_cost_info['cost_price']);
                $list['cost_time'] = date('Y-m-d',$store_cost_info['cost_time']);
                $data['list'][$store_cost_info['cost_id']] = $list;
            }
            exit($this->flexigridXML($data));
        } elseif ($_GET['query_type'] == 'book') {

            $condition = array();
            //被取消的预定订单列表
            $model_order = Model('order');
            if ($_POST['query'] != '' && in_array($_POST['qtype'],array('order_sn'))) {
                $order_info = $model_order->getOrderInfo(array('order_sn'=>$_POST['query']));
                if ($order_info) {
                    $condition['book_order_id'] = $order_info['order_id'];
                } else {
                    $condition['book_order_id'] = 0;
                }
            }

            $model_order_book = Model('order_book');
            
            $condition['book_store_id'] = $bill_info['ob_store_id'];
            $condition['book_cancel_time'] = $order_condition['finnshed_time'];
            unset($order_condition['finnshed_time']);
            $order_book_list = $model_order_book->getOrderBookList($condition,$_POST['rp'],'book_id desc','*');

            //然后取订单信息
            $tmp_book = array();
            $order_id_array = array();
            if (is_array($order_book_list)) {
                foreach ($order_book_list as $order_book_info) {
                    $order_id_array[] = $order_book_info['book_order_id'];
                    $tmp_book[$order_book_info['book_order_id']]['book_cancel_time'] = $order_book_info['book_cancel_time'];
                    $tmp_book[$order_book_info['book_order_id']]['book_real_pay'] = $order_book_info['book_real_pay'];
                }
            }
            $order_list = $model_order->getOrderList(array('order_id'=>array('in',$order_id_array)));
            $data = array();
            $data['now_page'] = $model_order->shownowpage();
            $data['total_num'] = $model_order->gettotalnum();
            foreach ($order_list as $order_info) {
                $list = array();
                $list['operation'] = "<a class=\"btn green\" href=\"{$GLOBALS['_PAGE_URL']}&c=Order&a=show_order&order_id={$order_info['order_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
                $list['order_sn'] = $order_info['order_sn'];
                $list['order_amount'] = daPriceFormat($order_info['order_amount']);
                $list['shipping_fee'] = daPriceFormat($order_info['shipping_fee']);
                $list['deposit_amount'] = daPriceFormat($tmp_book[$order_info['order_id']]['book_real_pay']);
                $list['add_time'] = date('Y-m-d',$order_info['add_time']);
                $list['cancel_time'] = date('Y-m-d',$tmp_book[$order_info['order_id']]['book_cancel_time']);
                $list['buyer_name'] = $order_info['buyer_name'];
                $list['buyer_id'] = $order_info['buyer_id'];
                $list['store_name'] = $order_info['store_name'];
                $list['store_id'] = $order_info['store_id'];
                $data['list'][$order_info['order_id']] = $list;
            }
            exit($this->flexigridXML($data));
        } else {

            //订单列表
            $model_order = Model('order');
            if ($_POST['query'] != '' && in_array($_POST['qtype'],array('order_sn','buyer_name'))) {
                $order_condition[$_POST['qtype']] = array('like',"%{$_POST['query']}%");
            }
            if ($_GET['order_sn'] != ''){
                $order_condition['order_sn'] = array('like',"%{$_GET['order_sn']}%");
            }
            if ($_GET['buyer_name'] != ''){
                if ($_GET['jq_query']) {
                    $order_condition['buyer_name'] = $_GET['buyer_name'];
                } else {
                    $order_condition['buyer_name'] = array('like',"%{$_GET['buyer_name']}%");
                }
            }

            $sort_fields = array('order_amount','shipping_fee','commis_amount','add_time','finnshed_time','buyer_id','store_id');
            if (in_array($_POST['sortorder'],array('asc','desc')) && in_array($_POST['sortname'],$sort_fields)) {
                $order = $_POST['sortname'].' '.$_POST['sortorder'];
            }
            $order_list = $model_order->getOrderList($order_condition,$_POST['rp'],'*',$order);

            //然后取订单商品佣金
            $order_id_array = array();
            if (is_array($order_list)) {
                foreach ($order_list as $order_info) {
                    $order_id_array[] = $order_info['order_id'];
                }
            }
            $order_goods_condition = array();
            $order_goods_condition['order_id'] = array('in',$order_id_array);
            $field = 'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount,order_id';
            $commis_list = $model_order->getOrderGoodsList($order_goods_condition,$field,null,null,'','order_id','order_id');

            $data = array();
            $data['now_page'] = $model_order->shownowpage();
            $data['total_num'] = $model_order->gettotalnum();
            foreach ($order_list as $order_info) {
                $list = array();
                $list['operation'] = "<a class=\"btn green\" href=\"{$GLOBALS['_PAGE_URL']}&c=Order&a=show_order&order_id={$order_info['order_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
                $list['order_sn'] = $order_info['order_sn'];
                $list['order_amount'] = daPriceFormat($order_info['order_amount']);
                $list['shipping_fee'] = daPriceFormat($order_info['shipping_fee']);
                $list['commis_amount'] = daPriceFormat($commis_list[$order_info['order_id']]['commis_amount']);
                $list['rpt_amount'] = daPriceFormat($order_info['rpt_amount']);
                $list['add_time'] = date('Y-m-d',$order_info['add_time']);
                $list['finnshed_time'] = date('Y-m-d',$order_info['finnshed_time']);
                $list['buyer_name'] = $order_info['buyer_name'];
                $list['buyer_id'] = $order_info['buyer_id'];
                $list['store_name'] = $order_info['store_name'];
                $list['store_id'] = $order_info['store_id'];
                $data['list'][$order_info['order_id']] = $list;
            }
            exit($this->flexigridXML($data));
        }
    }

    public function bill_check(){
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误','','html','error');
        }
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
        $condition['ob_state'] = BILL_STATE_STORE_COFIRM;
        $update = $model_bill->editOrderBill(array('ob_state'=>BILL_STATE_SYSTEM_CHECK),$condition);
        if ($update){
            $this->log('审核账单,账单号：'.$ob_id,1);
            showMessage('审核成功，账单进入付款环节');
        }else{
            $this->log('审核账单，账单号：'.$ob_id,0);
            showMessage('审核失败','','html','error');
        }
    }

    /**
     * 账单付款
     *
     */
    public function bill_pay(){
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误','','html','error');
        }
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
        $condition['ob_state'] = BILL_STATE_SYSTEM_CHECK;
        $bill_info = $model_bill->getOrderBillInfo($condition);
        if (!$bill_info){
            showMessage('参数错误','','html','error');
        }
        if (chksubmit()){
            if (!preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_POST['pay_date'])) {
                showMessage('参数错误','','html','error');
            }
            $input = array();
            $input['ob_pay_content'] = $_POST['pay_content'];
            $input['ob_pay_date'] = strtotime($_POST['pay_date']);
            $input['ob_state'] = BILL_STATE_SUCCESS;
            $update = $model_bill->editOrderBill($input,$condition);
            if ($update){
                $model_store_cost = Model('store_cost');
                $cost_condition = array();
                $cost_condition['cost_store_id'] = $bill_info['ob_store_id'];
                $cost_condition['cost_state'] = 0;
                $cost_condition['cost_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
                $model_store_cost->editStoreCost(array('cost_state'=>1),$cost_condition);

                // 发送店铺消息
                $param = array();
                $param['code'] = 'store_bill_gathering';
                $param['store_id'] = $bill_info['ob_store_id'];
                $param['param'] = array(
                    'bill_no' => $bill_info['ob_id']
                );
                QueueClient::push('sendStoreMsg', $param);

                $this->log('账单付款,账单号：'.$ob_id,1);
                showMessage('保存成功', $GLOBALS['_PAGE_URL'] . '&c=Bill');
            }else{
                $this->log('账单付款,账单号：'.$ob_id,1);
                showMessage('保存失败','','html','error');
            }
        }else{
							
		$this->setDirquna('shop');
            $this->render('bill.pay');
        }
    }

    /**
     * 打印结算单
     *
     */
    public function bill_print(){
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            showMessage('参数错误','','html','error');
        }
        $model_bill = Model('bill');
        $condition = array();
        $condition['ob_id'] = $ob_id;
        $condition['ob_state'] = BILL_STATE_SUCCESS;
        $bill_info = $model_bill->getOrderBillInfo($condition);
        if (!$bill_info){
            showMessage('参数错误','','html','error');
        }

        $this->assign('bill_info',$bill_info);
				
		$this->setDirquna('shop');
        $this->render('bill.print','null_layout');
    }


    /**
     * 导出平台月出账单表
     *
     */
    public function export_bill(){
        $model_bill = Model('bill');
        $condition = array();
        if (preg_match('/^[\d,]+$/', $_GET['ob_id'])) {
            $_GET['ob_id'] = explode(',',trim($_GET['ob_id'],','));
            $condition['ob_id'] = array('in',$_GET['ob_id']);
        }
        list($condition,$order) = $this->_get_bill_condition($condition);

        if (!is_numeric($_GET['curpage'])){
            $count = $model_bill->getOrderBillCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE){
                //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                $this->assign('list',$array);
                $this->assign('murl','javascript:history.back(-1)');
                $this->render('export.excel');
                exit();
            }
            $limit = false;
        }else{
            //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";
        }
        $data = $model_bill->getOrderBillList($condition,'*','','ob_id desc',$limit);

        $export_data = array();
        $export_data[0] = array('账单编号','开始日期','结束日期','订单金额','运费','佣金金额','退款金额','退还佣金','店铺费用','本期应结','出账日期','账单状态','店铺','店铺ID');
        $ob_order_totals = 0;
        $ob_shipping_totals = 0;
        $ob_commis_totals = 0;
        $ob_order_return_totals = 0;
        $ob_commis_return_totals = 0;
        $ob_store_cost_totals = 0;
        $ob_result_totals = 0;
        foreach ($data as $k => $v) {
            $export_data[$k+1][] = $v['ob_id'];
            $export_data[$k+1][] = date('Y-m-d',$v['ob_start_date']);
            $export_data[$k+1][] = date('Y-m-d',$v['ob_end_date']);
            $ob_order_totals += $export_data[$k+1][] = $v['ob_order_totals'];
            $ob_shipping_totals += $export_data[$k+1][] = $v['ob_shipping_totals'];
            $ob_commis_totals += $export_data[$k+1][] = $v['ob_commis_totals'];
            $ob_order_return_totals += $export_data[$k+1][] = $v['ob_order_return_totals'];
            $ob_commis_return_totals += $export_data[$k+1][] = $v['ob_commis_return_totals'];
            $ob_store_cost_totals += $export_data[$k+1][] = $v['ob_store_cost_totals'];
            $ob_result_totals += $export_data[$k+1][] = $v['ob_result_totals'];
            $export_data[$k+1][] = date('Y-m-d',$v['ob_create_date']);
            $export_data[$k+1][] = billState($v['ob_state']);
            $export_data[$k+1][] = $v['ob_store_name'];
            $export_data[$k+1][] = $v['ob_store_id'];
        }
        $count = count($export_data);
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = '合计';
        $export_data[$count][] = $ob_order_totals;
        $export_data[$count][] = $ob_shipping_totals;
        $export_data[$count][] = $ob_commis_totals;
        $export_data[$count][] = $ob_order_return_totals;
        $export_data[$count][] = $ob_commis_return_totals;
        $export_data[$count][] = $ob_store_cost_totals;
        $export_data[$count][] = $ob_result_totals;
        $csv = new Csv();
        $export_data = $csv->charset($export_data,CHARSET,'gbk');
        $csv->filename = 'bill';
        $csv->export($export_data);
    }

    /**
     * 导出结算订单明细CSV
     *
     */
    public function export_order(){
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>$ob_id));
        if (!$bill_info){
            exit();
        }

        $model_order = Model('order');
        $condition = array();
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['store_id'] = $bill_info['ob_store_id'];
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        $end_unixtime = $if_end_date ? $end_unixtime+86400-1 : null;
        if ($if_start_date || $if_end_date) {
            $condition['finnshed_time'] = array('between',"{$start_unixtime},{$end_unixtime}");
        } else {
            $condition['finnshed_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        if (preg_match('/^[\d,]+$/', $_GET['order_id'])) {
            $_GET['order_id'] = explode(',',trim($_GET['order_id'],','));
            $condition['order_id'] = array('in',$_GET['order_id']);
        }

        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('order_sn','buyer_name'))) {
            $condition[$_REQUEST['qtype']] = array('like',"%{$_REQUEST['query']}%");
        }
        if ($_GET['order_sn'] != ''){
            $condition['order_sn'] = array('like',"%{$_GET['order_sn']}%");
        }
        if ($_GET['buyer_name'] != ''){
            if ($_GET['jq_query']) {
                $condition['buyer_name'] = $_GET['buyer_name'];
            } else {
                $condition['buyer_name'] = array('like',"%{$_GET['buyer_name']}%");
            }
        }

        $sort_fields = array('order_amount','shipping_fee','commis_amount','add_time','finnshed_time','buyer_id','store_id');
        if (in_array($_POST['sortorder'],array('asc','desc')) && in_array($_POST['sortname'],$sort_fields)) {
            $order = $_POST['sortname'].' '.$_POST['sortorder'];
        }

        if (!is_numeric($_GET['curpage'])){
            $count = $model_order->getOrderCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE ){
                //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                $this->assign('list',$array);
                $this->assign('murl',$GLOBALS['_PAGE_URL'] . '&c=Bill&a=show_bill&ob_id='.$ob_id);
								
		$this->setDirquna('shop');
                $this->render('export.excel');
                exit();
            }
            $limit = false;
        }else{
            //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";
        }
        $data = $model_order->getOrderList($condition,'','*','order_id desc',$limit,array('order_goods'));

        //订单商品表查询条件
        $order_id_array = array();
        if (is_array($data)) {
            foreach ($data as $order_info) {
                $order_id_array[] = $order_info['order_id'];
            }
        }
        $order_goods_condition = array();
        $order_goods_condition['order_id'] = array('in',$order_id_array);

        $export_data = array();
        $export_data[0] = array('订单编号','订单金额','运费','佣金','下单日期','成交日期','商家','商家编号','买家','买家编号','商品');
        $order_totals = 0;
        $shipping_totals = 0;
        $commis_totals = 0;
        $k = 0;
        foreach ($data as $v) {
            //该订单算佣金
            $field = 'SUM(ROUND(goods_pay_price*commis_rate/100,2)) as commis_amount,order_id';
            $commis_list = $model_order->getOrderGoodsList($order_goods_condition,$field,null,null,'','order_id','order_id');
            $export_data[$k+1][] = $v['order_sn'];
            $order_totals += $export_data[$k+1][] = $v['order_amount'];
            $shipping_totals += $export_data[$k+1][] = $v['shipping_fee'];
            $commis_totals += $export_data[$k+1][] = floatval($commis_list[$v['order_id']]['commis_amount']);
            $export_data[$k+1][] = date('Y-m-d',$v['add_time']);
            $export_data[$k+1][] = date('Y-m-d',$v['finnshed_time']);
            $export_data[$k+1][] = $v['store_name'];
            $export_data[$k+1][] = $v['store_id'];
            $export_data[$k+1][] = $v['buyer_name'];
            $export_data[$k+1][] = $v['buyer_id'];
            $goods_string = '';
            if (is_array($v['extend_order_goods'])) {
                foreach ($v['extend_order_goods'] as $v) {
                    $goods_string .= $v['goods_name'].'|单价:'.$v['goods_price'].'|数量:'.$v['goods_num'].'|实际支付:'.$v['goods_pay_price'].'|佣金比例:'.$v['commis_rate'].'%';
                }
            }
            $export_data[$k+1][] = $goods_string;
            $k++;
        }
        $count = count($export_data);
        $export_data[$count][] = '合计';
        $export_data[$count][] = $order_totals;
        $export_data[$count][] = $shipping_totals;
        $export_data[$count][] = $commis_totals;
        $csv = new Csv();
        $export_data = $csv->charset($export_data,CHARSET,'gbk');
        $csv->filename = $ob_id.'-bill';
        $csv->export($export_data);
    }

    /**
     * 导出未退定金的预定订单明细CSV
     *
     */
    public function export_book(){
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>$ob_id));
        if (!$bill_info){
            exit();
        }

        $condition = array();
        //被取消的预定订单列表
        $model_order = Model('order');
        if ($_POST['query'] != '' && in_array($_POST['qtype'],array('order_sn'))) {
            $order_info = $model_order->getOrderInfo(array('order_sn'=>$_POST['query']));
            if ($order_info) {
                $condition['book_order_id'] = $order_info['order_id'];
            } else {
                $condition['book_order_id'] = 0;
            }
        }
        if (preg_match('/^[\d,]+$/', $_GET['order_id'])) {
            $_GET['order_id'] = explode(',',trim($_GET['order_id'],','));
            $condition['book_order_id'] = array('in',$_GET['order_id']);
        }
        $model_order_book = Model('order_book');
        
        $condition['book_store_id'] = $bill_info['ob_store_id'];
        $condition['book_cancel_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        unset($order_condition['finnshed_time']);
    
        if (!is_numeric($_GET['curpage'])){
            $count = $model_order_book->getOrderBookCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE ){
                //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                $this->assign('list',$array);
                $this->assign('murl',$GLOBALS['_PAGE_URL'] . '&c=Bill&a=show_bill&ob_id='.$ob_id);
								
		$this->setDirquna('shop');
                $this->render('export.excel');
                exit();
            }
            $limit = false;
        }else{
            //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";
        }

        $order_book_list = $model_order_book->getOrderBookList($condition,'','book_id desc','*',$limit);

        //然后取订单信息
        $tmp_book = array();
        $order_id_array = array();
        if (is_array($order_book_list)) {
            foreach ($order_book_list as $order_book_info) {
                $order_id_array[] = $order_book_info['book_order_id'];
                $tmp_book[$order_book_info['book_order_id']]['book_cancel_time'] = $order_book_info['book_cancel_time'];
                $tmp_book[$order_book_info['book_order_id']]['book_real_pay'] = $order_book_info['book_real_pay'];
            }
        }
        $data = $model_order->getOrderList(array('order_id'=>array('in',$order_id_array)),'','*','order_id desc');

        $export_data = array();
        $export_data[0] = array('订单编号','订单金额','运费','未退定金','下单日期','取消日期','商家','商家编号','买家','买家编号');
        $deposit_amount = 0;
        $k = 0;
        foreach ($data as $v) {
            //该订单算佣金
            $export_data[$k+1][] = $v['order_sn'];
            $export_data[$k+1][] = $v['order_amount'];
            $export_data[$k+1][] = $v['shipping_fee'];
            $deposit_amount += $export_data[$k+1][] = daPriceFormat($tmp_book[$v['order_id']]['book_real_pay']);
            $export_data[$k+1][] = date('Y-m-d',$v['add_time']);
            $export_data[$k+1][] = date('Y-m-d',$tmp_book[$v['order_id']]['book_cancel_time']);
            $export_data[$k+1][] = $v['store_name'];
            $export_data[$k+1][] = $v['store_id'];
            $export_data[$k+1][] = $v['buyer_name'];
            $export_data[$k+1][] = $v['buyer_id'];
            $k++;
        }
        $count = count($export_data);
        $export_data[$count][] = '合计';
        $export_data[$count][] = '';
        $export_data[$count][] = '';
        $export_data[$count][] = $deposit_amount;
        $csv = new Csv();
        $export_data = $csv->charset($export_data,CHARSET,'gbk');
        //期账单-未退定金预定订单列表
        $csv->filename = $ob_id.'-bill';
        $csv->export($export_data);
    }

    /**
     * 导出结算退单明细CSV
     *
     */
    public function export_refund_order(){
        $ob_id = intval($_GET['ob_id']);
        if ($ob_id <= 0) {
            exit();
        }
        $model_bill = Model('bill');
        $bill_info = $model_bill->getOrderBillInfo(array('ob_id'=>$ob_id));
        if (!$bill_info){
            exit();
        }

        $model_refund = Model('refund_return');
        $condition = array();
        $condition['seller_state'] = 2;
        $condition['store_id'] = $bill_info['ob_store_id'];
        $condition['goods_id'] = array('gt',0);
        if (preg_match('/^[\d,]+$/', $_GET['refund_id'])) {
            $_GET['refund_id'] = explode(',',trim($_GET['refund_id'],','));
            $condition['refund_id'] = array('in',$_GET['refund_id']);
        }
        if ($_GET['query'] != '' && in_array($_GET['qtype'],array('refund_sn','order_sn','buyer_name'))) {
            $condition[$_GET['qtype']] = array('like',"%{$_GET['query']}%");
        }
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']) : null;
        $end_unixtime = $if_end_date ? $end_unixtime+86400-1 : null;
        if ($if_start_date || $if_end_date) {
            $condition['admin_time'] = array('between',"{$start_unixtime},{$end_unixtime}");
        } else {
            $condition['admin_time'] = array('between',"{$bill_info['ob_start_date']},{$bill_info['ob_end_date']}");
        }
        $sort_fields = array('refund_amount','commis_amount','refund_type','admin_time','buyer_id','store_id');
        if (in_array($_GET['sortorder'],array('asc','desc')) && in_array($_GET['sortname'],$sort_fields)) {
            $order = $_GET['sortname'].' '.$_GET['sortorder'];
        }
        if (!is_numeric($_GET['curpage'])){
            $count = $model_refund->getRefundReturn($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                $this->assign('list',$array);
                $this->assign('murl',$GLOBALS['_PAGE_URL'] . '&c=Bill&a=show_bill&query_type=refund&ob_id='.$ob_id);
								
		$this->setDirquna('shop');
                $this->render('export.excel');
                exit();
            }
            $limit = false;
        }else{
            //下载
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = "{$limit1},{$limit2}";
        }
        $data = $model_refund->getRefundReturnList($condition,'','refund_return.*,ROUND(refund_amount*commis_rate/100,2) as commis_amount',$limit,$order);
        if (is_array($data) && count($data) == 1 && $data[0]['refund_id'] == '') {
            $refund_list = array();
        }
        $export_data = array();
        $export_data[0] = array('退单编号','订单编号','退单金额','退单佣金','类型','退款日期','商家','商家编号','买家','买家编号');
        $refund_amount = 0;
        $commis_totals = 0;
        $k = 0;
        foreach ($data as $v) {
            $export_data[$k+1][] = $v['refund_sn'];
            $export_data[$k+1][] = $v['order_sn'];
            $refund_amount += $export_data[$k+1][] = $v['refund_amount'];
            $commis_totals += $export_data[$k+1][] = daPriceFormat($v['commis_amount']);
            $export_data[$k+1][] = str_replace(array(1,2),array('退款','退货'),$v['refund_type']);
            $export_data[$k+1][] = date('Y-m-d',$v['admin_time']);
            $export_data[$k+1][] = $v['store_name'];
            $export_data[$k+1][] = $v['store_id'];
            $export_data[$k+1][] = $v['buyer_name'];
            $export_data[$k+1][] = $v['buyer_id'];
            $k++;
        }
        $count = count($export_data);
        $export_data[$count][] = '';
        $export_data[$count][] = '合计';
        $export_data[$count][] = $refund_amount;
        $export_data[$count][] = $commis_totals;
        $csv = new Csv();
        $export_data = $csv->charset($export_data,CHARSET,'gbk');
        $csv->filename = $ob_id.'-refund';
        $csv->export($export_data);
    }

    public function get_statis_xml(){
        $condition = array();
        if (preg_match('/^\d{4}$/',$_POST['query'])) {
            $condition['os_year'] = $_POST['query'];
        }
        $sort_fields = array('os_month','os_start_date','os_end_date','os_order_totals','os_shipping_totals','os_commis_totals','os_order_return_totals','os_commis_return_totals','os_store_cost_totals','os_result_totals');
        if (in_array($_POST['sortorder'],array('asc','desc')) && in_array($_POST['sortname'],$sort_fields)) {
            $order = $_POST['sortname'].' '.$_POST['sortorder'];
        }
        $model_bill = Model('bill');
        $bill_list = $model_bill->getOrderStatisList($condition,'*',$_POST['rp'],$order);
        $data = array();
        $data['now_page'] = $model_bill->shownowpage();
        $data['total_num'] = $model_bill->gettotalnum();
        foreach ($bill_list as $bill_info) {
            $list = array();
            $list['operation'] = "<a class=\"btn green\" href=\"{$GLOBALS['_PAGE_URL']}&c=Bill&a=show_statis&os_month={$bill_info['os_month']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
            $list['os_month'] = substr($bill_info['os_month'],0,4).'-'.substr($bill_info['os_month'],4);
            $list['os_start_date'] = date('Y-m-d',$bill_info['os_start_date']);
            $list['os_end_date'] = date('Y-m-d',$bill_info['os_end_date']);
            $list['os_order_totals'] = daPriceFormat($bill_info['os_order_totals']);
            $list['os_shipping_totals'] = daPriceFormat($bill_info['os_shipping_totals']);
            $list['os_commis_totals'] = daPriceFormat($bill_info['os_commis_totals']);
            $list['os_order_return_totals'] = daPriceFormat($bill_info['os_order_return_totals']);
            $list['os_commis_return_totals'] = daPriceFormat($bill_info['os_commis_return_totals']);
            $list['os_store_cost_totals'] = daPriceFormat($bill_info['os_store_cost_totals']);
            $list['os_result_totals'] = daPriceFormat($bill_info['os_result_totals']);
            $data['list'][$bill_info['os_month']] = $list;
        }
        exit($this->flexigridXML($data));
    }

    public function get_bill_xml(){
        $model_bill = Model('bill');
        $condition = array();
        list($condition,$order) = $this->_get_bill_condition($condition);
        $bill_list = $model_bill->getOrderBillList($condition,'*',$_POST['rp'],$order);
        $data = array();
        $data['now_page'] = $model_bill->shownowpage();
        $data['total_num'] = $model_bill->gettotalnum();
        foreach ($bill_list as $bill_info) {
            $list = array();
            if (in_array($bill_info['ob_state'],array(2,3))) {
                $list['operation'] = "<a class=\"btn orange\" href=\"{$GLOBALS['_PAGE_URL']}&c=Bill&a=show_bill&ob_id={$bill_info['ob_id']}\"><i class=\"fa fa-gavel\"></i>处理</a>";
            } else {
                $list['operation'] = "<a class=\"btn green\" href=\"{$GLOBALS['_PAGE_URL']}&c=Bill&a=show_bill&ob_id={$bill_info['ob_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
            }

            $list['ob_id'] = $bill_info['ob_id'];
            $list['ob_order_totals'] = daPriceFormat($bill_info['ob_order_totals']);
            $list['ob_shipping_totals'] = daPriceFormat($bill_info['ob_shipping_totals']);
            $list['ob_commis_totals'] = daPriceFormat($bill_info['ob_commis_totals']);
            $list['ob_order_return_totals'] = daPriceFormat($bill_info['ob_order_return_totals']);
            $list['ob_commis_return_totals'] = daPriceFormat($bill_info['ob_commis_return_totals']);
            $list['ob_store_cost_totals'] = daPriceFormat($bill_info['ob_store_cost_totals']);
            $list['ob_result_totals'] = daPriceFormat($bill_info['ob_result_totals']);
            $list['ob_create_date'] = date('Y-m-d',$bill_info['ob_create_date']);
            $list['ob_state'] = billState($bill_info['ob_state']);
            $list['ob_store_name'] = $bill_info['ob_store_name'];
            $list['ob_start_date'] = date('Y-m-d',$bill_info['ob_start_date']);
            $list['ob_end_date'] = date('Y-m-d',$bill_info['ob_end_date']);
            $list['ob_store_id'] = $bill_info['ob_store_id'];
            $data['list'][$bill_info['ob_id']] = $list;
        }
        exit($this->flexigridXML($data));
    }

    /**
     * 合并相同代码
     */
    private function _get_bill_condition($condition) {
        if ($_GET['query_year'] && $_GET['query_month']) {
            $_GET['os_month'] = intval($_GET['query_year'].$_GET['query_month']);
        } elseif ($_GET['query_year']) {
            $condition['os_month'] = array('between',$_GET['query_year'].'01,'.$_GET['query_year'].'12');
        }
        if (!empty($_GET['os_month'])) {
            $condition['os_month'] = intval($_GET['os_month']);
        }
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('ob_no','ob_id','ob_store_name'))) {
            $condition[$_REQUEST['qtype']] = $_REQUEST['query'];
        }
        if (is_numeric($_GET["ob_state"])) {
            $condition['ob_state'] = intval($_GET["ob_state"]);
        }
        if (is_numeric($_GET["ob_no"])) {
            $condition['ob_no'] = intval($_GET["ob_no"]);
        }
        if (is_numeric($_GET["ob_id"])) {
            $condition['ob_id'] = intval($_GET["ob_id"]);
        }
        if ($_GET['ob_store_name'] != ''){
            if ($_GET['jq_query']) {
                $condition['ob_store_name'] = $_GET['ob_store_name'];
            } else {
                $condition['ob_store_name'] = array('like',"%{$_GET['ob_store_name']}%");
            }
        }
        $sort_fields = array('ob_id','ob_start_date','ob_end_date','ob_order_totals','ob_shipping_totals','ob_commis_totals','ob_order_return_totals','ob_commis_return_totals','ob_store_cost_totals','ob_result_totals','ob_create_date','ob_state','ob_store_id');
        if (in_array($_REQUEST['sortorder'],array('asc','desc')) && in_array($_REQUEST['sortname'],$sort_fields)) {
            $order = $_REQUEST['sortname'].' '.$_REQUEST['sortorder'];
        } else {
            $order = 'ob_id desc';
        }
        return array($condition,$order);
    }
}
