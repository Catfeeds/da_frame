<?php
/**
 * 买家商品咨询
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
use Common\Lib\Db;
use Common\Lib\Model;
use Common\Lib\Page;

class MemberConsultController extends BaseMemberController {
    public function __construct() {
        parent::__construct();
        Language::read('member_store_consult_index');
    }

    /**
     * 商品咨询首页
     */
    public function index(){
        $this->my_consult();
    }

    /**
     * 查询买家商品咨询
     */
    public function my_consult(){
        //实例化商品咨询模型
        $consult    = Model('consult');
        $page   = new Page();
        $page->setEachNum(10);
        $page->setStyle('admin');
        $list_consult   = array();
        $search_array   = array();
        if($_GET['type'] != ''){
            if($_GET['type'] == 'to_reply'){
                if (C('dbdriver') == 'mysqli') {
                    $search_array['consult_reply']  = '';
                } else {
                    $search_array['consult_reply']  = array('exp', 'consult_reply IS NULL');
                }
            }
            if($_GET['type'] == 'replied'){
                if (C('dbdriver') == 'mysqli') {
                    $search_array['consult_reply']  = array('neq', '');
                } else {
                    $search_array['consult_reply']  = array('exp', 'consult_reply IS NOT NULL');
                }
            }
        }
        $search_array['member_id']  = "{$_SESSION['member_id']}";
        $list_consult   = $consult->getConsultList($search_array,$page);
        $this->assign('show_page',$page->show());
        $this->assign('list_consult',$list_consult);
        $_GET['type']   = empty($_GET['type'])?'consult_list':$_GET['type'];
        self::profile_menu('my_consult',$_GET['type']);
        $this->render('member_my_consult');
    }
    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function profile_menu($menu_type,$menu_key='',$array=array()) {
        Language::read('member_layout');
        $menu_array     = array();
        switch ($menu_type) {
            case 'my_consult':
                $menu_array = array(
                1=>array('menu_key'=>'consult_list',    'menu_name'=>Language::get('spd_member_path_all_consult'),           'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberConsult&a=my_consult'),
                2=>array('menu_key'=>'to_reply',    'menu_name'=>Language::get('spd_member_path_unreplied_consult'),         'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberConsult&a=my_consult&type=to_reply'),
                3=>array('menu_key'=>'replied', 'menu_name'=>Language::get('spd_member_path_replied_consult'),           'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberConsult&a=my_consult&type=replied'));
                break;
        }
        if(!empty($array)) {
            $menu_array[] = $array;
        }
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}
