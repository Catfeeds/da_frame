<?php
/**
 * 领取免费红包
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;

class RedpacketController extends BaseHomeController{
    public function __construct() {
        parent::__construct();
        //判断系统是否开启红包功能
        if (C('redpacket_allow') != 1){
            showDialog('系统未开启红包功能',"{$GLOBALS['_PAGE_URL']}",'error');
        }
        parent::checkLogin();
    }
    /**
     * 免费红包页面
     */
    public function getredpacket() {
        $t_id = intval($_GET['tid']);
        $error_url = getReferer();
        if (!$error_url){
            $error_url = "{$GLOBALS['_PAGE_URL']}";
        }
        if($t_id <= 0){
            showDialog('红包信息错误',$error_url,'error');
        }
        $model_redpacket = Model('redpacket');
        //获取领取方式
        $gettype_array = $model_redpacket->getGettypeArr();
        //获取红包状态
        $templatestate_arr = $model_redpacket->getTemplateState();
        //查询红包模板详情
        $where = array();
        $where['rpacket_t_id'] = $t_id;
        $where['rpacket_t_gettype'] = $gettype_array['free']['sign'];
        $where['rpacket_t_state'] = $templatestate_arr['usable']['sign'];
        //$where['rpacket_t_start_date'] = array('elt',time());
        $where['rpacket_t_end_date'] = array('egt',time());
        $template_info = $model_redpacket->getRptTemplateInfo($where);
        if (empty($template_info)){
            showDialog('红包信息错误',$error_url,'error');
        }
        if ($template_info['rpacket_t_total']<=$template_info['rpacket_t_giveout']){//红包不存在或者已兑换完
            showDialog('红包已兑换完',$error_url,'error');
        }
        $this->assign('template_info',$template_info);
        $this->render('redpacket.getredpacket');
    }
    /**
     * 领取免费红包
     */
    public function getredpacketsave() {
        $t_id = intval($_GET['tid']);
        if($t_id <= 0){
            showDialog('红包信息错误','','error');
        }
        $model_redpacket = Model('redpacket');
        //验证是否可领取红包
        $data = $model_redpacket->getCanChangeTemplateInfo($t_id, intval($_SESSION['member_id']));
        if ($data['state'] == false){
            showDialog($data['msg'], '', 'error');
        }
        try {
            $model_redpacket->beginTransaction();
            //添加红包信息
            $data = $model_redpacket->exchangeRedpacket($data['info'], $_SESSION['member_id'], $_SESSION['member_name']);
            if ($data['state'] == false) {
                throw new Exception($data['msg']);
            }
            $model_redpacket->commit();
            showDialog('红包领取成功', MEMBER_SITE_URL.'&c=MemberRedpacket&a=index', 'succ');
        } catch (Exception $e) {
            $model_redpacket->rollback();
            showDialog($e->getMessage(), '', 'error');
        }
        
    }
}
