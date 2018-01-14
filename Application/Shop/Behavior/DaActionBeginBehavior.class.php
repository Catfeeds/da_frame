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
namespace Shop\Behavior;
use Think\Behavior;
use Think\Hook;
defined('THINK_PATH') or exit();

// 初始化钩子信息
class DaActionBeginBehavior extends Behavior {

    // 行为扩展的执行入口必须是run
    public function run(&$content)
    {
//     	var_dump(BASE_STATIC_URL, BASE_RESOURCE_URL);
//     	exit;
    	
    	define('APP_SITE_URL',SHOP_SITE_URL);
    	define('TPL_NAME',TPL_SHOP_NAME); //用于控制主题
    	
    	define('SHOP_RESOURCE_SITE_URL', BASE_RESOURCE_URL . "/shop");
    	define('SHOP_TEMPLATES_URL',     BASE_STATIC_URL . "/shop");
    	define('SHOP_VIEW_BASE_URL', BASE_SITE_URL . DS . 'Application' . DS . MODULE_NAME . DS . 'View');
    }
}