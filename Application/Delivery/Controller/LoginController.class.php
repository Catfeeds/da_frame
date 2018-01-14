<?php
/**
 * 物流自提服务站首页
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Delivery\Controller;
use Delivery\Controller\BaseAccountCenterController;
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
        if ($_SESSION['delivery_login'] == 1) {
            @header("location: {$GLOBALS['_PAGE_URL']}&c=DCenter");die;
        }
        if (chksubmit()) {
            $where = array();
            $where['dlyp_name'] = $_POST['dname'];
            $where['dlyp_passwd'] = md5($_POST['dpasswd']);
            $dp_info = Model('delivery_point')->getDeliveryPointInfo($where);
            if (!empty($dp_info)) {
                $_SESSION['delivery_login'] = 1;
                $_SESSION['dlyp_id'] = $dp_info['dlyp_id'];
                $_SESSION['dlyp_name'] = $dp_info['dlyp_name'];
                showDialog('登录成功', "{$GLOBALS['_PAGE_URL']}&c=DCenter", 'succ');
            } else {
                showDialog('登录失败');
            }
        }
        $this->render('login');
    }
    /**
     * 登出
     */
    public function logout() {
        unset($_SESSION['delivery_login']);
        unset($_SESSION['dlyp_id']);
        unset($_SESSION['dlyp_name']);
        showDialog('退出成功', 'reload', 'succ');
    }
}
