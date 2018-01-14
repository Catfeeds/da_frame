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
use Common\Lib\Tpl;
use Common\Lib\Model;
use Common\Lib\Page;
use Common\Lib\UploadFile;
use Common\Lib\Validate;


class AdvController extends SystemController{

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
       $this->adv_manage();
    }

    /**
     * 广告管理
     */
    public function adv_manage() {
        $model_personal = Model('micro_adv');
        $condition = array();
        if(!empty($_GET['adv_type'])) {
            $condition['adv_type'] = array('like','%'.trim($_GET['adv_type']).'%');
        }
        if(!empty($_GET['adv_name'])) {
            $condition['adv_name'] = array('like','%'.trim($_GET['adv_name']).'%');
        }
        $list = $model_personal->getList($condition,10,'','*');
        $this->assign('show_page',$model_personal->showpage(2));
        $this->assign('list',$list);
        $this->get_adv_type_list();
        $this->show_menu_adv('adv_manage');
        $this->setDirquna('microshop');
$this->render('microshop_adv.manage');
    }

    /**
     * 微商城广告添加
     **/
    public function adv_add() {
        $this->get_adv_type_list();
        $this->show_menu_adv('adv_add');
        $this->setDirquna('microshop');
$this->render('microshop_adv.add');
    }

    public function adv_edit() {
        $adv_id = intval($_GET['adv_id']);
        if(empty($adv_id)) {
            showMessage(Language::get('param_error'),'','','error');
        }
        $model_adv = Model("micro_adv");
        $condition = array();
        $condition['adv_id'] = $adv_id;
        $adv_info = $model_adv->getOne($condition);
        $this->assign('adv_info',$adv_info);

        $this->get_adv_type_list();
        $this->show_menu_adv('adv_add');
        $this->setDirquna('microshop');
$this->render("microshop_adv.add");
    }

    public function adv_save() {
        $obj_validate = new Validate();
        $validate_array = array(
            array('input'=>$_POST['adv_sort'],'require'=>'true','validator'=>'Range','min'=>0,'max'=>255,'message'=>Language::get('class_sort_error')),
        );
        $obj_validate->validateparam = $validate_array;
        $error = $obj_validate->validate();
        if ($error != ''){
            showMessage(Language::get('error').$error,'','','error');
        }

        $param = array();
        $param['adv_type'] = trim($_POST['adv_type']);
        $param['adv_name'] = trim($_POST['adv_name']);
        $param['adv_url'] = trim($_POST['adv_url']);
        $param['adv_sort'] = intval($_POST['adv_sort']);
        if(!empty($_FILES['adv_image']['name'])) {
            $upload = new UploadFile();
            $upload->set('default_dir',ATTACH_MICROSHOP.DS.'adv');
            $result = $upload->upfile('adv_image');
            if(!$result) {
                showMessage($upload->error);
            }
            $param['adv_image'] = $upload->file_name;
            //删除老图片
            if(!empty($_POST['old_adv_image'])) {
                $old_image = BASE_UPLOAD_PATH.DS.ATTACH_MICROSHOP.DS.'adv'.DS.$_POST['old_adv_image'];
                if(is_file($old_image)) {
                    unlink($old_image);
                }
            }
        } else {
            if(empty($_POST['adv_id'])) {
                showMessage(Language::get('microshop_adv_image_error'),'','','error');
            }
        }

        $model_adv = Model("micro_adv");
        if(isset($_POST['adv_id']) && intval($_POST['adv_id']) > 0) {
            $result = $model_adv->modify($param,array('adv_id'=>$_POST['adv_id']));
        } else {
            $result = $model_adv->save($param);
        }
        if($result) {
            showMessage(Language::get('spd_common_save_succ'),"{$GLOBALS['_PAGE_URL']}&c=Adv&a=adv_manage");
        } else {
            showMessage(Language::get('spd_common_save_fail'),"{$GLOBALS['_PAGE_URL']}&c=Adv&a=adv_manage",'','error');
        }
    }

    /**
     * 广告删除
     */
    public function adv_drop() {
        $model = Model('micro_adv');
        $condition = array();
        $condition['adv_id'] = array('in',trim($_REQUEST['adv_id']));

        //删除图片
        $list = $model->getList($condition);
        if(!empty($list)) {
            foreach ($list as $adv_info) {
                //删除原始图片
                $image_name = BASE_UPLOAD_PATH.DS.ATTACH_MICROSHOP.DS.'adv'.DS.$adv_info['adv_image'];
                if(is_file($image_name)) {
                    unlink($image_name);
                }
            }
        }

        $result = $model->drop($condition);
        if($result) {
            showMessage(Language::get('spd_common_del_succ'),'');
        } else {
            showMessage(Language::get('spd_common_del_fail'),'','','error');
        }
    }

    /**
     * 广告排序
     */
    public function adv_sort_update() {
        if(intval($_GET['id']) <= 0) {
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('param_error')));
            die;
        }
        $new_sort = intval($_GET['value']);
        if ($new_sort > 255){
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('class_sort_error')));
            die;
        } else {
            $model_class = Model("micro_adv");
            $result = $model_class->modify(array('adv_sort'=>$new_sort),array('adv_id'=>$_GET['id']));
            if($result) {
                echo json_encode(array('result'=>TRUE,'message'=>''));
                die;
            } else {
                echo json_encode(array('result'=>FALSE,'message'=>''));
                die;
            }
        }
    }


    //微商城广告类型列表
    private function get_adv_type_list() {
        $adv_type_list = array();
        $adv_type_list['index'] = Language::get('microshop_adv_type_index');
        $adv_type_list['store_list'] = Language::get('microshop_adv_type_store_list');
        $this->assign('adv_type_list',$adv_type_list);
    }

    private function show_menu_adv($menu_key) {
        $menu_array = array(
            'adv_manage'=>array('menu_type'=>'link','menu_name'=>Language::get('spd_manage'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=Adv&a=adv_manage'),
            'adv_add'=>array('menu_type'=>'link','menu_name'=>Language::get('spd_new'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=Adv&a=adv_add'),
        );

        if($menu_key == 'adv_edit') {
            $menu_array['adv_edit'] = array('menu_type'=>'link','menu_name'=>Language::get('spd_edit'),'menu_url'=>'###');
            unset($menu_array['adv_add']);
        }
        $menu_array[$menu_key]['menu_type'] = 'text';
        $this->assign('menu',$menu_array);
    }
}
