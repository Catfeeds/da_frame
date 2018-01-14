<?php
/**
 * 缓存操作
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Common\Lib;

class Cache {

	protected $params;
	protected $enable;
	protected $handler;

	/**
	 * 实例化缓存驱动
	 *
	 * @param unknown_type $type
	 * @param unknown_type $args
	 * @return unknown
	 */
	public function connect($type,$args = array()){
		
		if (empty($type)) $type = C('cache_open') ? C('cache_type') : 'file';
		$type = strtolower($type);

		$class = 'Common\Lib\Cache'.ucwords($type);
		return new $class($args);
	}

	/**
	 * 取得实例
	 *
	 * @return object
	 */
	public static function getInstance(){
		$args = func_get_args();
		//var_dump($args);
		return get_obj_instance(__CLASS__,'connect',$args);
	}
}
