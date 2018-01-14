<?php
/**
 * 淘宝接口
 *
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace System\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;


class TaobaoApiController extends SystemController {

    public function __construct(){
        parent::__construct();
    }

    public function index() {
        $this->taobao_api_setting();
    }

    public function taobao_api_setting() {
        $model_setting = Model('setting');
        $setting_list = $model_setting->getListSetting();
        $this->assign('setting',$setting_list);
				
		$this->setDirquna('system');
        $this->render('taobao_api');
    }

    public function taobao_api_save() {
        $model_setting = Model('setting');

        $update_array['taobao_api_isuse'] = intval($_POST['taobao_api_isuse']);
        $update_array['taobao_app_key'] = $_POST['taobao_app_key'];
        $update_array['taobao_secret_key'] = $_POST['taobao_secret_key'];

        $result = $model_setting->updateSetting($update_array);
        if ($result === true){
            $this->log('淘宝接口保存', 1);
            showMessage(Language::get('spd_common_save_succ'));
        }else {
            $this->log('淘宝接口保存', 0);
            showMessage(Language::get('spd_common_save_fail'));
        }
    }
}
