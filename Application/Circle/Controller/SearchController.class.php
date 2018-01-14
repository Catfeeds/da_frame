<?php
/**
 * 圈子首页
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Circle\Controller;
use Circle\Controller\BaseCircleController;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Page;


class SearchController extends BaseCircleController {
    public function __construct(){
        parent::__construct();
        Language::read('circle');
        $this->themeTop();
    }
    /**
     * 话题搜索
     */
    public function theme(){
        $model = Model();
        $where = array();
        if($_GET['keyword'] != ''){
            $where['theme_name'] = array('like', '%'.$_GET['keyword'].'%');
        }
        $count = $model->table('circle_theme')->where($where)->count();
        $theme_list = $model->table('circle_theme')->where($where)->page(10,$count)->order('theme_addtime desc')->select();
        $this->assign('count', $count);
        $this->assign('show_page', $model->showpage('2'));
        $this->assign('theme_list', $theme_list);
        $this->assign('search_sign', 'theme');

        $this->circleSEO(L('search_theme'));
        $this->render('search.theme');
    }
    /**
     * 圈子搜索
     */
    public function group(){
        $model = Model();
        $where = array();
        $where['circle_status'] = 1;
        if($_GET['keyword'] != ''){
            $where['circle_name|circle_tag'] = array('like', '%'.$_GET['keyword'].'%');
        }
        if(intval($_GET['class_id']) > 0){
            $where['class_id'] = intval($_GET['class_id']);
        }
        $count = $model->table('circle')->where($where)->count();
        $circle_list = $model->table('circle')->where($where)->page(10,$count)->select();
        $this->assign('count', $count);
        $this->assign('circle_list', $circle_list);
        $this->assign('show_page', $model->showpage('2'));
        $this->assign('search_sign', 'group');

        $this->circleSEO(L('search_circle'));
        $this->render('search.group');
    }
}
