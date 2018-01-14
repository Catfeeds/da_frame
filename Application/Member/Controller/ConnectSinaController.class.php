<?php
/**
 * 新浪微博登录
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Member\Controller;
use Member\Controller\BaseLoginController;
use Common\Lib\Language;
use Common\Lib\Email;
use Common\Lib\Log;
use Common\Lib\Model;


class ConnectSinaController extends BaseLoginController{
    public function __construct(){
        parent::__construct();
        Language::read("home_login_register,home_login_index,home_sconnect");
        /**
         * 判断新浪微博登录功能是否开启
         */
        if (C('sina_isuse') != 1){
            showMessage(Language::get('home_sconnect_unavailable'),urlLogin('login', 'index'),'html','error');
        }
        if (!empty($_GET['code'])){
            $logic_connect_api = Logic('connect_api');
            $user_info = $logic_connect_api->getSinaUserInfo($_GET['code']);//获取用户信息
            if (!empty($user_info['id'])){
                $_SESSION['sinauser_info'] = $user_info;
            } else {
                showMessage('新浪账号信息错误',urlLogin('login', 'index'),'html','error');
            }
        }
        if (empty($_SESSION['sinauser_info'])){
            $logic_connect_api = Logic('connect_api');
            $sina_url = $logic_connect_api->getSinaOAuth2Url();
            @header("location: ".$sina_url);exit;
        }
        $this->assign('hidden_login', 1);
    }
    /**
     * 首页
     */
    public function index(){
        /**
         * 检查登录状态
         */
        if($_SESSION['is_login'] == '1') {
            $this->bindsina();
        }else {
            $this->autologin();
            $this->register();
        }
    }
    /**
     * 新浪微博账号绑定新用户
     */
    public function register(){
        //实例化模型
        $model_member   = Model('member');
        if (chksubmit()){
            $update_info    = array();
            $update_info['member_passwd']= md5(trim($_POST["password"]));
            if(!empty($_POST["email"])) {
                $update_info['member_email']= $_POST["email"];
                $_SESSION['member_email']= $_POST["email"];
            }
            $model_member->editMember(array('member_id'=>$_SESSION['member_id']),$update_info);
            //showMessage(Language::get('spd_common_save_succ'),SHOP_SITE_URL);
            //返回上一页
            $reload = urldecode($_COOKIE['redirect_uri']);
			 if(empty($reload)) {
                    $reload = urlMember('member', 'home');
             }
			showMessage(Language::get('spd_common_save_succ'),$reload);
        }else{
            //检查登录状态
            $model_member->checkloginMember();
            $sinauser_info = $_SESSION['sinauser_info'];
            $this->assign('sinauser_info',$sinauser_info);
            $logic_connect_api = Logic('connect_api');
            $member_info = $logic_connect_api->sinaRegister($sinauser_info, 'www');
            if($member_info['member_id']) {
                $model_member->createSession($member_info,true);
                $this->assign('user_passwd',$member_info['password']);
                $this->render('connect_sina');
            } else {
                showMessage(Language::get('login_usersave_regist_fail'),urlLogin('login', 'register') ,'html','error');
            }
        }
    }
    /**
     * 绑定新浪微博账号后自动登录
     */
    public function autologin(){
        //查询是否已经绑定该新浪微博账号,已经绑定则直接跳转
        $model_member   = Model('member');
        $array  = array();
        $sinauser_info = $_SESSION['sinauser_info'];
        $array['member_sinaopenid'] = $sinauser_info['id'];
        $member_info = $model_member->getMemberInfo($array);
        if (!empty($member_info)){
            if(!$member_info['member_state']){//1为启用 0 为禁用
                showMessage(Language::get('spd_notallowed_login'),'','html','error');
            }
            $model_member->createSession($member_info);
            $success_message = Language::get('login_index_login_success');
            //showMessage($success_message,SHOP_SITE_URL);
			//返回上一页
			$reload = urldecode($_COOKIE['redirect_uri']);
			 if(empty($reload)) {
                    $reload = urlMember('member', 'home');
             }
			showMessage($success_message,$reload);
        }
    }
    /**
     * 已有用户绑定新浪微博账号
     */
    public function bindsina(){
        $model_member   = Model('member');
        //验证新浪账号用户是否已经存在
        $array  = array();
        $sinauser_info = $_SESSION['sinauser_info'];
        $array['member_sinaopenid'] = $sinauser_info['id'];
        $member_info = $model_member->getMemberInfo($array);
        if (!empty($member_info)){
            showMessage(Language::get('home_sconnect_binding_exist'),urlMember('member_bind', 'sinabind'),'html','error');
        }
        $sina_str = serialize($sinauser_info);
        $edit_state     = $model_member->editMember(array('member_id'=>$_SESSION['member_id']),array('member_sinaopenid'=>$sinauser_info['id'], 'member_sinainfo'=>$sina_str));
        if ($edit_state){
            showMessage(Language::get('home_sconnect_binding_success'),urlMember('member_bind', 'sinabind'));
        }else {
            showMessage(Language::get('home_sconnect_binding_fail'),urlMember('member_bind', 'sinabind'),'html','error');
        }
    }
}
