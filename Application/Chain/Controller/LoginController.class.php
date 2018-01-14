<?php
/**
 * 物流自提服务站首页
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Chain\Controller;
use Chain\Controller\BaseAccountCenterController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;


class LoginController extends BaseAccountCenterController{
    public function __construct(){
        parent::__construct();
    }
    /**
     * 登录
     */
    public function index() {
        if ($_SESSION['chain_login'] == 1) {
            @header("location: " . $GLOBALS['_PAGE_URL'] . "&c=Index");die;
        }
        if (chksubmit(true, true, 'alert', 'change_seccode();')) {
            $where = array();
            $where['chain_user'] = $_POST['user'];
            $where['chain_pwd'] = md5($_POST['pwd']);
            $chain_info = Model('chain')->getChainInfo($where);
            if (!empty($chain_info)) {
                $_SESSION['chain_login']    = 1;
                $_SESSION['chain_id']       = $chain_info['chain_id'];
                $_SESSION['chain_store_id'] = $chain_info['store_id'];
                $_SESSION['chain_user']     = $chain_info['chain_user'];
                $_SESSION['chain_name']     = $chain_info['chain_name'];
                $_SESSION['chain_img']      = getChainImage($chain_info['chain_img'], $chain_info['store_id']);
                $_SESSION['chain_address']  = $chain_info['area_info'] . ' ' . $chain_info['chain_address'];
                $_SESSION['chain_phone']    = $chain_info['chain_phone'];
                showDialog('登录成功', "{$GLOBALS['_PAGE_URL']}&c=Index", 'succ');
            } else {
                showDialog('门店账户不存在或密码错误', "", "error", "change_seccode();");
            }
        }
        $this->render('login');
    }
    /**
     * 登出
     */
    public function logout() {
        unset($_SESSION['chain_login']);
        unset($_SESSION['chain_id']);
        unset($_SESSION['chain_store_id']);
        unset($_SESSION['chain_user']);
        unset($_SESSION['chain_name']);
        unset($_SESSION['chain_img']);
        unset($_SESSION['chain_address']);
        unset($_SESSION['chain_phone']);
        showDialog('退出成功', 'reload', 'succ');
    }
}
