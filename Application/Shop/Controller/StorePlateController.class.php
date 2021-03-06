<?php
/**
 * 关联版式
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

class StorePlateController extends BaseSellerController {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->plate_list();
    }

    /**
     * 关联版式列表
     */
    public function plate_list() {
        // 版式列表
        $where = array();
        $where['store_id'] = $_SESSION['store_id'];
        if (trim($_GET['p_name']) != '') {
            $where['plate_name'] = array('like', '%'.trim($_GET['p_name']).'%');
        }
        if (in_array($_GET['p_position'], array('0','1'))) {
            $where['plate_position'] = $_GET['p_position'];
        }
        $store_plate = Model('store_plate');
        $plate_list = $store_plate->getStorePlateList($where, '*', 10);
        $this->assign('show_page', $store_plate->showpage(2));
        $this->assign('plate_list', $plate_list);
        $this->assign('position', array(0=> '底部', 1 => '顶部'));

        $this->profile_menu('plate_list', 'plate_list');
        $this->render('store_plate.list');
    }

    /**
     * 关联版式添加
     */
    public function plate_add() {
        if (chksubmit()) {
            // 验证表单
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                    array("input" => $_POST["p_name"], "require" => "true", "message" => '请填写版式名称'),
                    array("input" => $_POST["p_content"], "require" => "true", "message" => '请填写版式内容')
            );
            $error = $obj_validate->validate();
            if ($error != '') {
                showDialog(L('error') . $error, urlShop('store_plate', 'index'));
            }
            $insert = array();
            $insert['plate_name']     = $_POST['p_name'];
            $insert['plate_position'] = $_POST['p_position'];
            $insert['plate_content']  = $_POST['p_content'];
            $insert['store_id']       = $_SESSION['store_id'];
            $result = Model('store_plate')->addStorePlate($insert);
            if ($result) {
                showDialog(L('spd_common_op_succ'), urlShop('store_plate', 'index'),'succ');
            } else {
                showDialog(L('spd_common_op_fail'), urlShop('store_plate', 'index'));
            }
        }
        // 是否能使用编辑器
        if(checkPlatformStore()){ // 平台店铺可以使用编辑器
            $editor_multimedia = true;
        } else {    // 三方店铺需要
            $editor_multimedia = false;
            if ($this->store_grade['sg_function'] == 'editor_multimedia') {
                $editor_multimedia = true;
            }
        }
        $this->assign('editor_multimedia', $editor_multimedia);
        $this->profile_menu('plate_add', 'plate_add');
        $this->render('store_plate.add');
    }

    /**
     * 关联版式编辑
     */
    public function plate_edit() {
        if (chksubmit()) {
            $plate_id = intval($_POST['p_id']);
            if ($plate_id <= 0) {
                showMessage(L('wrong_argument'), '', '', 'error');
            }
            // 验证表单
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                    array("input" => $_POST["p_name"], "require" => "true", "message" => '请填写版式名称'),
                    array("input" => $_POST["p_content"], "require" => "true", "message" => '请填写版式内容')
            );
            $error = $obj_validate->validate();
            if ($error != '') {
                showDialog(L('error') . $error, urlShop('store_plate', 'index'));
            }
            $update = array();
            $update['plate_name']     = $_POST['p_name'];
            $update['plate_position'] = $_POST['p_position'];
            $update['plate_content']  = $_POST['p_content'];

            $where = array();
            $where['plate_id']  = $plate_id;
            $where['store_id']  = $_SESSION['store_id'];
            $result = Model('store_plate')->editStorePlate($update, $where);
            if ($result) {
                showDialog(L('spd_common_op_succ'), urlShop('store_plate', 'index'),'succ');
            } else {
                showDialog(L('spd_common_op_fail'), urlShop('store_plate', 'index'));
            }
        }
        $plate_id = intval($_GET['p_id']);
        if ($plate_id <= 0) {
            showMessage(L('wrong_argument'), '', '', 'error');
        }
        $plate_info = Model('store_plate')->getStorePlateInfo(array('plate_id' => $plate_id, 'store_id' => $_SESSION['store_id']));
        $this->assign('plate_info', $plate_info);

        $this->profile_menu('plate_edit', 'plate_edit');
        $this->render('store_plate.add');
    }

    /**
     * 删除关联版式
     */
    public function drop_plate() {
        $plate_id = $_GET['p_id'];
        if (!preg_match('/^[\d,]+$/i', $plate_id)) {
            showDialog(L('wrong_argument'), '', 'error');
        }
        $plateid_array = explode(',', $plate_id);
        $return = Model('store_plate')->delStorePlate(array('plate_id' => array('in', $plateid_array), 'store_id' => $_SESSION['store_id']));
        if ($return) {
            showDialog(L('spd_common_del_succ'), 'reload', 'succ');
        } else {
            showDialog(L('spd_common_del_fail'), '', 'error');
        }
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
        $menu_array = array();
        switch ($menu_type) {
            case 'plate_list':
                $menu_array = array(
                    array('menu_key' => 'plate_list', 'menu_name' => '版式列表', 'menu_url' => urlShop('store_plate', 'plate_list'))
                );
                break;
            case 'plate_add':
                $menu_array = array(
                    array('menu_key' => 'plate_list', 'menu_name' => '版式列表', 'menu_url' => urlShop('store_plate', 'plate_list')),
                    array('menu_key' => 'plate_add', 'menu_name' => '添加版式', 'menu_url' => urlShop('store_plate', 'plate_add'))
                );
                break;
            case 'plate_edit':
                $menu_array = array(
                    array('menu_key' => 'plate_list', 'menu_name' => '版式列表', 'menu_url' => urlShop('store_plate', 'plate_list')),
                    array('menu_key' => 'plate_add', 'menu_name' => '添加版式', 'menu_url' => urlShop('store_plate', 'plate_add')),
                    array('menu_key' => 'plate_edit', 'menu_name' => '编辑版式', 'menu_url' => urlShop('store_plate', 'plate_edit'))
                );
                break;
        }
        if(!empty($array)) {
            $menu_array[] = $array;
        }
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}
