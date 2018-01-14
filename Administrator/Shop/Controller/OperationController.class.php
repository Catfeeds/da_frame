<?php
/**
 * 网站设置
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
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Validate;


class OperationController extends SystemController {
    public function __construct(){
        parent::__construct();
        Language::read('setting', 'Home');
    }

    public function index() {
        $this->setting();
    }

    /**
     * 基本设置
     */
    public function setting(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(

            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }else {
                $update_array = array();
                $update_array['promotion_allow'] = $_POST['promotion_allow'];
                $update_array['groupbuy_allow'] = $_POST['groupbuy_allow'];
                $update_array['pointshop_isuse'] = $_POST['pointshop_isuse'];
                $update_array['voucher_allow'] = $_POST['voucher_allow'];
                $update_array['pointprod_isuse'] = $_POST['pointprod_isuse'];
                $update_array['redpacket_allow'] = $_POST['redpacket_allow'];
                $result = $model_setting->updateSetting($update_array);
                if ($result === true){
                    $this->log(L('spd_edit,spd_operation,spd_operation_set'),1);
                    showMessage(L('spd_common_save_succ'));
                }else {
                    showMessage(L('spd_common_save_fail'));
                }
            }
        }
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);
		$this->setDirquna('shop');
        $this->render('operation.setting');
    }
}
