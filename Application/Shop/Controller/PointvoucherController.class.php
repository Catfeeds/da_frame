<?php
/**
 * 代金券
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

class PointvoucherController extends BasePointShopController {
    public function __construct() {
        parent::__construct();
        //读取语言包
        Language::read('home_voucher');
        //判断系统是否开启代金券功能
        if (C('voucher_allow') != 1){
            showDialog(L('voucher_pointunavailable'),"{$GLOBALS['_PAGE_URL']}",'error');
        }
    }
    public function index(){
        $this->pointvoucher();
    }
    /**
     * 代金券列表
     */
    public function pointvoucher(){
        //查询会员及其附属信息
        parent::pointshopMInfo();

        $model_voucher = Model('voucher');

        //代金券模板状态
        $templatestate_arr = $model_voucher->getTemplateState();

        $model_member = Model('member');
        //查询会员信息
        $member_info = $model_member->getMemberInfoByID($_SESSION['member_id']);
                
        //查询代金券列表
        $where = array();
        
        //修改为所有的代金券都可以展示，而不仅是只展示积分兑换的代金券
        //$gettype_arr = $model_voucher->getVoucherGettypeArray();
        //$where['voucher_t_gettype'] = $gettype_arr['points']['sign'];
        
        $where['voucher_t_state'] = $templatestate_arr['usable'][0];
        $where['voucher_t_end_date'] = array('gt',time());
        if (intval($_GET['sc_id']) > 0){
            $where['voucher_t_sc_id'] = intval($_GET['sc_id']);
        }
        if (intval($_GET['price']) > 0){
            $where['voucher_t_price'] = intval($_GET['price']);
        }
        $store_id = intval($_GET['store_id']);
        if ($store_id > 0) {
            $where['voucher_t_store_id'] = $store_id;
        }
        //查询仅我能兑换和所需积分
        $points_filter = array();
        if (intval($_GET['isable']) == 1){
            $points_filter['isable'] = $member_info['member_points'];
        }
        if (intval($_GET['points_min']) > 0){
            $points_filter['min'] = intval($_GET['points_min']);
        }
        if (intval($_GET['points_max']) > 0){
            $points_filter['max'] = intval($_GET['points_max']);
        }
                
        if (count($points_filter) > 0){
            asort($points_filter);
            if (count($points_filter) > 1){
                $points_filter = array_values($points_filter);
                $where['voucher_t_points'] = array('between',array($points_filter[0],$points_filter[1]));
            } else {
                if ($points_filter['min']){
                    $where['voucher_t_points'] = array('egt',$points_filter['min']);
                } elseif ($points_filter['max']) {
                    $where['voucher_t_points'] = array('elt',$points_filter['max']);
                } elseif (isset($points_filter['isable'])) {
                    $where['voucher_t_points'] = array('elt',$points_filter['isable']);
                }
            }
        }
        //仅我能兑换的会员级别
        if (intval($_GET['isable']) == 1){
            $member_currgrade = $model_member->getOneMemberGrade($member_info['member_exppoints']);
            $member_info['member_grade_level'] = $member_currgrade?$member_currgrade['level']:0;
            $where['voucher_t_mgradelimit'] = array('elt',$member_info['member_grade_level']);
        }
        
        //排序
        switch ($_GET['orderby']){
            case 'exchangenumdesc':
                $orderby = 'voucher_t_giveout desc,';
                break;
            case 'exchangenumasc':
                $orderby = 'voucher_t_giveout asc,';
                break;
            case 'pointsdesc':
                $orderby = 'voucher_t_points desc,';
                break;
            case 'pointsasc':
                $orderby = 'voucher_t_points asc,';
                break;
        }
        $orderby .= 'voucher_t_id desc';
        $voucherlist = $model_voucher->getVoucherTemplateList($where, '*', 0, 18, $orderby);
        $this->assign('voucherlist',$voucherlist);
        $this->assign('show_page', $model_voucher->showpage(5));
        
        if ($store_id <= 0) {
            //查询代金券面额
            $pricelist = $model_voucher->getVoucherPriceList();
            $this->assign('pricelist',$pricelist);
    
            //查询店铺分类
            $store_class = rkcache('store_class', true);
            $this->assign('store_class', $store_class);
        }
        //分类导航
        $nav_link = array(
                0=>array('title'=>Language::get('homepage'),'link'=>SHOP_SITE_URL),
                1=>array('title'=>'积分中心','link'=>urlShop('pointshop','index')),
                2=>array('title'=>'代金券列表')
        );
        $this->assign('nav_link_list', $nav_link);
        $this->render('pointvoucher');
    }
    /**
     * 兑换代金券
     */
    public function voucherexchange(){
        $vid = intval($_GET['vid']);
        if($vid <= 0){
            $vid = intval($_POST['vid']);
        }
        if($_SESSION['is_login'] != '1'){
            $js = "login_dialog();";
            showDialog('','','js',$js);
        }elseif ($_GET['dialog']){
            $js = "CUR_DIALOG = ajax_form('vexchange', '"  .
               L('home_voucher_exchangtitle') . "', '" . 
            $GLOBALS['_PAGE_URL'] .
            "&c=Pointvoucher&a=voucherexchange&vid={$vid}', 550);";
            showDialog('','','js',$js);
            die;
        }
        $result = true;
        $message = "";
        if ($vid <= 0){
            $result = false;
            L('wrong_argument');
        }
        if ($result){
            //查询可兑换代金券模板信息
            $template_info = Model('voucher')->getCanChangeTemplateInfo($vid,intval($_SESSION['member_id']),intval($_SESSION['store_id']));
            if ($template_info['state'] == false){
                $result = false;
                $message = $template_info['msg'];
            }else {
                //查询会员信息
                $member_info = Model('member')->getMemberInfoByID($_SESSION['member_id'],'member_points');
                $this->assign('member_info',$member_info);
                $this->assign('template_info',$template_info['info']);
            }
        }
        $this->assign('message',$message);
        $this->assign('result',$result);
        $this->render('pointvoucher.exchange','null_layout');
    }
    /**
     * 兑换代金券保存信息
     *
     */
    public function voucherexchange_save(){
        if($_SESSION['is_login'] != '1'){
            $js = "login_dialog();";
            showDialog('','','js',$js);
        }
        $vid = intval($_POST['vid']);
        $js = "DialogManager.close('vexchange');";
        if ($vid <= 0){
            showDialog(L('wrong_argument'),'','error',$js);
        }
        $model_voucher = Model('voucher');
        //验证是否可以兑换代金券
        $data = $model_voucher->getCanChangeTemplateInfo($vid,intval($_SESSION['member_id']),intval($_SESSION['store_id']));
        if ($data['state'] == false){
            showDialog($data['msg'],'','error',$js);
        }
        //添加代金券信息
        $data = $model_voucher->exchangeVoucher($data['info'],$_SESSION['member_id'],$_SESSION['member_name']);
        if ($data['state'] == true){
            showDialog($data['msg'],'','succ',$js);
        } else {
            showDialog($data['msg'],'','error',$js);
        }
    }
}
