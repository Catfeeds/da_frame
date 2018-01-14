<?php
/**
 * 微商城个人秀
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Microshop\Controller;
use Microshop\Controller\MircroShopController;
use Common\Lib\Language;
use Common\Lib\Model;


class PersonalController extends MircroShopController{

    public function __construct() {
        parent::__construct();
        $this->assign('index_sign','personal');
    }

    public function index(){
        $this->lists();
    }

    public function lists() {
        $model_class = Model('micro_personal_class');
        $class_list = $model_class->getList(TRUE,NULL,'class_sort asc');
        $this->assign('class_list',$class_list);

        if (empty($_GET['class_id']))
        {
        	$_GET['class_id'] = $class_list[0]['class_id'];
        }
        
        $condition = array();
        if(isset($_GET['keyword'])) {
            $condition['commend_message'] = array('like','%'.$_GET['keyword'].'%');
        }
        if(isset($_GET['class_id'])&&!empty($_GET['class_id'])) {
            $condition['class_id'] = $_GET['class_id'];
        }

        $order = 'microshop_sort asc,commend_time desc';
        if($_GET['order'] == 'hot') {
            $order = 'microshop_sort asc,click_count desc';
        }
        
        /*
        if (empty($condition)) {
        	$condition[1] = 1;
        }
        */
        
        self::get_personal_list($condition,$order);
        $this->assign('html_title',Language::get('spd_microshop_personal').'-'.Language::get('spd_microshop').'-'.C('site_name'));
        $this->render('personal_list');
    }

    public function detail() {

        $personal_id = intval($_GET['personal_id']);
        if($personal_id <= 0) {
            header('location: '.MICROSHOP_SITE_URL);die;
        }
        $model_personal = Model('micro_personal');
        $condition = array();
        $condition['personal_id'] = $personal_id;
        $detail = $model_personal->getOneWithUserInfo($condition);
        if(empty($detail)) {
            header('location: '.MICROSHOP_SITE_URL);die;
        }

        //点击数加1
        $update = array();
        $update['click_count'] = array('exp','click_count+1');
        $model_personal->modify($update,$condition);
        $this->assign('detail',$detail);

        //侧栏
        self::get_sidebar_list($detail['commend_member_id']);

        //获得分享app列表
        self::get_share_app_list();
        $this->assign('comment_id',$detail['personal_id']);
        $this->assign('comment_type','personal');
        $this->assign('html_title',$detail['commend_message'].'-'.Language::get('spd_microshop_personal').'-'.Language::get('spd_microshop').'-'.C('site_name'));
        $this->render('personal_detail');

    }

}
