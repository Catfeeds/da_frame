<?php
/**
 * 微信登录
 *
 *
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


class ConnectWxController extends BaseLoginController{
    public function __construct(){
        parent::__construct();
        Language::read("home_login_register,home_login_index");
        $this->assign('hidden_login', 1);
    }
    /**
     * 微信登录
     */
    public function index(){
        if(empty($_GET['code'])) {
            $this->render('connect_wx.index','null_layout');
        } else {
            $this->get_info();
        }
        
    }
    /**
     * 微信注册后修改密码
     */
    public function edit_info(){
        if (chksubmit()) {
            $model_member = Model('member');
            $member = array();
            $member['member_passwd'] = md5($_POST["password"]);
            if(!empty($_POST["email"])) {
                $member['member_email']= $_POST["email"];
                $_SESSION['member_email']= $_POST["email"];
            }
            $model_member->editMember(array('member_id'=> $_SESSION['member_id']),$member);
            showDialog(Language::get('spd_common_save_succ'),urlMember('member', 'home'),'succ');
        }
    }
    /**
     * 回调获取信息
     */
    public function get_info(){
        $code = $_GET['code'];
        if(!empty($code)) {
            $logic_connect_api = Logic('connect_api');
            $user_info = $logic_connect_api->getWxUserInfo($code);

//             var_dump($user_info);
//             exit;
            
            if(!empty($user_info['unionid'])) {
                $unionid = $user_info['unionid'];
                $model_member = Model('member');
                $member = $model_member->getMemberInfo(array('weixin_unionid'=> $unionid));
                if(!empty($member)) {//会员信息存在时自动登录
                    $model_member->createSession($member);
                    //showDialog('登录成功',urlMember('member', 'home'),'succ');
					//返回上一页
					$reload = urldecode($_COOKIE['redirect_uri']);
					 if(empty($reload)) {
		                    $reload = urlMember('member', 'home');
		             }
					showDialog('登录成功',$reload,'succ');
					
					
					
                }
                if(!empty($_SESSION['member_id'])) {//已登录时绑定微信
                    $member_id = $_SESSION['member_id'];
                    $member = array();
                    $member['weixin_unionid'] = $unionid;
                    $member['weixin_info'] = $user_info['weixin_info'];
                    $model_member->editMember(array('member_id'=> $member_id),$member);
                    //showDialog('微信绑定成功',urlMember('member', 'home'),'succ');
					//返回上一页
					$reload = urldecode($_COOKIE['redirect_uri']);
					 if(empty($reload)) {
		                    $reload = urlMember('member', 'home');
		             }
					showDialog('微信绑定成功',$reload,'succ');
					
                } else {//自动注册会员并登录
                    $this->register($user_info);
                    exit;
                }
            }
        }
        showDialog('微信登录失败',urlLogin('login', 'index'),'error');
    }
    /**
     * 注册
     */
    public function register($user_info){
        Language::read("home_login_register,home_login_index");
        $unionid = $user_info['unionid'];
        $nickname = $user_info['nickname'];
        if(!empty($unionid)) {
            $logic_connect_api = Logic('connect_api');
            $member = $logic_connect_api->wxRegister($user_info, 'www');
            if(!empty($member)) {
                $model_member = Model('member');
                $model_member->createSession($member,true);//自动登录
                $this->assign('user_info',$user_info);
                $this->assign('headimgurl',$user_info['headimgurl']);
                $this->assign('password',$member['password']);
                $this->render('connect_wx.register');
            }
        }
    }
}