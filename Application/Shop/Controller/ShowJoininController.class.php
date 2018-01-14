<?php
/**
 * 店铺开店
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
use Common\Lib\Log;
use Common\Lib\Model;

class ShowJoininController extends BaseHomeController {
    public function __construct() {
        parent::__construct();
    }
    /**
     * 店铺开店页
     *
     */
    public function index() {
        Language::read("home_login_index");
        $code_info = C('store_joinin_pic');
        $info['pic'] = array();
        if(!empty($code_info)) {
            $info = unserialize($code_info);
        }
		//5.3 shopda 个人企业入驻处理
		$model_store_joinin = Model('store_joinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id'=>$_SESSION['member_id']));
		if ($joinin_detail['is_person']) {
			$this->assign('store_zizhi','is_person');
		}
        $this->assign('pic_list',$info['pic']);//首页图片
        $this->assign('show_txt',$info['show_txt']);//贴心提示
        $model_help = Model('help');
        $condition['type_id'] = '4';//入驻指南
        $help_list = $model_help->getHelpList($condition,'',4);//显示4个
        $this->assign('help_list',$help_list);
        $this->assign('article_list','');//底部不显示文章分类
        $this->assign('show_sign','joinin');
        $this->assign('html_title',C('site_name').' - '.'商家入驻');
        $this->setLayout('store_joinin_layout');
        $this->render('store_joinin');
    }

}
