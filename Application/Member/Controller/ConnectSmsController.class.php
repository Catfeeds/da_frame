<?php
/**
 * 手机短信登录
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
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
use Common\Lib\Seccode;
use Common\Lib\Sms;


class ConnectSmsController extends BaseLoginController{
    public function __construct(){
        parent::__construct();
        Language::read("home_login_register,home_login_index");
        $this->assign('hidden_nctoolbar', 1);
        $model_member = Model('member');
        $model_member->checkloginMember();
    }
    /**
     * 手机注册验证码
     */
    public function index(){
        $this->register();
    }
    /**
     * 手机注册
     */
    public function register(){
        $model_member = Model('member');
        $phone = $_POST['register_phone'];
        $captcha = $_POST['register_captcha'];
        if (chksubmit()){
 
            if(C('sms_register') != 1) {
                showDialog('系统没有开启手机注册功能','','error');
            }
            $member_name = $_POST['member_name'];
            $member = $model_member->getMemberInfo(array('member_name'=> $member_name));//检查重名
            if(!empty($member)) {
                showDialog('用户名已被注册','','error');
            }
            $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));//检查手机号是否已被注册
            if(!empty($member)) {
                showDialog('手机号已被注册','','error');
            }
            $logic_connect_api = Logic('connect_api');
            $state_data = $logic_connect_api->checkSmsCaptcha($phone, $captcha, 1);
            if($state_data['state'] == false) {
                showDialog('验证码错误或已过期，重新输入', $_SERVER['HTTP_REFERER'] . "&is_sms_register=1" , 'error');
               
            }
			//分销 会员邀请
			$invite_id = intval(base64_decode($_COOKIE['uid']))/1;
			if(!empty($invite_id)) {
				$member=$model_member->getMemberInfo(array('member_id'=>$invite_id));
				$invite_one = $invite_id;
				$invite_two = $member['invite_one'];
				$invite_three = $member['invite_two'];
			}else{
				$invite_one = 0;
				$invite_two = 0;
				$invite_three = 0;
			}
            
            $member = array();
            $member['member_name'] = $member_name;
            $member['member_passwd'] = $_POST['password'];
            $member['member_email'] = $_POST['email'];
            $member['member_mobile'] = $phone;
            $member['member_mobile_bind'] = 1;
			//添加奖励积分
			$member['inviter_id'] = intval(base64_decode($_COOKIE['uid']))/1;
			//分销
			$member['invite_one'] = $invite_one;
			$member['invite_two'] = $invite_two;
			$member['invite_three'] = $invite_three;
            $result = $model_member->addMember($member);
            if($result) {
                $member = $model_member->getMemberInfo(array('member_name'=> $member_name));
                $model_member->createSession($member,true);//自动登录
                showDialog('注册成功',urlMember('member_information', 'member'),'succ');
            } else {
                showDialog(Language::get('spd_common_save_fail'),'','error');
            }
 
        } else {
            $phone = $_GET['phone'];
            $num = substr($phone,-4);
            $logic_connect_api = Logic('connect_api');
            $member_name = $logic_connect_api->getMemberName('mb', $num);
            $this->assign('member_name',$member_name);
            $this->assign('password',rand(100000, 999999));
            $this->render('connect_sms.register','null_layout');
        }
    }
    /**
     * 短信验证码
     */
    public function get_captcha(){
        $state = '发送失败';
        $phone = $_GET['phone'];
        if (checkSeccode($_GET['shopdamap'],$_GET['captcha']) && strlen($phone) == 11){
            $log_type = $_GET['type'];//短信类型:1为注册,2为登录,3为找回密码
            $state = 'true';
            $logic_connect_api = Logic('connect_api');
            $state_data = $logic_connect_api->sendCaptcha($phone, $log_type);
   
            if($state_data['state'] == false) {
                $state = $state_data['msg'];
            }
        } else {
            $state = '验证码错误';
        }
        exit($state);
    }
    /**
     * 验证注册验证码
     */
    public function check_captcha(){
 
        $state = '短信验证失败';
        $phone = $_GET['phone'];
        $captcha = $_GET['sms_captcha'];
        if (strlen($phone) == 11 && strlen($captcha) == 6){
            $state = 'true';
            $logic_connect_api = Logic('connect_api');
            $state_data = $logic_connect_api->checkSmsCaptcha($phone, $captcha, 1);
            if($state_data['state'] == false) {
                $state = '短信验证码错误或已过期，重新输入';
            }
        }
        exit($state);
    }
    /**
     * 登录
     */
    public function login(){
        if (checkSeccode($_POST['shopdamap'],$_POST['captcha'])){
            if(C('sms_login') != 1) {
                showDialog('系统没有开启手机登录功能','','error');
            }
            $phone = $_POST['phone'];
            $captcha = $_POST['sms_captcha'];
            $logic_connect_api = Logic('connect_api');
            $state_data = $logic_connect_api->checkSmsCaptcha($phone, $captcha, 2);
            
            if($state_data['state'] == false) {//半小时内进行验证为有效
                showDialog('验证码错误或已过期，重新输入','','error');
            }
            $model_member = Model('member');
            $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));//检查手机号是否已被注册
            if(!empty($member)) {
                $model_member->createSession($member);//自动登录
                $reload = $_POST['ref_url'];
                if(empty($reload)) {
                    $reload = urlMember('member', 'home');
                }
                showDialog('登录成功',$reload,'succ');
            }
            else {
            	showDialog('用户不存在','','error');
            }
        }
    }
    /**
     * 找回密码
     */
    public function find_password(){
        if (checkSeccode($_POST['shopdamap'],$_POST['captcha'])){
            if(C('sms_password') != 1) {
                showDialog('系统没有开启手机找回密码功能','','error');
            }
            $phone = $_POST['phone'];
            $captcha = $_POST['sms_captcha'];
            $logic_connect_api = Logic('connect_api');
            $state_data = $logic_connect_api->checkSmsCaptcha($phone, $captcha, 3);
            if($state_data['state'] == false) {//半小时内进行验证为有效
                showDialog('验证码错误或已过期，重新输入','','error');
            }
            $model_member = Model('member');
            $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));//检查手机号是否已被注册
            if(!empty($member)) {
                $new_password = md5($_POST['password']);
				$model_member->editMember(array('member_id'=> $member['member_id']),array('member_passwd'=> $new_password,'member_mobile'=> $phone,'member_mobile_bind'=> 1));
                $model_member->createSession($member);//自动登录
                showDialog('密码修改成功',urlMember('member_information', 'member'),'succ');
            }
        }
    }
}
