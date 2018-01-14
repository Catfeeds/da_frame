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
use Common\Lib\Cache;
use Common\Lib\Model;
use Common\Lib\UploadFile;
use Common\Lib\Validate;



class GoodsClassController extends SystemController {

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
       $this->goodsclass_list();
    }

    /**
     * 微商城商品(随心看)分类管理
     **/
    public function goodsclass_list() {
        $model_class = Model("micro_goods_class");
        $list = $model_class->getList(TRUE);
        $this->assign('list',$list);
        $this->show_menu_goods_class("goods_class_list");
        $this->setDirquna('microshop');
		$this->render("microshop_goods_class.list");
    }

    /**
     * 微商城商品(随心看)分类添加
     **/
    public function goodsclass_add() {
        //取得一级分类列表
        $model_microshop_goods_class = Model('micro_goods_class');
        $condition = array();
        $condition['class_parent_id'] = 0;
        $goods_class_list = $model_microshop_goods_class->getList($condition);
        $this->assign('list',$goods_class_list);

        $class_parent_id = intval($_GET['class_parent_id']);
        if(!empty($class_parent_id)) {
            $this->assign('class_parent_id',$class_parent_id);
        }

        $this->show_menu_goods_class('goods_class_add');
        $this->setDirquna('microshop');
$this->render('microshop_goods_class.add');
    }


    /**
     * 微商城商品(随心看)分类编辑
     **/
    public function goodsclass_edit() {
        $class_id = intval($_GET['class_id']);
        if(empty($class_id)) {
            showMessage(Language::get('param_error'),'','','error');
        }
        $model_class = Model("micro_goods_class");
        $condition = array();
        $condition['class_id'] = $class_id;
        $class_info = $model_class->getOne($condition);
        $this->assign('class_info',$class_info);

        $this->show_menu_goods_class("goods_class_edit");
        $this->setDirquna('microshop');
$this->render("microshop_goods_class.add");
    }

    /**
     * 微商城商品(随心看)分类保存
     **/
    public function goodsclass_save() {
        $obj_validate = new Validate();
        $validate_array = array(
            array('input'=>$_POST['class_name'],'require'=>'true',"validator"=>"Length","min"=>"1","max"=>"10",'message'=>Language::get('class_name_error')),
            array('input'=>$_POST['class_sort'],'require'=>'true','validator'=>'Range','min'=>0,'max'=>255,'message'=>Language::get('class_sort_error')),
            array('input'=>$_POST['class_parent_id'],'require'=>'true','validator'=>'Number','message'=>Language::get('parent_id_error'))
        );
        $obj_validate->validateparam = $validate_array;
        $error = $obj_validate->validate();
        if ($error != ''){
            showMessage(Language::get('error').$error,'','','error');
        }

        $param = array();
        $param['class_name'] = trim($_POST['class_name']);
        if(isset($_POST['class_parent_id']) && intval($_POST['class_parent_id']) > 0) {
            $param['class_parent_id'] = $_POST['class_parent_id'];
        }
        if(isset($_POST['class_keyword'])) {
            $param['class_keyword'] = $_POST['class_keyword'];
        }
        $param['class_sort'] = intval($_POST['class_sort']);
        if(!empty($_FILES['class_image']['name'])) {
            $upload = new UploadFile();
            $upload->set('default_dir',ATTACH_MICROSHOP);
            $result = $upload->upfile('class_image');
            if(!$result) {
                showMessage($upload->error);
            }
            $param['class_image'] = $upload->file_name;
            //删除老图片
            if(!empty($_POST['old_class_image'])) {
                $old_image = BASE_UPLOAD_PATH.DS.ATTACH_MICROSHOP.DS.$_POST['old_class_image'];
                if(is_file($old_image)) {
                    unlink($old_image);
                }
            }
        }

        $model_class = Model("micro_goods_class");
        if(isset($_POST['class_id']) && intval($_POST['class_id']) > 0) {
            $result = $model_class->modify($param,array('class_id'=>$_POST['class_id']));
        } else {
            $result = $model_class->save($param);
        }
        if($result) {
            showMessage(Language::get('class_add_success'),"{$GLOBALS['_PAGE_URL']}&c=GoodsClass&a=goodsclass_list");
        } else {
            showMessage(Language::get('class_add_fail'),"{$GLOBALS['_PAGE_URL']}&c=GoodsClass&a=goodsclass_list",'','error');
        }

    }

    /*
     * ajax修改分类排序
     */
    public function goodsclass_sort_update() {
        if(intval($_GET['id']) <= 0) {
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('param_error')));
            die;
        }
        $new_sort = intval($_GET['value']);
        if ($new_sort > 255){
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('class_sort_error')));
            die;
        } else {
            $model_class = Model("micro_goods_class");
            $result = $model_class->modify(array('class_sort'=>$new_sort),array('class_id'=>$_GET['id']));
            if($result) {
                echo json_encode(array('result'=>TRUE,'message'=>'class_add_success'));
                die;
            } else {
                echo json_encode(array('result'=>FALSE,'message'=>Language::get('class_add_fail')));
                die;
            }
        }
    }

    /*
     * ajax修改分类名称
     */
    public function goodsclass_name_update() {
        $class_id = intval($_GET['id']);
        if($class_id <= 0) {
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('param_error')));
            die;
        }

        $new_name = trim($_GET['value']);
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array('input'=>$new_name,'require'=>'true',"validator"=>"Length","min"=>"1","max"=>"10",'message'=>Language::get('class_name_error')),
        );
        $error = $obj_validate->validate();
        if ($error != ''){
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('class_name_error')));
            die;
        } else {
            $model_class = Model("micro_goods_class");
            $result = $model_class->modify(array('class_name'=>$new_name),array('class_id'=>$class_id));
            if($result) {
                echo json_encode(array('result'=>TRUE,'message'=>'class_add_success'));
                die;
            } else {
                echo json_encode(array('result'=>FALSE,'message'=>Language::get('class_add_fail')));
                die;
            }
        }
    }

    /**
     * 随心看分类删除
     **/
     public function goodsclass_drop() {

        $class_id = trim($_REQUEST['class_id']);
        $model_microshop_goods_class = Model('micro_goods_class');
        $condition = array();
        $condition['class_parent_id'] = array('in',$class_id);
        $goods_class_list = $model_microshop_goods_class->getList($condition,'','','class_id');
        if(!empty($goods_class_list) && is_array($goods_class_list)) {
            foreach($goods_class_list as $val) {
                $class_id .= ','.$val['class_id'];
            }
        }
        $class_id = rtrim($class_id,',');
        $condition = array();
        $condition['class_id'] = array('in',$class_id);
        //删除分类图片
        $list = $model_microshop_goods_class->getList($condition);
        if(!empty($list)) {
            foreach ($list as $value) {
                if(!empty($value['class_image'])) {
                    //删除老图片
                    $old_image = BASE_UPLOAD_PATH.DS.ATTACH_MICROSHOP.DS.$value['class_image'];
                    if(is_file($old_image)) {
                        unlink($old_image);
                    }
                }
            }
        }

        //删除绑定关系
        $model_microshop_goods_relation = Model('micro_goods_relation');
        $model_microshop_goods_relation->drop($condition);

        //删除分类
        $result = $model_microshop_goods_class->drop($condition);
        if($result) {
            showMessage(Language::get('class_drop_success'),'');
        } else {
            showMessage(Language::get('class_drop_fail'),'','','error');
        }

     }

    /**
     * 分类关键字和商品分类的绑定
     **/
    public function goodsclass_binding() {

        $class_id = intval($_GET['class_id']);
        if($class_id <= 0) {
            showMessage(Language::get('param_error'),'','','error');
        }
        $this->assign('class_id',$class_id);

        $goods_class_list = Model('goods_class')->getGoodsClassForCacheModel();
        $goods_class_root = array();
        foreach($goods_class_list as $val) {
            if($val['gc_parent_id'] == '0') {
                $goods_class_root[] = $val;
            }
        }
        $this->assign('goods_class_root',$goods_class_root);
        $this->assign('goods_class',$goods_class_list);

        $model_goods_relation = Model('micro_goods_relation');
        $class_binding_list = $model_goods_relation->getList(array('class_id'=>$class_id));
        $this->assign('class_binding_list',$class_binding_list);
        $class_binding_string = '';
        if(!empty($class_binding_list)) {
            foreach ($class_binding_list as $val) {
                $class_binding_string .= $val['shop_class_id'].',';
            }
        }
        $this->assign('class_binding_string',rtrim($class_binding_string,','));

        $this->show_menu_goods_class('goods_class_binding');
        $this->setDirquna('microshop');
$this->render('microshop_goods_class.binding');

    }

    /**
     * 分类关键字和商品分类的绑定保存
     **/
    public function goodsclass_binding_save() {
        $class_id = intval($_POST['class_id']);
        $shop_class_id = trim($_POST['shop_class_id']);
        $shop_class_array = explode(',',$shop_class_id);
        $param = array();
        foreach($shop_class_array as $val) {
            if(!empty($val)) {
                $param[] = array('class_id'=>$class_id,'shop_class_id'=>$val);
            }
        }
        $model_goods_relation = Model('micro_goods_relation');
        $model_goods_relation->drop(array('class_id'=>$class_id));
        $result = $model_goods_relation->saveAll($param);
        if($result) {
            showMessage(Language::get('goods_relation_save_success'),self::MICROSHOP_CLASS_LIST);
        } else {
            showMessage(Language::get('goods_relation_save_fail'), self::MICROSHOP_CLASS_LIST,'','error');
        }
    }

    /**
     * 设为默认分类
     **/
    public function goodsclass_default() {
        $class_id = intval($_GET['class_id']);
        if($class_id <= 0) {
            showMessage(Language::get('param_error'),'','','error');
        }
        $model_goods_class = Model('micro_goods_class');
        $model_goods_class->modify(array('class_default'=>0),TRUE);
        $result = $model_goods_class->modify(array('class_default'=>1),array('class_id'=>$class_id));
        if($result) {
            showMessage(Language::get('spd_common_op_succ'),'');
        } else {
            showMessage(Language::get('spd_common_op_fail'),'','','error');
        }
    }

    public function goodsclass_get() {

        $goods_class_id = intval($_GET['class_id']);
        $goods_class_list = Model('goods_class')->getGoodsClassForCacheModel();
        if(empty($goods_class_list[$goods_class_id]['childchild'])) {
            if(empty($goods_class_list[$goods_class_id]['child'])) {
                $goods_class_child = $goods_class_id;
            } else {
                $goods_class_child = $goods_class_list[$goods_class_id]['child'];
            }
        } else {
            $goods_class_child = $goods_class_list[$goods_class_id]['childchild'];
        }
        $goods_class_child = explode(',',$goods_class_child);

        $model_goods_relation = Model('micro_goods_relation');
        $goods_relation_list = $model_goods_relation->getList(TRUE);
        $goods_id_list = array();
        $goods_class_selected_list = array();
        if(!empty($goods_relation_list) && is_array($goods_relation_list)) {
            foreach($goods_relation_list as $val) {
                $goods_class_selected_list[] = $val['shop_class_id'];
            }
        }

        $goods_class_child_array = array();
        if(!empty($goods_class_child) && is_array($goods_class_child)) {
            foreach($goods_class_child as $val) {
                if(in_array($val,$goods_class_selected_list)) {
                    $goods_class_list[$val]['selected'] = TRUE;
                }
                $goods_class_child_array[] = $goods_class_list[$val];
            }
        }
        echo json_encode($goods_class_child_array);
        die;

    }

    /**
     * ajax操作
     */
    public function ajax(){
        if ($_GET['branch'] == 'class_commend') {
            if(intval($_GET['id']) > 0) {
                $model= Model('micro_goods_class');
                $condition['class_id'] = intval($_GET['id']);
                $update[$_GET['column']] = trim($_GET['value']);
                $model->modify($update,$condition);
                echo 'true';die;
            } else {
                echo 'false';die;
            }
        }
    }
    private function show_menu_goods_class($menu_key) {
        $menu_array = array(
                'goods_class_list'=>array('menu_type'=>'link','menu_name'=>Language::get('spd_manage'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=GoodsClass&a=goodsclass_list'),
                'goods_class_add'=>array('menu_type'=>'link','menu_name'=>Language::get('spd_new'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=GoodsClass&a=goodsclass_add'),
        );
        if($menu_key == 'goods_class_edit') {
            $menu_array['goods_class_edit'] = array('menu_type'=>'link','menu_name'=>Language::get('spd_edit'),'menu_url'=>'###');
        }
        if($menu_key == 'goods_class_binding') {
            $menu_array['goods_class_binding'] = array('menu_type'=>'link','menu_name'=>Language::get('microshop_goods_class_binding'),'menu_url'=>'###');
        }
        $menu_array[$menu_key]['menu_type'] = 'text';
        $this->assign('menu',$menu_array);
    }

}
