<?php
/**
 * 默认展示页面
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Home\Controller;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;


class IndexController extends SystemController{
    public function __construct(){
        parent::__construct();
        Language::read('index', 'Home');
    }
    public function index(){
        //输出管理员信息
        $this->assign('admin_info',$this->getAdminInfo());
        //输出菜单
        $result = $this->getNav();
        list($top_nav, $left_nav, $map_nav, $quicklink) = $result;
        $this->assign('top_nav',$top_nav);
        $this->assign('left_nav',$left_nav);
        $this->assign('map_nav',$map_nav);
        // 快捷菜单
        $this->assign('quicklink', $quicklink);

        $this->render('index','index_layout');
    }

    /**
     * 退出
     */
    public function logout(){
        setDaCookie('sys_key','',-1,'',null);
        @header("Location: {$GLOBALS['_PAGE_URL']}");
        exit;
    }
    /**
     * 修改密码
     */
    public function modifypw(){
        if (chksubmit()){
            if (trim($_POST['new_pw']) !== trim($_POST['new_pw2'])){
                //showMessage('两次输入的密码不一致，请重新输入');
                showMessage(Language::get('index_modifypw_repeat_error'));
            }
            $admininfo = $this->getAdminInfo();
            //查询管理员信息
            $admin_model = Model('admin');
            $admininfo = $admin_model->getOneAdmin($admininfo['id']);
            if (!is_array($admininfo) || count($admininfo)<= 0){
                showMessage(Language::get('index_modifypw_admin_error'));
            }
            //旧密码是否正确
            if ($admininfo['admin_password'] != md5(trim($_POST['old_pw']))){
                showMessage(Language::get('index_modifypw_oldpw_error'));
            }
            $new_pw = md5(trim($_POST['new_pw']));
            $update = array();
            $update['admin_password'] = $new_pw;
            $update['admin_id'] = $admininfo['admin_id'];
            $result = $admin_model->updateAdmin($update);
            if ($result){
                showDialog(Language::get('index_modifypw_success'), urlAdmin('index', 'logout'), 'succ');
            }else{
                showMessage(Language::get('index_modifypw_fail'));
                showDialog(Language::get('index_modifypw_fail'), '', '', 'CUR_DIALOG.click();');
            }
        }else{
            Language::read('admin', 'Home');
            $this->render('admin.modifypw', 'null_layout');
        }
    }
    
    public function save_avatar() {
        $admininfo = $this->getAdminInfo();
        $admin_model = Model('admin');
        $admininfo = $admin_model->getOneAdmin($admininfo['id']);
        if ($_GET['avatar'] == '') {
            echo false;die;
        }
        @unlink(BASE_UPLOAD_PATH . '/' . ATTACH_ADMIN_AVATAR . '/' . cookie('admin_avatar'));
        $update['admin_avatar'] = $_GET['avatar'];
        $update['admin_id'] = $admininfo['admin_id'];
        $result = $admin_model->updateAdmin($update);
        if ($result) {
            setDaCookie('admin_avatar',$_GET['avatar'],86400 * 365,'',null);
        }
        echo $result;die;
    }
}
