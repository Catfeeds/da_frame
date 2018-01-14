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


namespace Cms\Controller;
use Common\Lib\Language;
use Cms\Controller\CMSHomeController;
use Common\Lib\Model;
use Common\Lib\Page;


class SpecialController extends CMSHomeController{

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
        $special_list = $model_special->getCMSList($conition, 10, 'special_id desc');
        $this->assign('show_page', $model_special->showpage(2));
        $this->assign('special_list', $special_list);
        $this->render('special_list');
    }

    /**
     * 专题详细页
     */
    public function special_detail() {
    	
        $special_file = getCMSSpecialHtml($_GET['special_id']);
        if($special_file) {
            $this->assign('special_file', $special_file);
            $this->assign('index_sign', 'special');
   
            $this->render('special_detail');
        } else {
            showMessage('专题不存在', '', '', 'error');
        }
    }
}
