<?php
/**
 * 账号同步
 *
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace System\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Sms;
use Common\Lib\Validate;


class AccountController extends SystemController {
    private $links;
	
    public function __construct(){
        parent::__construct();
        Language::read('setting', 'Home');
		
		$this->links = array(
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Account&a=qq','lang'=>'qqSettings'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Account&a=sina','lang'=>'sinaSettings'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Account&a=sms','text'=>'手机短信'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Account&a=wx','text'=>'微信登录'),
		//array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Account&a=uc','text'=>'UC互联')  //临时注释
         );
    }

    public function index() {
        $this->qq();
    }

    /**
     * QQ互联
     */
    public function qq(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $obj_validate = new Validate();
            if (trim($_POST['qq_isuse']) == '1'){
                $obj_validate->validateparam = array(
                    array("input"=>$_POST["qq_appid"], "require"=>"true","message"=>Language::get('qq_appid_error')),
                    array("input"=>$_POST["qq_appkey"], "require"=>"true","message"=>Language::get('qq_appkey_error'))
                );
            }
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }else {
                $update_array = array();
                $update_array['qq_isuse']   = $_POST['qq_isuse'];
                $update_array['qq_appcode'] = $_POST['qq_appcode'];
                $update_array['qq_appid']   = $_POST['qq_appid'];
                $update_array['qq_appkey']  = $_POST['qq_appkey'];
                $result = $model_setting->updateSetting($update_array);
                if ($result === true){
                    $this->log(L('spd_edit,qqSettings'),1);
                    showMessage(Language::get('spd_common_save_succ'));
                }else {
                    $this->log(L('spd_edit,qqSettings'),0);
                    showMessage(Language::get('spd_common_save_fail'));
                }
            }
        }

        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);

        //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'qq'));
		$this->setDirquna('system');
        $this->render('account.qq_setting');
    }

    /**
     * sina微博设置
     */
    public function sina(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $obj_validate = new Validate();
            if (trim($_POST['sina_isuse']) == '1'){
                $obj_validate->validateparam = array(
                    array("input"=>$_POST["sina_wb_akey"], "require"=>"true","message"=>Language::get('sina_wb_akey_error')),
                    array("input"=>$_POST["sina_wb_skey"], "require"=>"true","message"=>Language::get('sina_wb_skey_error'))
                );
            }
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }else {
                $update_array = array();
                $update_array['sina_isuse']     = $_POST['sina_isuse'];
                $update_array['sina_wb_akey']   = $_POST['sina_wb_akey'];
                $update_array['sina_wb_skey']   = $_POST['sina_wb_skey'];
                $update_array['sina_appcode']   = $_POST['sina_appcode'];
                $result = $model_setting->updateSetting($update_array);
                if ($result === true){
                    $this->log(L('spd_edit,sinaSettings'),1);
                    showMessage(Language::get('spd_common_save_succ'));
                }else {
                    $this->log(L('spd_edit,sinaSettings'),0);
                    showMessage(Language::get('spd_common_save_fail'));
                }
            }
        }
        $is_exist = function_exists('curl_init');
        if ($is_exist){
            $list_setting = $model_setting->getListSetting();
            $this->assign('list_setting',$list_setting);
        }
        $this->assign('is_exist',$is_exist);

        //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'sina'));
        $this->setDirquna('system');
        $this->render('account.sina_setting');
    }

    /**
     * 手机短信设置
     */
    public function sms(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['sms_register']   = $_POST['sms_register'];
            $update_array['sms_login']   = $_POST['sms_login'];
            $update_array['sms_password']  = $_POST['sms_password'];
            $result = $model_setting->updateSetting($update_array);
            if ($result){
                $this->log('编辑账号同步，手机短信设置');
                showMessage(Language::get('spd_common_save_succ'));
            }else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);
        //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'sms'));
        $this->setDirquna('system');
        $this->render('account.sms_setting');
    }

    /**
     * 微信登录设置
     */
    public function wx(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['weixin_isuse']   = $_POST['weixin_isuse'];
            $update_array['weixin_appid']   = $_POST['weixin_appid'];
            $update_array['weixin_secret']  = $_POST['weixin_secret'];
            $result = $model_setting->updateSetting($update_array);
            if ($result){
                $this->log('编辑账号同步，微信登录设置');
                showMessage(Language::get('spd_common_save_succ'));
            }else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);
        //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'wx'));
        $this->setDirquna('system');
        $this->render('account.wx_setting');
    }
	/**
	 * Ucenter整合设置
	 *
	 * @param
	 * @return
	 */
	public function uc() {
		/**
		 * 读取语言包
		 */
		$lang	= Language::getLangContent();

		/**
		 * 实例化模型
		 */
		$model_setting = Model('setting');
		/**
		 * 保存信息
		 */
		if (chksubmit()){
			$update_array = array();
			$update_array['ucenter_status']		= trim($_POST['ucenter_status']);
            $update_array['ucenter_type']		= trim($_POST['ucenter_type']);
			$update_array['ucenter_app_id']		= trim($_POST['ucenter_app_id']);
			$update_array['ucenter_app_key']	= trim($_POST['ucenter_app_key']);
			$update_array['ucenter_ip'] 		= trim($_POST['ucenter_ip']);
			$update_array['ucenter_url'] 		= trim($_POST['ucenter_url']);
			$update_array['ucenter_connect_type'] = trim($_POST['ucenter_connect_type']);
			$update_array['ucenter_mysql_server'] = trim($_POST['ucenter_mysql_server']);
			$update_array['ucenter_mysql_username'] = trim($_POST['ucenter_mysql_username']);
			$update_array['ucenter_mysql_passwd'] = htmlspecialchars_decode(trim($_POST['ucenter_mysql_passwd']));
			$update_array['ucenter_mysql_name'] = trim($_POST['ucenter_mysql_name']);
			$update_array['ucenter_mysql_pre']	= trim($_POST['ucenter_mysql_pre']);

			$result = $model_setting->updateSetting($update_array);
			if ($result === true){
				showMessage(Language::get('spd_common_save_succ'));
			}else {
				showMessage(Language::get('spd_common_save_fail'));
			}
		}
		/**
		 * 读取设置内容 $list_setting
		 */
		$list_setting = $model_setting->getListSetting();
		/**
		 * 模板输出
		 */
		$this->assign('list_setting',$list_setting);
		 //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'uc'));
		$this->setDirquna('system');
		$this->render('account.uc_setting');
	}

}
