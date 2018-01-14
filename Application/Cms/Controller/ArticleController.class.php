<?php
/**
 * cms文章
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Cms\Controller;
use Common\Lib\Language;
use Cms\Controller\CMSHomeController;
use Common\Lib\Model;
use Common\Lib\Page;


class ArticleController extends CMSHomeController{

    public function __construct() {
        parent::__construct();
        $this->assign('index_sign', 'article');
    }

    public function index() {
        $this->article_list();
    }

    /**
     * 文章列表
     */
    public function article_list() {
        //获取文章列表
        if(empty($_GET['type'])) {
            $page_number = 10;
            $template_name = 'article_list';
        } else {
            $page_number = 40;
            $template_name = 'article_list.modern';
        }
        $condition = array();
        if(!empty($_GET['class_id'])) {
            $condition['article_class_id'] = intval($_GET['class_id']);
        }
        $condition['article_state'] = self::ARTICLE_STATE_PUBLISHED;
        $model_article = Model('cms_article');
        $article_list = $model_article->getList($condition, $page_number, 'article_sort asc, article_id desc');
        $this->assign('show_page', $model_article->showpage(2));
        $this->assign('article_list', $article_list);
        $this->get_article_sidebar();

        $this->render($template_name);
    }

    /**
     * 文章详情
     */
    public function article_detail() {
        $article_id = intval($_GET['article_id']);
        if($article_id <= 0) {
            showMessage(Language::get('wrong_argument'),'','','error');
        }

        $model_article = Model('cms_article');
        $article_detail = $model_article->getOne(array('article_id'=>$article_id));
        if(empty($article_detail)) {
            showMessage(Language::get('article_not_exist'), CMS_SITE_URL, '', 'error');
        }

        //相关文章
        $article_link_list = $this->get_article_link_list($article_detail['article_link']);
        $this->assign('article_link_list', $article_link_list);

        //相关商品
        $article_goods_list = unserialize($article_detail['article_goods']);
        $this->assign('article_goods_list', $article_goods_list);

        //计数加1
        $model_article->modify(array('article_click'=>array('exp','article_click+1')),array('article_id'=>$article_id));

        //文章心情
        $article_attitude_list = array();
        $article_attitude_list[1] = Language::get('attitude1');
        $article_attitude_list[2] = Language::get('attitude2');
        $article_attitude_list[3] = Language::get('attitude3');
        $article_attitude_list[4] = Language::get('attitude4');
        $article_attitude_list[5] = Language::get('attitude5');
        $article_attitude_list[6] = Language::get('attitude6');
        $this->assign('article_attitude_list', $article_attitude_list);

        //分享
        $this->get_share_app_list();
 
        $this->assign('article_detail', $article_detail);
        $this->assign('detail_object_id', $article_id);
        $this->get_article_sidebar();

        //seo
        $this->assign('seo_title', $article_detail['article_title']);

        $this->render('article_detail');
    }

    /**
     * 文章评论
     */
    public function article_comment_detail() {
        $article_id = intval($_GET['article_id']);
        if($article_id <= 0) {
            showMessage(Language::get('wrong_argument'),'','','error');
        }

        $model_article = Model('cms_article');
        $article_detail = $model_article->getOne(array('article_id'=>$article_id));
        if(empty($article_detail)) {
            showMessage(Language::get('article_not_exist'), CMS_SITE_URL, '', 'error');
        }

        $article_hot_comment = $model_article->getList(array('article_state'=>self::ARTICLE_STATE_PUBLISHED), null, 'article_comment_count desc', '*', 10);
        $this->assign('hot_comment', $article_hot_comment);

        $this->assign('article_detail', $article_detail);
        $this->assign('detail_object_id', $article_id);
        $this->assign('comment_all', 'all');

        //推荐文章
        $this->get_article_comment();

        $this->render('comment_detail');
    }


    /**
     * 文章列表
     */
    public function article_search() {
        $condition = array();
        $condition['article_title'] = array("like",'%'.trim($_GET['keyword']).'%');
        $condition['article_state'] = self::ARTICLE_STATE_PUBLISHED;
        $model_article = Model('cms_article');
        $article_list = $model_article->getList($condition, 20, 'article_sort asc, article_id desc');
        $this->assign('show_page', $model_article->showpage(2));
        $this->assign('total_num', $model_article->gettotalnum());
        $this->assign('article_list', $article_list);
        $this->get_article_sidebar();

        $this->render('search_article');
    }

    /**
     * 根据标签搜索
     */
    public function article_tag_search() {
        $article_list = array();
        if(intval($_GET['tag_id']) > 0) {
            $model_article = Model('cms_article');

            $condition = array();
            $condition['relation_tag_id'] = $_GET['tag_id'];
            $condition['article_state'] = self::ARTICLE_STATE_PUBLISHED;
            $article_list = $model_article->getListByTagID($condition, 20, 'article_sort asc, article_id desc');

            $this->assign('show_page', $model_article->showpage(2));
            $this->assign('total_num', $model_article->gettotalnum());
        }

        $this->assign('article_list', $article_list);
        $this->get_article_sidebar();

        $this->render('search_article');
    }

    /**
     * 文章侧栏
     */
    private function get_article_sidebar() {

        $model_tag = Model('cms_tag');
        $model_article = Model('cms_article');

        //标签
        $cms_tag_list = $model_tag->getList(TRUE, null, 'tag_sort asc', '', 10);
        $cms_tag_list = array_under_reset($cms_tag_list, 'tag_id');
        $this->assign('cms_tag_list', $cms_tag_list);

        //推荐文章(图文)
        $condition = array();
        $condition['article_commend_image_flag'] = 1;
        $article_commend_image_list = $model_article->getList($condition, null, 'article_id desc', '*', 3);
        $this->assign('article_commend_image_list', $article_commend_image_list);

        //推荐文章
        $this->get_article_comment();

    }

}
