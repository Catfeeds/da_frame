<?php
/**
 * cms文章分类
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
use Common\Lib\Validate;


class CmsTagController extends SystemController {

    public function __construct(){
        parent::__construct();
        Language::read('cms', 'Home');
    }

    public function index() {
        $this->cms_tag_list();
    }

    /**
     * cms文章分类列表
     **/
    public function cms_tag_list() {
        $model = Model('cms_tag');
        $list = $model->getList(TRUE, null, 'tag_id desc');
        $this->show_menu('list');
        $this->assign('list',$list);
        $this->setDirquna('cms');
$this->render("cms_tag.list");
    }

    /**
     * cms文章分类添加
     **/
    public function cms_tag_add() {
        $this->show_menu('add');
        $this->setDirquna('cms');
$this->render('cms_tag.add');
    }

    /**
     * cms文章分类保存
     **/
    public function cms_tag_save() {
        $obj_validate = new Validate();
        $validate_array = array(
            array('input'=>$_POST['tag_name'],'require'=>'true',"validator"=>"Length","min"=>"1","max"=>"20",'message'=>Language::get('tag_name_error')),
            array('input'=>$_POST['tag_sort'],'require'=>'true','validator'=>'Range','min'=>0,'max'=>255,'message'=>Language::get('tag_sort_error')),
        );
        $obj_validate->validateparam = $validate_array;
        $error = $obj_validate->validate();
        if ($error != ''){
            showMessage(Language::get('error').$error,'','','error');
        }

        $param = array();
        $param['tag_name'] = trim($_POST['tag_name']);
        $param['tag_sort'] = intval($_POST['tag_sort']);
        $model_class = Model('cms_tag');
        $result = $model_class->save($param);
        if($result) {
            $this->log(Language::get('cms_log_tag_save').$result, 1);
            showMessage(Language::get('tag_add_success'), $GLOBALS['_PAGE_URL'] . '&c=CmsTag&a=cms_tag_list');
        } else {
            $this->log(Language::get('cms_log_tag_save').$result, 0);
            showMessage(Language::get('tag_add_fail'), $GLOBALS['_PAGE_URL'] . '&c=CmsTag&a=cms_tag_list','','error');
        }


    }

    /**
     * cms标签排序修改
     */
    public function update_tag_sort() {
        $new_sort = intval($_GET['value']);
        if ($new_sort > 255){
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('class_sort_error')));
            die;
        } else {
            $this->update_tag('tag_sort', $new_sort);
        }
    }

    /**
     * cms标签标题修改
     */
    public function update_tag_name() {
        $new_value = trim($_GET['value']);
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array('input'=>$new_value,'require'=>'true',"validator"=>"Length","min"=>"1","max"=>"10",'message'=>Language::get('tag_name_error')),
        );
        $error = $obj_validate->validate();
        if ($error != ''){
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('tag_name_error')));
            die;
        } else {
            $this->update_tag('tag_name', $new_value);
        }
    }

    /**
     * cms标签修改
     */
    private function update_tag($column, $new_value) {
        $tag_id = intval($_GET['id']);
        if($tag_id <= 0) {
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('param_error')));
            die;
        }

        $model = Model("cms_tag");
        $result = $model->modify(array($column=>$new_value),array('tag_id'=>$tag_id));
        if($result) {
            echo json_encode(array('result'=>TRUE, 'message'=>'success'));
            die;
        } else {
            echo json_encode(array('result'=>FALSE, 'message'=>Language::get('spd_common_save_fail')));
            die;
        }
    }

    /**
     * cms标签删除
     **/
     public function cms_tag_drop() {
        $tag_id = trim($_POST['tag_id']);
        $model = Model('cms_tag');
        $condition = array();
        $condition['tag_id'] = array('in',$tag_id);
        $result = $model->drop($condition);
        if($result) {
            $this->log(Language::get('cms_log_tag_drop').$_POST['tag_id'], 1);
            showMessage(Language::get('tag_drop_success'),'');
        } else {
            $this->log(Language::get('cms_log_tag_drop').$_POST['tag_id'], 0);
            showMessage(Language::get('tag_drop_fail'),'','','error');
        }

     }

    private function show_menu($menu_key) {
        $menu_array = array(
            'list'=>array('menu_type'=>'link','menu_name'=>Language::get('spd_list'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=CmsTag&a=cms_tag_list'),
            'add'=>array('menu_type'=>'link','menu_name'=>Language::get('spd_new'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=CmsTag&a=cms_tag_add'),
        );
        $menu_array[$menu_key]['menu_type'] = 'text';
        $this->assign('menu',$menu_array);
    }


}
