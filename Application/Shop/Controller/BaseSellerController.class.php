<?php

/**
 * 店铺 control新父类
 *
 */
namespace Shop\Controller;

use Shop\Controller\BaseController;
use Common\Lib\Cache;
use Common\Lib\Chat;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\QueueClient;

class BaseSellerController extends BaseController {
	
	// 店铺信息
	protected $store_info = array ();
	// 店铺等级
	protected $store_grade = array ();
	public function __construct() {
		parent::__construct ();
		
		Language::read ( 'common,store_layout,member_layout,member_refund,refund' );
		if (! C ( 'site_status' ))
			halt ( C ( 'closed_reason' ) );
		$this->setDir ( 'seller' );
		$this->setLayout ( 'seller_layout' );
		
		$nav_list = rkcache ( 'nav', true );
		$this->assign ( 'nav_list', $nav_list );
		
		if (convert_word_underscore ( CONTROLLER_NAME ) !== 'seller_login') {
			
			if (empty ( $_SESSION ['seller_id'] )) {
				$url = url ( 'SellerLogin', 'show_login', array () );
				@header ( "location: {$url}" );
				exit ();
			}
			
			// 验证店铺是否存在
			$model_store = Model ( 'store' );
			$this->store_info = $model_store->getStoreInfoByID ( $_SESSION ['store_id'] );
			if (empty ( $this->store_info )) {
				$url = url ( 'seller_login', 'show_login', array () );
				@header ( 'location: {$url}' );
				die ();
			}
			
			// 店铺关闭标志
			if (intval ( $this->store_info ['store_state'] ) === 0) {
				$this->assign ( 'store_closed', true );
				$this->assign ( 'store_close_info', $this->store_info ['store_close_info'] );
			}
			
			// 店铺等级
			if (checkPlatformStore ()) {
				$this->store_grade = array (
						'sg_id' => '0',
						'sg_name' => '自营店铺专属等级',
						'sg_goods_limit' => '0',
						'sg_album_limit' => '0',
						'sg_space_limit' => '999999999',
						'sg_template_number' => '6',
						
						// see also store_settingControl.theme()
						// 'sg_template' => 'default|style1|style2|style3|style4|style5',
						'sg_price' => '0.00',
						'sg_description' => '',
						'sg_function' => 'editor_multimedia',
						'sg_sort' => '0' 
				);
			} else {
				$store_grade = rkcache ( 'store_grade', true );
				$this->store_grade = $store_grade [$this->store_info ['grade_id']];
			}
			
			if ($_SESSION ['seller_is_admin'] !== 1 && convert_word_underscore ( CONTROLLER_NAME ) !== 'seller_center' && convert_word_underscore ( CONTROLLER_NAME ) !== 'seller_logout') {
				if (! in_array ( CONTROLLER_NAME, $_SESSION ['seller_limits'] )) {
					showMessage ( '没有权限', '', '', 'error' );
				}
			}
			
			// 卖家菜单
			// TODO:使用SESSION中的 MENU
			$seller_menu = $_SESSION['seller_menu'];

			// TODO: 移除这部分测试
// 			$login_seller_info = $_SESSION ['login_seller_info'];
// 			$login_seller_group_info = $_SESSION ['login_seller_group_info'];
// 			$seller_menu = $this->getSellerMenuList ( $login_seller_info ['is_admin'], explode ( ',', $login_seller_group_info ['limits'] ) );
// 			$seller_menu = $seller_menu ['seller_menu'];
			
			$left_nav_menu = $this->get_left_nav_menu($seller_menu);
			$root_nav = $this->get_root_nav($seller_menu, true);
			$none_click_nav = $this->get_root_nav($seller_menu, false);
			

// 			var_dump($left_nav_menu);
			
			$this->assign ( 'menu', $seller_menu );
			$this->assign("root_nav", $root_nav);
			$this->assign("none_click_nav", $none_click_nav);
			
			// 当前菜单
			$current_menu = $this->_getCurrentMenu ( $_SESSION ['seller_function_list'] );
			$this->assign ( 'current_menu', $current_menu );
			// 左侧菜单
			
			if (convert_word_underscore ( CONTROLLER_NAME ) == 'seller_center') {
				if (! empty ( $_SESSION ['seller_quicklink'] )) {
					$left_menu = array ();
					foreach ( $_SESSION ['seller_quicklink'] as $value ) {
						$left_menu [] = $_SESSION ['seller_function_list'] [$value];
					}
				}
			} else {
				
				$left_menu = $_SESSION ['seller_menu'] [$current_menu ['model']] ['child'];
			}
			
			$this->assign ( 'left_menu', $left_menu );
			
			$seller_quick_link = $_SESSION ['seller_quicklink'];
			
			
			$seller_quick_link_dom = $this->get_seller_quicklink($seller_menu, $_SESSION ['seller_quicklink']);
			if (! ( (convert_word_underscore($_GET['c']) == 'seller_center') && (($_GET['a'] == 'index') || empty($_GET['a'])) ) )
			{
				$seller_quick_link_dom = '';
			}
			else
			{
				$left_nav_menu = '';
			}
			
			$this->assign ( 'seller_quicklink', $seller_quick_link );
			$this->assign ( 'seller_quicklink_dom', $seller_quick_link_dom );
			
			$this->assign ( 'left_nav_menu', $left_nav_menu );
			
			$this->checkStoreMsg ();
		}
	}
	
	/**
	 * 记录卖家日志
	 *
	 * @param $content 日志内容        	
	 * @param $state 1成功
	 *        	0失败
	 */
	protected function recordSellerLog($content = '', $state = 1) {
		$seller_info = array ();
		$seller_info ['log_content'] = $content;
		$seller_info ['log_time'] = TIMESTAMP;
		$seller_info ['log_seller_id'] = $_SESSION ['seller_id'];
		$seller_info ['log_seller_name'] = $_SESSION ['seller_name'];
		$seller_info ['log_store_id'] = $_SESSION ['store_id'];
		$seller_info ['log_seller_ip'] = getIp ();
		$seller_info ['log_url'] = convert_word_underscore ( CONTROLLER_NAME ) . '&' . ACTION_NAME;
		$seller_info ['log_state'] = $state;
		$model_seller_log = Model ( 'seller_log' );
		$model_seller_log->addSellerLog ( $seller_info );
	}
	
	/**
	 * 记录店铺费用
	 *
	 * @param $cost_price 费用金额        	
	 * @param $cost_remark 费用备注        	
	 */
	protected function recordStoreCost($cost_price, $cost_remark) {
		// 平台店铺不记录店铺费用
		if (checkPlatformStore ()) {
			return false;
		}
		$model_store_cost = Model ( 'store_cost' );
		$param = array ();
		$param ['cost_store_id'] = $_SESSION ['store_id'];
		$param ['cost_seller_id'] = $_SESSION ['seller_id'];
		$param ['cost_price'] = $cost_price;
		$param ['cost_remark'] = $cost_remark;
		$param ['cost_state'] = 0;
		$param ['cost_time'] = TIMESTAMP;
		$model_store_cost->addStoreCost ( $param );
		
		// 发送店铺消息
		$param = array ();
		$param ['code'] = 'store_cost';
		$param ['store_id'] = $_SESSION ['store_id'];
		$param ['param'] = array (
				'price' => $cost_price,
				'seller_name' => $_SESSION ['seller_name'],
				'remark' => $cost_remark 
		);
		
		QueueClient::push ( 'sendStoreMsg', $param );
	}
	
	protected function getSellerMenuList($is_admin, $limits) {
		$seller_menu = array ();
		$first_menu_list = array();
		
		//DEBUG
// 		$is_admin = 2;
// 		$limits = array("store_goods_add", "store_navigation");
		
		if (intval ( $is_admin ) !== 1) {
			$full_menu_list = $this->_getMenuList ();
			foreach ($full_menu_list as $group_key => $menu_list) {
				//2级菜单
				foreach ( $menu_list['child'] as $key => $value ) {

					//三级菜单
					foreach ( $value ['child'] as $child_key => $child_value ) {
						if (! in_array ( $child_value ['c'], $limits )) {
							unset ( $full_menu_list[$group_key]['child'] [$key] ['child'] [$child_key] );
						}
					}
					
					if (count($full_menu_list[$group_key]['child'][$key]['child']) == 0) {
						unset($full_menu_list[$group_key]['child'][$key]);
					}	
				}	
				
				if (count($full_menu_list[$group_key]['child']) == 0) {
					unset($full_menu_list[$group_key]);
				}
				
				if (count ( $full_menu_list [$group_key] ['child'] ) > 0) {
					$seller_menu [$group_key] = $full_menu_list [$group_key];
				}
			}
			
		} else {
			$seller_menu = $this->_getMenuList ();
		}
		
		foreach ($seller_menu as $key => $val)
		{
			unset($val['child']);
			$first_menu_list[] = $val;
		}
		
		$seller_function_list = $this->_getSellerFunctionList ( $seller_menu );
		$ret = array (
				'seller_menu' => $seller_menu,
				'seller_function_list' => $seller_function_list,
				'nav_menu_list' => $first_menu_list,
		);
		
		//var_dump($ret);
		
		return $ret;
	}
	private function _getCurrentMenu($seller_function_list) {
		$current_menu = $seller_function_list [convert_word_underscore ( CONTROLLER_NAME )];
		if (empty ( $current_menu )) {
			$current_menu = array (
					'model' => 'index',
					'model_name' => '首页' 
			);
		}
		return $current_menu;
	}
	
	private function _getMenuList() {
		$ret = array();
		$conf_file = file_get_contents(dirname(__FILE__) . "/../Menu/config.json");
		$menu_arr = json_decode($conf_file, true);
		foreach ($menu_arr as $group_key => $group_item)
		{
			$group_name = $group_item['name'];
			foreach ($group_item['child'] as $group_2_key => $group_2_item)
			{
				$group_2_name = $group_2_item['name'];
				
				foreach ($group_2_item['child'] as $group_3_key => $menu_name)
				{
					$m_c_a_arr = explode("|", $group_3_key);
					
					$m = $m_c_a_arr[0];
					$c = $m_c_a_arr[1];
					$a = $m_c_a_arr[2];
					
					$menu_item = array(
							"name" => $menu_name,
							"c" => $c,
							"a" => $a,
							"m" => $m,
					);
					$ret[$group_key]['child'][$group_2_key]['child'][] = $menu_item;
					
				}
				
				$ret[$group_key]['child'][$group_2_key]['name'] = $group_2_name;
			}
			$ret[$group_key]['name'] = $group_name;
		}
		return $ret;
	}
	
	private function _getSellerFunctionList($full_menu_list) {
		$format_menu = array ();
		
		foreach ($full_menu_list as $group_key => $menu_list) {
			foreach ( $menu_list['child'] as $key => $menu_value ) {
				foreach ( $menu_value ['child'] as $submenu_value ) {
					$format_menu [$submenu_value ['c']] = array (
							'group_key' => $group_key,
							"group_name" => $menu_list['name'],
							'model' => $key,
							'model_name' => $menu_value ['name'],
							'name' => $submenu_value ['name'],
							'c' => $submenu_value ['c'],
							'a' => $submenu_value ['a']
					);
				}
			}
		}
		
		return $format_menu;
	}
	
	/**
	 * 自动发布店铺动态
	 *
	 * @param array $data
	 *        	相关数据
	 * @param string $type
	 *        	类型 'new','coupon','xianshi','mansong','bundling','groupbuy'
	 *        	所需字段
	 *        	new goods表' goods_id,store_id,goods_name,goods_image,goods_price,goods_freight
	 *        	xianshi p_xianshi_goods表' goods_id,store_id,goods_name,goods_image,goods_price,goods_freight,xianshi_price
	 *        	mansong p_mansong表' mansong_name,start_time,end_time,store_id
	 *        	bundling p_bundling表' bl_id,bl_name,bl_img,bl_discount_price,bl_freight_choose,bl_freight,store_id
	 *        	groupbuy goods_group表' group_id,group_name,goods_id,goods_price,groupbuy_price,group_pic,rebate,start_time,end_time
	 *        	coupon在后台发布
	 */
	public function storeAutoShare($data, $type) {
		$param = array (
				3 => 'new',
				4 => 'coupon',
				5 => 'xianshi',
				6 => 'mansong',
				7 => 'bundling',
				8 => 'groupbuy' 
		);
		$param_flip = array_flip ( $param );
		if (! in_array ( $type, $param ) || empty ( $data )) {
			return false;
		}
		
		$auto_setting = Model ( 'store_sns_setting' )->getStoreSnsSettingInfo ( array (
				'sauto_storeid' => $_SESSION ['store_id'] 
		) );
		$auto_sign = false; // 自动发布开启标志
		
		if ($auto_setting ['sauto_' . $type] == 1) {
			$auto_sign = true;
			if (CHARSET == 'GBK') {
				foreach ( ( array ) $data as $k => $v ) {
					$data [$k] = Language::getUTF8 ( $v );
				}
			}
			$goodsdata = addslashes ( json_encode ( $data ) );
			if ($auto_setting ['sauto_' . $type . 'title'] != '') {
				$title = $auto_setting ['sauto_' . $type . 'title'];
			} else {
				$auto_title = 'spd_store_auto_share_' . $type . rand ( 1, 5 );
				$title = Language::get ( $auto_title );
			}
		}
		if ($auto_sign) {
			// 插入数据
			$stracelog_array = array ();
			$stracelog_array ['strace_storeid'] = $this->store_info ['store_id'];
			$stracelog_array ['strace_storename'] = $this->store_info ['store_name'];
			$stracelog_array ['strace_storelogo'] = empty ( $this->store_info ['store_avatar'] ) ? '' : $this->store_info ['store_avatar'];
			$stracelog_array ['strace_title'] = $title;
			$stracelog_array ['strace_content'] = '';
			$stracelog_array ['strace_time'] = TIMESTAMP;
			$stracelog_array ['strace_type'] = $param_flip [$type];
			$stracelog_array ['strace_goodsdata'] = $goodsdata;
			Model ( 'store_sns_tracelog' )->saveStoreSnsTracelog ( $stracelog_array );
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 商家消息数量
	 */
	private function checkStoreMsg() { // 判断cookie是否存在
		$cookie_name = 'storemsgnewnum' . $_SESSION ['seller_id'];
		if (cookie ( $cookie_name ) != null && intval ( cookie ( $cookie_name ) ) >= 0) {
			$countnum = intval ( cookie ( $cookie_name ) );
		} else {
			$where = array ();
			$where ['store_id'] = $_SESSION ['store_id'];
			$where ['sm_readids'] = array (
					'exp',
					'sm_readids NOT LIKE \'%,' . $_SESSION ['seller_id'] . ',%\' OR sm_readids IS NULL' 
			);
			if ($_SESSION ['seller_smt_limits'] !== false) {
				$where ['smt_code'] = array (
						'in',
						$_SESSION ['seller_smt_limits'] 
				);
			}
			$countnum = Model ( 'store_msg' )->getStoreMsgCount ( $where );
			setDaCookie ( $cookie_name, intval ( $countnum ), 2 * 3600 ); // 保存2小时
		}
		$this->assign ( 'store_msg_num', $countnum );
	}
	
	/*获取左侧 MENU HTML*/
	private function get_left_nav_menu($menu_list)
	{
		$c = convert_word_underscore($_GET['c']);
		$a = convert_word_underscore($_GET['a']);
		$cur_menu_key = $c . "|" . $a;
		
		$ret = "";

		foreach ($menu_list as $first_key => $first_list)
		{
			//var_dump($first_key, $first_list);
			$first_name = $first_list['name'];
			
			//var_dump($first_name);
			
			$ret .= "<div style='display:none;' class='left_nav_div' nav-group-key='{$first_key}' nav-name='{$first_name}'>";
			
			foreach ($first_list['child'] as $second_key => $second_list)
			{
				$second_name = $second_list['name'];
				
				//var_dump($second_name);
				
				$ret .= "<dl>";
				
				$ret .= "<dt><a href='javascript:void(0);'><span class='ico-system-0'></span><h3>{$second_name}</h3></a></dt>";
				$ret .= "<dd><ul>";
				 
				foreach ($second_list['child'] as $menu_item)
				{
					$group_key = $menu_item['group_key'];
					$menu_key = $menu_item['c'] . "|" . $menu_item['a'];
					
					$extend_attr = " active='0' ";
					if ($menu_key == $cur_menu_key)
					{
						$extend_attr = " active='1' ";	
					}
					
					$url_data = urlShop($menu_item['c'], $menu_item['a']);
					$menu_name = $menu_item['name'];
					
					$child_menu_controller_name = $menu_item['c'];
					$child_menu_action_name = $menu_item['a'];
					
					$ret .= "<li controller='{$child_menu_controller_name}' action='{$child_menu_action_name}' url-data='{$url_data}' onclick='openItem(\"{$menu_key}\", this);' {$extend_attr}><a  controller='{$child_menu_controller_name}' action='{$child_menu_action_name}'  {$extend_attr} href='javascript:void(0);' group_key='{$group_key}' menu_key='{$menu_key}'>{$menu_name}</a></li>";
				}
				$ret .= "</ul></dd>";
				
				$ret .= "</dl><dl class='clear-dl'></dl>";
			}
			
			$ret .= "</div>";
		}
		//var_dump($ret);
		return $ret;
		
	}
	
	/*获取一级导航*/
	private function get_root_nav($menu_list, $with_click_event = true)
	{
		$c = convert_word_underscore($_GET['c']);
		$a = convert_word_underscore($_GET['a']);
		$cur_menu_key = $c . "|" . $a;
		
		$ret = "";
		
		foreach ($menu_list as $first_key => $first_list)
		{
			//var_dump($first_key, $first_list);
			$first_name = $first_list['name'];
				
			//var_dump($first_name);
			
			if ($with_click_event)
			{
				$ret .= "<li onclick='openNav(\"{$first_key}\", this);' key='{$first_key}' class='root_nav_li' nav-name='{$first_name}'>";
			}
			else
			{
				$ret .= "<li key='{$first_key}' class='root_nav_li' nav-name='{$first_name}'>";
			}
			$ret .= "<a href='javascript:void(0);' key='{$first_key}'>{$first_name}</a>";
			$ret .= "</li>";
		}
		//var_dump($ret);
		return $ret;
	}
	
	/*获取快捷菜单*/
	private function get_seller_quicklink($menu_list, $quicklink_key_list) {
		$c = convert_word_underscore($_GET['c']);
		$a = convert_word_underscore($_GET['a']);
		$cur_menu_key = $c . "|" . $a;
		
		$ret = "<div  class='left_nav_div quick-link-div' style='display:none;' nav-group-key='' nav-name=''>" . 
				"<dl><dt><a href='javascript:void(0);'><span class='ico-system-0'></span><h3 onclick=''>" . 
				"<i>快捷菜单   </i><i class='fa fa-arrows-alt' aria-hidden='true'></i></h3>" . 
				"</a></dt><dd><ul></ul></dd></dl></div>";
		
		//$ret .= "<dt><a href='javascript:void(0);'><span class='ico-system-0'></span><h3></h3></a></dt>";
		
		foreach ($menu_list as $first_key => $first_list)
		{
			//var_dump($first_key, $first_list);
			$first_name = $first_list['name'];
				
			//var_dump($first_name);
				
			$ret .= "<div  class='left_nav_div quick-link-div'  style='display:none;' nav-group-key='{$first_key}' nav-name='{$first_name}'>";
				
			foreach ($first_list['child'] as $second_key => $second_list)
			{
				$second_name = $second_list['name'];
		
				//var_dump($second_name);
		
				$ret .= "<dl>";
		
				$ret .= "<dd><ul>";
					
				foreach ($second_list['child'] as $menu_item)
				{
					$key = convert_word_underscore($menu_item['c']) . "|" . convert_word_underscore($menu_item['a']);
					if (!in_array($key, $quicklink_key_list))
					{
						continue;
					}
					
					$group_key = $menu_item['group_key'];
					$menu_key = $menu_item['c'] . "|" . $menu_item['a'];
						
					$extend_attr = " active='0' ";
					if ($menu_key == $cur_menu_key)
					{
						$extend_attr = " active='1' ";
					}
						
					$url_data = urlShop($menu_item['c'], $menu_item['a']);
					$menu_name = $menu_item['name'];
					$ret .= "<li quick-link-key='{$menu_key}' url-data='{$url_data}' onclick='openItem(\"{$menu_key}\", this);' {$extend_attr}><a {$extend_attr} href='javascript:void(0);' group_key='{$group_key}' menu_key='{$menu_key}'>{$menu_name}</a></li>";
				}
				$ret .= "</ul></dd>";
		
				$ret .= "</dl>";
			}
				
			$ret .= "</div>";
		}
		//var_dump($ret);
		return $ret;
	}
}
