<?php
/**
 * 微商城
 *
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Microshop\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\UploadFile;


class ManageController extends SystemController {

    const MICROSHOP_CLASS_LIST = 'admin.php?m=Microshop&c=GoodsClass&a=goodsclass_list';
    const GOODS_FLAG = 1;
    const PERSONAL_FLAG = 2;
    const ALBUM_FLAG = 3;
    const STORE_FLAG = 4;

    public function __construct(){
        parent::__construct();
        Language::read('store', 'Home');
        Language::read('microshop', 'Home');
    }

    public function index() {
       $this->manage();
    }

    /**
     * 微商城管理
     */
    public function manage() {
        $model_setting = Model('setting');
        $setting_list = $model_setting->getListSetting();
        $this->assign('setting',$setting_list);
        $this->setDirquna('microshop');
$this->render('microshop_manage');
    }

    /**
     * 微商城管理保存
     */
    public function manage_save() {
        $model_setting = Model('setting');
        $update_array = array();
        $update_array['microshop_isuse'] = intval($_POST['microshop_isuse']);
        $update_array['microshop_style'] = trim($_POST['microshop_style']);
        $update_array['microshop_personal_limit'] = intval($_POST['microshop_personal_limit']);
        $old_image = array();
        if(!empty($_FILES['microshop_logo']['name'])) {
            $upload = new UploadFile();
            $upload->set('default_dir',ATTACH_MICROSHOP);
            $result = $upload->upfile('microshop_logo');
            if(!$result) {
                showMessage($upload->error);
            }
            $update_array['microshop_logo'] = $upload->file_name;
            $old_image[] = BASE_UPLOAD_PATH.DS.ATTACH_MICROSHOP.DS.C('microshop_logo');
        }
        if(!empty($_FILES['microshop_header_pic']['name'])) {
            $upload = new UploadFile();
            $upload->set('default_dir',ATTACH_MICROSHOP);
            $result = $upload->upfile('microshop_header_pic');
            if(!$result) {
                showMessage($upload->error);
            }
            $update_array['microshop_header_pic'] = $upload->file_name;
            $old_image[] = BASE_UPLOAD_PATH.DS.ATTACH_MICROSHOP.DS.C('microshop_header_pic');
        }
        $update_array['microshop_seo_keywords'] = $_POST['microshop_seo_keywords'];
        $update_array['microshop_seo_description'] = $_POST['microshop_seo_description'];

        $result = $model_setting->updateSetting($update_array);
        if ($result === true){
            if(!empty($old_image)) {
                foreach ($old_image as $value) {
                    if(is_file($value)) {
                        unlink($value);
                    }
                }
            }
            showMessage(Language::get('spd_common_save_succ'));
        }else {
            showMessage(Language::get('spd_common_save_fail'));
        }
    }
}
