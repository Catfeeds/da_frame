<?php
/**
 * 系统配文件
 * 所有系统级别的配置
 */
$config = array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => ADDON_PATH), //扩展模块列表
    'MODULE_DENY_LIST'   => array('Common',),
    //'MODULE_ALLOW_LIST'  => array('Shop','Admin'),

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => '_t$6uTi:+U=BsG2j!w}cpKz|AF^V-Y~L]Cg<{?9Z', //默认数据加密KEY

    /* 调试配置 */
    'SHOW_PAGE_TRACE' => false,
    'TMPL_CACHE_ON'   => true,
    'DB_FIELD_CACHE'  => true,
    'HTML_CACHE_ON'   => true,
    'APP_DEBUG'       => false,

    /* URL配置 */
    'URL_CASE_INSENSITIVE'  => true, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'             => 0, //URL模式
    'VAR_URL_PARAMS'        => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'     => '/', //PATHINFO URL分割符
	//'URL_CASE_INSENSITIVE'  => false,
    
    /* 全局过滤配置 */
    'DEFAULT_FILTER'        => '', //全局过滤函数

    /*cookie*/
    'COOKIE_PREFIX'         => 'FC71_',
    
    /* 扩展类库 */
    'LOAD_EXT_FILE'         => 'shopda_core,shopda_base,ftp,htmlawed',
    
    /*应用配置*/
    'BASE_SITE_URL'         => 'http://shop.shopda.cn',
    'SHOP_SITE_URL'         => 'http://shop.shopda.cn/index.php?m=Shop',
    'CMS_SITE_URL'          => 'http://shop.shopda.cn/index.php?m=Cms',
    'MICROSHOP_SITE_URL'    => 'http://shop.shopda.cn/index.php?m=Microshop',
    'CIRCLE_SITE_URL'       => 'http://shop.shopda.cn/index.php?m=Circle',
    'MOBILE_SITE_URL'       => 'http://shop.shopda.cn/index.php?m=Mobile',
    'WAP_SITE_URL'          => 'http://shop.shopda.cn/wap',
    'CHAT_SITE_URL'         => 'http://shop.shopda.cn/index.php?m=Chat',
    'WECHAT_SITE_URL'       => 'http://shop.shopda.cn/wechat/ems',
    'NODE_SITE_URL'         => 'http://127.0.0.1:33',
    'DELIVERY_SITE_URL'     => 'http://shop.shopda.cn/index.php?m=Delivery',
    'CHAIN_SITE_URL'        => 'http://shop.shopda.cn/index.php?m=Chain',
    'MEMBER_SITE_URL'       => 'http://shop.shopda.cn/index.php?m=Member',
    'CRONTAB_URL'           => 'http://shop.shopda.cn/index.php?m=Crontab',

    'ADMIN_SITE_URL'        => 'http://shop.shopda.cn/admin.php',
    
    //--新版依赖路径--//
    'BASE_PUBLIC_URL'       => 'http://shop.shopda.cn/Public',
    'BASE_STATIC_URL'       => 'http://shop.shopda.cn/Public/static',
    'BASE_RESOURCE_URL'     => 'http://shop.shopda.cn/Public/resource',
    'RESOURCE_SITE_URL'     => 'http://shop.shopda.cn/Public/resource/common',
    'UPLOAD_SITE_URL'       => 'http://shop.shopda.cn/Uploads',
 
    //--admin相关--//
    'SUN_FLOWER_FILE_URL'   => 'http://shop.shopda.cn',

    'LANG_TYPE'             => 'zh_cn',
    
    'CACHE_OPEN'            => false, //开启 CACHE_TYPE不能设置FILE，注意CACHE 一旦开启至少要支持 redis，有HARDCODE cacheredis 类型
    'CACHE_TYPE'            => 'file', //cache 类型 redis, memcache, file
	'FILE_CACHE_EXPIRE'     => 3600,
    'SESSION_EXPIRE'        => 3600,   //缓存时间
    
//     'REDIS' =>  array (
//     		'prefix' => 'shopda_',
//     		'master' =>
//     		array (
//     				'port' => 6379,
//     				'host' => '127.0.0.1',
//     				'pconnect' => 0,
//     		),
//     		'slave' =>
//     		array (
//     		),
//     ),

	//全文索引
    'FULLINDEXER' =>  array (
    		'open' => false,
    		'appname' => 'shopda',
    ),
    
    'SUBDOMAIN_SUFFIX'     => '',
    
//     'SESSION_TYPE' => 'redis',
//     'SESSION_SAVE_PATH' => 'tcp://127.0.0.1:6379',
    
    
    'FLOWSTAT_TABLENUM'    => 3,
    'QUEUE' => array (
		'open' => false,
		'host' => '127.0.0.1',
		'port' => 6379,
    ),
    'HTTPS'                => false,   
    
    'NODE_CHAT'            => true,
    'RUNTIME_PATH_LIST'    => array("./RuntimeBE/", "./RuntimeFE/"),
    
    'TMPL_EXCEPTION_FILE'=> APP_PATH . '/Common/View/default/error.html',
    'URL_CASE_INSENSITIVE' => true,
    
    'VERSION'               => '201704200001',
    'SETUP_DATE'            => '2017-12-26 22:11:32',
    'DBDRIVER'              => 'mysqli',
    'TABLEPRE'              => 'shopda_',
    'DB' => array (
    		array (
    				'dbhost'    => 'localhost',
    				'dbport'    => '3306',
    				'dbuser'    => 'root',
    				'dbpwd'     => 'admin888',
    				'dbname'    => 'v5_5_shopnc',
    				'dbcharset' => 'UTF-8',
    				'master'    => true,
    		),
    ),
);

return $config;