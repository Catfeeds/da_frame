<?php
/**
 * 会员中心——卖家评价
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Page;

class StoreEvaluateController extends BaseSellerController{
    public function __construct(){
        parent::__construct() ;
        Language::read('member_layout,member_evaluate');
        $this->assign('pj_act','store_evaluate');
    }

    /**
     * 评价列表
     */
    public function lists(){
        $model_evaluate_goods = Model('evaluate_goods');

        $condition = array();
        if(!empty($_GET['goods_name'])) {
            $condition['geval_goodsname'] = array('like', '%'.$_GET['goods_name'].'%');
        }
        if(!empty($_GET['member_name'])) {
            $condition['geval_frommembername'] = array('like', '%'.$_GET['member_name'].'%');
        }
        $condition['geval_storeid'] = $_SESSION['store_id'];
        $goodsevallist = $model_evaluate_goods->getEvaluateGoodsList($condition, 10, 'geval_id desc');

        $this->assign('goodsevallist',$goodsevallist);
        $this->assign('show_page',$model_evaluate_goods->showpage());
        $this->render('evaluation.index');
    }
    /**
     * 解释来自买家的评价
     */
    public function explain_save(){
        $geval_id = intval($_POST['geval_id']);
        $geval_explain = trim($_POST['geval_explain']);
        //验证表单
        if (!$geval_explain){
            $data['result'] = false;
            $data['message'] = '解释内容不能为空';
            echo json_encode($data);die;
        }
        $data = array();
        $data['result'] = true;

        $model_evaluate_goods = Model('evaluate_goods');

        $evaluate_info = $model_evaluate_goods->getEvaluateGoodsInfoByID($geval_id, $_SESSION['store_id']);
        if(empty($evaluate_info)) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }

        $update = array('geval_explain' => $geval_explain);
        $condition = array('geval_id' => $geval_id);
        $result = $model_evaluate_goods->editEvaluateGoods($update, $condition);

        if($result) {
            $data['message'] = '解释成功';
        } else {
            $data['result'] = false;
            $data['message'] = '解释保存失败';
        }
        echo json_encode($data);die;
    }
    /**
     * 解释来自买家的评价
     */
    public function explain_again_save(){
        $geval_id = intval($_POST['geval_id']);
        $geval_explain_again = $_POST['geval_explain_again'];
        //验证表单
        if (!$geval_explain_again){
            $data['result'] = false;
            $data['message'] = '解释内容不能为空';
            echo json_encode($data);die;
        }
        $data = array();
        $data['result'] = true;
    
        $model_evaluate_goods = Model('evaluate_goods');
    
        $evaluate_info = $model_evaluate_goods->getEvaluateGoodsInfoByID($geval_id, $_SESSION['store_id']);
        if(empty($evaluate_info)) {
            $data['result'] = false;
            $data['message'] = L('param_error');
            echo json_encode($data);die;
        }
    
        $update = array('geval_explain_again' => $geval_explain_again);
        $condition = array('geval_id' => $geval_id);
        $result = $model_evaluate_goods->editEvaluateGoods($update, $condition);
    
        if($result) {
            $data['message'] = '解释追评成功';
        } else {
            $data['result'] = false;
            $data['message'] = '解释追评失败';
        }
        echo json_encode($data);die;
    }
}
