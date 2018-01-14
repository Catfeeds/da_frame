<?php
/**
 * file 缓存
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Common\Lib;

class CacheFile extends Cache{

	public function __construct($params = array()){
		$this->params['expire'] = C('file_cache_expire');
		$this->params['path'] = BASE_ROOT_PATH . DS . trim(RUNTIME_PATH, './') . DS  . 'Cache' . DS . MODULE_NAME;
		$this->enable = true;
	}

	private function init(){
		return true;
	}

	private function isConnected(){
		return $this->enable;
	}

	public function get($key, $path=null){
		$ret = false;
		
		$filename = realpath($this->_path($key));
 		
		//var_dump($key,$this->_path($key), $filename);
		
		if (is_file($filename)){
			$ret = require($filename);
		}else{
			$ret = false;
		}
		//var_dump($ret);
		return $ret;
	}

	public function set($key, $value, $path=null, $expire=null){
		
		$file_arr = array();
		$runtimePathArr = array("./RuntimeBE/", "./RuntimeFE/");
		foreach ($runtimePathArr as $runtimePath)
		{
			$GLOBALS['RUNTIME_PATH_FOR_CACHE'] = $runtimePath;
			$filename = $this->_path($key);
			$file_arr[] = $filename;
		}
 
		//var_dump($file_arr);
 
		$ret = true;
		foreach ($file_arr as $filename) {
			$write_res = write_file($filename,$value);
			//var_dump($filename, $value, $write_res);
			if (false == $write_res){
				$ret =  false;
			}
		}
		return $ret;
	}

	public function rm($key, $path=null){
		
		$file_arr = array();
		$runtimePathArr = C('RUNTIME_PATH_LIST');
		foreach ($runtimePathArr as $runtimePath)
		{
			$GLOBALS['RUNTIME_PATH_FOR_CACHE'] = $runtimePath;
			$filename = $this->_path($key);
			$file_arr[] = $filename;
		}
		
		$ret = true;
		foreach ($file_arr as $filename) {
			if (is_file($filename)) {
				@unlink($filename);
			}else{
				$ret = false;
			}
		}
		return $ret;
	}

	//缓存文件收敛
	private function _path($key){
		
		$key = str_replace("//", "/", trim($key));
		$key = str_replace("/", "_", $key);
		
		$runtimePath = $GLOBALS['RUNTIME_PATH_FOR_CACHE'];
		if (empty($runtimePath))
		{
			$runtimePath = RUNTIME_PATH;
		}

		switch (strtolower($key)) {
			default:
				$path = BASE_ROOT_PATH . DS . trim($runtimePath, './') .  DS .'Cache' . DS . "Shopda";
				break;
		}
		$ret = $path.'/' . "_" . $key.'.php';
 
		return $ret;
	}
}
?>