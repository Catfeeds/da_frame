<?php
/**
 * cms用户中心文章
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Cms\Controller;
use Common\Lib\Language;
use Cms\Controller\CMSMemberController;
use Common\Lib\Model;
use Common\Lib\Page;


class MemberArticleController extends CMSMemberController{

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->article_list();
    }

    /**
     * 文章列表
     */
    public function article_list() {
        $condition = array();
        if(!empty($_GET['article_state'])) {
            $condition['article_state'] = $_GET['article_state'];
        } else {
            $condition['article_state'] = array('in',array(self::ARTICLE_STATE_PUBLISHED, self::ARTICLE_STATE_VERIFY)) ;
        }
        $this->get_article_list($condition);
    }

    /**
     * 草稿列表
     */
    public function draft_list() {
        $condition = array();
        $condition['article_state'] = self::ARTICLE_STATE_DRAFT;
        $this->get_article_list($condition);
    }

    /**
     * 草稿列表
     */
    public function recycle_list() {
        $condition = array();
        $condition['article_state'] = self::ARTICLE_STATE_RECYCLE;
        $this->get_article_list($condition);
    }

    /**
     * 获得文章列表
     */
    private function get_article_list($condition = array()) {
        if(!empty($_GET['keyword'])) {
            $condition['article_title'] = array('like', '%'.$_GET['keyword'].'%');
        }
        $condition['article_type'] = $this->publisher_type;
        $condition['article_publisher_id'] = $this->publisher_id;
        $model_article = Model('cms_article');
        $article_list = $model_article->getList($condition, 20, 'article_id desc');
        $this->assign('show_page',$model_article->showpage(2));
        $this->assign('article_list', $article_list);

        $this->assign('article_state_list', $this->get_article_state_list());
        $this->assign('index_sign', 'article');
        $this->render('member_article_list', 'cms_member_layout');
    }

    /**
     * 文章编辑
     */
    public function article_edit() {
        $article_id = intval($_GET['article_id']);
        $article_detail = $this->check_article_auth($article_id);
        if($article_detail) {
            $model_article_class = Model('cms_article_class');
            $article_class_list = $model_article_class->getList(TRUE, null, 'class_sort asc');
            $this->assign('article_class_list', $article_class_list);

            $model_tag = Model('cms_tag');
            $tag_list = $model_tag->getList(TRUE, null, 'tag_sort asc');
            $this->assign('tag_list', $tag_list);

            //相关文章
            $article_link_list = $this->get_article_link_list($article_detail['article_link']);
            $this->assign('article_link_list', $article_link_list);

            //相关商品
            $article_goods_list = unserialize($article_detail['article_goods']);
            $this->assign('article_goods_list', $article_goods_list);

            $this->assign('article_detail', $article_detail);

            $this->render('publish_article','cms_member_layout');
        } else {
            showMessage(Language::get('wrong_argument'),'','','error');
        }
    }

    /**
     * 移到回收站
     */
    public function article_publish() {
        $this->article_state_change($this->publish_state);
    }

    /**
     * 移到回收站
     */
    public function article_recycle() {
        $this->article_state_change(self::ARTICLE_STATE_RECYCLE);
    }

    /**
     * 移到草稿箱
     */
    public function article_draft() {
        $this->article_state_change(self::ARTICLE_STATE_DRAFT);
    }

    /**
     * 删除
     */
    public function article_drop() {
        $article_id = intval($_GET['article_id']);
        $article_auth = $this->check_article_auth($article_id);
        if($article_auth) {
            $model_article = Model('cms_article');
            $result = $model_article->drop(array('article_id'=>$article_id));
            if($result) {
                showMessage(Language::get('spd_common_del_succ'),'');
            } else {
                showMessage(Language::get('spd_common_del_fail'),'','','error');
            }
        } else {
            showMessage(Language::get('wrong_argument'),'','','error');
        }
    }

    /**
     * 改变文章状态
 */
    private function article_state_change($article_state_new) {
        $article_id = intval($_GET['article_id']);
        $article_auth = $this->check_article_auth($article_id);
        if($article_auth) {
            $model_article = Model('cms_article');
            $result = $model_article->modify(array('article_state'=>$article_state_new),array('article_id'=>$article_id));
            showMessage(Language::get('spd_common_op_succ'),'');
        } else {
            showMessage(Language::get('spd_common_op_fail'),'','','error');
        }
    }
}
