<?php
/**
 * 收货地址
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Member\Controller;
use Member\Controller\BaseMemberController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;
use Common\Lib\Validate;


class MemberAddressController extends BaseMemberController {
    /**
     * 会员地址
     *
     * @param
     * @return
     */
    public function address() {

        Language::read('member_address');
        $lang   = Language::getLangContent();

        $address_class = Model('address');
        /**
         * 判断页面类型
         */
        if (!empty($_GET['type'])){
            /**
             * 新增/编辑地址页面
             */
            if (intval($_GET['id']) > 0){
                /**
                 * 得到地址信息
                 */
                $address_info = $address_class->getOneAddress(intval($_GET['id']));
                if ($address_info['member_id'] != $_SESSION['member_id']){
                    showMessage($lang['member_address_wrong_argument'],$GLOBALS['_PAGE_URL'] . '&c=MemberAddress&aaddress','html','error');
                }
                /**
                 * 输出地址信息
                 */
                $this->assign('address_info',$address_info);
            }
            /**
             * 增加/修改页面输出
             */
            $this->assign('type',$_GET['type']);
            $this->render('member_address.edit','null_layout');
            exit();
        }
        /**
         * 判断操作类型
         */
        if (chksubmit()){
            if ($_POST['city_id'] == '') {
                $_POST['city_id'] = $_POST['area_id'];
            }
            /**
             * 验证表单信息
             */
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["true_name"],"require"=>"true","message"=>$lang['member_address_receiver_null']),
                array("input"=>$_POST["area_id"],"require"=>"true","validator"=>"Number","message"=>$lang['member_address_wrong_area']),
                array("input"=>$_POST["city_id"],"require"=>"true","validator"=>"Number","message"=>$lang['member_address_wrong_area']),
                array("input"=>$_POST["region"],"require"=>"true","message"=>$lang['member_address_area_null']),
                array("input"=>$_POST["address"],"require"=>"true","message"=>$lang['member_address_address_null']),
                array("input"=>$_POST['tel_phone'].$_POST['mob_phone'],'require'=>'true','message'=>$lang['member_address_phone_and_mobile'])
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showValidateError($error);
            }
            $data = array();
            $data['member_id'] = $_SESSION['member_id'];
            $data['true_name'] = $_POST['true_name'];
            $data['area_id'] = intval($_POST['area_id']);
            $data['city_id'] = intval($_POST['city_id']);
            $data['area_info'] = $_POST['region'];
            $data['address'] = $_POST['address'];
            $data['tel_phone'] = $_POST['tel_phone'];
            $data['mob_phone'] = $_POST['mob_phone'];
            $data['is_default'] = $_POST['is_default'] ? 1 : 0;
            if ($_POST['is_default']) {
                $address_class->editAddress(array('is_default'=>0),array('member_id'=>$_SESSION['member_id'],'is_default'=>1));
            }

            if (intval($_POST['id']) > 0){
                $rs = $address_class->editAddress($data, array('address_id' => intval($_POST['id']),'member_id'=>$_SESSION['member_id']));
                if (!$rs){
                    showDialog($lang['member_address_modify_fail'],'','error');
                }
            }else {
                $count = $address_class->getAddressCount(array('member_id'=>$_SESSION['member_id']));
                if ($count >= 20) {
                    showDialog('最多允许添加20个有效地址','','error');
                }
                $rs = $address_class->addAddress($data);
                if (!$rs){
                    showDialog($lang['member_address_add_fail'],'','error');
                }
            }
            showDialog($lang['spd_common_op_succ'],'reload','js');
        }
        $del_id = isset($_GET['id']) ? intval(trim($_GET['id'])) : 0 ;
        if ($del_id > 0){
            $rs = $address_class->delAddress(array('address_id'=>$del_id,'member_id'=>$_SESSION['member_id']));
            if ($rs){
                showDialog(Language::get('member_address_del_succ'),$GLOBALS['_PAGE_URL'] . '&c=MemberAddress&aaddress','js');
            }else {
                showDialog(Language::get('member_address_del_fail'),'','error');
            }
        }
        $address_list = $address_class->getAddressList(array('member_id'=>$_SESSION['member_id']));

        self::profile_menu('address','address');
        $this->assign('address_list',$address_list);
        $this->render('member_address.index');
    }

    /**
     * 添加自提点型收货地址
     */
    public function delivery_add() {
        if (chksubmit()) {
            $info = Model('delivery_point')->getDeliveryPointOpenInfo(array('dlyp_id'=>intval($_POST['dlyp_id'])));
            if (empty($info)) {
                showDialog('该自提点不存在','','error');
            }
            $data = array();
            $data['member_id'] = $_SESSION['member_id'];
            $data['true_name'] = $_POST['true_name'];
            $data['area_id'] = $info['dlyp_area_3'];
            $data['city_id'] = $info['dlyp_area_2'];
            $data['area_info'] = $info['dlyp_area_info'];
            $data['address'] = $info['dlyp_address'];
            $data['tel_phone'] = $_POST['tel_phone'];
            $data['mob_phone'] = $_POST['mob_phone'];
            $data['dlyp_id'] = $info['dlyp_id'];
            $data['is_default'] = 0;
            if (intval($_POST['address_id'])) {
                $result = Model('address')->editAddress($data, array('address_id' => intval($_POST['address_id'])));
            } else {
                $count = Model('address')->getAddressCount(array('member_id'=>$_SESSION['member_id']));
                if ($count >= 20) {
                    showDialog('最多允许添加20个有效地址','','error');
                }
                $result = Model('address')->addAddress($data);
            }
            if (!$result){
                showDialog('保存失败','','error');
            }
            showDialog('保存成功','reload','js');
        } else {
            if (intval($_GET['id']) > 0) {
                $model_addr = Model('address');
                $condition = array('address_id'=>intval($_GET['id']),'member_id'=>$_SESSION['member_id']);
                $address_info = $model_addr->getAddressInfo($condition);
                //取出省级ID
                $area_info = Model('area')->getAreaInfo(array('area_id'=>$address_info['city_id']));
                $address_info['province_id'] = $area_info['area_parent_id'];
                $this->assign('address_info',$address_info);
            }
            $this->render('member_address.delivery_add','null_layout');
        }
    }

    /**
     * 展示自提点列表
     */
    public function delivery_list() {
        $model_delivery = Model('delivery_point');
        $condition = array();
        $condition['dlyp_area'] = intval($_GET['area_id']);
        $list = $model_delivery->getDeliveryPointOpenList($condition,5);
        $this->assign('show_page',$model_delivery->showpage());
        $this->assign('list',$list);
        $this->render('member_address.delivery_list','null_layout');
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_type,$menu_key='') {
        /**
         * 读取语言包
         */
        Language::read('member_layout');
        $menu_array = array();
        switch ($menu_type) {
            case 'address':
                $menu_array = array(
                1=>array('menu_key'=>'address','menu_name'=>'地址列表',   'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=MemberAddress&a=address'));
                break;
        }
        $this->assign('member_menu',$menu_array);
        $this->assign('menu_key',$menu_key);
    }
}
