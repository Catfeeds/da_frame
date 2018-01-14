<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Common\Behavior;
use Think\Behavior;
use Think\Hook;
use Common\Lib\Language;
use Common\Lib\Security;

// 初始化钩子信息
class DaActionBeginBehavior extends Behavior {

	// 行为扩展的执行入口必须是run
	public function run(&$content)
	{
		$this->filterInput();
		$this->defineGlobal();
		$this->defineAppVars();
		
		$this->initDbConf();
		$this->defineFilePath();
		
		$this->loadSetting2Conf();
		$this->defineSettingsVars();
		$this->initRouter();
		
		$GLOBALS['setting_config'] = C();
		define('MD5_KEY',md5($GLOBALS['setting_config']['md5_key']));
		if(function_exists('date_default_timezone_set')){
			if (is_numeric($GLOBALS['setting_config']['time_zone'])){
				@date_default_timezone_set('Asia/Shanghai');
			}else{
				@date_default_timezone_set($GLOBALS['setting_config']['time_zone']);
			}	
		}
	}
	
	//--以下不用修改--//
	public function defineGlobal()
	{
		define('BASE_ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);

		//-----初始化------//
		define('DS','/');
		define('IN_SHOPDA', true);
		define('START_TIME',microtime(true));
		define('TIMESTAMP',time());
 
		//-----附件------//
		define('ATTACH_PATH','shop');
		define('ATTACH_COMMON','shop/common');
		define('ATTACH_AVATAR','shop/avatar');
		define('ATTACH_EDITOR','shop/editor');
		define('ATTACH_MEMBERTAG','shop/membertag');
		define('ATTACH_STORE','shop/store');
		define('ATTACH_GOODS','shop/store/goods');
		define('ATTACH_STORE_DECORATION','shop/store/decoration');
		define('ATTACH_LOGIN','shop/login');
		define('ATTACH_ARTICLE','shop/article');
		define('ATTACH_BRAND','shop/brand');
		define('ATTACH_GOODS_CLASS','shop/goods_class');
		define('ATTACH_ADV','shop/shopda');
		define('ATTACH_ACTIVITY','shop/activity');
		define('ATTACH_WATERMARK','shop/watermark');
		define('ATTACH_POINTPROD','shop/pointprod');
		define('ATTACH_GROUPBUY','shop/groupbuy');
		define('ATTACH_SLIDE','shop/store/slide');
		define('ATTACH_VOUCHER','shop/voucher');
		define('ATTACH_REDPACKET','shop/redpacket');
		define('ATTACH_STORE_JOININ','shop/store_joinin');
		define('ATTACH_REC_POSITION','shop/rec_position');
		define('ATTACH_CONTRACTICON','shop/contracticon');
		define('ATTACH_CONTRACTPAY','shop/contractpay');
		define('ATTACH_WAYBILL','shop/waybill');
		define('ATTACH_MOBILE','mobile');
		define('ATTACH_CIRCLE','circle');
		define('ATTACH_CMS','cms');
		define('ATTACH_LIVE','live');
		define('ATTACH_MALBUM','shop/member');
		define('ATTACH_MICROSHOP','microshop');
		define('ATTACH_DELIVERY','delivery');
		define('ATTACH_CHAIN', 'chain');
		define('ATTACH_ADMIN_AVATAR','admin/avatar');
		
		//----TPL----//
		define('TPL_SHOP_NAME','default');
		define('TPL_CIRCLE_NAME', 'default');
		define('TPL_MICROSHOP_NAME', 'default');
		define('TPL_CMS_NAME', 'default');
		define('TPL_ADMIN_NAME', 'default');
		define('TPL_DELIVERY_NAME', 'default');
		define('TPL_CHAIN_NAME', 'default');
		define('TPL_MEMBER_NAME', 'default');

	}
	
	public function defineSettingsVars()
	{
		//应用常量设置
		$config = C();
		define('URL_MODEL',$config['url_model']);
		define('SUBDOMAIN_SUFFIX', $config['subdomain_suffix']);
		define('BASE_SITE_URL', $config['base_site_url']);
		define('SHOP_SITE_URL', $config['shop_site_url']);
		define('MICROSHOP_SITE_URL', $config['microshop_site_url']);
		define('CMS_SITE_URL', $config['cms_site_url']);
		define('CIRCLE_SITE_URL', $config['circle_site_url']);
		define('ADMIN_SITE_URL', $config['admin_site_url']);
		define('MOBILE_SITE_URL', $config['mobile_site_url']);
		define('WAP_SITE_URL', $config['wap_site_url']);
		define('UPLOAD_SITE_URL',$config['upload_site_url']);
		define('RESOURCE_SITE_URL',$config['resource_site_url']);
		define('DELIVERY_SITE_URL',$config['delivery_site_url']);
		define('LOGIN_SITE_URL',$config['member_site_url']);
		define('CRONTAB_URL', $config['crontab_url']);
		define('BASE_PUBLIC_URL', $config['base_public_url']);
		define('BASE_STATIC_URL', $config['base_static_url']);
		define('BASE_RESOURCE_URL', $config['base_resource_url']);
		define('BASE_CMS_RESOURCE_URL', BASE_RESOURCE_URL . '/cms/');
		define('BASE_CMS_STATIC_URL', BASE_STATIC_URL . '/cms/');
		define('BASE_CIRCLE_STATIC_URL', BASE_STATIC_URL . '/circle/');
		define('BASE_CHAT_STATIC_URL', BASE_STATIC_URL . '/chat/');
		define('BASE_API_URL', BASE_SITE_URL . "/Api/");
		define('MAIN_API_URL', BASE_API_URL . "/main/");
		define('RESOURCE_SITE_URL_HTTPS', RESOURCE_SITE_URL);
		define('CHAIN_SITE_URL', $config['chain_site_url']);
		define('MEMBER_SITE_URL', $config['member_site_url']);
		define('UPLOAD_SITE_URL_HTTPS', $config['upload_site_url']);
		define('CHAT_SITE_URL', $config['chat_site_url']);
		define('NODE_SITE_URL', $config['node_site_url']);
		define('SUN_FLOWER_FILE_URL', $config['sun_flower_file_url']);
		
		define('DBDRIVER',$config['dbdriver']);
		define('SESSION_EXPIRE',$config['session_expire']);
		define('LANG_TYPE',$config['lang_type']);
		define('COOKIE_PREFIX',$config['cookie_prefix']);
		
		//默认平台店铺id
		define('DEFAULT_PLATFORM_STORE_ID', $config['default_store_id']); //
		
		//TODO 合并LOGIN MEMBER
		define('LOGIN_TEMPLATES_URL', BASE_STATIC_URL . "/member/");
		define('LOGIN_RESOURCE_SITE_URL',BASE_RESOURCE_URL . '/member/');
		
		define('CHAIN_TEMPLATES_URL', BASE_STATIC_URL . "/chain/");
		define('CHAIN_RESOURCE_SITE_URL',BASE_RESOURCE_URL . '/chain/');
	 
		define('CHAT_TEMPLATES_URL', BASE_STATIC_URL . "/chat/");
		define('CHAT_RESOURCE_SITE_URL',BASE_RESOURCE_URL . '/chat/');
		
		define('CIRCLE_TEMPLATES_URL', BASE_STATIC_URL . "/circle/");
		define('CIRCLE_RESOURCE_SITE_URL',BASE_RESOURCE_URL . '/circle/');
		
		define('CMS_TEMPLATES_URL', BASE_STATIC_URL . "/cms/");
		define('CMS_RESOURCE_SITE_URL',BASE_RESOURCE_URL . '/cms/');
		
		define('DELIVERY_TEMPLATES_URL', BASE_STATIC_URL . "/delivery/");
		define('DELIVERY_RESOURCE_SITE_URL',BASE_RESOURCE_URL . '/delivery/');
		
		define('MEMBER_TEMPLATES_URL', BASE_STATIC_URL . "/member/");
		define('MEMBER_RESOURCE_SITE_URL',BASE_RESOURCE_URL . '/member/');
		
		define('MICROSHOP_TEMPLATES_URL', BASE_STATIC_URL . "/microshop/");
		define('MICROSHOP_RESOURCE_SITE_URL',BASE_RESOURCE_URL . '/microshop/');
		
		define('MOBILE_TEMPLATES_URL', BASE_STATIC_URL . "/mobile/");
		define('MOBILE_RESOURCE_SITE_URL',BASE_RESOURCE_URL . '/mobile/');
		
		define('SHOP_RESOURCE_SITE_URL',BASE_RESOURCE_URL . '/shop/');
		define('SHOP_STATIC_URL',BASE_STATIC_URL . '/shop/');
		
		define('CHAT_TEMPLATES_URL', BASE_STATIC_URL . "/chat/");
		define('CHAT_RESOURCE_SITE_URL',BASE_RESOURCE_URL . '/chat/');

		//ADMIN
		define('ADMIN_STATIC_URL',BASE_STATIC_URL . DS . 'administrator');
		define('ADMIN_RESOURCE_URL',BASE_RESOURCE_URL . DS . 'administrator');
 		
		//----ADMIN MODULES----//
		define('ADMIN_MODULES_SYSTEM', 'System');
		define('ADMIN_MODULES_SHOP', 'Shop');
		define('ADMIN_MODULES_CMS', 'Cms');
		define('ADMIN_MODULES_CIECLE', 'Circle');
		define('ADMIN_MODULES_MICEOSHOP', 'Microshop');
		define('ADMIN_MODULES_MOBILE', 'Mobile');
		define('ADMIN_MODULES_HOME', 'Home');
		
		//----FUNNY GAMES----//
		define('FG_BASE_URL', BASE_SITE_URL . "/funny_games/");
		define('FG_GAME_URL', FG_BASE_URL . "/games/");
		define('FG_QRCODE_URL', FG_BASE_URL . "/qrcodes/");
		define('FG_CONFIG_URL', FG_BASE_URL . "/config/");
		
	}
	
	
	public function defineFilePath()
	{
		//DEFINE FILE PATH
		define('BASE_DATA_PATH',BASE_ROOT_PATH.'/Data');
		define('BASE_LOG_PATH', BASE_ROOT_PATH . "/Data/log");
		define('BASE_UPLOAD_PATH',BASE_ROOT_PATH.'/Uploads');
		define('BASE_RESOURCE_PATH',BASE_ROOT_PATH.'/Public/resource');
		define('COMMON_RESOURCE_PATH',BASE_RESOURCE_PATH . "/common");
		define('ADMIN_BASE_PATH', BASE_ROOT_PATH . '/Administrator');
		
		define('FG_BASE_PATH', BASE_ROOT_PATH . "/funny_games");
		define('FG_GAME_PATH', FG_BASE_PATH . "/games");
		define('FG_QRCODE_PATH', FG_BASE_PATH . "/qrcodes");
		define('FG_CONFIG_PATH', FG_BASE_PATH . "/config");
	}
	
	public function loadSetting2Conf()
	{
		$setting = rkcache('setting');
		if (empty($setting))
		{
			$setting = rkcache('setting',true);
		}

		C($setting);
	}
	
	public function initRouter()
	{
		$_GET['c'] = empty($_GET['c']) ? $_GET['c'] : convert_word_underscore(CONTROLLER_NAME);
		$_GET['a'] = empty($_GET['a']) ? $_GET['a'] : ACTION_NAME;
	}
	
	public function defineAppVars()
	{
		//---BIZ-商家入驻状态定义----//
		//新申请
		define('STORE_JOIN_STATE_NEW', 10);
		//完成付款
		define('STORE_JOIN_STATE_PAY', 11);
		//初审成功
		define('STORE_JOIN_STATE_VERIFY_SUCCESS', 20);
		//初审失败
		define('STORE_JOIN_STATE_VERIFY_FAIL', 30);
		//付款审核失败
		define('STORE_JOIN_STATE_PAY_FAIL', 31);
		//开店成功
		define('STORE_JOIN_STATE_FINAL', 40);
	
		//默认颜色规格id(前台显示图片的规格)
		define('DEFAULT_SPEC_COLOR_ID', 1);
	
		//会员登录注册发送短信间隔（单位为秒）
		define('DEFAULT_CONNECT_SMS_TIME', 60);
		//会员登录注册时每个手机号发送短信个数
		define('DEFAULT_CONNECT_SMS_PHONE', 5);
		//会员登录注册时每个IP发送短信个数
		define('DEFAULT_CONNECT_SMS_IP', 20);
	
		/**
		 * 商品图片
		*/
		define('GOODS_IMAGES_WIDTH', '60,240,360,1280');
		define('GOODS_IMAGES_HEIGHT', '60,240,360,12800');
		define('GOODS_IMAGES_EXT', '_60,_240,_360,_1280');
	
		/**
		 *  订单状态
		*/
		//已取消
		define('ORDER_STATE_CANCEL', 0);
		//已产生但未支付
		define('ORDER_STATE_NEW', 10);
		//已支付
		define('ORDER_STATE_PAY', 20);
		//已发货
		define('ORDER_STATE_SEND', 30);
		//已收货，交易成功
		define('ORDER_STATE_SUCCESS', 40);
		//订单超过N小时未支付自动取消
		define('ORDER_AUTO_CANCEL_TIME', 1);
		//订单超过N天未收货自动收货
		define('ORDER_AUTO_RECEIVE_DAY', 10);
	
		//预订尾款支付期限(小时)
		define('BOOK_AUTO_END_TIME', 72);
	
		//门店支付订单支付提货期限(天)
		define('CHAIN_ORDER_PAYPUT_DAY', 7);
		/**
		 * 订单删除状态
		*/
		//默认未删除
		define('ORDER_DEL_STATE_DEFAULT', 0);
		//已删除
		define('ORDER_DEL_STATE_DELETE', 1);
		//彻底删除
		define('ORDER_DEL_STATE_DROP', 2);
	
		/**
		 * 文章显示位置状态,1默认网站前台,2买家,3卖家,4全站
		 * @var unknown
		*/
		define('ARTICLE_POSIT_SHOP', 1);
		define('ARTICLE_POSIT_BUYER', 2);
		define('ARTICLE_POSIT_SELLER', 3);
		define('ARTICLE_POSIT_ALL', 4);
	
		//兑换码过期后可退款时间，15天
		define('CODE_INVALID_REFUND', 15);
	}

	public function initDbConf()
	{
		$da_config = C();
	
		$db_config = $da_config['db'];
		$master = array();
		$slave = array();
		foreach ($db_config as $db_conf_item)
		{
			if ($db_conf_item['master'])
			{
				$master[] = $db_conf_item;
			}
			else
			{
				$slave[] = $db_conf_item;
			}
		}
		if (empty($slave))
		{
			$slave[] = $master[0];
		}
		if (empty($master))
		{
			$master[] = $slave[0];
		}
		$da_config['db']['master'] = $master[array_rand($master, 1)];
		$da_config['db']['slave'] = $slave[array_rand($slave, 1)];
		$da_config['shopda_version'] = '<span class="vol"><font class="o">shopda</font></span>';
		C($da_config);
	
		//TP数据库
		$tpDbConf = array(
				/* 数据库配置 */
				'DB_TYPE'   => 'mysqli',  
				'DB_HOST'   => $da_config['db']['master']['dbhost'],
				'DB_NAME'   => $da_config['db']['master']['dbname'], 
				'DB_USER'   => $da_config['db']['master']['dbuser'], 
				'DB_PWD'    => $da_config['db']['master']['dbpwd'],  
				'DB_PORT'   => $da_config['db']['master']['dbport'], 
				'DB_PREFIX' => $da_config['tablepre'], 
		);
		C($tpDbConf);
		
		define('DBPRE',$da_config['tablepre']);
		define('DBNAME',$da_config['db']['master']['dbname']);
		define('CHARSET',$da_config['db']['master']['dbcharset']);
	}
	
	public function filterInput()
	{
		//对GET POST接收内容进行过滤,$ignore内的下标不被过滤
		$ignore = array('article_content','pgoods_body','doc_content','content',
				'sn_content','g_body','store_description','p_content',
				'groupbuy_intro','remind_content','note_content','adv_pic_url',
				'adv_word_url','adv_slide_url','appcode','mail_content',
				'message_content', 'member_gradedesc');
		$_GET = !empty($_GET) ? Security::getAddslashesForInput($_GET, $ignore) : array();
		$_POST = !empty($_POST) ? Security::getAddslashesForInput($_POST, $ignore) : array();
		$_REQUEST = !empty($_REQUEST) ? Security::getAddslashesForInput($_REQUEST,$ignore) : array();
		$_SERVER = !empty($_SERVER) ? Security::getAddSlashes($_SERVER) : array();
	}
	
}