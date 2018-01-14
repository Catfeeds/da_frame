<?php
/**
 * 手机端微信公众账号二维码设置
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Mobile\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;


class MbSettingController extends SystemController {
    public function __construct(){
        parent::__construct();
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
            $update_array = array();
            $update_array['signin_isuse'] = intval($_POST['signin_isuse'])==1?1:0;
            $update_array['points_signin'] = intval($_POST['points_signin'])?$_POST['points_signin']:0;
            $result = $model_setting->updateSetting($update_array);
            if ($result === true){
                $this->log('编辑手机端设置',1);
                showDialog(L('spd_common_save_succ'));
            } else {
                showDialog(L('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);
	$this->setDirquna('mobile');
        $this->render('mb_setting');
    }
}
