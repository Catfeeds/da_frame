<?php
/**
 * QRCODE
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Shop\Controller;
use Think\Controller;

class DaqrController extends Controller {

	public function __construct() {
		parent::__construct();
	}

	public function gen_qr_code()
	{
		$url = $_GET['url_content'];
		
		// 生成商店二维码
		require_once(BASE_ROOT_PATH .DS.'Api/phpqrcode'.DS.'index.php');
		
		$PhpQRCode = new \PhpQRCode();
		$PhpQRCode->set('pngTempDir', FG_QRCODE_PATH . DS);
		//print_r($PhpQRCode);
		
		//生成二维码
		$png_base_name = md5($url);
		$file_name =  FG_QRCODE_PATH . DS . $png_base_name . '.png';
		//var_dump($file_name);
		
		
		$PhpQRCode->set('data',$url);
		$PhpQRCode->set('pngTempName', basename($file_name));
		$PhpQRCode->init();
		
		$qr_png_url = FG_QRCODE_URL . basename($file_name);
		
		header('Content-type: image/png');
		$ret = @file_get_contents($file_name);
		echo $ret;
		
		exit;
	}
	
	
	public function gen_qr_code_data() 
	{
		$url = $_GET['url_content'];
		
		// 生成商店二维码
		require_once(BASE_ROOT_PATH .DS.'Api/phpqrcode'.DS.'index.php');
		
		$PhpQRCode = new \PhpQRCode();
		$PhpQRCode->set('pngTempDir', FG_QRCODE_PATH . DS);
		//print_r($PhpQRCode);
		
		//生成二维码
		$png_base_name = md5($url);
		$file_name =  FG_QRCODE_PATH . DS . $png_base_name . '.png';
		//var_dump($file_name);
		
		
		$PhpQRCode->set('data',$url);
		$PhpQRCode->set('pngTempName', basename($file_name));
		$PhpQRCode->init();
		
		$qr_png_url = FG_QRCODE_URL . basename($file_name);
		
		$ret = array("url" => $qr_png_url);
		$ret = json_encode($ret);		
		echo $ret;
		exit;
	}
}