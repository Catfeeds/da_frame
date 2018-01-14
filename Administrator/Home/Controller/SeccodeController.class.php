<?php
/**
 * 验证码
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Home\Controller;
use Think\Controller;
use Common\Lib\Seccode;
use Common\Lib\Cache;



class SeccodeController extends AdminController {

	public function __construct(){
		parent::__construct();
	}
	/**
	 * 产生验证码
	 *
	 */
	public function makecode(){
		$refererhost = parse_url($_SERVER['HTTP_REFERER']);
		$refererhost['host'] .= !empty($refererhost['port']) ? (':'.$refererhost['port']) : '';

		$seccode = makeSeccode($_GET['shopdamap']);

		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		
		$width = 90;
        $height = 26;
        if ($_GET['type']) {
            $param = explode(',', $_GET['type']);
            $width = intval($param[1]);
            $height = intval($param[0]);
        }
		
		$code = new Seccode();
		$code->code = $seccode;
		$code->width = $width;
		$code->height = $height;
		$code->background = 1;
		$code->adulterate = 1;
		$code->scatter = '';
		$code->color = 1;
		$code->size = 0;
		$code->shadow = 1;
		$code->animator = 0;
		$code->datapath =  BASE_RESOURCE_PATH.'/seccode/';
		$code->display();
	}

	/**
	 * AJAX验证
	 *
	 */
	public function check(){
		if (checkSeccode($_GET['shopdamap'],$_GET['captcha'])){
			exit('true');
		}else{
			exit('false');
		}
	}
}
