<?php
/**
 * 平台客服
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
use Common\Lib\Page;
use Common\Lib\Validate;

class MemberMallconsultController extends BaseMemberController {
    public function __construct() {
        parent::__construct();
    }

    /**
     * 平台客服咨询首页
     */
    public function index(){
        // 咨询列表
        $model_mallconsult = Model('mall_consult');
        $consult_list = $model_mallconsult->getMallConsultList(array('member_id' => $_SESSION['member_id']), '*', '10');
        $this->assign('consult_list', $consult_list);
        $this->assign('show_page', $model_mallconsult->showpage());

        // 回复状态
        $this->typeState();

        $this->profile_menu('consult_list');
        $this->render('member_mallconsult.list');
    }

    /**
     * 平台咨询详细
     */
    public function mallconsult_info() {
        $id = intval($_GET['id']);
        if ($id <= 0) {
            showMessage(L('wrong_argument'), '', '', 'error');
        }
        // 咨询详细信息
        $consult_info = Model('mall_consult')->getMallConsultInfo(array('mc_id' => $id, 'member_id' => $_SESSION['member_id']));
        $this->assign('consult_info', $consult_info);

        // 咨询类型列表
        $type_list = Model('mall_consult_type')->getMallConsultTypeList(array(), 'mct_id,mct_name', 'mct_id');
        $this->assign('type_list', $type_list);

        // 回复状态
        $this->typeState();

        $this->render('member_mallconsult.info');
    }

    /**
     * 添加平台客服咨询
     */
    public function add_mallconsult() {
        // 咨询类型列表
        $type_list = Model('mall_consult_type')->getMallConsultTypeList(array());
        $this->assign('type_list', $type_list);
        if ($_GET['inajax']) {
            $this->render('member_mallconsult.add_inajax', 'null_layout');
        } else {
            $this->render('member_mallconsult.add');
        }
    }

    /**
     * 保存平台咨询
     */
    public function save_mallconsult() {
        if (!chksubmit()) {
            showDialog(L('wrong_argument'), 'reload');
        }

        //验证表单信息
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input"=>$_POST["type_id"],"require"=>"true","validator"=>"Number","message"=>"请选择咨询类型"),
            array("input"=>$_POST["consult_content"],"require"=>"true","message"=>"请填写咨询内容")
        );
        $error = $obj_validate->validate();
        if ($error != ''){
            showDialog($error);
        }

        $insert = array();
        $insert['mct_id'] = $_POST['type_id'];
        $insert['member_id'] = $_SESSION['member_id'];
        $insert['member_name'] = $_SESSION['member_name'];
        $insert['mc_content'] = $_POST['consult_content'];

        $result = Model('mall_consult')->addMallConsult($insert);
        if ($result) {
            showDialog(L('spd_common_op_succ'), 'reload', 'succ');
        } else {
            showDialog(L('spd_common_op_fail'), 'reload');
        }
    }

    /**
     * 删除平台客服咨询
     */
    public function del_mallconsult() {
        $id = intval($_GET['id']);
        if ($id <= 0) {
            showDialog(L('wrong_argument'));
        }

        $result = Model('mall_consult')->delMallConsult(array('mc_id' => $id, 'member_id' => $_SESSION['member_id']));
        if ($result) {
            showDialog(L('spd_common_del_succ'), 'reload', 'succ');
        } else {
            showDialog(L('spd_common_del_fail'));
        }
    }

    /**
     * 咨询的回复状态
     */
    private function typeState() {
        $state = array('0'=>'未回复', '1'=>'已回复');
        $this->assign('state', $state);
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_key  当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key='') {
        $menu_array = array(
            1=>array('menu_key'=>'consult_list', 'menu_name'=>'平台客服咨询列表', 'menu_url'=>urlShop('member_mallconsult', 'index')),
        );
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}
