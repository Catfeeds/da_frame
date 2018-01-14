<?php
/**
 * 分享设置
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


class SnsSharesettingController extends SystemController {
    private $app_arr = array();

    public function __construct(){
        parent::__construct();
        Language::read('sns_sharesetting', 'Home');
        $model = Model('sns_binding');
        $this->app_arr = $model->getApps();
    }

    public function index() {
        $this->sharesetting();
    }

    /**
     * 绑定接口列表
     */
    public function sharesetting(){
        $model_setting = Model('setting');
        $list_setting = $model_setting->getListSetting();
        //sinaweibo
        if($list_setting['share_qqweibo_isuse']){
            $this->app_arr['qqweibo']['isuse'] = '1';
        }
        //qqweibo
        if($list_setting['share_sinaweibo_isuse']){
            $this->app_arr['sinaweibo']['isuse'] = '1';
        }
        $this->assign('app_arr',$this->app_arr);
		$this->setDirquna('shop');
        $this->render('snssharesetting.index');
    }

    /**
     * 开启和禁用功能
     */
    public function set(){
        $key = trim($_GET['key']);
        if(!$key){
            showMessage(Language::get('param_error'));
        }
        $app_key = array_keys($this->app_arr);
        if(empty($app_key) || !in_array($key,$app_key)){
            showMessage(Language::get('param_error'));
        }
        $setting_model = Model('setting');
        $update_array = array();
        $key = "share_{$key}_isuse";
        $state = intval($_GET['state']) == 1 ?1:0;
        $update_array[$key] = $state;
        $result = $setting_model->updateSetting($update_array);
        if ($result){
            $this->log(L('spd_edit,spd_binding_manage'),null);
            showMessage(Language::get('spd_common_op_succ'));
        }else {
            showMessage(Language::get('spd_common_op_fail'));
        }
    }
    /**
     * 编辑接口设置功能
     */
    public function edit(){
        $key = trim($_GET['key']);
        if(!$key){
            showMessage(Language::get('param_error'));
        }
        $app_key = array_keys($this->app_arr);
        if(empty($app_key) || !in_array($key,$app_key)){
            showMessage(Language::get('param_error'));
        }
        $setting_model = Model('setting');
        if(chksubmit()){
            $update_array = array();
            $update_array["share_{$key}_isuse"] = intval($_POST['isuse']) == 1 ?1:0;
            $update_array["share_{$key}_appid"] = $_POST['appid'];
            $update_array["share_{$key}_appkey"] = $_POST['appkey'];
            //只更新需要code的app
            if(isset($_POST['appcode'])){
                $update_array["share_{$key}_appcode"] = $_POST['appcode'];
            }
            //只更新需要secretkey的app
            if(isset($_POST['secretkey'])){
                $update_array["share_{$key}_secretkey"] = $_POST['secretkey'];
            }
            $result = $setting_model->updateSetting($update_array);
            if ($result){
                $this->log(L('spd_edit,spd_binding_manage'),null);
                showMessage(Language::get('spd_common_save_succ'),$GLOBALS['_PAGE_URL'] . '&c=SnsSharesetting&a=sharesetting');
            }else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }else{
            $list_setting = $setting_model->getListSetting();
            $edit_arr = array();
            $edit_arr = $this->app_arr[$key];
            $edit_arr['key'] = $key;
            $edit_arr['isuse'] = $list_setting["share_{$key}_isuse"];
            $edit_arr['appid'] = $list_setting["share_{$key}_appid"];
            $edit_arr['appkey'] = $list_setting["share_{$key}_appkey"];
            //需要code的app
            if(in_array($key,array('qqzone','sinaweibo'))){
                $edit_arr['appcode'] = "{$list_setting["share_{$key}_appcode"]}";
            }
            $this->assign('edit_arr',$edit_arr);
			$this->setDirquna('shop');
            $this->render('snssharesetting.edit');
        }
    }
}
