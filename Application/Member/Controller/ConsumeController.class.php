<?php
/**
 * 消费记录
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Member\Controller;
use Member\Controller\BaseMemberControl;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Page;


class ConsumeController extends BaseMemberControl {
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $model_consume = Model('consume');
        $consume_list = $model_consume->getConsumeList(array('member_id' => $_SESSION['member_id']), '*', 20);
        $this->assign('show_page', $model_consume->showpage());
        $this->assign('consume_list', $consume_list);
        $this->assign('consume_type', $this->type);
        $this->profile_menu('consume', 'consume');
        $this->render('consume.list');
    }
    
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type,$menu_key='') {
        $menu_array = array();
        switch ($menu_type) {
            case 'consume':
                $menu_array = array(
                1=>array('menu_key'=>'consume','menu_name'=>'消费记录',   'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=Consume'));
                break;
        }
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}
