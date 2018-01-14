<?php
/**
 * 领取免费代金券
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;

class VoucherController extends BaseHomeController{
    public function __construct() {
        parent::__construct();
        parent::checkLogin();
    }
    /**
     * 免费代金券页面
     */
    public function getvoucher() {
        $t_id = intval($_GET['tid']);
        $error_url = getReferer();
        if (!$error_url){
            $error_url = "{$GLOBALS['_PAGE_URL']}";
        }
        if($t_id <= 0){
            showDialog('代金券信息错误',$error_url,'error');
        }
        $model_voucher = Model('voucher');
        //获取领取方式
        $gettype_array = $model_voucher->getVoucherGettypeArray();
        //获取代金券状态
        $templatestate_arr = $model_voucher->getTemplateState();
        //查询代金券模板详情
        $where = array();
        $where['voucher_t_id'] = $t_id;
        $where['voucher_t_gettype'] = $gettype_array['free']['sign'];
        $where['voucher_t_state'] = $templatestate_arr['usable'][0];
        $where['voucher_t_end_date'] = array('gt',time());
        $template_info = $model_voucher->getVoucherTemplateInfo($where);
        if (empty($template_info)){
            showDialog('代金券信息错误', $error_url, 'error');
        }
        if ($template_info['voucher_t_total']<=$template_info['voucher_t_giveout']){//代金券不存在或者已兑换完
            showDialog('代金券已兑换完', $error_url, 'error');
        }
        $this->assign('template_info',$template_info);
        $this->render('voucher.getvoucher');
    }
    /**
     * 领取免费代金券
     */
    public function getvouchersave() {
        $t_id = intval($_GET['tid']);
        if($t_id <= 0){
            showDialog('代金券信息错误','','error');
        }
        $model_voucher = Model('voucher');
        //验证是否可领取代金券
        $data = $model_voucher->getCanChangeTemplateInfo($t_id, intval($_SESSION['member_id']), intval($_SESSION['store_id']));
        if ($data['state'] == false){
            showDialog($data['msg'], '', 'error');
        }
        try {
            $model_voucher->beginTransaction();
            //添加代金券信息
            $data = $model_voucher->exchangeVoucher($data['info'], $_SESSION['member_id'], $_SESSION['member_name']);
            if ($data['state'] == false) {
                throw new Exception($data['msg']);
            }
            $model_voucher->commit();
            showDialog('代金券领取成功', ($_GET['jump'] === '0') ? '':urlMember('member_voucher', 'voucher_list'), 'succ');
        } catch (Exception $e) {
            $model_voucher->rollback();
            showDialog($e->getMessage(), '', 'error');
        }
        
    }
}
