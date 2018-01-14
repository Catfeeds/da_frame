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
namespace Crontab\Behavior;
use Think\Behavior;
use Think\Hook;
defined('THINK_PATH') or exit();

// 初始化钩子信息
class DaActionBeginBehavior extends Behavior {

    // 行为扩展的执行入口必须是run
    public function run(&$content)
    {
    	define('TRANS_MASTER',true);
 
    	if (PHP_SAPI == 'cli') {
    		$_GET['c'] = $_SERVER['argv'][1];
    		$_GET['a'] = empty($_SERVER['argv'][2]) ? 'index' : $_SERVER['argv'][2];
    	}
    }
}