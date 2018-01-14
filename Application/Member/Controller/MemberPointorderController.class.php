<?php
/**
 * 会员中心——积分兑换信息
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Member\Controller;
use Member\Controller\BaseMemberController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;


class MemberPointorderController extends BaseMemberController{
    public function __construct() {
        parent::__construct();
        //读取语言包
        Language::read('member_member_points,member_pointorder');
        //判断系统是否开启积分和积分兑换功能
        if (C('points_isuse') != 1 || C('pointprod_isuse') != 1){
            showDialog(L('member_pointorder_unavailable'),urlShop('member', 'home'),'error');
        }
        $this->assign('c', 'member_points');
    }
    public function index() {
        $this->orderlist();
    }
    /**
     * 兑换信息列表
     */
    public function orderlist() {
        //兑换信息列表
        $where = array();
        $where['point_buyerid'] = $_SESSION['member_id'];

        $model_pointorder = Model('pointorder');
        $order_list = $model_pointorder->getPointOrderList($where, '*', 10, 0, 'point_orderid desc');
        $order_idarr = array();
        $order_listnew = array();
        if (is_array($order_list) && count($order_list)>0){
            foreach ($order_list as $k => $v){
                $order_listnew[$v['point_orderid']] = $v;
                $order_idarr[] = $v['point_orderid'];
            }
        }

        //查询兑换商品
        if (is_array($order_idarr) && count($order_idarr)>0){
            $prod_list = $model_pointorder->getPointOrderGoodsList(array('point_orderid'=>array('in',$order_idarr)));
            if (is_array($prod_list) && count($prod_list)>0){
                foreach ($prod_list as $v){
                    if (isset($order_listnew[$v['point_orderid']])){
                        $order_listnew[$v['point_orderid']]['prodlist'][] = $v;
                    }
                }
            }
        }

        //信息输出
        $this->assign('order_list',$order_listnew);
        $this->assign('page',$model_pointorder->showpage(2));
        self::profile_menu('pointorder','orderlist');
        $this->render('member_pointorder');
    }
    /**
     *  取消兑换
     */
    public function cancel_order(){
        $model_pointorder = Model('pointorder');
        //取消订单
        $data = $model_pointorder->cancelPointOrder($_GET['order_id'],$_SESSION['member_id']);
        if ($data['state']){
            showDialog(L('member_pointorder_cancel_success'),$GLOBALS['_PAGE_URL'] .'&c=MemberPointorder','succ');
        }else {
            showDialog($data['msg'],$GLOBALS['_PAGE_URL'] . '&c=MemberPointorder','error');
        }
    }
    /**
     * 确认收货
     */
    public function receiving_order(){
        $data = Model('pointorder')->receivingPointOrder($_GET['order_id']);
        if ($data['state']){
            showDialog(L('member_pointorder_confirmreceiving_success'),$GLOBALS['_PAGE_URL'] .'&c=MemberPointorder','succ');
        }else {
            showDialog($data['msg'],$GLOBALS['_PAGE_URL'] . '&c=MemberPointorder','error');
        }
    }
    /**
     * 从第三方取快递信息
     *
     */
    public function get_express(){
    
        $content = Model('express')->get_express($_GET['e_code'], $_GET['shipping_code']);
    
        $output = array();
        foreach ($content as $k=>$v) {
            if ($v['time'] == '') continue;
            $output[]= $v['time'].'&nbsp;&nbsp;'.$v['context'];
        }
        if (empty($output)) exit(json_encode(false));
    
        echo json_encode($output);
    }
    /**
     * 兑换信息详细
     */
    public function order_info(){
        $order_id = intval($_GET['order_id']);
        if ($order_id <= 0){
            showDialog(L('member_pointorder_parameter_error'),$GLOBALS['_PAGE_URL'] .'&c=MemberPointorder','error');
        }
        $model_pointorder = Model('pointorder');
        //查询兑换订单信息
        $where = array();
        $where['point_orderid'] = $order_id;
        $where['point_buyerid'] = $_SESSION['member_id'];
        $order_info = $model_pointorder->getPointOrderInfo($where);
        if (!$order_info){
            showDialog(L('member_pointorder_record_error'),$GLOBALS['_PAGE_URL'] . '&c=MemberPointorder','error');
        }
        //获取订单状态
        $pointorderstate_arr = $model_pointorder->getPointOrderStateBySign();
        $this->assign('pointorderstate_arr',$pointorderstate_arr);

        //查询兑换订单收货人地址
        $orderaddress_info = $model_pointorder->getPointOrderAddressInfo(array('point_orderid'=>$order_id));
        $this->assign('orderaddress_info',$orderaddress_info);

        //兑换商品信息
        $prod_list = $model_pointorder->getPointOrderGoodsList(array('point_orderid'=>$order_id));
        $this->assign('prod_list',$prod_list);

        //物流公司信息
        if ($order_info['point_shipping_ecode'] != ''){
            $data = Model('express')->getExpressInfoByECode($order_info['point_shipping_ecode']);
            if ($data['state']){
                $express_info = $data['data']['express_info'];
            }
            $this->assign('express_info',$express_info);
        }

        $this->assign('order_info',$order_info);
        $this->assign('left_show','order_view');
        $this->render('member_pointorder_info');
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
            case 'pointorder':
                $menu_array = array(
                    1=>array('menu_key'=>'points',  'menu_name'=>'积分明细',    'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberPoints'),
                    2=>array('menu_key'=>'orderlist','menu_name'=>Language::get('member_pointorder_list_title'),    'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberPointorder&a=orderlist')
                );
                break;
            case 'pointorderinfo':
                $menu_array = array(
                    1=>array('menu_key'=>'points',  'menu_name'=>'积分明细',    'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberPoints'),
                    2=>array('menu_key'=>'orderlist','menu_name'=>Language::get('spd_member_path_pointorder_list'),  'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberPointorder&a=orderlist'),
                    3=>array('menu_key'=>'orderinfo','menu_name'=>Language::get('spd_member_path_pointorder_info'),  '')
                );
                break;
        }
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}
