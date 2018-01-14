<?php
/**
 * 店铺卖家登录
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
use Common\Lib\Email;
use Common\Lib\Log;
use Common\Lib\Model;


class SellerLoginController extends BaseSellerController {

    public function __construct() {
        parent::__construct();
        if (!empty($_SESSION['seller_id'])) {
            @header('location: ' . $GLOBALS['_PAGE_URL'] . '&c=SellerCenter');
            exit;
        }
    }

    public function index() {
        $this->show_login();
    }

    public function show_login() {
        $this->assign('shopdamap', getShopdaHash());
        $this->setLayout('null_layout');
        $this->render('login');
    }

    public function login() {
        $result = chksubmit(true,true,'num', 'change_seccode();');
        if ($result){
            if ($result === -11){
            	showDialog('用户名或密码错误', '', 'error', 'change_seccode();');
            } elseif ($result === -12){
                showDialog('验证码错误', '', 'error', 'change_seccode();');
            }
        } else {
            showDialog('非法提交', '', 'error', 'change_seccode();');
        }

        $model_seller = Model('seller');
        $seller_info = $model_seller->getSellerInfo(array('seller_name' => $_POST['seller_name']));
	
//         //TODO:确认 是否存在一个member_id(一个C端用户)对应多个 seller_info的情况
//         if (empty($seller_info)) {
//         	$member_name = $_POST['seller_name'];
//         	$member_mod = Model("member");
//         	$member_arr = $member_mod->where(array("member_name" => $member_name))->find();
//         	if (!empty($member_arr))
//         	{
//         		$member_id = $member_arr['member_id'];
//         		$seller_info = $model_seller->where(array("member_id" => $member_id))->find();
//         	}
//         }
        
        if($seller_info) {

            $model_member = Model('member');
            $member_info = $model_member->getMemberInfo(
                array(
                    'member_id' => $seller_info['member_id'],
                    'member_passwd' => md5($_POST['password'])
                )
            );
            if($member_info) {
                // 更新卖家登陆时间
                $model_seller->editSeller(array('last_login_time' => TIMESTAMP), array('seller_id' => $seller_info['seller_id']));

                $model_seller_group = Model('seller_group');
                $seller_group_info = $model_seller_group->getSellerGroupInfo(array('group_id' => $seller_info['seller_group_id']));

                $model_store = Model('store');
                $store_info = $model_store->getStoreInfoByID($seller_info['store_id']);

                $_SESSION['is_login'] = '1';
                $_SESSION['member_id'] = $member_info['member_id'];
                $_SESSION['member_name'] = $member_info['member_name'];
                $_SESSION['member_email'] = $member_info['member_email'];
                $_SESSION['is_buy'] = $member_info['is_buy'];
                $_SESSION['avatar'] = $member_info['member_avatar'];

                $_SESSION['grade_id'] = $store_info['grade_id'];
                $_SESSION['seller_id'] = $seller_info['seller_id'];
                $_SESSION['seller_name'] = $seller_info['seller_name'];
                $_SESSION['seller_is_admin'] = intval($seller_info['is_admin']);
                $_SESSION['store_id'] = intval($seller_info['store_id']);
                $_SESSION['store_name'] = $store_info['store_name'];
                $_SESSION['store_avatar'] = $store_info['store_avatar'];
                $_SESSION['is_own_shop'] = (bool) $store_info['is_own_shop'];
                $_SESSION['bind_all_gc'] = (bool) $store_info['bind_all_gc'];
                $_SESSION['seller_limits'] = explode(',', $seller_group_info['limits']);
                $_SESSION['seller_group_id'] = $seller_info['seller_group_id'];
                $_SESSION['seller_gc_limits'] = $seller_group_info['gc_limits'];
                if($seller_info['is_admin']) {
                    $_SESSION['seller_group_name'] = '管理员';
                    $_SESSION['seller_smt_limits'] = false;
                } else {
                    $_SESSION['seller_group_name'] = $seller_group_info['group_name'];
                    $_SESSION['seller_smt_limits'] = explode(',', $seller_group_info['smt_limits']);
                }
                if(!$seller_info['last_login_time']) {
                    $seller_info['last_login_time'] = TIMESTAMP;
                }
                $_SESSION['seller_last_login_time'] = date('Y-m-d H:i', $seller_info['last_login_time']);
                
                $seller_menu = $this->getSellerMenuList($seller_info['is_admin'], explode(',', $seller_group_info['limits']));
                
//                 var_dump($seller_info, $seller_group_info, $seller_menu);
//                 exit;
                
                $_SESSION['login_seller_info'] = $seller_info;
                $_SESSION['login_seller_group_info'] = $seller_group_info;
                
                $_SESSION['seller_menu'] = $seller_menu['seller_menu'];
                $_SESSION['seller_function_list'] = $seller_menu['seller_function_list'];
                if(!empty($seller_info['seller_quicklink'])) {
                    $quicklink_array = explode(',', $seller_info['seller_quicklink']);
                    foreach ($quicklink_array as $value) {
                        $_SESSION['seller_quicklink'][$value] = $value ;
                    }
                }
                setDaCookie('auto_login', '', -3600);
                $this->recordSellerLog('登录成功');
                
                showDialog('登录成功', $GLOBALS['_PAGE_URL'] . '&c=SellerLogin', 'succ');
         
                exit;
            } else {
                showDialog('商家账户密码错误', '', 'error', 'change_seccode();');
            }
        } else {
            showDialog('商家账号不存在，请确认', '', 'error', 'change_seccode();');
        }
    }
}
