<?php
/**
 * cms专题
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Page;

class SpecialController extends BaseHomeController{

    public function __construct() {
        parent::__construct();
        $this->assign('index_sign','special');
    }

    public function index() {
        $this->special_list();
    }

    /**
     * 专题列表
     */
    public function special_list() {
 
        $conition = array();
        $conition['special_state'] = 2;
        $model_special = Model('cms_special');
        $special_list = $model_special->getShopList($conition, 10, 'special_id desc');
        $this->assign('show_page', $model_special->showpage(2));
        $this->assign('special_list', $special_list);

        //分类导航
        $nav_link = array(
            0=>array(
                'title'=>Language::get('homepage'),
                'link'=>SHOP_SITE_URL
            ),
            1=>array(
                'title'=>'专题'
            )
        );
        $this->assign('nav_link_list', $nav_link);
 
        $this->render('special_list');
    }

    /**
     * 专题详细页
     */
    public function special_detail() {
		$special_id = intval($_GET['special_id']);
        $model_special = Model('cms_special');
        $special_detail = $model_special->getonlyOne($_GET['special_id']);
        $special_file = getCMSSpecialHtml($special_id);
		$seo_param = array();
        $seo_param['name'] = $special_detail['special_title'];
        $seo_param['key'] = $special_detail['special_stitle'];
        $seo_param['description'] = $special_detail['special_stitle'];
		 Model('seo')->type('product')->param($seo_param)->show();
        if($special_file) {
            $this->assign('special_file', $special_file);
            $this->assign('index_sign', 'special');
            $this->render('special_detail');
        } else {
            showMessage('专题不存在', '', '', 'error');
        }

    }
}
