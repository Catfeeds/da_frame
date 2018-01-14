<?php
/**
 * 图片空间操作
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Page;

class SnsCircleController extends BaseSNSController {
    public function __construct() {
        parent::__construct();
        /**
         * 读取语言包
         */
        Language::read('sns_circle,member_sns,sns_home');
        $this->assign('menu_sign', 'circle');

        $this->get_visitor();   // 获取访客
 
        $where = array();
        $where['name']  = !empty($this->master_info['member_truename'])?$this->master_info['member_truename']:$this->master_info['member_name'];
        Model('seo')->type('sns')->param($where)->show();

        $this->sns_messageboard();
    }
    /**
     * index 默认为话题
     */
    public function index(){
        $this->theme();
    }
    /**
     * 话题
     */
    public function theme(){
        $model = Model();
        $theme_list = $model->table('circle_theme')->where(array('member_id'=>$this->master_id))->page(10)->order('theme_id desc')->select();
        $this->assign('showpage', $model->showpage('2'));
        $this->assign('theme_list', $theme_list);
        if(!empty($theme_list)){
            $theme_list = array_under_reset($theme_list, 'theme_id');
            $themeid_array = array(); $circleid_array = array();
            foreach ($theme_list as $val){
                $themeid_array[]    = $val['theme_id'];
                $circleid_array[]   = $val['circle_id'];
            }
            $themeid_array = array_unique($themeid_array);
            $circleid_array = array_unique($circleid_array);
            // 附件
            $affix_list = $model->table('circle_affix')->where(array('affix_type'=>1, 'member_id'=>$this->master_id, 'theme_id'=>array('in', $themeid_array)))->select();
            $affix_list = array_under_reset($affix_list, 'theme_id', 2);
            $this->assign('affix_list', $affix_list);
        }

        $this->profile_menu('theme');
        $this->render('sns_circletheme');
    }
    /**
     * 圈子
     */
    public function circle(){
        $model = Model();
        $cm_list = $model->table('circle_member')->where(array('member_id'=>$this->master_id))->order('cm_jointime desc')->select();
        if(!empty($cm_list)){
            $cm_list = array_under_reset($cm_list, 'circle_id'); $circleid_array = array_keys($cm_list);
            $circle_list = $model->table('circle')->where(array('circle_id'=>array('in', $circleid_array)))->select();
            $this->assign('circle_list', $circle_list);
        }
        $this->profile_menu('circle');
        $this->render('sns_circle');
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key=''){
        $menu_array = array();

        $theme_menuname = $this->relation==3?L('sns_my_theme'):L('sns_TA_theme');
        $circle_menuname = $this->relation==3?L('sns_my_group'):L('sns_TA_group');
        $menu_array = array(
            1=>array('menu_key'=>'theme','menu_name'=>$theme_menuname,'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=SnsCircle&a=theme&mid='.$this->master_id),
            2=>array('menu_key'=>'circle','menu_name'=>$circle_menuname,'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=SnsCircle&a=circle&mid='.$this->master_id),
        );

        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}
