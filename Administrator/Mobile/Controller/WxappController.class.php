<?php
/**
 * 微信小程序首页配置
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
use Common\Lib\UploadFile;

class WxappController extends SystemController {
    
    public function __construct(){
        parent::__construct();
        Language::read('setting', 'Home');
		
		$this->links = array(
        array('url'=>$GLOBALS['_PAGE_URL'] . '&c=Wxapp&a=config','text'=>'微信小程序'),
       );
    }

    public function index() {
        $this->wxapp_edit();
    }

    /**
     * 编辑
     */
    public function wxapp_edit() {

    	$setting_mod = Model('setting');
    	if (chksubmit()) {
    		$app_wxapp_appid = $_POST['app_wxapp_appid'];
    		$app_wxapp_appkey = $_POST['app_wxapp_appkey'];
    		
    		$appid = $setting_mod->where(array("name" => "app_wxapp_appid"))->find();
    		$appkey = $setting_mod->where(array("name" => "app_wxapp_appkey"))->find();
    		
    		if (empty($appid))
    		{
    			$setting_mod->insert(array("name" => "app_wxapp_appid", "value" => $app_wxapp_appid));
    		}
    		else
    		{
    			$setting_mod->where(array("name" => "app_wxapp_appid"))->update(array("value" => $app_wxapp_appid));
    		}
    		
    		if (empty($appkey))
    		{
    			$setting_mod->insert(array("name" => "app_wxapp_appkey", "value" => $app_wxapp_appkey));
    		}
    		else 
    		{
    			$setting_mod->where(array("name" => "app_wxapp_appkey"))->update(array("value" => $app_wxapp_appkey));
    		}
    		showDialog("修改成功");
    	}
    	
    	$setting_list = $setting_mod->where(array("1" => 1))->select();
    	$setting_config = array();
    	foreach ($setting_list as $item)
    	{
    		$setting_config[$item['name']] = $item['value'];
    	}

    	$this->assign('list_setting', $setting_config);
    	
    	$this->setDirquna('mobile');
    	$this->render('wxapp.config.edit');
    }


}