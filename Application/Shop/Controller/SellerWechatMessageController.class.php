<?php
/**
 * 微信管理
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Chat;
use Common\Lib\Db;
use Common\Lib\Model;
use Common\Lib\Page;

class SellerWechatMessageController extends BaseSellerController {

    /**
     * 构造方法
     *
     */
    public function __construct() {
        parent::__construct();
    }
	/**
     * 消息自动回复
     *
     */
    function message_index()
    {
		$account_id = intval($_SESSION['member_id']);
		$model_wechat = Model('wechat');
		$find_data = $model_wechat->where(array('user_id'=>$account_id))->find();
		//$find_data = $GLOBALS['db']->getRow("SELECT * FROM ".DB_PREFIX."wechat WHERE user_id = ".$account_id);
    	//$userid=$this->visitor->get('user_id');
    	//$model_wechat=& m('wechat');
    	//$find_data = $model_wechat->get("user_id =".$userid);
    	if(empty($find_data))
    	{
		    @header("Location: {$GLOBALS['_PAGE_URL']}&c=SellerWechat&a=index");
			return;
		    //app_redirect(url("biz","wechat"));return;     
            //$this->show_warning('请先设置微信接口！','马上去设置','app=wechat');return ;
    	}
		
    	
    	//$this->_curlocal('微信管理',url('app=wechat&c=SnsAlbum'),'消息自动回复');
    	//$this->_curitem('message_index');
    	//$this->_config_seo('title', '消息自动回复');
		
		$this->assign('keyinfo',$keyinfo);
		$this->assign('page_title', '消息自动回复');
		$this->profile_menu('wechat_message');
		$this->render('seller_wechat_message_index');
    }
	 /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_key   当前导航的menu_key
     * @return
     */
    private function profile_menu($menu_key = '') {
        $menu_array = array();
        $menu_array[] = array(
            'menu_key' => 'wechat_message',
            'menu_name' => '消息自动回复设置',
            'menu_url' => urlShop('seller_wechat_follow', 'message_index')
        );
        $this->assign('member_menu', $menu_array);
        $this->assign('menu_key', $menu_key);
    }
	}