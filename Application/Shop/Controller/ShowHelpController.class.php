<?php
/**
 * 店铺帮助
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
use Common\Lib\Model;

class ShowHelpController extends BaseHomeController {
    public function __construct() {
        parent::__construct();
        $this->assign('show_sign','help');
    }
    /**
     * 店铺帮助页
     *
     */
    public function index() {
        $model_help = Model('help');
        $list = $model_help->getShowStoreHelpList();
        $type_id = intval($_GET['t_id']);//帮助类型编号
        if ($type_id < 1 || empty($list[$type_id])) {
            $type_array = current($list);
            $type_id = $type_array['type_id'];
        }
        $this->assign('type_id',$type_id);
        $help_id = intval($_GET['help_id']);//帮助编号
        if ($help_id < 1 || empty($list[$type_id]['help_list'][$help_id])) {
            $help_array = current($list[$type_id]['help_list']);
            $help_id = $help_array['help_id'];
        }
        $this->assign('help_id',$help_id);
        $help = $list[$type_id]['help_list'][$help_id];
        $this->assign('list',$list);//左侧帮助类型及帮助
        $this->assign('help',$help);//当前帮助
        $this->assign('article_list','');//底部不显示首页的文章分类
        $phone_array = explode(',',C('site_phone'));
        $this->assign('phone_array',$phone_array);
        $this->assign('html_title',C('site_name').' - '.'商家帮助指南');
        $this->setLayout('store_joinin_layout');
        $this->render('store_help');
    }

}
