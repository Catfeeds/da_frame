<?php
/**
 * 买家退货
 *
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
use Common\Lib\Cache;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;


class MemberReturnController extends BaseMemberController {
    public function __construct(){
        parent::__construct();
        Language::read('member_member_index');
        $model_refund = Model('refund_return');
        $model_refund->getRefundStateArray();
        $this->assign('c', 'member_refund');
    }
    /**
     * 退货记录列表页
     *
     */
    public function index(){
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['buyer_id'] = $_SESSION['member_id'];

        $keyword_type = array('order_sn','refund_sn','goods_name');
        if (trim($_GET['key']) != '' && in_array($_GET['type'],$keyword_type)){
            $type = $_GET['type'];
            $condition[$type] = array('like','%'.$_GET['key'].'%');
        }
        if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != ''){
            $add_time_from = strtotime(trim($_GET['add_time_from']));
            $add_time_to = strtotime(trim($_GET['add_time_to']));
            if ($add_time_from !== false || $add_time_to !== false){
                $condition['add_time'] = array('time',array($add_time_from,$add_time_to));
            }
        }
        $return_list = $model_refund->getReturnList($condition,10);
        $this->assign('return_list',$return_list);
        $this->assign('show_page',$model_refund->showpage());
        $store_list = $model_refund->getRefundStoreList($return_list);
        $this->assign('store_list', $store_list);
        self::profile_menu('member_order','buyer_return');
        $this->render('member_return');
    }
    /**
     * 发货
     *
     */
    public function ship(){
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['buyer_id'] = $_SESSION['member_id'];
        $condition['refund_id'] = intval($_GET['return_id']);
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        $this->assign('return',$return);
        $express_list  = rkcache('express',true);
        $this->assign('express_list',$express_list);
        if ($return['seller_state'] != '2' || $return['goods_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
            showDialog(Language::get('wrong_argument'),'reload','error');
        }
        if (chksubmit()) {
            $refund_array = array();
            $refund_array['ship_time'] = time();
            $refund_array['delay_time'] = time();
            $refund_array['express_id'] = $_POST['express_id'];
            $refund_array['invoice_no'] = $_POST['invoice_no'];
            $refund_array['goods_state'] = '2';
            $state = $model_refund->editRefundReturn($condition, $refund_array);
            if ($state) {
                showDialog(Language::get('spd_common_save_succ'),$GLOBALS['_PAGE_URL'] . '&c=MemberReturn&a=index','succ');
            } else {
                showDialog(Language::get('spd_common_save_fail'),'reload','error');
            }
        }
        $info['buyer'] = array();
        if(!empty($return['pic_info'])) {
            $info = unserialize($return['pic_info']);
        }
        $this->assign('pic_list',$info['buyer']);
        $condition = array();
        $condition['order_id'] = $return['order_id'];
        $model_refund->getRightOrderList($condition, $return['order_goods_id']);
        $model_trade = Model('trade');
        $return_delay = $model_trade->getMaxDay('return_delay');//发货默认5天后才能选择没收到
        $this->assign('return_delay',$return_delay);
        $this->assign('return_confirm',$model_trade->getMaxDay('return_confirm'));//卖家不处理收货时按同意并弃货处理
        $this->assign('ship',1);
        $this->render('member_return_view');
    }
    /**
     * 延迟时间
     *
     */
    public function delay(){
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['buyer_id'] = $_SESSION['member_id'];
        $condition['refund_id'] = intval($_GET['return_id']);
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        $this->assign('return',$return);
        if (chksubmit()) {
            if ($return['seller_state'] != '2' || $return['goods_state'] != '3') {//检查状态,防止页面刷新不及时造成数据错误
                showDialog(Language::get('wrong_argument'),'reload','error','CUR_DIALOG.close();');
            }
            $refund_array = array();
            $refund_array['delay_time'] = time();
            $refund_array['goods_state'] = '2';
            $state = $model_refund->editRefundReturn($condition, $refund_array);
            if ($state) {
                showDialog(Language::get('spd_common_save_succ'),'reload','succ','CUR_DIALOG.close();');
            } else {
                showDialog(Language::get('spd_common_save_fail'),'reload','error','CUR_DIALOG.close();');
            }
        }
        $model_trade = Model('trade');
        $return_delay = $model_trade->getMaxDay('return_delay');//发货默认5天后才能选择没收到
        $this->assign('return_delay',$return_delay);
        $this->assign('return_confirm',$model_trade->getMaxDay('return_confirm'));//卖家不处理收货时按弃货处理
        $this->render('member_return_delay','null_layout');
    }
    /**
     * 退货记录查看页
     *
     */
    public function view(){
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['buyer_id'] = $_SESSION['member_id'];
        $condition['refund_id'] = intval($_GET['return_id']);
        $return_list = $model_refund->getReturnList($condition);
        $return = $return_list[0];
        $this->assign('return',$return);
        $express_list  = rkcache('express',true);
        if ($return['express_id'] > 0 && !empty($return['invoice_no'])) {
            $this->assign('return_e_name',$express_list[$return['express_id']]['e_name']);
        }
        $info['buyer'] = array();
        if(!empty($return['pic_info'])) {
            $info = unserialize($return['pic_info']);
        }
        $this->assign('pic_list',$info['buyer']);
        $detail_array = $model_refund->getDetailInfo(array('refund_id'=> $return['refund_id']));
        $this->assign('detail_array',$detail_array);
        $condition = array();
        $condition['order_id'] = $return['order_id'];
        $model_refund->getRightOrderList($condition, $return['order_goods_id']);
        $this->render('member_return_view');
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type,$menu_key='') {
        $menu_array = array();
        switch ($menu_type) {
            case 'member_order':
                $menu_array = array(
                array('menu_key'=>'buyer_refund','menu_name'=>Language::get('spd_member_path_buyer_refund'), 'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberRefund'),
                array('menu_key'=>'buyer_return','menu_name'=>Language::get('spd_member_path_buyer_return'), 'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberReturn'),
                array('menu_key'=>'buyer_vr_refund','menu_name'=>'虚拟兑码退款',    'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberVrRefund'));
                break;
        }
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}
