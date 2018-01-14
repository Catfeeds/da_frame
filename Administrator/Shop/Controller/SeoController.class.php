<?php
/**
 * 网站设置
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
use Common\Lib\Cache;
use Common\Lib\Log;
use Common\Lib\Model;


class SeoController extends SystemController {

    public function __construct(){
        parent::__construct();
        Language::read('setting', 'Home');
    }

    public function index() {
        $this->seo();
    }

    /**
     * SEO与rewrite设置
     */
    public function seo(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['rewrite_enabled'] = $_POST['rewrite_enabled'];
            $result = $model_setting->updateSetting($update_array);
            if ($result === true){
                $this->log(L('spd_edit,spd_seo_set'),1);
                showMessage(L('spd_common_save_succ'));
            }else {
                $this->log(L('spd_edit,spd_seo_set'),0);
                showMessage(L('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();

        //读取SEO信息
        $list = Model('seo')->select();
        $seo = array();
        foreach ((array)$list as $value) {
            $seo[$value['type']] = $value;
        }

        $this->assign('list_setting',$list_setting);
        $this->assign('seo',$seo);

        $category = Model('goods_class')->getGoodsClassForCacheModel();
        $this->assign('category',$category);
		$this->setDirquna('shop');

        $this->render('seo.setting');
    }

    public function ajax_category(){
        $model = Model('goods_class');
        $list = $model->field('gc_title,gc_keywords,gc_description')->where(array('gc_id' => intval($_GET['id'])))->find();
        //转码
        if (strtoupper(CHARSET) == 'GBK'){
            $list = Language::getUTF8($list);//网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
        }
        echo json_encode($list);exit();
    }

    /**
     * SEO设置保存
     */
    public function seo_update(){
        $model_seo = Model('seo');
        if (chksubmit()){
            $update = array();
            if (is_array($_POST['SEO'][0])){
                $seo = $_POST['SEO'][0];
            }else{
                $seo = $_POST['SEO'];
            }
            foreach ((array)$seo as $key=>$value) {
                $model_seo->where(array('type'=>$key))->update($value);
            }
            dkcache('seo');
            showMessage(L('spd_common_save_succ'));
        }else{
            showMessage(L('spd_common_save_fail'));
        }
    }

    /**
     * 分类SEO保存
     *
     */
    public function seo_category(){
        if (chksubmit()){
            $where = array('gc_id' => intval($_POST['category']));
            $input = array();
            $input['gc_title'] = $_POST['cate_title'];
            $input['gc_keywords'] = $_POST['cate_keywords'];
            $input['gc_description'] = $_POST['cate_description'];
            if (Model('goods_class')->editGoodsClass($input, $where)){
                dkcache('goods_class_seo');
                showMessage(L('spd_common_save_succ'));
            }
        }
        showMessage(L('spd_common_save_fail'));
    }
}
