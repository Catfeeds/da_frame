<?php
/**
 * 前台分类
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Page;


class CategoryController extends BaseHomeController {
    /**
     * 分类列表
     */
    public function index(){
        Language::read('home_category_index');
        $lang   = Language::getLangContent();
        //导航
        $nav_link = array(
            '0'=>array('title'=>$lang['homepage'],'link'=>SHOP_SITE_URL),
            '1'=>array('title'=>$lang['category_index_goods_class'])
        );
        $this->assign('nav_link_list',$nav_link);

        $this->assign('html_title',C('site_name').' - '.Language::get('category_index_goods_class'));
        $this->render('category');
    }
}
