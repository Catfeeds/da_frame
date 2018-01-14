<?php
/**
 * 会员中心——买家评价
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;

class MemberEvaluateController extends BaseMemberController{
    public function __construct(){
        parent::__construct() ;
        Language::read('member_layout,member_evaluate');
        $this->assign('pj_act','member_evaluate');
    }

    /**
     * 订单添加评价
     */
    public function add(){
        $order_id = intval($_GET['order_id']);
        $return = Logic('member_evaluate')->validation($order_id, $_SESSION['member_id']);
        if (!$return['state']) {
            showMessage($return['msg'],$GLOBALS['_PAGE_URL'] . '&c=MemberOrder','html','error');
        }
        extract($return['data']);
        //判断是否提交
        if (chksubmit()){
            $return = Logic('member_evaluate')->save($_POST, $order_info, $store_info, $order_goods, $this->member_info['member_id'], $this->member_info['member_name']);
            if (!$return['state']) {
                showDialog($return['msg'],'reload','error');
            } else {
                showDialog(Language::get('member_evaluation_evaluat_success'),$GLOBALS['_PAGE_URL'] . '&c=MemberOrder', 'succ');
            }
        } else {
            //处理积分、经验值计算说明文字
            $ruleexplain = '';
            $exppoints_rule = C("exppoints_rule")?unserialize(C("exppoints_rule")):array();
            $exppoints_rule['exp_comments'] = intval($exppoints_rule['exp_comments']);
            $points_comments = intval(C('points_comments'));
            if ($exppoints_rule['exp_comments'] > 0 || $points_comments > 0){
                $ruleexplain .= '评价完成将获得';
                if ($exppoints_rule['exp_comments'] > 0){
                    $ruleexplain .= (' “'.$exppoints_rule['exp_comments'].'经验值”');
                }
                if ($points_comments > 0){
                    $ruleexplain .= (' “'.$points_comments.'积分”');
                }
                $ruleexplain .= '。';
            }
            $this->assign('ruleexplain', $ruleexplain);
    
            $model_sns_alumb = Model('sns_album');
            $ac_id = $model_sns_alumb->getSnsAlbumClassDefault($_SESSION['member_id']);
            $this->assign('ac_id', $ac_id);
            
            //不显示左菜单
            $this->assign('left_show','order_view');
            $this->assign('order_info',$order_info);
            $this->assign('order_goods',$order_goods);
            $this->assign('store_info',$store_info);
            $this->render('evaluation.add');
        }
    }

    /**
     * 订单添加评价
     */
    public function add_again(){
        $order_id = intval($_GET['order_id']);
        $return = Logic('member_evaluate')->validationAgain($order_id, $_SESSION['member_id']);
        if (!$return['state']) {
            showMessage($return['msg'],$GLOBALS['_PAGE_URL'] . '&c=MemberOrder','html','error');
        }
        extract($return['data']);
    
        //判断是否提交
        if (chksubmit()){
            $return = Logic('member_evaluate')->saveAgain($_POST, $order_info, $evaluate_goods);
            if (!$return['state']) {
                showDialog($return['msg'],'reload','error');
            } else {
                showDialog(Language::get('member_evaluation_evaluat_success'),$GLOBALS['_PAGE_URL'] . '&c=MemberOrder', 'succ');
            }
        } else {
            $model_sns_alumb = Model('sns_album');
            $ac_id = $model_sns_alumb->getSnsAlbumClassDefault($_SESSION['member_id']);
            $this->assign('ac_id', $ac_id);
        
            //不显示左菜单
            $this->assign('left_show','order_view');
            $this->assign('order_info',$order_info);
            $this->assign('evaluate_goods',$evaluate_goods);
            $this->assign('store_info',$store_info);
            $this->render('evaluation.add_again');
        }
    }

    /**
     * 虚拟商品评价
     */
    public function add_vr(){
        $order_id = intval($_GET['order_id']);
        $return = Logic('member_evaluate')->validationVr($order_id, $_SESSION['member_id']);
        if (!$return['state']) {
            showMessage($return['msg'],$GLOBALS['_PAGE_URL'] . '&c=MemberVrOrder','html','error');
        }
        extract($return['data']);
        //判断是否为页面
        if (!$_POST){
            $order_goods[] = $order_info;
            //处理积分、经验值计算说明文字
            $ruleexplain = '';
            $exppoints_rule = C("exppoints_rule")?unserialize(C("exppoints_rule")):array();
            $exppoints_rule['exp_comments'] = intval($exppoints_rule['exp_comments']);
            $points_comments = intval(C('points_comments'));
            if ($exppoints_rule['exp_comments'] > 0 || $points_comments > 0){
                $ruleexplain .= '评价完成将获得';
                if ($exppoints_rule['exp_comments'] > 0){
                    $ruleexplain .= (' “'.$exppoints_rule['exp_comments'].'经验值”');
                }
                if ($points_comments > 0){
                    $ruleexplain .= (' “'.$points_comments.'积分”');
                }
                $ruleexplain .= '。';
            }
            $this->assign('ruleexplain', $ruleexplain);

            //不显示左菜单
            $this->assign('left_show','order_view');
            $this->assign('order_info',$order_info);
            $this->assign('order_goods',$order_goods);
            $this->assign('store_info',$store_info);
            $this->render('evaluation.add');
        }else {
            $return = Logic('member_evaluate')->saveVr($_POST, $order_info, $store_info, $_SESSION['member_id'], $_SESSION['member_name']);
            if (!$return['state']) {
                showDialog($return['msg'],'reload','error');
            } else {
                showDialog(Language::get('member_evaluation_evaluat_success'),$GLOBALS['_PAGE_URL'] . '&c=MemberVrOrder', 'succ');
            }
        }
    }

    /**
     * 评价列表
     */
    public function lists(){
        $model_evaluate_goods = Model('evaluate_goods');

        $condition = array();
        $condition['geval_frommemberid'] = $_SESSION['member_id'];
        $goodsevallist = $model_evaluate_goods->getEvaluateGoodsList($condition, 10);
        $this->assign('goodsevallist',$goodsevallist);
        $this->assign('show_page',$model_evaluate_goods->showpage());

        $this->render('evaluation.index');
    }

}
