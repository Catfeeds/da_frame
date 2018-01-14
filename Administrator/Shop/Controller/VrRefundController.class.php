<?php
/**
 * 虚拟订单退款
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


class VrRefundController extends SystemController {
    const EXPORT_SIZE = 1000;

    public function __construct(){
        parent::__construct();
		    $this->links = array(
            array('url'=> $GLOBALS['_PAGE_URL'] . '&c=VrRefund&a=refund_manage','text'=>'待处理'),
            array('url'=> $GLOBALS['_PAGE_URL'] . '&c=VrRefund&a=refund_all','text'=>'所有记录'),
    );
	
        Language::read('refund', 'Home');
        $model_vr_refund = Model('vr_refund');
        $model_vr_refund->getRefundStateArray();
    }

    public function index() {
        $this->refund_manage();
    }

    /**
     * 待处理列表
     */
    public function refund_manage() {
        $_GET['waiting'] = 1;
        $this->refund_all();
    }

    /**
     * 所有记录
     */
    public function refund_all() {
        $this->assign('top_link',$this->sublink($this->links,$_GET['waiting'] ? 'refund_manage' : 'refund_all'));
		$this->setDirquna('shop');
        $this->render('vr_refund_all.list');
    }

    /**
     * 所有记录
     */
    public function get_all_xml() {
        $model_vr_refund = Model('vr_refund');
        $condition = array();

        list($condition,$order) = $this->_get_condition($condition);

        $refund_list = $model_vr_refund->getRefundList($condition,$_POST['rp'],'','*',$order);
        $data = array();
        $data['now_page'] = $model_vr_refund->shownowpage();
        $data['total_num'] = $model_vr_refund->gettotalnum();
        $admin_array = $model_vr_refund->getRefundStateArray('admin');
        foreach ($refund_list as $k => $refund_info) {
            $list = array();$operation_detail = '';
            if ($_GET['waiting'] == 1 && $refund_info['admin_state'] == 1) {
                $list['operation'] = "<a class=\"btn orange\" href=\"{$GLOBALS['_PAGE_URL']}&c=VrRefund&a=edit&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-gavel\"></i>处理</a>";
            } else {
                $list['operation'] = "<a class=\"btn green\" href=\"{$GLOBALS['_PAGE_URL']}&c=VrRefund&a=view&refund_id={$refund_info['refund_id']}\"><i class=\"fa fa-list-alt\"></i>查看</a>";
            }

            $list['refund_sn'] = $refund_info['refund_sn'];
            $list['refund_amount'] = daPriceFormat($refund_info['refund_amount']);
            $list['buyer_message'] = "<span title='{$refund_info['buyer_message']}'>{$refund_info['buyer_message']}</span>";
            $list['add_times'] = date('Y-m-d H:i:s',$refund_info['add_time']);
            $list['goods_name'] = "<a class='open' title='{$refund_info['goods_name']}' href='". urlShop('goods', 'index', array('goods_id' => $refund_info['goods_id'])) ."' target='blank'>{$refund_info['goods_name']}</a>";
            $list['admin_state'] = $admin_array[$refund_info['admin_state']];
            $list['admin_message'] = $refund_info['admin_message'];
            $list['admin_message'] = "<span title='{$refund_info['admin_message']}'>{$refund_info['admin_message']}</span>";

            $list['goods_id'] = !empty($refund_info['goods_id']) ? $refund_info['goods_id'] : '';
            $list['order_sn'] = $refund_info['order_sn'];
            $list['buyer_name'] = $refund_info['buyer_name'];
            $list['buyer_id'] = $refund_info['buyer_id'];
            $list['store_name'] = $refund_info['store_name'];
            $list['store_id'] = $refund_info['store_id'];
            $data['list'][$refund_info['refund_id']] = $list;
        }
        exit($this->flexigridXML($data));
    }

    /**
     * 审核页
     *
     */
    public function edit() {
        $model_vr_refund = Model('vr_refund');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund = $model_vr_refund->getRefundInfo($condition);
        $order_id = $refund['order_id'];
        $model_vr_order = Model('vr_order');
        $order = $model_vr_order->getOrderInfo(array('order_id'=> $order_id));
        $order['pay_amount'] = $order['order_amount']-$order['rcb_amount']-$order['pd_amount'];//在线支付金额=订单总价格-充值卡支付金额-预存款支付金额
        $this->assign('order',$order);
        $detail_array = $model_vr_refund->getDetailInfo($condition);
        if(empty($detail_array)) {
            $model_vr_refund->addDetail($refund,$order);
            $detail_array = $model_vr_refund->getDetailInfo($condition);
        }
        $this->assign('detail_array',$detail_array);
        if (chksubmit()) {
            if ($refund['admin_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
                showMessage(Language::get('spd_common_save_fail'));
            }
            if ($detail_array['pay_time'] > 0) {
                $refund['pay_amount'] = $detail_array['pay_amount'];//已完成在线退款金额
            }
            $refund['admin_time'] = time();
            $refund['admin_state'] = '2';
            if ($_POST['admin_state'] == '3') {
                $refund['admin_state'] = '3';
            }
            $refund['admin_message'] = $_POST['admin_message'];
            $state = $model_vr_refund->editOrderRefund($refund);
            if ($state) {

                // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $refund['buyer_id'];
                $param['param'] = array(
                    'refund_url' => urlShop('member_vr_refund', 'view', array('refund_id' => $refund['refund_id'])),
                    'refund_sn' => $refund['refund_sn']
                );
                QueueClient::push('sendMemberMsg', $param);

                $this->log('虚拟订单退款审核，退款编号'.$refund['refund_sn']);
                showMessage(Language::get('spd_common_save_succ'),$GLOBALS['_PAGE_URL'] . '&c=VrRefund&a=refund_manage');
            } else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }
        $this->assign('refund',$refund);
        $code_array = explode(',', $refund['code_sn']);
        $this->assign('code_array',$code_array);
		$this->setDirquna('shop');
        $this->render('vr_refund.edit');
    }

    /**
     * 查看页
     *
     */
    public function view() {
        $model_vr_refund = Model('vr_refund');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund = $model_vr_refund->getRefundInfo($condition);
        $this->assign('refund',$refund);
        $code_array = explode(',', $refund['code_sn']);
        $this->assign('code_array',$code_array);
        $detail_array = $model_vr_refund->getDetailInfo($condition);
        $this->assign('detail_array',$detail_array);
		$this->setDirquna('shop');
        $this->render('vr_refund.view');
    }

    /**
     * 封装共有查询代码
     */
    private function _get_condition($condition) {
        if ($_REQUEST['query'] != '' && in_array($_REQUEST['qtype'],array('order_sn','store_name','buyer_name','goods_name','refund_sn'))) {
            $condition[$_REQUEST['qtype']] = array('like',"%{$_REQUEST['query']}%");
        }
        if ($_GET['keyword'] != '' && in_array($_GET['keyword_type'],array('order_sn','store_name','buyer_name','goods_name','refund_sn'))) {
            if ($_GET['jq_query']) {
                $condition[$_GET['keyword_type']] = $_GET['keyword'];
            } else {
                $condition[$_GET['keyword_type']] = array('like',"%{$_GET['keyword']}%");
            }
        }
        if (!in_array($_GET['qtype_time'],array('add_time','admin_time'))) {
            $_GET['qtype_time'] = null;
        }
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_date']): null;
        if ($_GET['qtype_time'] && ($start_unixtime || $end_unixtime)) {
            $condition[$_GET['qtype_time']] = array('time',array($start_unixtime,$end_unixtime));
        }
        if (floatval($_GET['query_start_amount']) > 0 && floatval($_GET['query_end_amount']) > 0) {
            $condition['refund_amount'] = array('between',floatval($_GET['query_start_amount']).','.floatval($_GET['query_end_amount']));
        }
        if ($_GET['waiting'] == 1) {
            $condition['admin_state'] = 1;
        }
        $sort_fields = array('buyer_name','store_name','goods_id','refund_id','seller_time','refund_amount','buyer_id','store_id');
        if ($_REQUEST['sortorder'] != '' && in_array($_REQUEST['sortname'],$sort_fields)) {
            $order = $_REQUEST['sortname'].' '.$_REQUEST['sortorder'];
        }
        return array($condition,$order);
    }

    /**
     * csv导出
     */
    public function export_step1() {
        $model_refund = Model('vr_refund');
        $condition = array();
        if (preg_match('/^[\d,]+$/', $_GET['refund_id'])) {
            $_GET['refund_id'] = explode(',',trim($_GET['refund_id'],','));
            $condition['refund_id'] = array('in',$_GET['refund_id']);
        }
        list($condition,$order) = $this->_get_condition($condition);

        if (!is_numeric($_GET['curpage'])){
            $count = $model_refund->getRefundCount($condition);
            if ($count > self::EXPORT_SIZE ){   //显示下载链接
                $array = array();
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                $this->assign('list',$array);
                $this->assign('murl','javascript:history.back(-1)');
				$this->setDirquna('shop');
                $this->render('export.excel');
                exit();
            }
            $limit = false;
        } else {
            $limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $limit = $limit1 .','. $limit2;
        }
        $refund_list = $model_refund->getRefundList($condition,'',$limit,'*',$order);
        $this->createCsv($refund_list);
    }

    /**
     * 生成csv文件
     */
    private function createCsv($refund_list) {
        $model_refund = Model('vr_refund');
        $data = array();
        $admin_array = $model_refund->getRefundStateArray('admin');
        foreach ($refund_list as $k => $refund_info) {
            $list = array();
            $list['refund_sn'] = $refund_info['refund_sn'];
            $list['refund_amount'] = daPriceFormat($refund_info['refund_amount']);
            $list['buyer_message'] = $refund_info['buyer_message'];
            $list['add_times'] = date('Y-m-d H:i:s',$refund_info['add_time']);
            $list['goods_name'] = $refund_info['goods_name'];
            $list['admin_state'] = $admin_array[$refund_info['admin_state']];
            $list['admin_message'] = $refund_info['admin_message'];
            $list['goods_id'] = !empty($refund_info['goods_id']) ? $refund_info['goods_id'] : '';
            $list['order_sn'] = $refund_info['order_sn'];
            $list['buyer_name'] = $refund_info['buyer_name'];
            $list['buyer_id'] = $refund_info['buyer_id'];
            $list['store_name'] = $refund_info['store_name'];
            $list['store_id'] = $refund_info['store_id'];
            $data[] = $list;
        }

        $header = array(
                'refund_sn' => '退单编号',
                'refund_amount' => '退款金额',
                'buyer_message' => '申请原因',
                'add_times' => '申请时间',
                'goods_name' => '涉及商品',
                'admin_state' => '平台处理',
                'admin_message' => '平台处理备注',
                'goods_id' => '商品ID',
                'order_sn' => '订单编号',
                'buyer_name' => '买家',
                'buyer_id' => '买家ID',
                'store_name' => '商家名称',
                'store_id'  => '商家ID'
        );
		array_unshift($data, $header);
		$csv = new Csv();
	    $export_data = $csv->charset($data,CHARSET,'gbk');
	    $csv->filename = $csv->charset('vr_refund',CHARSET).$_GET['curpage'] . '-'.date('Y-m-d');
	    $csv->export($data);  

         }
}
