<?php
/**
 * cms调用接口
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


class ApiController extends CMSHomeController{

    public function __construct() {
        parent::__construct();
    }

    /**
     * 商品列表
     */
    public function goods_list() {
        $model_goods = Model('goods');

        $condition = array();
        if($_GET['search_type'] == 'goods_url') {
            $condition['goods_id'] = intval($_GET['search_keyword']);
        } else {
            $condition['goods_name'] = array('like', '%' . $_GET['search_keyword'] . '%');
        }
        $goods_list = $model_goods->getGoodsOnlineList($condition, 'goods_id,goods_name,store_id,goods_image,goods_price', 10);
        $this->assign('show_page', $model_goods->showpage(2));
        $this->assign('goods_list', $goods_list);
        $this->render('api_goods_list', 'null_layout');
    }

    /**
     * 文章列表
     */
    public function article_list() {
        //获取文章列表
        $condition = array();
        if($_GET['search_type'] == 'article_id') {
            $condition['article_id'] = intval($_GET['search_keyword']);
        } else {
            $condition['article_title'] = array('like','%'.trim($_GET['search_keyword']).'%');
        }
        $condition['article_state'] = self::ARTICLE_STATE_PUBLISHED;

        $model_article = Model('cms_article');
        $article_list = $model_article->getList($condition , 10, 'article_id desc');
        $this->assign('show_page',$model_article->showpage(1));
        $this->assign('article_list', $article_list);
        $this->render('api_article_list','null_layout');
    }

    /**
     * 图片商品添加
     */
    public function goods_info_by_url() {
        $url = urldecode($_GET['url']);
        if(empty($url)) {
            self::return_json(Language::get('goods_not_exist'), 'false');
        }
        $model_goods_info = Model('goods_info_by_url');
        $result = $model_goods_info->get_goods_info_by_url($url);
        if($result) {
            self::echo_json($result);
        } else {
            self::return_json(Language::get('goods_not_exist'), 'false');
        }
    }

}
