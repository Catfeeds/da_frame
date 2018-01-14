<?php
/**
 * 公共方法
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
use Common\Lib\Cache;
use Common\Lib\CacheFile;
use Common\Lib\Chat;
use Common\Lib\Db;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;
use Common\Lib\Seccode;
use Common\Lib\Security;
use Common\Lib\Tpl;
use Common\Lib\Validate;

/**
 * 获取HTTP请求头
 */
if (! function_exists ( 'getallheaders' )) {
	function getallheaders() {
		foreach ( $_SERVER as $name => $value ) {
			if (substr ( $name, 0, 5 ) == 'HTTP_') {
				$headers [str_replace ( ' ', '-', ucwords ( strtolower ( str_replace ( '_', ' ', substr ( $name, 5 ) ) ) ) )] = $value;
			}
		}
		return $headers;
	}
}

/*
 * 自定义memory_get_usage()
 *
 * @return 内存使用额度，如果该方法无效，返回0
 */
if (! function_exists ( 'memory_get_usage' )) {
	function memory_get_usage() { // 目前程序不兼容5以下的版本
		return 0;
	}
}

/**
 * 获取文件列表(所有子目录文件)
 *
 * @param string $path
 *        	目录
 * @param array $file_list
 *        	存放所有子文件的数组
 * @param array $ignore_dir
 *        	需要忽略的目录或文件
 * @return array 数据格式的返回结果
 */
function readFileList($path, &$file_list, $ignore_dir = array()) {
	$path = rtrim ( $path, '/' );
	if (is_dir ( $path )) {
		$handle = @opendir ( $path );
		if ($handle) {
			while ( false !== ($dir = readdir ( $handle )) ) {
				if ($dir != '.' && $dir != '..') {
					if (! in_array ( $dir, $ignore_dir )) {
						if (is_file ( $path . DS . $dir )) {
							$file_list [] = $path . DS . $dir;
						} elseif (is_dir ( $path . DS . $dir )) {
							readFileList ( $path . DS . $dir, $file_list, $ignore_dir );
						}
					}
				}
			}
			@closedir ( $handle );
			// return $file_list;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * 数据库模型实例化入口
 *
 * @param string $model
 *        	模型名称
 * @return obj 对象形式的返回结果
 */
function Model($model = null) {
	static $_cache = array ();
	if (! is_null ( $model ) && isset ( $_cache [$model] ))
		return $_cache [$model];
	
	if (empty ( $model )) {
		return new Model ( $model );
	}
	
	try {
		$class_name = "\\Common\Model\\" . $model . "Model";
		$_cache [$model] = new $class_name ();
		return $_cache [$model];
	} catch ( Exception $e ) {
		$error = 'Model Error:  Class ' . $class_name . ' is not exists!';
		$error .= "<br>" . $e->getMessage ();
		throw_exception ( $error );
	}
}

/**
 * 行为模型实例
 *
 * @param string $model
 *        	模型名称
 * @return obj 对象形式的返回结果
 */
function Logic($model = null, $base_path = null) {
	static $_cache = array ();
	if (! is_null ( $model ) && isset ( $_cache [$model] ))
		return $_cache [$model];
	
	if (empty ( $model )) {
		return new Model ( $model );
	}
	
	try {
		$class_name = "\\Common\Logic\\" . $model . "Logic";
		$_cache [$model] = new $class_name ();
		return $_cache [$model];
	} catch ( Exception $e ) {
		$error = 'Logic Error:  Class ' . $class_name . ' is not exists!';
		$error .= "<br>" . $e->getMessage ();
		throw_exception ( $error );
	}
}

/**
 * 删除缓存目录
 *
 * @param string $dir
 *        	目录名
 * @return boolean
 */
function delCacheFile($dir) {
	
	$runtimePathArr = array("./RuntimeBE/", "./RuntimeFE/");
	
	foreach ($runtimePathArr as $runtimePath)
	{
		$path = BASE_ROOT_PATH . DS . trim($runtimePath, './') .  DS .'Cache' . DS . "Shopda";
		
		$path = str_replace ( DS . DS, DS, $path );
		 
		//var_dump($dir);
		
		if (is_dir ( $path )) { 
			$file_list = array ();
			readFileList ( $path, $file_list );
		
			if (! empty ( $file_list )) {
				foreach ( $file_list as $v ) {
		
					$base_cache_file_name = basename($v);
		
					if (strstr($base_cache_file_name , trim($dir)))
					{
						if (basename ( $v ) != 'index.html') {
							//echo 'A';
							@unlink ( $v );
						}
					}
				}
			}
		}
	}
	return true;
}

/**
 * 取得随机数
 *
 * @param int $length
 *        	生成随机数的长度
 * @param int $numeric
 *        	是否只产生数字随机数 1是0否
 * @return string
 */
function random($length, $numeric = 0) {
	$seed = base_convert ( md5 ( microtime () . $_SERVER ['DOCUMENT_ROOT'] ), 16, $numeric ? 10 : 35 );
	$seed = $numeric ? (str_replace ( '0', '', $seed ) . '012340567890') : ($seed . 'zZ' . strtoupper ( $seed ));
	$hash = '';
	$max = strlen ( $seed ) - 1;
	for($i = 0; $i < $length; $i ++) {
		$hash .= $seed {mt_rand ( 0, $max )};
	}
	return $hash;
}

/**
 *
 * @param
 *        	mix
 * @return mix
 *
 */
function genSecKey($length = 20, $charList = "") {
	if (empty ( $charList )) {
		$charList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	}
	mt_srand ( 10000000 * ( double ) microtime () );
	for($i = 0, $str = '', $lc = strlen ( $charList ) - 1; $i < $length; $i ++) {
		$str .= $charList [mt_rand ( 0, $lc )];
	}
	return $str;
}

/**
 * 封装分页操作到函数，方便调用
 *
 * @param string $cmd
 *        	命令类型
 * @param mixed $arg
 *        	参数
 * @return mixed
 */
function pagecmd($cmd = '', $arg = '') {
	static $page;
	if ($page == null) {
		$page = new Page ();
	}
	
	switch (strtolower ( $cmd )) {
		case 'seteachnum' :
			$page->setEachNum ( $arg );
			break;
		case 'settotalnum' :
			$page->setTotalNum ( $arg );
			break;
		case 'setstyle' :
			$page->setStyle ( $arg );
			break;
		case 'show' :
			return $page->show ( $arg );
			break;
		case 'obj' :
			return $page;
			break;
		case 'gettotalnum' :
			return $page->getTotalNum ();
			break;
		case 'gettotalpage' :
			return $page->getTotalPage ();
			break;
		case 'getnowpage' :
			return $page->getNowPage ();
			break;
		case 'settotalpagebynum' :
			return $page->setTotalPageByNum ( $arg );
			break;
		default :
			break;
	}
}

/**
 * 取得对象实例
 *
 * @param object $class        	
 * @param string $method        	
 * @param array $args        	
 * @return object
 */
function get_obj_instance($class, $method = '', $args = array()) {
	static $_cache = array ();
	$key = $class . $method . (empty ( $args ) ? null : md5 ( serialize ( $args ) ));
	if (isset ( $_cache [$key] )) {
		return $_cache [$key];
	} else {
		if (class_exists ( $class )) {
			$obj = new $class ();
			if (method_exists ( $obj, $method )) {
				if (empty ( $args )) {
					$_cache [$key] = $obj->$method ();
				} else {
					$_cache [$key] = call_user_func_array ( array (
							&$obj,
							$method 
					), $args );
				}
			} else {
				$_cache [$key] = $obj;
			}
			return $_cache [$key];
		} else {
			throw_exception ( 'Class ' . $class . ' isn\'t exists!' );
		}
	}
}

// 记录和统计时间（微秒）
function addUpTime($start, $end = '', $dec = 3) {
	static $_info = array ();

	if (! empty ( $end )) { // 统计时间
		if (! isset ( $_info [$end] )) {
			$_info [$end] = microtime ( TRUE );
		}
		return number_format ( ($_info [$end] - $_info [$start]), $dec );
	} else { // 记录时间
		$_info [$start] = microtime ( TRUE );
	}
}

/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 *
 * @param string $name
 *        	缓存名称
 * @param mixed $value
 *        	缓存值
 * @param string $path
 *        	缓存路径
 * @return mixed
 */
function F($name, $value = '', $path = DATA_PATH) {
	static $_cache = array ();
	$filename = $path . $name . '.php';
	if ('' !== $value) {
		if (is_null ( $value )) {
			// 删除缓存
			if (false !== strpos ( $name, '*' )) {
				return false; // TODO
			} else {
				unset ( $_cache [$name] );
				return Think\Storage::unlink ( $filename, 'F' );
			}
		} else {
			Think\Storage::put ( $filename, serialize ( $value ), 'F' );
			// 缓存数据
			$_cache [$name] = $value;
			return true;
		}
	}
	// 获取缓存数据
	if (isset ( $_cache [$name] ))
		return $_cache [$name];
	if (Think\Storage::has ( $filename, 'F' )) {
		$value = unserialize ( Think\Storage::read ( $filename, 'F' ) );
		$_cache [$name] = $value;
	} else {
		$value = false;
	}
	return $value;
}
function http_post($url, $param) {
	$postdata = http_build_query ( $param );
	
	$opts = array (
			'http' => array (
					'method' => 'POST',
					'header' => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postdata 
			) 
	);
	
	$context = stream_context_create ( $opts );
	
	return @file_get_contents ( $url, false, $context );
}
function http_postdata($url, $postdata) {
	$opts = array (
			'http' => array (
					'method' => 'POST',
					'header' => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postdata 
			) 
	);
	
	$context = stream_context_create ( $opts );
	
	return @file_get_contents ( $url, false, $context );
}
function convert_word_camel_case($str) {
	$str = preg_replace_callback ( '/([-_]+([a-z]{1}))/i', function ($matches) {
		return strtoupper ( $matches [2] );
	}, $str );
	$str = ucfirst ( $str );
	return $str;
}
function convert_word_underscore($str) {
	$str = preg_replace_callback ( '/([A-Z]{1})/', function ($matches) {
		return '_' . strtolower ( $matches [0] );
	}, $str );
	$str = strtolower ( $str );
	$str = trim ( $str, "_" );
	return $str;
}

/**
 * 调用推荐位
 *
 * @param unknown_type $rec_id        	
 * @return string
 */
function rec_position($rec_id = null) {
	if (! is_numeric ( $rec_id ))
		return null;
	$string = '';
	
	$info = rkcache ( "rec_position/{$rec_id}", function ($rec_id) {
			$rec_id = substr ( $rec_id, strlen ( 'rec_position/' ) );
			return Model ( 'rec_position' )->where ( array (
					'rec_id' => $rec_id 
			) )->find ();
		} );
 
	$info ['content'] = unserialize ( $info ['content'] );
	if ($info ['content'] ['target'] == 2)
		$target = 'target="_blank"';
	else
		$target = '';
	if ($info ['pic_type'] == 0) { // 文字
		foreach ( ( array ) $info ['content'] ['body'] as $v ) {
			$href = '';
			if ($v ['url'] != '')
				$href = "href=\"{$v['url']}\"";
			$string .= "<li><a {$target} {$href}>{$v['title']}</a></li>";
		}
		$string = "<ul>{$string}</ul>";
	} else { // 图片
		$width = $height = '';
		if (is_numeric ( $info ['content'] ['width'] ))
			$width = "width=\"{$info['content']['width']}\"";
		if (is_numeric ( $info ['content'] ['height'] ))
			$height = "height=\"{$info['content']['height']}\"";
		if (is_array ( $info ['content'] ['body'] )) {
			if (count ( $info ['content'] ['body'] ) > 1) {
				foreach ( $info ['content'] ['body'] as $v ) {
					if ($info ['pic_type'] == 1)
						$v ['title'] = UPLOAD_SITE_URL . '/' . $v ['title'];
					$href = '';
					if ($v ['url'] != '')
						$href = "href=\"{$v['url']}\"";
					$string .= "<li><a {$target} {$href}><img {$width} {$height} src=\"{$v['title']}\"></a></li>";
				}
				$string = "<ul>{$string}</ul>";
			} else {
				$v = $info ['content'] ['body'] [0];
				if ($info ['pic_type'] == 1)
					$v ['title'] = UPLOAD_SITE_URL . '/' . $v ['title'];
				$href = '';
				if ($v ['url'] != '')
					$href = "href=\"{$v['url']}\"";
				$string .= "<a {$target} {$href}><img {$width} {$height} src=\"{$v['title']}\"></a>";
			}
		}
	}
	return $string;
}


/**
 * 调用推荐位
 *
 * @param int $rec_id
 *        	推荐位ID
 * @return string 推荐位内容
 */
function rec($rec_id = null) {
	return rec_position ( $rec_id );
}

function getView() {
	$ret = Think\View::instance ();
	return $ret;
}



function secCheckToken()
{
	return Security::checkToken ();
}


/**
 * 检测FORM是否提交
 *
 * @param $check_token 是否验证token
 * @param $check_captcha 是否验证验证码
 * @param $return_type 'alert','num'
 * @return boolean
 */
function chksubmit($check_token = false, $check_captcha = false, $return_type = 'alert', $extjs = '') {
	$submit = isset ( $_POST ['form_submit'] ) ? $_POST ['form_submit'] : $_GET ['form_submit'];
	if ($submit != 'ok')
		return false;
	if ($check_token && ! secCheckToken()) {
		if ($return_type == 'alert') {
			showDialog ( 'Token error!' );
		} else {
			return - 11;
		}
	}
	if ($check_captcha) {
		if (! checkSeccode ( $_POST ['shopdamap'], $_POST ['captcha'] )) {
			setDaCookie ( 'seccode' . $_POST ['shopdamap'], '', - 3600 );
			if ($return_type == 'alert') {
				showDialog ( '验证码错误!' , "", "error", $extjs);
			} else {
				return - 12;
			}
		}
		setDaCookie ( 'seccode' . $_POST ['shopdamap'], '', - 3600 );
	}
	return true;
}


function daParseUrl($url)
{
	$ret = array();
	$parseRes = parse_url($url);

	$tempQueryArr = array();
	$query = $parseRes['query'];
	$query = str_replace('&amp;', '&', $query);
 
	$queryArr = explode("&", $query);
  
	foreach ($queryArr as $item)
	{
		$itemArr = explode("=", $item);
		$tempQueryArr[$itemArr[0]] = $itemArr[1];
	}
	$ret['query'] = $tempQueryArr;
 
	return $ret;
}

/**
 * 获取提交fields
 * */
function getSubmitDataFields($arr)
{
	$list = convertMultiArr2List($arr);
	$key_list = array_keys($list);
	return $key_list;
}

/**
 * 获取提交数据
 * */
function getSubmitDataArr($conf_fields_str = '')
{
	$ret = array();
	if (empty($conf_fields_str))
	{
		$conf_fields_str = $_POST['sh' . 'o' . 'p' . 'd' . 'a' . '_form_fields_str'];
	}

	if (empty($conf_fields_str))
	{
		return $ret;
	}
	$conf_fields_arr = explode(",", $conf_fields_str);
	sort($conf_fields_arr);

	foreach ($conf_fields_arr as $item)
	{
		$post_key = $item;
		$post_val = $_POST[$post_key];

		$item = trim($item);
		$item_arr = explode(":", $item);
		if (count($item_arr) == 1)
		{
			$ret[$item] = $post_val;
		}
		else
		{
			$eval_item_str = implode("']['", $item_arr);
			$eval_item_str = "\$ret['" . $eval_item_str . "']=\$post_val;";
			eval($eval_item_str);
		}
	}
	return $ret;
}


/**
 * 多维数组转1维数组
 * */
function convertMultiArr2List($arr, $preKey = null)
{
	$retData = array();
	if (!is_array($arr)) {
		return array();
	}

	foreach ($arr as $key => $value) {
		$retKey = $preKey !== null ? "{$preKey}:{$key}" : $key;
		if (!is_array($value)) {
			$retData[$retKey] = $value;
		} else {
			$res = convertMultiArr2List($value, $retKey);
			if (is_array($res)) {
				$retData = array_merge($retData, $res);
			}
		}
	}

	return $retData;
}

/**
 * 解析url
 * */
function da_parse_url($url)
{
	$url_arr = parse_url($url);
	$temp_query_arr = array();
	$query = isset($url_arr['query']) ? $url_arr['query'] : array();
	if (empty($query))
	{
		return $url_arr;
	}
	else
	{
		$query_arr = explode("&", $query);
 
		foreach ($query_arr as $query_item)
		{
			$query_item_arr = explode("=", $query_item);
			//var_dump($query_item_arr);
			$temp_query_arr[$query_item_arr[0]] = $query_item_arr[1];
		}
	}
	$url_arr['query'] = $temp_query_arr;
	return $url_arr;
}

function sortList($arr, $sort_by_key, $sort_order = 'ASC', $sort_type = 'REGULAR') {
	if (! is_array ( $arr ) || empty ( $arr )) {
		return $arr;
	}
	
	$sort_order = strtoupper ( $sort_order ) == 'ASC' ? SORT_ASC : SORT_DESC;
	$sort_type = strtoupper ( $sort_type );
	switch ($sort_type) {
		case 'REGULAR' :
			$sort_type = SORT_REGULAR;
			break;
		case 'NUMERIC' :
			$sort_type = SORT_NUMERIC;
			break;
		case 'STRING' :
			$sort_type = SORT_STRING;
			break;
		default :
			$sort_type = SORT_REGULAR;
			break;
	}
	
	$sort_by_arr = array ();
	foreach ( $arr as $k => $v ) {
		$sort_by_arr [$k] = $v [$sort_by_key];
	}
	array_multisort ( $sort_by_arr, $sort_order, $sort_type, $arr );
	
	return $arr;
}


function clear_url_param($url)
{
	$ret = "";
	if (empty($url))
	{
		return $ret;
	}
	$url_arr = parse_url($url);
	$ret = $url_arr['scheme'] . "://" . $url_arr['host'] . $url_arr['path'];
	
	return $ret;
}