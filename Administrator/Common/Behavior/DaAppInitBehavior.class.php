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

defined('THINK_PATH') or exit();

// 初始化钩子信息
class DaAppInitBehavior extends Behavior {


	// 行为扩展的执行入口必须是run
	public function run(&$content)
	{
		ini_set("register_globals", "On");
	}

}