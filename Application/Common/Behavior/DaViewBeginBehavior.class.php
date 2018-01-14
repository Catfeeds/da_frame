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
use Think\View;

defined('THINK_PATH') or exit();

// 初始化钩子信息
class DaViewBeginBehavior extends Behavior {

    // 行为扩展的执行入口必须是run
    public function run(&$content) 
    {
    	$view = View::instance();
    	$output = $view->gettVar();
    	
    	$output['setting_config'] = C();
    	//页头
    	$output['html_title'] = $output['html_title']!='' ? $output['html_title'] :$GLOBALS['setting_config']['site_name'];
    	$output['seo_keywords'] = $output['seo_keywords']!='' ? $output['seo_keywords'] :$GLOBALS['setting_config']['site_name'];
    	$output['seo_description'] = $output['seo_description']!='' ? $output['seo_description'] :$GLOBALS['setting_config']['site_name'];
    	$output['ref_url'] = getReferer();
    	@header("Content-type: text/html; charset=".CHARSET);

    	$view->assign("output", $output);
    	
    }
    
}