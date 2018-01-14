<?php
/**
 * 下载设置
 *
 *
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


class MbAppController extends SystemController{
    public function __construct(){
        parent::__construct();
    }

    public function index() {
        $this->mb_app();
    }

    /**
     * 设置下载地址
     *
     */
    public function mb_app() {
        $model_setting = Model('setting');
        $mobile_apk = $model_setting->getRowSetting('mobile_apk');
        $mobile_apk_version = $model_setting->getRowSetting('mobile_apk_version');
        $mobile_ios = $model_setting->getRowSetting('mobile_ios');
        if (chksubmit()) {
            $update_array = array();
            $update_array['mobile_apk'] = $_POST['mobile_apk'];
            $update_array['mobile_apk_version'] = $_POST['mobile_apk_version'];
            $update_array['mobile_ios'] = $_POST['mobile_ios'];
            $state = $model_setting->updateSetting($update_array);
            if ($state) {
                $this->log('设置手机端下载地址');
                showMessage(Language::get('spd_common_save_succ'),$GLOBALS['_PAGE_URL'] . '&c=MbApp&a=mb_app');
            } else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }
        $this->assign('mobile_apk',$mobile_apk);
        $this->assign('mobile_version',$mobile_apk_version);
        $this->assign('mobile_ios',$mobile_ios);
        $this->setDirquna('mobile');
$this->render('mb_app.edit');
    }

    /**
     * 生成二维码
     */
    public function mb_qr() {
        $url = urlShop('mb_app', 'index');
        $mobile_app = 'mb_app.png';
        require_once(BASE_ROOT_PATH.DS.'Api/phpqrcode'.DS.'index.php');
        $PhpQRCode = new \PhpQRCode();
        $PhpQRCode->set('pngTempDir',BASE_UPLOAD_PATH.DS.ATTACH_COMMON.DS);
        $PhpQRCode->set('data',$url);
        $PhpQRCode->set('pngTempName', $mobile_app);
        $PhpQRCode->init();

        $this->log('生成手机端二维码');
        showMessage('生成二维码成功',$GLOBALS['_PAGE_URL'] . '&c=MbApp&a=mb_app');
    }
}
