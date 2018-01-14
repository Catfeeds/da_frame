<?php
/**
 * cms管理
 *
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Cms\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\UploadFile;


class CmsManageController extends SystemController {

    public function __construct(){
        parent::__construct();
        Language::read('cms', 'Home');
    }

    public function index() {
        $this->cms_manage();
    }

    /**
     * cms设置
     */
    public function cms_manage() {
        $model_setting = Model('setting');
        $setting_list = $model_setting->getListSetting();
        $this->assign('setting',$setting_list);
        $this->setDirquna('cms');
$this->render('cms_manage');
    }

    /**
     * cms设置保存
     */
    public function cms_manage_save() {
        $model_setting = Model('setting');
        $update_array = array();
        $update_array['cms_isuse'] = intval($_POST['cms_isuse']);
        if(!empty($_FILES['cms_logo']['name'])) {
            $upload = new UploadFile();
            $upload->set('default_dir',ATTACH_CMS);
            $result = $upload->upfile('cms_logo');
            if(!$result) {
                showMessage($upload->error);
            }
            $update_array['cms_logo'] = $upload->file_name;
            $old_image = BASE_UPLOAD_PATH.DS.ATTACH_CMS.DS.C('microshop_logo');
            if(is_file($old_image)) {
                unlink($old_image);
            }
        }
        $update_array['cms_submit_verify_flag'] = intval($_POST['cms_submit_verify_flag']);
        $update_array['cms_comment_flag'] = intval($_POST['cms_comment_flag']);
        $update_array['cms_attitude_flag'] = intval($_POST['cms_attitude_flag']);
        $update_array['cms_seo_title'] = $_POST['cms_seo_title'];
        $update_array['cms_seo_keywords'] = $_POST['cms_seo_keywords'];
        $update_array['cms_seo_description'] = $_POST['cms_seo_description'];

        $result = $model_setting->updateSetting($update_array);
        if ($result === true){
            $this->log(Language::get('cms_log_manage_save'), 0);
            showMessage(Language::get('spd_common_save_succ'));
        }else {
            $this->log(Language::get('cms_log_manage_save'), 0);
            showMessage(Language::get('spd_common_save_fail'));
        }
    }


}
