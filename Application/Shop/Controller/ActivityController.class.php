<?php
/**
 * 活动
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


class ActivityController extends BaseHomeController {
    /**
     * 单个活动信息页
     */
    public function index(){
        //读取语言包
        Language::read('home_activity_index');
        //得到导航ID
        $nav_id = intval($_GET['nav_id']) ? intval($_GET['nav_id']) : 0 ;
        $this->assign('index_sign',$nav_id);
        //查询活动信息
        $activity_id = intval($_GET['activity_id']);
        if($activity_id<=0){
            showMessage(Language::get('para_error'),"{$GLOBALS['_PAGE_URL']}",'html','error');//'缺少参数:活动编号'
        }
        $activity   = Model('activity')->getOneById($activity_id);
        if(empty($activity) || $activity['activity_type'] != '1' || $activity['activity_state'] != 1 || $activity['activity_start_date']>time() || $activity['activity_end_date']<time()){
            showMessage(Language::get('activity_index_activity_not_exists'),"{$GLOBALS['_PAGE_URL']}",'html','error');//'指定活动并不存在'
        }
        $this->assign('activity',$activity);
        //查询活动内容信息
        $list   = array();
        $list   = Model('activity_detail')->getGoodsList(array('order'=>'activity_detail.activity_detail_sort asc','activity_id'=>"$activity_id",'goods_show'=>'1','activity_detail_state'=>'1'));

        $this->assign('list',$list);
        $this->assign('html_title',C('site_name').' - '.$activity['activity_title']);
        $this->render('activity_show');
    }
}
