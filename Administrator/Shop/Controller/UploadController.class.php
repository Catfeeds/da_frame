<?php
/**
 * 上传设置
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
use Common\Lib\UploadFile;


class UploadController extends SystemController {

    public function __construct(){
        parent::__construct();
		
		   $this->links = array(
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Upload&a=param','lang'=>'upload_param'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Upload&a=default_thumb','lang'=>'default_thumb'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Upload&a=font','lang'=>'font_set')
    );
	
        Language::read('setting', 'Home');
    }

    public function index() {
        $this->param();
    }

    /**
     * 上传参数设置
     *
     */
    public function param(){
        if (chksubmit()){
            $model_setting = Model('setting');
            $result = $model_setting->updateSetting(array('image_dir_type'=>intval($_POST['image_dir_type'])));
            if ($result){
                $this->log(L('spd_edit,upload_param'),1);
                showMessage(L('spd_common_save_succ'));
            }else {
                $this->log(L('spd_edit,upload_param'),0);
                showMessage(L('spd_common_save_fail'));
            }
        }

        //获取默认图片设置属性
        $model_setting = Model('setting');
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);

        //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'param'));
		$this->setDirquna('shop');

        $this->render('upload.param');
    }

    /**
     * 默认图设置
     */
    public function default_thumb(){
        $model_setting = Model('setting');
        if (chksubmit()){
            //上传图片
            $upload = new UploadFile();
            $upload->set('default_dir',ATTACH_COMMON);
            //默认商品图片
            if (!empty($_FILES['default_goods_image']['tmp_name'])){
                $upload->set('thumb_width', GOODS_IMAGES_WIDTH);
                $upload->set('thumb_height', GOODS_IMAGES_HEIGHT);
                $upload->set('thumb_ext', GOODS_IMAGES_EXT);
                $upload->set('filling',false);
                $result = $upload->upfile('default_goods_image');
                if ($result){
                    $_POST['default_goods_image'] = $upload->file_name;
                }else {
                    showMessage($upload->error,'','','error');
                }
            }
            //默认店铺标志
            if (!empty($_FILES['default_store_logo']['tmp_name'])){
                $upload->set('file_name', '');
                $upload->set('thumb_width', 0);
                $upload->set('thumb_height',0);
                $upload->set('thumb_ext',   false);
                $result = $upload->upfile('default_store_logo');
                if ($result){
                    $_POST['default_store_logo'] = $upload->file_name;
                }else {
                    showMessage($upload->error,'','','error');
                }
            }
            //默认店铺头像
            if (!empty($_FILES['default_store_avatar']['tmp_name'])){
                $upload->set('file_name', '');
                $upload->set('thumb_width', 0);
                $upload->set('thumb_height',0);
                $upload->set('thumb_ext',   false);
                $result = $upload->upfile('default_store_avatar');
                if ($result){
                    $_POST['default_store_avatar'] = $upload->file_name;
                }else {
                    showMessage($upload->error,'','','error');
                }
            }
            $list_setting = $model_setting->getListSetting();
            $update_array = array();
            if (!empty($_POST['default_goods_image'])){
                $update_array['default_goods_image'] = $_POST['default_goods_image'];
            }
            if (!empty($_POST['default_store_logo'])){
                $update_array['default_store_logo'] = $_POST['default_store_logo'];
            }
            if (!empty($_POST['default_store_avatar'])){
                $update_array['default_store_avatar'] = $_POST['default_store_avatar'];
            }
            if (!empty($update_array)){
                $result = $model_setting->updateSetting($update_array);
            }else{
                $result = true;
            }
            if ($result === true){
                //判断有没有之前的图片，如果有则删除
                if (!empty($list_setting['default_goods_image']) && !empty($_POST['default_goods_image'])){
                    @unlink(BASE_UPLOAD_PATH.DS.ATTACH_COMMON.DS.$list_setting['default_goods_image']);
                    $img_ext = explode(',', GOODS_IMAGES_EXT);
                    foreach ($img_ext as $val) {
                        @unlink(BASE_UPLOAD_PATH.DS.ATTACH_COMMON.DS.str_ireplace('.', $val . '.', $list_setting['default_goods_image']));
                    }
                }
                if (!empty($list_setting['default_store_logo']) && !empty($_POST['default_store_logo'])){
                    @unlink(BASE_UPLOAD_PATH.DS.ATTACH_COMMON.DS.$list_setting['default_store_logo']);
                }
                $this->log(L('spd_edit,default_thumb'),1);
                showMessage(L('spd_common_save_succ'));
            }else {
                $this->log(L('spd_edit,default_thumb'),0);
                showMessage(L('spd_common_save_fail'));
            }
        }

        $list_setting = $model_setting->getListSetting();

        //模板输出
        $this->assign('list_setting',$list_setting);

        //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'default_thumb'));
		$this->setDirquna('shop');

        $this->render('upload.thumb');
    }

    /**
     * 水印字体
     *
     * @param
     * @return
     */
    public function font(){
        //获取水印字体
        $dir_list = array();
        readFileList(BASE_RESOURCE_PATH.DS.'font',$dir_list);
        if (!empty($dir_list) && is_array($dir_list)){
            $fontInfo = array();
            include BASE_RESOURCE_PATH.DS.'font'.DS.'font.info.php';
            foreach ($dir_list as $value){
                $file_ext_array = explode('.',$value);
                if (strtolower(end($file_ext_array)) == 'ttf' && file_exists($value)){
                    $file_path_array = explode('/', $value);
                    $value = array_pop($file_path_array);
                    $tmp = explode('.',$value);
                    $file_list[$value] = $fontInfo[$tmp[0]];
                }
            }
            //转码
            if (strtoupper(CHARSET) == 'GBK'){
                $file_list = Language::getGBK($file_list);
            }
            $this->assign('file_list',$file_list);
        }
        $this->assign('top_link',$this->sublink($this->links,'font'));
		$this->setDirquna('shop');

        $this->render('upload.font');
    }

}
