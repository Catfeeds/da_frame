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
use Common\Lib\UploadFile;


class JoininController extends BaseAccountCenterController {
    public function __construct(){
        parent::__construct();
        if (C('delivery_isuse') == 0) {
            showMessage('物流自提服务站功能未开启', $GLOBALS['_PAGE_URL'] . '&c=Login', '', 'error');
        }
        if ($_SESSION['delivery_login'] == 1) {
            @header("location: {$GLOBALS['_PAGE_URL']}&c=DCenter");die;
        }
    }
    /**
     * 申请加入
     */
    public function index() {
        $this->render('joinin');
    }
    /**
     * 保存申请
     */
    public function save_delivery() {
        if (!chksubmit()) {
            showDialog(L('wrong_argument'));
        }
        $insert = array();
        $insert['dlyp_name']        = $_POST['dname'];
        $insert['dlyp_passwd']      = md5($_POST['dpasswd']);
        $insert['dlyp_truename']    = $_POST['dtruename'];
        $insert['dlyp_mobile']      = $_POST['dmobile'];
        $insert['dlyp_telephony']   = $_POST['dtelephony'];
        $insert['dlyp_address_name']= $_POST['daddressname'];
        $insert['dlyp_area_1']      = intval($_POST['area_id_1']);
        $insert['dlyp_area_2']      = intval($_POST['area_id_2']);
        $insert['dlyp_area_3']      = intval($_POST['area_id_3']);
        $insert['dlyp_area_4']      = intval($_POST['area_id_4']);
        $insert['dlyp_area']        = intval($_POST['area_id']);
        $insert['dlyp_area_info']   = $_POST['region'];
        $insert['dlyp_address']     = $_POST['daddress'];
        $insert['dlyp_idcard']      = $_POST['didcard'];
        $insert['dlyp_addtime']     = TIMESTAMP;
        $insert['dlyp_state']       = 10;
        $upload = new UploadFile();
        $upload->set('default_dir',ATTACH_DELIVERY);
        $result = $upload->upfile('didcardimg');
        if(!$result){
            showDialog($upload->error);
        }
        $insert['dlyp_idcard_image']    = $upload->file_name;
        $result = Model('delivery_point')->addDeliveryPoint($insert);
        if ($result) {
            showDialog('操作成功，等待管理员审核', $GLOBALS['_PAGE_URL'] . '&c=Login', 'succ');
        } else {
            showDialog(L('spd_common_op_fail'));
        }
    }
    /**
     * ajax验证用户名是否存在
     */
    public function check() {
        $where = array();
        if ($_GET['dname'] != '') {
            $where['dlyp_name'] = $_GET['dname'];
        }
        if ($_GET['didcard'] != '') {
            $where['dlyp_idcard'] = $_GET['didcard'];
        }
        if ($_GET['dmobile'] != '') {
            $where['dlyp_mobile'] = $_GET['dmobile'];
        }
        $dp_info = Model('delivery_point')->getDeliveryPointInfo($where);
        if (empty($dp_info)) {
            echo 'true';die;
        } else {
            echo 'false';die;
        }
    }
}
