<?php
/**
 * 快递接口设置 
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


class ExpressApiController extends SystemController {
    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['express_api']   = $_POST['express_api'];
            $update_array['express_kuaidi100_id']   = $_POST['express_kuaidi100_id'];
            $update_array['express_kuaidi100_key']  = $_POST['express_kuaidi100_key'];
            $update_array['express_kdniao_id']   = $_POST['express_kdniao_id'];
            $update_array['express_kdniao_key']  = $_POST['express_kdniao_key'];
            $result = $model_setting->updateSetting($update_array);
            if ($result){
                $this->log('快递接口设置');
                showMessage(Language::get('spd_common_save_succ'));
            } else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);
	$this->setDirquna('shop');
        $this->render('express_api.edit');
    }

}
