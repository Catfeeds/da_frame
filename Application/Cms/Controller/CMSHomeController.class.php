<?php
/**
 * 前台control父类,店铺control父类,会员control父类
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Cms\Controller;
use Common\Lib\Language;
use Cms\Controller\CMSController;
use Common\Lib\Model;


class CMSHomeController extends CMSController{

    public function __construct() {
        parent::__construct();
        $model_navigation = Model('cms_navigation');
        $navigation_list = $model_navigation->getList(TRUE, null, 'navigation_sort asc');
        $this->assign('navigation_list', $navigation_list);

        $model_article_class = Model('cms_article_class');
        $article_class_list = $model_article_class->getList(TRUE, null, 'class_sort asc');
        $article_class_list = array_under_reset($article_class_list, 'class_id');
        $this->assign('article_class_list', $article_class_list);


        $model_picture_class = Model('cms_picture_class');
        $picture_class_list = $model_picture_class->getList(TRUE, null, 'class_sort asc');
        $picture_class_list = array_under_reset($picture_class_list, 'class_id');
        $this->assign('picture_class_list', $picture_class_list);

        $this->assign('index_sign','index');
        $this->assign('top_function_block',TRUE);
    }

    /**
     * 推荐文章
     */
    protected function get_article_comment() {

        $model_article = Model('cms_article');
        $condition = array();
        $condition['article_commend_flag'] = 1;
        $article_commend_list = $model_article->getListWithClassName($condition, NULL, 'article_id desc', '*', 9);
        $this->assign('article_commend_list', $article_commend_list);

    }

}



