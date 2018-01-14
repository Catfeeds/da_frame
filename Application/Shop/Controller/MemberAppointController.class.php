<?php
/**
 * 买家 预约/到货通知
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;


class MemberAppointController extends BaseMemberController {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 买家我的订单，以总订单pay_sn来分组显示
     *
     */
    public function index() {
        $model_arrtivalnotice = Model('arrival_notice');
        $appoint_list = $model_arrtivalnotice->getArrivalNoticeList(array('member_id' => $_SESSION['member_id']), '*', '', '15');
        $this->assign('appoint_list', $appoint_list);
        $this->assign('show_page', $model_arrtivalnotice->showpage());
        self::profile_menu('member_appoint');
        $this->render('member_appoint.index');
    }
    
    /**
     * 删除
     */
    public function del_appoint() {
        $id = intval($_GET['id']);
        $model_arrtivalnotice = Model('arrival_notice');
        $model_arrtivalnotice->delArrivalNotice(array('member_id' => $_SESSION['member_id'], 'an_id' => $id));
        showDialog('操作成功', 'reload', 'succ');
    }
    
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key='') {
        $menu_array = array(
            array('menu_key'=>'member_appoint','menu_name'=>'预约/到货通知', 'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberAppoint')
        );
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}
