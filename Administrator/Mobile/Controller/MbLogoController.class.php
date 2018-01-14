<?php
/**
 * 手机端LOGO图片设置
 
 *
 *
 */


namespace Mobile\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\UploadFile;


class MbLogoController extends SystemController {
    public function __construct(){
        parent::__construct();
           Language::read('mobile', 'Home');
    }
	
	
    public function index(){
        $model_setting = Model('setting');
        if (chksubmit()){
            if ($_FILES['mobile_logo']['tmp_name'] != ''){
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_COMMON);
				$upload->file_name='home_logo.png';
                $result = $upload->upfile('mobile_logo');

                if ($result){
                    $_POST['mobile_logo'] = $upload->file_name;
                }else {
                    showMessage($upload->error);
                }
            } else {
            	showMessage("请选择一张新的图片");
            }
            $update_array = array();
            if (!empty($_POST['mobile_logo'])){
				$update_array['mobile_logo'] = $_POST['mobile_logo'];
			}
            $result = $model_setting->updateSetting($update_array);
            if ($result){
                if (!empty($mobile_logo)){
                    @unlink(BASE_UPLOAD_PATH.DS.ATTACH_COMMON.'/'.'home_logo.png');
                }
                showMessage(Language::get('spd_common_save_succ'));
            }else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }
        $this->assign('mobile_logo',$mobile_logo);
        $this->setDirquna('mobile');
$this->render('mb_logo.index');
    }
}
