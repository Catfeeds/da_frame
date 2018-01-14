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
namespace Chat\Behavior;
use Think\Behavior;
use Think\Hook;
defined('THINK_PATH') or exit();

// 初始化钩子信息
class DaActionBeginBehavior extends Behavior {

    // 行为扩展的执行入口必须是run
    public function run(&$content)
    {
    	define('APP_SITE_URL',SHOP_SITE_URL); //仅个入口文件有，并无用处
    	define('TPL_NAME',TPL_SHOP_NAME);
    	
    	define('SHOP_RESOURCE_SITE_URL', BASE_PUBLIC_RES_URL . DS. 'shop' . DS .TPL_NAME); //合并为同一个
    	define('SHOP_TEMPLATES_URL',     SHOP_RESOURCE_SITE_URL);
    	
    	//define('BASE_TPL_PATH',BASE_PATH.'/templates/'.TPL_NAME); //仅用于模板include路径，删除 TODO
    }
}