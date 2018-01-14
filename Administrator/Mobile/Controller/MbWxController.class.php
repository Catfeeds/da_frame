<?php
/**
 * 手机端微信公众账号二维码设置
 *
 *
 *
 *
 */

namespace Mobile\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\UploadFile;


class MbWxController extends SystemController {
    public function __construct(){
        parent::__construct();
//         Language::read('mobile', 'Home');
    }

    public function index(){
        $model_setting = Model('setting');
        $mobile_wx = $model_setting->getRowSetting('mobile_wx');
        $mobile_wx = $mobile_wx['value'];
        if (chksubmit()){
            if ($_FILES['mobile_wx']['tmp_name'] != ''){
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_MOBILE);

                $result = $upload->upfile('mobile_wx');
                if ($result){
                    $_POST['mobile_wx'] = $upload->file_name;
                }else {
                    showMessage($upload->error);
                }
            }
            $update_array = array();
            $update_array['mobile_wx'] = $_POST['mobile_wx'];
            $result = $model_setting->updateSetting($update_array);
            if ($result){
                if (!empty($mobile_wx)){
                    @unlink(BASE_UPLOAD_PATH.DS.ATTACH_MOBILE.'/'.$mobile_wx);
                }
                showMessage(Language::get('spd_common_save_succ'));
            }else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }
        $this->assign('mobile_wx',$mobile_wx);
        $this->setDirquna('mobile');
		$this->render('mb_wx.index');
    }
}
