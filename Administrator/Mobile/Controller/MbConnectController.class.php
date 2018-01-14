<?php
/**
 * 手机端微信公众账号二维码设置
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Mobile\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;


class MbConnectController extends SystemController {
    
    public function __construct(){
        parent::__construct();
        Language::read('setting', 'Home');
		
		$this->links = array(
        array('url'=>$GLOBALS['_PAGE_URL'] . '&c=MbConnect&a=wx','text'=>'微信登录'),
        array('url'=>$GLOBALS['_PAGE_URL'] . '&c=MbConnect&a=wap_wx','text'=>'WAP微信登录'),
        array('url'=>$GLOBALS['_PAGE_URL'] . '&c=MbConnect&a=qq','text'=>'QQ互联'),
        array('url'=>$GLOBALS['_PAGE_URL'] . '&c=MbConnect&a=sina','text'=>'新浪微博')
       );
    }

    public function index() {
        $this->wx();
    }

    /**
     * 微信登录
     */
    public function wx() {
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['app_weixin_isuse']   = $_POST['app_weixin_isuse'];
            $update_array['app_weixin_appid']   = $_POST['app_weixin_appid'];
            $update_array['app_weixin_secret']  = $_POST['app_weixin_secret'];
            $result = $model_setting->updateSetting($update_array);
            if ($result){
                $this->log('第三方账号登录，微信登录设置');
                showMessage(Language::get('spd_common_save_succ'));
            }else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);
        //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'wx'));
	$this->setDirquna('mobile');
        $this->render('mb_connect_wx.edit');
    }

    /**
     * WAP微信登录
     */
    public function wap_wx() {
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['wap_weixin_isuse']   = $_POST['wap_weixin_isuse'];
            $update_array['wap_weixin_appid']   = $_POST['wap_weixin_appid'];
            $update_array['wap_weixin_secret']  = $_POST['wap_weixin_secret'];
            $result = $model_setting->updateSetting($update_array);
            if ($result){
                $this->log('第三方账号登录，WAP微信登录设置');
                showMessage(Language::get('spd_common_save_succ'));
            }else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);
        //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'wap_wx'));
	$this->setDirquna('mobile');
        $this->render('mb_connect_wap_wx.edit');
    }

    /**
     * QQ互联登录
     */
    public function qq() {
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['app_qq_isuse']   = $_POST['app_qq_isuse'];
            $update_array['app_qq_akey']   = $_POST['app_qq_akey'];
            $update_array['app_qq_skey']  = $_POST['app_qq_skey'];
            $result = $model_setting->updateSetting($update_array);
            if ($result){
                $this->log('第三方账号登录，QQ互联登录设置');
                showMessage(Language::get('spd_common_save_succ'));
            }else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);
        //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'qq'));
	$this->setDirquna('mobile');
        $this->render('mb_connect_qq.edit');
	
    }

    /**
     * 新浪微博登录
     */
    public function sina() {
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['app_sina_isuse']   = $_POST['app_sina_isuse'];
            $update_array['app_sina_akey']   = $_POST['app_sina_akey'];
            $update_array['app_sina_skey']  = $_POST['app_sina_skey'];
            $result = $model_setting->updateSetting($update_array);
            if ($result){
                $this->log('第三方账号登录，新浪微博登录设置');
                showMessage(Language::get('spd_common_save_succ'));
            }else {
                showMessage(Language::get('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);
        //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'sina'));
	$this->setDirquna('mobile');
        $this->render('mb_connect_sn.edit');
    }
}
