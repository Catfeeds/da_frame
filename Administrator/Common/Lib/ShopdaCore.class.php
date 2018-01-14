<?php

namespace Common\Lib;

use Think;

// use Common\Lib\Model;
// use Common\Lib\Cache;
// use Common\Lib\Db;
// use Common\Lib\Email;
// use Common\Lib\Language;
// use Common\Lib\Log;
// use Common\Lib\Page;
// use Common\Lib\Process;
// use Common\Lib\Security;
// use Common\Lib\Tpl;
// use Common\Lib\Validate;
// use Common\Lib\Oss;
class ShopdaCore {
	
	/**
	 * 生成需要的js循环。递归调用	PHP
	 *
	 * 形式参考 （ 2个规格）
	 * $('input[type="checkbox"]').click(function(){
	 * str = '';
	 * for (var i=0; i<spec_group_checked[0].length; i++ ){
	 * td_1 = spec_group_checked[0][i];
	 * for (var j=0; j<spec_group_checked[1].length; j++){
	 * td_2 = spec_group_checked[1][j];
	 * str += '<tr><td>'+td_1[0]+'</td><td>'+td_2[0]+'</td><td><input type="text" /></td><td><input type="text" /></td><td><input type="text" /></td>';
	 * }
	 * }
	 * $('table[class="spec_table"] > tbody').empty().html(str);
	 * });
	 */
	static public function recursionSpec($len, $sign) {
		if ($len < $sign) {
			echo "for (var i_" . $len . "=0; i_" . $len . "<spec_group_checked[" . $len . "].length; i_" . $len . "++){ td_" . (intval ( $len ) + 1) . " = spec_group_checked[" . $len . "][i_" . $len . "];\n";
			$len ++;
			recursionSpec ( $len, $sign );
		} else {
			echo "var tmp_spec_td = new Array();\n";
			for($i = 0; $i < $len; $i ++) {
				echo "tmp_spec_td[" . ($i) . "] = td_" . ($i + 1) . "[1];\n";
			}
			echo "tmp_spec_td.sort(function(a,b){ return a-b});\n";
			echo "var spec_bunch = 'i_';\n";
			for($i = 0; $i < $len; $i ++) {
				echo "spec_bunch += tmp_spec_td[" . ($i) . "];\n";
			}
			echo "str += '<input type=\"hidden\" name=\"spec['+spec_bunch+'][goods_id]\" spd_type=\"'+spec_bunch+'|id\" value=\"\" />';";
			for($i = 0; $i < $len; $i ++) {
				echo "if (td_" . ($i + 1) . "[2] != null) {  str += '<input type=\"hidden\" name=\"spec['+spec_bunch+'][color]\" value=\"'+td_" . ($i + 1) . "[1]+'\" />';}";
				echo "str +='<td><input type=\"hidden\" name=\"spec['+spec_bunch+'][sp_value]['+td_" . ($i + 1) . "[1]+']\" value=\"'+td_" . ($i + 1) . "[0]+'\" />'+td_" . ($i + 1) . "[0]+'</td>';\n";
			}
			echo "str +='<td><input class=\"text price\" type=\"text\" name=\"spec['+spec_bunch+'][marketprice]\" data_type=\"marketprice\" spd_type=\"'+spec_bunch+'|marketprice\" value=\"\" /><em class=\"add-on\"><i class=\"icon-renminbi\"></i></em></td>' +
                    '<td><input class=\"text price\" type=\"text\" name=\"spec['+spec_bunch+'][price]\" data_type=\"price\" spd_type=\"'+spec_bunch+'|price\" value=\"\" /><em class=\"add-on\"><i class=\"icon-renminbi\"></i></em></td>' +
                    '<td><input class=\"text stock\" type=\"text\" name=\"spec['+spec_bunch+'][stock]\" data_type=\"stock\" spd_type=\"'+spec_bunch+'|stock\" value=\"\" /></td>' +
                    '<td><input class=\"text stock\" type=\"text\" name=\"spec['+spec_bunch+'][alarm]\" data_type=\"alarm\" spd_type=\"'+spec_bunch+'|alarm\" value=\"\" /></td>' +
                    '<td><input class=\"text sku\" type=\"text\" name=\"spec['+spec_bunch+'][sku]\" spd_type=\"'+spec_bunch+'|sku\" value=\"\" /></td>' +
                    '<td><input class=\"text sku\" type=\"text\" name=\"spec['+spec_bunch+'][barcode]\" spd_type=\"'+spec_bunch+'|barcode\" value=\"\" /></td>' +
                    '</tr>';\n";
			for($i = 0; $i < $len; $i ++) {
				echo "}\n";
			}
		}
	}


	/**
	 * 格式化ubb标签
	 *
	 * @param string $theme_content/$reply_content
	 *        	话题内容/回复内容
	 * @return string
	 */
	static public function ubb($ubb) {
		$ubb = str_replace ( array (
				'[B]',
				'[/B]',
				'[I]',
				'[/I]',
				'[U]',
				'[/U]',
				'[IMG]',
				'[/IMG]',
				'[/FONT]',
				'[/FONT-SIZE]',
				'[/FONT-COLOR]' 
		), array (
				'<b>',
				'</b>',
				'<i>',
				'</i>',
				'<u>',
				'</u>',
				'<img class="pic" src="',
				'"/>',
				'</span>',
				'</span>',
				'</span>' 
		), preg_replace ( array (
				"/\[URL=(.*)\](.*)\[\/URL\]/iU",
				"/\[FONT=([A-Za-z ]*)\]/iU",
				"/\[FONT-SIZE=([0-9]*)\]/iU",
				"/\[FONT-COLOR=([A-Za-z0-9]*)\]/iU",
				"/\[SMILIER=([A-Za-z_]*)\/\]/iU",
				"/\[FLASH\](.*)\[\/FLASH\]/iU",
				"/\\n/i" 
		), array (
				"<a href=\"$1\" target=\"_blank\">$2</a>",
				"<span style=\"font-family:$1\">",
				"<span style=\"font-size:$1px\">",
				"<span style=\"color:#$1\">",
				"<img src=\"" . BASE_CIRCLE_STATIC_URL . "/images/smilier/$1.png\">",
				"<embed src=\"$1\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" wmode=\"opaque\" width=\"480\" height=\"400\"></embed>",
				"<br />" 
		), $ubb ) );
		return $ubb;
	}
	/**
	 * 去掉ubb标签
	 *
	 * @param string $theme_content/$reply_content
	 *        	话题内容/回复内容
	 * @return string
	 */
	static public function removeUBBTag($ubb) {
		$ubb = str_replace ( array (
				'[B]',
				'[/B]',
				'[I]',
				'[/I]',
				'[U]',
				'[/U]',
				'[/FONT]',
				'[/FONT-SIZE]',
				'[/FONT-COLOR]' 
		), array (
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'' 
		), preg_replace ( array (
				"/\[URL=(.*)\](.*)\[\/URL\]/iU",
				"/\[FONT=([A-Za-z ]*)\]/iU",
				"/\[FONT-SIZE=([0-9]*)\]/iU",
				"/\[FONT-COLOR=([A-Za-z0-9]*)\]/iU",
				"/\[SMILIER=([A-Za-z_]*)\/\]/iU",
				"/\[IMG\](.*)\[\/IMG\]/iU",
				"/\[FLASH\](.*)\[\/FLASH\]/iU",
				"<img class='pi' src=\"$1\"/>" 
		), array (
				"$2",
				"",
				"",
				"",
				"",
				"",
				"",
				"" 
		), $ubb ) );
		return $ubb;
	}
	

	/**
	 * sns表情标示符替换为html
	 */
	static public function parsesmiles($message) {
		$smilescache_file = COMMON_RESOURCE_PATH . DS . 'smilies' . DS . 'smilies.php';
		if (file_exists ( $smilescache_file )) {
			include $smilescache_file;
			if (strtoupper ( CHARSET ) == 'GBK') {
				$smilies_array = Language::getGBK ( $smilies_array );
			}
			if (! empty ( $smilies_array ) && is_array ( $smilies_array )) {
				$imagesurl = RESOURCE_SITE_URL . DS . 'js' . DS . 'smilies' . DS . 'images' . DS;
				$replace_arr = array ();
				foreach ( $smilies_array ['replacearray'] as $key => $smiley ) {
					$replace_arr [$key] = '<img src="' . $imagesurl . $smiley ['imagename'] . '" title="' . $smiley ['desc'] . '" border="0" alt="' . $imagesurl . $smiley ['desc'] . '" />';
				}
	
				$message = preg_replace ( $smilies_array ['searcharray'], $replace_arr, $message );
			}
		}
		return $message;
	}
	
	/**
	 * 输出聊天信息
	 *
	 * @return string
	 */
	static public function getChat($layout) {
		return Chat::getChatHtml ( $layout );
	}

	
	/**
	 * 验证是否为平台店铺
	 *
	 * @return boolean
	 */
	static public function checkPlatformStore($store_id = 0) {
		if (isset ( $_SESSION ['is_own_shop'] )) {
			return $_SESSION ['is_own_shop'];
		} else {
			$own_shop_ids = Model ( 'store' )->getOwnShopIds ( true );
			return in_array ( $store_id, $own_shop_ids );
		}
	}

	/**
	 * 输出validate的验证信息
	 *
	 * @param array/string $error
	 */
	static public function showValidateError($error) {
		if (! empty ( $_GET ['inajax'] )) {
			$err_arr = explode ( '<br/>', $error ) ;
			foreach ( $err_arr as $v ) {
				if (trim ( $v != '' )) {
					return showDialog ( $v, '', 'error', '', 3 );
				}
			}
		} else {
			return showDialog ( $error, '', 'error', '', 3 );
		}
		return;
	}
	
	
	/**
	 * KV缓存 读
	 *
	 * @param string $key
	 *        	缓存名称
	 * @param boolean $callback
	 *        	缓存读取失败时是否使用回调 true代表使用cache.model中预定义的缓存项 默认不使用回调
	 * @param callable $callback
	 *        	传递非boolean值时 通过is_callable进行判断 失败抛出异常 成功则将$key作为参数进行回调
	 * @return mixed
	 */
	static public function rkcache($key, $callback = false) {
		if (C ( 'cache_open' )) {
			$cacher = Cache::getInstance ( C ( 'cache_type' ) );
		} else {
			$cacher = Cache::getInstance ( 'file', null );
		}
		if (! $cacher) {
			throw new Exception ( 'Cannot fetch cache object!' );
		}
	
		$value = false;
		if (! C ( "app_debug" )) {
			$value = $cacher->get ( $key );
		}
	
		if ($value === false && $callback !== false) {
	
			if ($callback === true) {
				$callback = array (
						Model ( 'cache' ),
						'call'
				);
			}
	
			if (! is_callable ( $callback )) {
				throw new Exception ( 'Invalid rkcache callback!' );
			}
			$value = call_user_func ( $callback, $key );
	
			wkcache ( $key, $value );
		}
		return $value;
	}
	
	/**
	 * KV缓存 写
	 *
	 * @param string $key
	 *        	缓存名称
	 * @param mixed $value
	 *        	缓存数据 若设为否 则下次读取该缓存时会触发回调（如果有）
	 * @param int $expire
	 *        	缓存时间 单位秒 null代表不过期
	 * @return boolean
	 */
	static public function wkcache($key, $value, $expire = null) {
		if (C ( 'cache_open' )) {
			$cacher = Cache::getInstance ( C ( 'cache_type' ) );
		} else {
			$cacher = Cache::getInstance ( 'file', null );
		}
	
		if (! $cacher) {
			throw new Exception ( 'Cannot fetch cache object!' );
		}
		// 		var_dump($key, $value);
		return $cacher->set ( $key, $value, null, $expire );
	}
	
	/**
	 * KV缓存 删
	 *
	 * @param string $key
	 *        	缓存名称
	 * @return boolean
	 */
	static public function dkcache($key) {
		if (C ( 'cache_open' )) {
			$cacher = Cache::getInstance ( C ( 'cache_type' ) );
		} else {
			$cacher = Cache::getInstance ( 'file', null );
		}
		if (! $cacher) {
			throw new Exception ( 'Cannot fetch cache object!' );
		}
	
		return $cacher->rm ( $key );
	}
	
	/**
	 * 读取缓存信息
	 *
	 * @param string $key
	 *        	要取得缓存键
	 * @param string $prefix
	 *        	键值前缀
	 * @param string $fields
	 *        	所需要的字段
	 * @return array/bool
	 */
	static public function rcache($key = null, $prefix = '', $fields = '*') {
		if ($key === null || ! C ( 'cache_open' ))
			return array ();
	
		$ins = Cache::getInstance ( C ( 'cache_type' ) );
		$cache_info = $ins->hget ( $key, $prefix, $fields );
		if ($cache_info === false) {
			// 取单个字段且未被缓存
			$data = array ();
		} elseif (is_array ( $cache_info )) {
			// 如果有一个键值为false(即未缓存)，则整个函数返回空，让系统重新生成全部缓存
			$data = $cache_info;
			foreach ( $cache_info as $k => $v ) {
				if ($v === false) {
					$data = array ();
					break;
				}
			}
		} else {
			// string 取单个字段且被缓存
			$data = array (
					$fields => $cache_info
			);
		}
		// 验证缓存是否过期
		if (isset ( $data ['cache_expiration_time'] ) && $data ['cache_expiration_time'] < TIMESTAMP) {
			$data = array ();
		}
		return $data;
	}
	
	/**
	 * 写入缓存
	 *
	 * @param string $key
	 *        	缓存键值
	 * @param array $data
	 *        	缓存数据
	 * @param string $prefix
	 *        	键值前缀
	 * @param int $period
	 *        	缓存周期 单位分，0为永久缓存
	 * @return bool 返回值
	 */
	static public function wcache($key = null, $data = array(), $prefix, $period = 0) {
		if ($key === null || ! C ( 'cache_open' ) || ! is_array ( $data ))
			return;
		$period = intval ( $period );
		if ($period != 0) {
			$data ['cache_expiration_time'] = TIMESTAMP + $period * 60;
		}
		$ins = Cache::getInstance ( C ( 'cache_type' ) );
		$ins->hset ( $key, $prefix, $data );
		$cache_info = $ins->hget ( $key, $prefix );
		return true;
	}
	
	/**
	 * 删除缓存
	 *
	 * @param string $key
	 *        	缓存键值
	 * @param string $prefix
	 *        	键值前缀
	 * @return boolean
	 */
	static public function dcache($key = null, $prefix = '') {
		if ($key === null || ! C ( 'cache_open' ))
			return true;
		$ins = Cache::getInstance ( C ( 'cache_type' ) );
		return $ins->hdel ( $key, $prefix );
	}
	
	
	
	/**
	 * 获取token
	 *
	 * @return string
	 */
	static public function securityGetToken() {
		return Security::getToken ();
	}
	static public function securityGetTokenValue() {
		return Security::getTokenValue ();
	}
	
	/**
	 * 二级域名解析
	 *
	 * @return int 店铺id
	 */
	static public function subdomain() {
		$store_id = 0;
		/**
		 * 获得系统配置,二级域名功能是否开启
		 */
		if (C ( 'enabled_subdomain' ) == '1') { // 开启了二级域名
			$line = explode ( SUBDOMAIN_SUFFIX, $_SERVER ['HTTP_HOST'] );
			$line = trim ( $line [0], '.' );
			if (empty ( $line ) || strtolower ( $line ) == 'www')
				return 0;
			
			$model_store = Model ( 'store' );
			$store_info = $model_store->getStoreInfo ( array (
					'store_domain' => $line 
			) );
			// 二级域名存在
			if ($store_info ['store_id'] > 0) {
				$store_id = $store_info ['store_id'];
				$_GET ['store_id'] = $store_info ['store_id'];
			}
		}
		return $store_id;
	}
	
	/**
	 * 通知邮件/通知消息 内容转换函数
	 *
	 * @param string $message
	 *        	内容模板
	 * @param array $param
	 *        	内容参数数组
	 * @return string 通知内容
	 */
	static public function daReplaceText($message, $param) {
		if (! is_array ( $param ))
			return false;
		foreach ( $param as $k => $v ) {
			$message = str_replace ( '{$' . $k . '}', $v, $message );
		}
		return $message;
	}
	
	/**
	 * 字符串切割函数，一个字母算一个位置,一个字算2个位置
	 *
	 * @param string $string
	 *        	待切割的字符串
	 * @param int $length
	 *        	切割长度
	 * @param string $dot
	 *        	尾缀
	 */
	static public function str_cut($string, $length, $dot = '') {
		$string = str_replace ( array (
				'&nbsp;',
				'&amp;',
				'&quot;',
				'&#039;',
				'&ldquo;',
				'&rdquo;',
				'&mdash;',
				'&lt;',
				'&gt;',
				'&middot;',
				'&hellip;' 
		), array (
				' ',
				'&',
				'"',
				"'",
				'“',
				'”',
				'—',
				'<',
				'>',
				'·',
				'…' 
		), $string );
		$strlen = strlen ( $string );
		if ($strlen <= $length)
			return $string;
		$maxi = $length - strlen ( $dot );
		$strcut = '';
		if (strtolower ( CHARSET ) == 'utf-8') {
			$n = $tn = $noc = 0;
			while ( $n < $strlen ) {
				$t = ord ( $string [$n] );
				if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1;
					$n ++;
					$noc ++;
				} elseif (194 <= $t && $t <= 223) {
					$tn = 2;
					$n += 2;
					$noc += 2;
				} elseif (224 <= $t && $t < 239) {
					$tn = 3;
					$n += 3;
					$noc += 2;
				} elseif (240 <= $t && $t <= 247) {
					$tn = 4;
					$n += 4;
					$noc += 2;
				} elseif (248 <= $t && $t <= 251) {
					$tn = 5;
					$n += 5;
					$noc += 2;
				} elseif ($t == 252 || $t == 253) {
					$tn = 6;
					$n += 6;
					$noc += 2;
				} else {
					$n ++;
				}
				if ($noc >= $maxi)
					break;
			}
			if ($noc > $maxi)
				$n -= $tn;
			$strcut = substr ( $string, 0, $n );
		} else {
			$dotlen = strlen ( $dot );
			$maxi = $length - $dotlen;
			for($i = 0; $i < $maxi; $i ++) {
				$strcut .= ord ( $string [$i] ) > 127 ? $string [$i] . $string [++ $i] : $string [$i];
			}
		}
		$strcut = str_replace ( array (
				'&',
				'"',
				"'",
				'<',
				'>' 
		), array (
				'&amp;',
				'&quot;',
				'&#039;',
				'&lt;',
				'&gt;' 
		), $strcut );
		return $strcut . $dot;
	}
	
	/**
	 * unicode转为utf8
	 *
	 * @param string $str
	 *        	待转的字符串
	 * @return string
	 */
	static public function unicodeToUtf8($str, $order = "little") {
		$utf8string = "";
		$n = strlen ( $str );
		for($i = 0; $i < $n; $i ++) {
			if ($order == "little") {
				$val = str_pad ( dechex ( ord ( $str [$i + 1] ) ), 2, 0, STR_PAD_LEFT ) . str_pad ( dechex ( ord ( $str [$i] ) ), 2, 0, STR_PAD_LEFT );
			} else {
				$val = str_pad ( dechex ( ord ( $str [$i] ) ), 2, 0, STR_PAD_LEFT ) . str_pad ( dechex ( ord ( $str [$i + 1] ) ), 2, 0, STR_PAD_LEFT );
			}
			$val = intval ( $val, 16 ); // 由于上次的.连接，导致$val变为字符串，这里得转回来。
			$i ++; // 两个字节表示一个unicode字符。
			$c = "";
			if ($val < 0x7F) { // 0000-007F
				$c .= chr ( $val );
			} elseif ($val < 0x800) { // 0080-07F0
				$c .= chr ( 0xC0 | ($val / 64) );
				$c .= chr ( 0x80 | ($val % 64) );
			} else { // 0800-FFFF
				$c .= chr ( 0xE0 | (($val / 64) / 64) );
				$c .= chr ( 0x80 | (($val / 64) % 64) );
				$c .= chr ( 0x80 | ($val % 64) );
			}
			$utf8string .= $c;
		}
		/* 去除bom标记 才能使内置的iconv函数正确转换 */
		if (ord ( substr ( $utf8string, 0, 1 ) ) == 0xEF && ord ( substr ( $utf8string, 1, 2 ) ) == 0xBB && ord ( substr ( $utf8string, 2, 1 ) ) == 0xBF) {
			$utf8string = substr ( $utf8string, 3 );
		}
		return $utf8string;
	}
	
	/*
	 * 重写$_SERVER['REQUREST_URI']
	 */
	static public function request_uri() {
		if (isset ( $_SERVER ['REQUEST_URI'] )) {
			$uri = $_SERVER ['REQUEST_URI'];
		} else {
			if (isset ( $_SERVER ['argv'] )) {
				$uri = $_SERVER ['PHP_SELF'] . '?' . $_SERVER ['argv'] [0];
			} else {
				$uri = $_SERVER ['PHP_SELF'] . '?' . $_SERVER ['QUERY_STRING'];
			}
		}
		
		$url_parse_arr = parse_url ( BASE_SITE_URL );
		$scheme = $url_parse_arr ['scheme'];
		$host = $url_parse_arr ['host'];
		
		$ret = $scheme . "://" . $host . $uri;
		
		return $ret;
	}
	
	/**
	 * 产生验证码
	 *
	 * @param string $shopdamap
	 *        	哈希数
	 * @return string
	 */
	static public function makeSeccode($shopdamap) {
		$seccode = random ( 6, 1 );
		$seccodeunits = '';
		
		$s = sprintf ( '%04s', base_convert ( $seccode, 10, 23 ) );
		$seccodeunits = 'ABCEFGHJKMPRTVXY2346789';
		if ($seccodeunits) {
			$seccode = '';
			for($i = 0; $i < 4; $i ++) {
				$unit = ord ( $s {$i} );
				$seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits [$unit - 0x30] : $seccodeunits [$unit - 0x57];
			}
		}
		setDaCookie ( 'seccode', encrypt ( strtoupper ( $seccode ) . "\t" . time (), MD5_KEY ), 3600 );
		return $seccode;
	}
	
	/**
	 * 验证验证码
	 *
	 * @param string $shopdamap
	 *        	哈希数
	 * @param string $value
	 *        	待验证值
	 * @return boolean
	 */
	static public function checkSeccode($shopdamap, $value) {
		list ( $checkvalue, $checktime ) = explode ( "\t", decrypt ( cookie ( 'seccode' ), MD5_KEY ) );
		$return = $checkvalue == strtoupper ( $value );
		if (! $return)
			setDaCookie ( 'seccode', '', - 3600 );
		return $return;
	}
	
	/**
	 * 设置cookie
	 *
	 * @param string $name
	 *        	cookie 的名称
	 * @param string $value
	 *        	cookie 的值
	 * @param int $expire
	 *        	cookie 有效周期
	 * @param string $path
	 *        	cookie 的服务器路径 默认为 /
	 * @param string $domain
	 *        	cookie 的域名
	 * @param string $secure
	 *        	是否通过安全的 HTTPS 连接来传输 cookie,默认为false
	 */
	static public function setDaCookie($name, $value, $expire = '3600', $path = '', $domain = '', $secure = false) {
		if (empty ( $path ))
			$path = '/';
		if (empty ( $domain ))
			$domain = SUBDOMAIN_SUFFIX ? SUBDOMAIN_SUFFIX : '';
		$name = defined ( 'COOKIE_PREFIX' ) ? COOKIE_PREFIX . $name : strtoupper ( substr ( md5 ( MD5_KEY ), 0, 4 ) ) . '_' . $name;
		$expire = intval ( $expire ) ? intval ( $expire ) : (intval ( SESSION_EXPIRE ) ? intval ( SESSION_EXPIRE ) : 3600);
		$result = setcookie ( $name, $value, time () + $expire, $path, $domain, $secure );
		$_COOKIE [$name] = $value;
	}
	
	/**
	 * 取得COOKIE的值
	 *
	 * @param string $name        	
	 * @return unknown
	 */
	static public function cookie($name = '') {
		$name = defined ( 'COOKIE_PREFIX' ) ? COOKIE_PREFIX . $name : strtoupper ( substr ( md5 ( MD5_KEY ), 0, 4 ) ) . '_' . $name;
		$ret = $_COOKIE [$name];
		return $ret;
	}
	
	/**
	 * 当访问的URL不存在时调用此函数并退出脚本
	 *
	 * @param string $c        	
	 * @param string $a        	
	 * @return void
	 */
	static public function requestNotFound($c = null, $a = null) {
		showMessage ( '您访问的页面不存在！', SHOP_SITE_URL, 'exception', 'error', 1, 3000 );
		exit ( 0 );
	}
	
	/**
	 * 输出信息
	 *
	 * @param string $msg
	 *        	输出信息
	 * @param string/array $url
	 *        	跳转地址 当$url为数组时，结构为 array('msg'=>'跳转连接文字','url'=>'跳转连接');
	 * @param string $show_type
	 *        	输出格式 默认为html
	 * @param string $msg_type
	 *        	信息类型 succ 为成功，error为失败/错误
	 * @param string $is_show
	 *        	是否显示跳转链接，默认是为1，显示
	 * @param int $time
	 *        	跳转时间，默认为2秒
	 * @return string 字符串类型的返回结果
	 */
	static public function showMessage($msg, $url = '', $show_type = 'html', $msg_type = 'succ', $is_show = 1, $time = 2000) {
		/**
		 * 如果默认为空，则跳转至上一步链接
		 */
		$url = ($url != '' ? $url : getReferer ());
		
		$msg_type = in_array ( $msg_type, array (
				'succ',
				'error' 
		) ) ? $msg_type : 'error';
		
		/**
		 * 输出类型
		 */
		switch ($show_type) {
			case 'json' :
				$return = '{';
				$return .= '"msg":"' . $msg . '",';
				$return .= '"url":"' . $url . '"';
				$return .= '}';
				echo $return;
				break;
			case 'exception' :
				echo '<!DOCTYPE html>';
				echo '<html>';
				echo '<head>';
				echo '<meta http-equiv="Content-Type" content="text/html; charset=' . CHARSET . '" />';
				echo '<title></title>';
				echo '<style type="text/css">';
				echo 'body { font-family: "Verdana";padding: 0; margin: 0;}';
				echo 'h2 { font-size: 12px; line-height: 30px; border-bottom: 1px dashed #CCC; padding-bottom: 8px;width:800px; margin: 20px 0 0 150px;}';
				echo 'dl { float: left; display: inline; clear: both; padding: 0; margin: 10px 20px 20px 150px;}';
				echo 'dt { font-size: 14px; font-weight: bold; line-height: 40px; color: #333; padding: 0; margin: 0; border-width: 0px;}';
				echo 'dd { font-size: 12px; line-height: 40px; color: #333; padding: 0px; margin:0;}';
				echo '</style>';
				echo '</head>';
				echo '<body>';
				echo '<h2>' . '错误信息' . '</h2>';
				echo '<dl>';
				echo '<dd>' . $msg . '</dd>';
				echo '<dt><p /></dt>';
				echo '<dd>' . '系统运行异常，由此给您带来的访问不便我们深感歉意，请联系客服寻求帮助' . '</dd>';
				echo '<dd><p /><p /><p /><p /></dd>';
				echo '</dl>';
				echo '</body>';
				echo '</html>';
				exit ( 0 );
				break;
			case 'javascript' :
				echo "<script>";
				echo "alert('" . $msg . "');";
				echo "location.href='" . $url . "'";
				echo "</script>";
				exit ( 0 );
				break;
			case 'tenpay' :
				echo "<html><head>";
				echo "<meta name=\"TENCENT_ONLINE_PAYMENT\" content=\"China TENCENT\">";
				echo "<script language=\"javascript\">";
				echo "window.location.href='" . $url . "';";
				echo "</script>";
				echo "</head><body></body></html>";
				exit ( 0 );
				break;
			default :
				/**
				 * 不显示右侧工具条
				 */
				viewAssign ( 'hidden_nctoolbar', 1 );
				if (is_array ( $url )) {
					foreach ( $url as $k => $v ) {
						$url [$k] ['url'] = $v ['url'] ? $v ['url'] : getReferer ();
					}
				}
				/**
				 * 读取信息布局的语言包
				 */
				Language::read ( 'msg', 'Home' );
				/**
				 * html输出形式
				 * 指定为指定项目目录下的error模板文件
				 */
				viewSetDir ( '' );
				viewAssign ( 'html_title', Language::get ( 'spd_html_title' ) );
				viewAssign ( 'msg', $msg );
				viewAssign ( 'url', $url );
				viewAssign ( 'msg_type', $msg_type );
				viewAssign ( 'is_show', $is_show );
				viewAssign ( "time", $time );
				viewRender ( 'common/msg', 'msg_layout' );
		}
		exit ( 0 );
	}
	
	/**
	 * 消息提示，主要适用于普通页面AJAX提交的情况
	 *
	 * @param string $message
	 *        	消息内容
	 * @param string $url
	 *        	提示完后的URL去向
	 * @param stting $alert_type
	 *        	提示类型 error/succ/notice 分别为错误/成功/警示
	 * @param string $extrajs
	 *        	扩展JS
	 * @param int $time
	 *        	停留时间
	 */
	static public function showDialog($message = '', $url = '', $alert_type = 'error', $extrajs = '', $time = 2) {
		if (empty ( $_GET ['inajax'] )) {
			if ($url == 'reload')
				$url = '';
			showMessage ( $message . $extrajs, $url, 'html', $alert_type, 1, $time * 1000 );
		}
		$message = str_replace ( "'", "\\'", strip_tags ( $message ) );
		
		$paramjs = null;
		if ($url == 'reload') {
			$paramjs = 'window.location.reload()';
		} elseif ($url != '') {
			$paramjs = 'window.location.href =\'' . $url . '\'';
		}
		if ($paramjs) {
			$paramjs = 'function (){' . $paramjs . '}';
		} else {
			$paramjs = 'null';
		}
		$modes = array (
				'error' => 'alert',
				'succ' => 'succ',
				'notice' => 'notice',
				'js' => 'js' 
		);
		$cover = $alert_type == 'error' ? 1 : 0;
		$extra .= 'showDialog(\'' . $message . '\', \'' . $modes [$alert_type] . '\', null, ' . ($paramjs ? $paramjs : 'null') . ', ' . $cover . ', null, null, null, null, ' . (is_numeric ( $time ) ? $time : 'null') . ', null);';
		$extra = $extra ? '<script type="text/javascript" reload="1">' . $extra . '</script>' : '';
		if ($extrajs != '' && substr ( trim ( $extrajs ), 0, 7 ) != '<script') {
			$extrajs = '<script type="text/javascript" reload="1">' . $extrajs . '</script>';
		}
		$extra .= $extrajs;
		ob_end_clean ();
		header ( "Expires: -1" );
		header ( "Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE );
		header ( "Pragma: no-cache" );
		header ( "Content-type: text/xml; charset=" . CHARSET );
		
		$string = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\r\n";
		$string .= '<root><![CDATA[' . $message . $extra . ']]></root>';
		echo $string;
		exit ( 0 );
	}
	
	/**
	 * 取验证码hash值
	 *
	 * @param        	
	 *
	 * @return string 字符串类型的返回结果
	 */
	static public function getShopdaHash($controller = '', $action = '') {
		$controller = $controller ? $controller : convert_word_underscore(CONTROLLER_NAME);
		$action = $action ? $action : ACTION_NAME;
		if (C ( 'captcha_status_login' )) {
			return substr ( md5 ( SHOP_SITE_URL . $controller . $action ), 0, 8 );
		} else {
			return '';
		}
	}
	
	/**
	 * 加密函数
	 *
	 * @param string $txt
	 *        	需要加密的字符串
	 * @param string $key
	 *        	密钥
	 * @return string 返回加密结果
	 */
	static public function encrypt($txt, $key = '') {
		if (empty ( $txt ))
			return $txt;
		if (empty ( $key ))
			$key = md5 ( MD5_KEY );
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
		$ikey = "-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
		$nh1 = rand ( 0, 64 );
		$nh2 = rand ( 0, 64 );
		$nh3 = rand ( 0, 64 );
		$ch1 = $chars {$nh1};
		$ch2 = $chars {$nh2};
		$ch3 = $chars {$nh3};
		$nhnum = $nh1 + $nh2 + $nh3;
		$knum = 0;
		$i = 0;
		while ( isset ( $key {$i} ) )
			$knum += ord ( $key {$i ++} );
		$mdKey = substr ( md5 ( md5 ( md5 ( $key . $ch1 ) . $ch2 . $ikey ) . $ch3 ), $nhnum % 8, $knum % 8 + 16 );
		$txt = base64_encode ( time () . '_' . $txt );
		$txt = str_replace ( array (
				'+',
				'/',
				'=' 
		), array (
				'-',
				'_',
				'.' 
		), $txt );
		$tmp = '';
		$j = 0;
		$k = 0;
		$tlen = strlen ( $txt );
		$klen = strlen ( $mdKey );
		for($i = 0; $i < $tlen; $i ++) {
			$k = $k == $klen ? 0 : $k;
			$j = ($nhnum + strpos ( $chars, $txt {$i} ) + ord ( $mdKey {$k ++} )) % 64;
			$tmp .= $chars {$j};
		}
		$tmplen = strlen ( $tmp );
		$tmp = substr_replace ( $tmp, $ch3, $nh2 % ++ $tmplen, 0 );
		$tmp = substr_replace ( $tmp, $ch2, $nh1 % ++ $tmplen, 0 );
		$tmp = substr_replace ( $tmp, $ch1, $knum % ++ $tmplen, 0 );
		return $tmp;
	}
	
	/**
	 * 解密函数
	 *
	 * @param string $txt
	 *        	需要解密的字符串
	 * @param string $key
	 *        	密匙
	 * @return string 字符串类型的返回结果
	 */
	static public function decrypt($txt, $key = '', $ttl = 0) {
		if (empty ( $txt ))
			return $txt;
		if (empty ( $key ))
			$key = md5 ( MD5_KEY );
		
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
		$ikey = "-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
		$knum = 0;
		$i = 0;
		$tlen = strlen ( $txt );
		while ( isset ( $key {$i} ) )
			$knum += ord ( $key {$i ++} );
		$ch1 = $txt {$knum % $tlen};
		$nh1 = strpos ( $chars, $ch1 );
		$txt = substr_replace ( $txt, '', $knum % $tlen --, 1 );
		$ch2 = $txt {$nh1 % $tlen};
		$nh2 = strpos ( $chars, $ch2 );
		$txt = substr_replace ( $txt, '', $nh1 % $tlen --, 1 );
		$ch3 = $txt {$nh2 % $tlen};
		$nh3 = strpos ( $chars, $ch3 );
		$txt = substr_replace ( $txt, '', $nh2 % $tlen --, 1 );
		$nhnum = $nh1 + $nh2 + $nh3;
		$mdKey = substr ( md5 ( md5 ( md5 ( $key . $ch1 ) . $ch2 . $ikey ) . $ch3 ), $nhnum % 8, $knum % 8 + 16 );
		$tmp = '';
		$j = 0;
		$k = 0;
		$tlen = strlen ( $txt );
		$klen = strlen ( $mdKey );
		for($i = 0; $i < $tlen; $i ++) {
			$k = $k == $klen ? 0 : $k;
			$j = strpos ( $chars, $txt {$i} ) - $nhnum - ord ( $mdKey {$k ++} );
			while ( $j < 0 )
				$j += 64;
			$tmp .= $chars {$j};
		}
		$tmp = str_replace ( array (
				'-',
				'_',
				'.' 
		), array (
				'+',
				'/',
				'=' 
		), $tmp );
		$tmp = trim ( base64_decode ( $tmp ) );
		
		if (preg_match ( "/\d{10}_/s", substr ( $tmp, 0, 11 ) )) {
			if ($ttl > 0 && (time () - substr ( $tmp, 0, 11 ) > $ttl)) {
				$tmp = null;
			} else {
				$tmp = substr ( $tmp, 11 );
			}
		}
		return $tmp;
	}
	
	/**
	 * 取得IP
	 *
	 *
	 * @return string 字符串类型的返回结果
	 */
	static public function getIp() {
		if ($_SERVER ['HTTP_CLIENT_IP'] && $_SERVER ['HTTP_CLIENT_IP'] != 'unknown') {
			$ip = $_SERVER ['HTTP_CLIENT_IP'];
		} elseif ($_SERVER ['HTTP_X_FORWARDED_FOR'] && $_SERVER ['HTTP_X_FORWARDED_FOR'] != 'unknown') {
			$ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER ['REMOTE_ADDR'];
		}
		return preg_match ( '/^\d[\d.]+\d$/', $ip ) ? $ip : '';
	}
	
	/**
	 * 读取目录列表
	 * 不包括 .
	 *
	 *
	 *
	 * .. 文件 三部分
	 *
	 * @param string $path
	 *        	路径
	 * @return array 数组格式的返回结果
	 */
	static public function readDirList($path) {
		if (is_dir ( $path )) {
			$handle = opendir ( $path );
			$dir_list = array ();
			if ($handle) {
				while ( false !== ($dir = readdir ( $handle )) ) {
					if ($dir != '.' && $dir != '..' && is_dir ( $path . DS . $dir )) {
						$dir_list [] = $dir;
					}
				}
				return $dir_list;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * 转换特殊字符
	 *
	 * @param string $string
	 *        	要转换的字符串
	 * @return string 字符串类型的返回结果
	 */
	static public function replaceSpecialChar($string) {
		$str = str_replace ( "\r\n", "", $string );
		$str = str_replace ( "\t", "    ", $string );
		$str = str_replace ( "\n", "", $string );
		return $string;
	}
	
	/**
	 * 编辑器内容
	 *
	 * @param int $id
	 *        	编辑器id名称，与name同名
	 * @param string $value
	 *        	编辑器内容
	 * @param string $width
	 *        	宽 带px
	 * @param string $height
	 *        	高 带px
	 * @param string $style
	 *        	样式内容
	 * @param string $upload_state
	 *        	上传状态，默认是开启
	 */
	static public function showEditor($id, $value = '', $width = '700px', $height = '300px', $style = 'visibility:hidden;', $upload_state = "true", $media_open = false, $type = 'all') {
		// 是否开启多媒体
		$media = '';
		if ($media_open) {
			$media = ", 'flash', 'media'";
		}
		switch ($type) {
			case 'basic' :
				$items = "['source', '|', 'fullscreen', 'undo', 'redo', 'cut', 'copy', 'paste', '|', 'about']";
				break;
			case 'simple' :
				$items = "['source', '|', 'fullscreen', 'undo', 'redo', 'cut', 'copy', 'paste', '|',
            'fontname', 'fontsize', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
            'removeformat', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
            'insertunorderedlist', '|', 'emoticons', 'image', 'link', '|', 'about']";
				break;
			default :
				$items = "['source', '|', 'fullscreen', 'undo', 'redo', 'print', 'cut', 'copy', 'paste',
            'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
            'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
            'superscript', '|', 'selectall', 'clearhtml','quickformat','|',
            'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
            'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image'" . $media . ", 'table', 'hr', 'emoticons', 'link', 'unlink', '|', 'about']";
				break;
		}
		// 图片、Flash、视频、文件的本地上传都可开启。默认只有图片，要启用其它的需要修改resource\kindeditor\php下的upload_json.php的相关参数
		echo '<textarea id="' . $id . '" name="' . $id . '" style="width:' . $width . ';height:' . $height . ';' . $style . '">' . $value . '</textarea>';
		echo '
<script src="' . RESOURCE_SITE_URL . '/kindeditor/kindeditor-min.js" charset="utf-8"></script>
<script src="' . RESOURCE_SITE_URL . '/kindeditor/lang/zh_CN.js" charset="utf-8"></script>
<script>
	var KE;
  KindEditor.ready(function(K) {
        KE = K.create("textarea[name=\'' . $id . '\']", {
						items : ' . $items . ',
						cssPath : "' . RESOURCE_SITE_URL . '/kindeditor/themes/default/default.css",
						allowImageUpload : ' . $upload_state . ',
						allowFlashUpload : false,
						allowMediaUpload : false,
						allowFileManager : false,
						syncType:"form",
						afterCreate : function() {
							var self = this;
							self.sync();
						},
						afterChange : function() {
							var self = this;
							self.sync();
						},
						afterBlur : function() {
							var self = this;
							self.sync();
						}
        });
			KE.appendHtml = function(id,val) {
				this.html(this.html() + val);
				if (this.isCreated) {
					var cmd = this.cmd;
					cmd.range.selectNodeContents(cmd.doc.body).collapse(false);
					cmd.select();
				}
				return this;
			}
	});
</script>
	';
		return true;
	}
	
	/**
	 * 获取目录大小
	 *
	 * @param string $path
	 *        	目录
	 * @param int $size
	 *        	目录大小
	 * @return int 整型类型的返回结果
	 */
	static public function getDirSize($path, $size = 0) {
		$dir = dir ( $path );
		if (! empty ( $dir->path ) && ! empty ( $dir->handle )) {
			while ( $filename = $dir->read () ) {
				if ($filename != '.' && $filename != '..') {
					if (is_dir ( $path . DS . $filename )) {
						$size += getDirSize ( $path . DS . $filename );
					} else {
						$size += filesize ( $path . DS . $filename );
					}
				}
			}
		}
		return $size ? $size : 0;
	}
	
	/**
	 * 价格格式化
	 *
	 * @param int $price        	
	 * @return string
	 *
	 */
	static public function daPriceFormat($price) {
		return number_format ( $price, 2, '.', '' );
	}
	
	/**
	 * 价格格式化
	 *
	 * @param int $price        	
	 * @return string
	 *
	 */
	static public function daPriceFormatForList($price) {
		if ($price >= 10000) {
			return number_format ( floor ( $price / 100 ) / 100, 2, '.', '' ) . '万';
		} else {
			return '&yen;' . daPriceFormat ( $price );
		}
	}
	
	/**
	 * 取得商品缩略图的完整URL路径，接收商品信息数组，返回所需的商品缩略图的完整URL
	 *
	 * @param array $goods
	 *        	商品信息数组
	 * @param string $type
	 *        	缩略图类型 值为60,240,360,1280
	 * @return string
	 */
	static public function thumb($goods = array(), $type = '') {
		$type_array = explode ( ',_', ltrim ( GOODS_IMAGES_EXT, '_' ) );
		if (! in_array ( $type, $type_array )) {
			$type = '240';
		}
		if (empty ( $goods )) {
			return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
		}
		if (array_key_exists ( 'apic_cover', $goods )) {
			$goods ['goods_image'] = $goods ['apic_cover'];
		}
		if (empty ( $goods ['goods_image'] )) {
			return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
		}
		$search_array = explode ( ',', GOODS_IMAGES_EXT );
		$file = str_ireplace ( $search_array, '', $goods ['goods_image'] );
		$fname = basename ( $file );
		// 取店铺ID
		if (preg_match ( '/^(\d+_)/', $fname )) {
			$store_id = substr ( $fname, 0, strpos ( $fname, '_' ) );
		} else {
			$store_id = $goods ['store_id'];
		}
		
		if (! C ( 'oss.open' )) {
			$file = $type == '' ? $file : str_ireplace ( '.', '_' . $type . '.', $file );
			if (! file_exists ( BASE_UPLOAD_PATH . '/' . ATTACH_GOODS . '/' . $store_id . '/' . $file )) {
				return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
			}
			$thumb_host = UPLOAD_SITE_URL . '/' . ATTACH_GOODS;
			return $thumb_host . '/' . $store_id . '/' . $file;
		} else {
			return C ( 'oss.img_url' ) . '/' . ATTACH_GOODS . '/' . $store_id . '/' . $file . '@!product-' . $type;
		}
	}
	
	/**
	 * 取得商品缩略图的完整URL路径，接收图片名称与店铺ID
	 *
	 * @param string $file
	 *        	图片名称
	 * @param string $type
	 *        	缩略图尺寸类型，值为60,240,360,1280
	 * @param mixed $store_id
	 *        	店铺ID 如果传入，则返回图片完整URL,如果为假，返回系统默认图
	 * @return string
	 */
	static public function cthumb($file, $type = '', $store_id = false) {
		$type_array = explode ( ',_', ltrim ( GOODS_IMAGES_EXT, '_' ) );
		if (! in_array ( $type, $type_array )) {
			$type = '240';
		}
		
		if (empty ( $file )) {
			return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
		}
		$search_array = explode ( ',', GOODS_IMAGES_EXT );
		$file = str_ireplace ( $search_array, '', $file );
		$fname = basename ( $file );
		
		// 取店铺ID
		if ($store_id === false || ! is_numeric ( $store_id )) {
			$store_id = substr ( $fname, 0, strpos ( $fname, '_' ) );
		}
		if (! C ( 'oss.open' )) {
			// 本地存储时，增加判断文件是否存在，用默认图代替
			$file_path = BASE_UPLOAD_PATH . '/' . ATTACH_GOODS . '/' . $store_id . '/' . ($type == '' ? $file : str_ireplace ( '.', '_' . $type . '.', $file ));
			// var_dump($file_path);
			if (! file_exists ( $file_path )) {
				$ret = UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
				return $ret;
			}
			$thumb_host = UPLOAD_SITE_URL . '/' . ATTACH_GOODS;
			$ret = $thumb_host . '/' . $store_id . '/' . ($type == '' ? $file : str_ireplace ( '.', '_' . $type . '.', $file ));
			// var_dump($ret);
			return $ret;
		} else {
			return C ( 'oss.img_url' ) . '/' . ATTACH_GOODS . '/' . $store_id . '/' . $file . '@!product-' . $type;
		}
	}
	
	/**
	 * 商品二维码
	 *
	 * @param array $goods_info        	
	 * @return string
	 */
	static public function goodsQRCode($goods_info) {
		if (! file_exists ( BASE_UPLOAD_PATH . '/' . ATTACH_STORE . '/' . $goods_info ['store_id'] . '/' . $goods_info ['goods_id'] . '.png' )) {
			return UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . 'default_qrcode.png';
		}
		return UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . $goods_info ['store_id'] . DS . $goods_info ['goods_id'] . '.png';
	}
	
	/**
	 * 店铺二维码
	 *
	 * @param array $store_id        	
	 * @return string
	 */
	static public function storeQRCode($store_id) {
		if (! file_exists ( BASE_UPLOAD_PATH . '/' . ATTACH_STORE . '/' . $store_id . '/' . $store_id . '_store.png' )) {
			return UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . 'default_qrcode.png';
		}
		return UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . $store_id . DS . $store_id . '_store.png';
	}
	
	/**
	 * 取得抢购缩略图的完整URL路径
	 *
	 * @param string $imgurl
	 *        	商品名称
	 * @param string $type
	 *        	缩略图类型 值为small,mid,max
	 * @return string
	 */
	static public function gthumb($image_name = '', $type = '') {
		if (! in_array ( $type, array (
				'small',
				'mid',
				'max' 
		) ))
			$type = 'small';
		if (empty ( $image_name )) {
			return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( '240' );
		}
		list ( $base_name, $ext ) = explode ( '.', $image_name );
		list ( $store_id ) = explode ( '_', $base_name );
		$file_path = ATTACH_GROUPBUY . DS . $store_id . DS . $base_name . '_' . $type . '.' . $ext;
		if (! file_exists ( BASE_UPLOAD_PATH . DS . $file_path )) {
			return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( '240' );
		}
		return UPLOAD_SITE_URL . DS . $file_path;
	}
	
	/**
	 * 取得买家缩略图的完整URL路径
	 *
	 * @param string $imgurl
	 *        	商品名称
	 * @param string $type
	 *        	缩略图类型 值为240,1024
	 * @return string
	 */
	static public function snsThumb($image_name = '', $type = '') {
		if (! in_array ( $type, array (
				'240',
				'1024' 
		) ))
			$type = '240';
		if (empty ( $image_name )) {
			return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( '240' );
		}
		
		if (strpos ( $image_name, '/' )) {
			$image = explode ( '/', $image_name );
			$image = end ( $image );
		} else {
			$image = $image_name;
		}
		
		list ( $member_id ) = explode ( '_', $image );
		$file_path = ATTACH_MALBUM . DS . $member_id . DS . str_ireplace ( '.', '_' . $type . '.', $image_name );
		if (! file_exists ( BASE_UPLOAD_PATH . DS . $file_path )) {
			return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( '240' );
		}
		return UPLOAD_SITE_URL . DS . $file_path;
	}
	
	/**
	 * 取得积分商品缩略图的完整URL路径
	 *
	 * @param string $imgurl
	 *        	商品名称
	 * @param string $type
	 *        	缩略图类型 值为small
	 * @return string
	 */
	static public function pointprodThumb($image_name = '', $type = '') {
		if (! in_array ( $type, array (
				'small',
				'mid' 
		) ))
			$type = '';
		if (empty ( $image_name )) {
			return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( '240' );
		}
		
		if ($type) {
			$file_path = ATTACH_POINTPROD . DS . str_ireplace ( '.', '_' . $type . '.', $image_name );
		} else {
			$file_path = ATTACH_POINTPROD . DS . $image_name;
		}
		if (! file_exists ( BASE_UPLOAD_PATH . DS . $file_path )) {
			return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( '240' );
		}
		return UPLOAD_SITE_URL . DS . $file_path;
	}
	
	/**
	 * 取得品牌图片
	 *
	 * @param string $image_name        	
	 * @return string
	 */
	static public function brandImage($image_name = '') {
		if ($image_name != '') {
			return UPLOAD_SITE_URL . '/' . ATTACH_BRAND . '/' . $image_name;
		}
		return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/default_brand_image.gif';
	}
	
	/**
	 * 取得订单状态文字输出形式
	 *
	 * @param array $order_info
	 *        	订单数组
	 * @return string $order_state 描述输出
	 *        
	 */
	static public function orderState($order_info) {
		switch ($order_info ['order_state']) {
			case ORDER_STATE_CANCEL :
				$order_state = '已取消';
				break;
			case ORDER_STATE_NEW :
				if ($order_info ['chain_code']) {
					$order_state = '门店付款自提';
				} else {
					$order_state = '待付款';
				}
				break;
			case ORDER_STATE_PAY :
				if ($order_info ['chain_code']) {
					$order_state = '待自提';
				} else {
					$order_state = '待发货';
				}
				break;
			case ORDER_STATE_SEND :
				$order_state = '待收货';
				break;
			case ORDER_STATE_SUCCESS :
				$order_state = '交易完成';
				break;
		}
		return $order_state;
	}
	
	/**
	 * 取得订单支付类型文字输出形式
	 *
	 * @param array $payment_code        	
	 * @return string
	 */
	static public function orderPaymentName($payment_code) {
		return str_replace ( array (
				'offline',
				'online',
				'ali_native',
				'alipay',
				'tenpay',
				'chinabank',
				'predeposit',
				'wxpay',
				'wx_jsapi',
				'wx_saoma',
				'chain' 
		), array (
				'货到付款',
				'在线付款',
				'支付宝移动支付',
				'支付宝',
				'财付通',
				'网银在线',
				'站内余额支付',
				'微信支付[客户端]',
				'微信支付[jsapi]',
				'微信支付[扫码]',
				'门店支付' 
		), $payment_code );
	}
	
	/**
	 * 取得订单商品销售类型文字输出形式
	 *
	 * @param array $goods_type        	
	 * @return string 描述输出
	 */
	static public function orderGoodsType($goods_type) {
		return str_replace ( array (
				'1',
				'2',
				'3',
				'4',
				'5',
				'8',
				'9' 
		), array (
				'',
				'抢购',
				'限时折扣',
				'优惠套装',
				'赠品',
				'',
				'换购' 
		), $goods_type );
	}
	
	/**
	 * 取得结算文字输出形式
	 *
	 * @param array $bill_state        	
	 * @return string 描述输出
	 */
	static public function billState($bill_state) {
		return str_replace ( array (
				'1',
				'2',
				'3',
				'4' 
		), array (
				'已出账',
				'商家已确认',
				'平台已审核',
				'结算完成' 
		), $bill_state );
	}
	static public function getMicroshopImageSize($image_url, $max_width = 238) {
		$local_file_path = str_replace ( UPLOAD_SITE_URL, BASE_UPLOAD_PATH, $image_url );
		if (file_exists ( $local_file_path )) {
			list ( $width, $height ) = getimagesize ( $local_file_path );
		} else {
			list ( $width, $height ) = getimagesize ( $image_url );
		}
		if ($width > $max_width) {
			$height = $height * $max_width / $width;
			$width = $max_width;
		}
		return array (
				'width' => $width,
				'height' => $height 
		);
	}
	static public function output_data($datas, $extend_data = array(), $error = false, $with_site_setting = false) {
		$data = array ();
		
		$data ['code'] = 200;
		if ($error) {
			$data ['code'] = 400;
		}
		
		if (! empty ( $extend_data )) {
			$data = array_merge ( $data, $extend_data );
		}
		
		$data ['datas'] = $datas;
		$data ['_log_id'] = LOG_ID;
		if (isset ( $GLOBALS ['mobile_token'] )) {
			$data ['mobile_token'] = $GLOBALS ['mobile_token'];
		}
		
		if ($with_site_setting || ($_GET['with_site_setting'] == 1)) 
		{
			$site_setting = Logic("setting")->getPublicSiteSetting();
			$data["site_setting"] = $site_setting;
		}
		
		$jsonFlag = 0 && C ( 'debug' ) && version_compare ( PHP_VERSION, '5.4.0' ) >= 0 ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE : 0;
		
		if ($jsonFlag) {
			header ( 'Content-type: text/plain; charset=utf-8' );
		}
		
		if (! empty ( $_GET ['callback'] )) {
			echo $_GET ['callback'] . '(' . json_encode ( $data, $jsonFlag ) . ')';
			exit ( 0 );
		} else {
			header ( "Access-Control-Allow-Origin:*" );
			echo json_encode ( $data, $jsonFlag );
			exit ( 0 );
		}
	}
	static public function output_error($message, $extend_data = array()) {
		$datas = array (
				'error' => $message 
		);
		output_data ( $datas, $extend_data, true );
	}
	static public function mobile_page($page_count) {
		// 输出是否有下一页
		$extend_data = array ();
		$current_page = intval ( $_GET ['curpage'] );
		if ($current_page <= 0) {
			$current_page = 1;
		}
		if ($current_page >= $page_count) {
			$extend_data ['hasmore'] = false;
		} else {
			$extend_data ['hasmore'] = true;
		}
		$extend_data ['page_total'] = $page_count;
		return $extend_data;
	}
	static public function get_server_ip() {
		if (isset ( $_SERVER )) {
			if ($_SERVER ['SERVER_ADDR']) {
				$server_ip = $_SERVER ['SERVER_ADDR'];
			} else {
				$server_ip = $_SERVER ['LOCAL_ADDR'];
			}
		} else {
			$server_ip = getenv ( 'SERVER_ADDR' );
		}
		return $server_ip;
	}
	static public function http_get($url) {
		return file_get_contents ( $url );
	}
	
	/**
	 * 删除地址参数(把参数的值赋值为0)
	 *
	 * @param array $param        	
	 */
	static public function dropParam($param) {
		$purl = getParam ();
		if (! empty ( $param )) {
			foreach ( $param as $val ) {
				$purl ['param'] [$val] = 0;
			}
		}
		return urlShop ( $purl ['c'], $purl ['a'], $purl ['param'] );
	}
	
	/**
	 * 替换地址参数(替换参数值)
	 *
	 * @param array $param        	
	 */
	static function replaceParam($param) {
		$purl = getParam ();
		if (! empty ( $param )) {
			foreach ( $param as $key => $val ) {
				$purl ['param'] [$key] = $val;
			}
		}
		
		return urlShop ( $purl ['c'], $purl ['a'], $purl ['param'] );
	}
	
	/**
	 * 替换并删除地址参数
	 *
	 * @param array $param        	
	 */
	static public function replaceAndDropParam($paramToReplace, $paramToDrop) {
		$purl = getParam ();
		if (! empty ( $paramToReplace )) {
			foreach ( $paramToReplace as $key => $val ) {
				$purl ['param'] [$key] = $val;
			}
		}
		if (! empty ( $paramToDrop )) {
			foreach ( $paramToDrop as $val ) {
				$purl ['param'] [$val] = 0;
			}
		}
		
		return urlShop ( $purl ['c'], $purl ['a'], $purl ['param'] );
	}
	
	/**
	 * 删除部分地址参数（适用于商品搜索品牌、属性部分）
	 * 例：参数c=1_2_3_4
	 * 如需要删除act中的3可使用该函数 removeRaram(array('c' => 3))
	 * 该函数只能删除一个参数
	 *
	 * @param array $param        	
	 */
	static public function removeParam($param) {
		$purl = getParam ();
		if (! empty ( $param )) {
			foreach ( $param as $key => $val ) {
				if (! isset ( $purl ['param'] [$key] )) {
					continue;
				}
				$tpl_params = explode ( '_', $purl ['param'] [$key] );
				foreach ( $tpl_params as $k => $v ) {
					if ($val == $v) {
						unset ( $tpl_params [$k] );
					}
				}
				if (empty ( $tpl_params )) {
					$purl ['param'] [$key] = 0;
				} else {
					$purl ['param'] [$key] = implode ( '_', $tpl_params );
				}
			}
		}
		return urlShop ( $purl ['c'], $purl ['a'], $purl ['param'] );
	}
	static public function getParam() {
		$param = $_GET;
		$purl = array ();
		$purl ['c'] = $param ['c'];
		unset ( $param ['c'] );
		$purl ['a'] = $param ['a'];
		unset ( $param ['a'] );
		unset ( $param ['curpage'] );
		$purl ['param'] = $param;
		return $purl;
	}
	
	/**
	 * 获得折线图统计图数据
	 * param $statarr 图表需要的设置项
	 */
	static public function getStatData_LineLabels($stat_arr) {
		// 图表区、图形区和通用图表配置选项
		$stat_arr ['chart'] ['type'] = 'line';
		// 图表序列颜色数组
		$stat_arr ['colors'] ? '' : $stat_arr ['colors'] = array (
				'#058DC7',
				'#ED561B',
				'#8bbc21',
				'#0d233a' 
		);
		// 去除版权信息
		$stat_arr ['credits'] ['enabled'] = false;
		// 导出功能选项
		$stat_arr ['exporting'] ['enabled'] = false;
		// 标题如果为字符串则使用默认样式
		is_string ( $stat_arr ['title'] ) ? $stat_arr ['title'] = array (
				'text' => "<b>{$stat_arr['title']}</b>",
				'x' => - 20 
		) : '';
		// 子标题如果为字符串则使用默认样式
		is_string ( $stat_arr ['subtitle'] ) ? $stat_arr ['subtitle'] = array (
				'text' => "<b>{$stat_arr['subtitle']}</b>",
				'x' => - 20 
		) : '';
		// Y轴如果为字符串则使用默认样式
		if (is_string ( $stat_arr ['yAxis'] )) {
			$text = $stat_arr ['yAxis'];
			unset ( $stat_arr ['yAxis'] );
			$stat_arr ['yAxis'] ['title'] ['text'] = $text;
		}
		return json_encode ( $stat_arr );
	}
	
	/**
	 * 获得Column2D统计图数据
	 */
	static public function getStatData_Column2D($stat_arr) {
		// 图表区、图形区和通用图表配置选项
		$stat_arr ['chart'] ['type'] = 'column';
		// 去除版权信息
		$stat_arr ['credits'] ['enabled'] = false;
		// 导出功能选项
		$stat_arr ['exporting'] ['enabled'] = false;
		// 标题如果为字符串则使用默认样式
		is_string ( $stat_arr ['title'] ) ? $stat_arr ['title'] = array (
				'text' => "<b>{$stat_arr['title']}</b>",
				'x' => - 20 
		) : '';
		// 子标题如果为字符串则使用默认样式
		is_string ( $stat_arr ['subtitle'] ) ? $stat_arr ['subtitle'] = array (
				'text' => "<b>{$stat_arr['subtitle']}</b>",
				'x' => - 20 
		) : '';
		// Y轴如果为字符串则使用默认样式
		if (is_string ( $stat_arr ['yAxis'] )) {
			$text = $stat_arr ['yAxis'];
			unset ( $stat_arr ['yAxis'] );
			$stat_arr ['yAxis'] ['title'] ['text'] = $text;
		}
		// 柱形的颜色数组
		$color = array (
				'#7a96a4',
				'#cba952',
				'#667b16',
				'#a26642',
				'#349898',
				'#c04f51',
				'#5c315e',
				'#445a2b',
				'#adae50',
				'#14638a',
				'#b56367',
				'#a399bb',
				'#070dfa',
				'#47ff07',
				'#f809b7' 
		);
		
		foreach ( $stat_arr ['series'] as $series_k => $series_v ) {
			foreach ( $series_v ['data'] as $data_k => $data_v ) {
				$data_v ['color'] = $color [$data_k];
				$series_v ['data'] [$data_k] = $data_v;
			}
			$stat_arr ['series'] [$series_k] ['data'] = $series_v ['data'];
		}
		return json_encode ( $stat_arr );
	}
	
	/**
	 * 获得Basicbar统计图数据
	 */
	static public function getStatData_Basicbar($stat_arr) {
		// 图表区、图形区和通用图表配置选项
		$stat_arr ['chart'] ['type'] = 'bar';
		// 去除版权信息
		$stat_arr ['credits'] ['enabled'] = false;
		// 导出功能选项
		$stat_arr ['exporting'] ['enabled'] = false;
		// 显示datalabel
		$stat_arr ['plotOptions'] ['bar'] ['dataLabels'] ['enabled'] = true;
		// 标题如果为字符串则使用默认样式
		is_string ( $stat_arr ['title'] ) ? $stat_arr ['title'] = array (
				'text' => "<b>{$stat_arr['title']}</b>",
				'x' => - 20 
		) : '';
		// 子标题如果为字符串则使用默认样式
		is_string ( $stat_arr ['subtitle'] ) ? $stat_arr ['subtitle'] = array (
				'text' => "<b>{$stat_arr['subtitle']}</b>",
				'x' => - 20 
		) : '';
		// Y轴如果为字符串则使用默认样式
		if (is_string ( $stat_arr ['yAxis'] )) {
			$text = $stat_arr ['yAxis'];
			unset ( $stat_arr ['yAxis'] );
			$stat_arr ['yAxis'] ['title'] ['text'] = $text;
		}
		// 柱形的颜色数组
		$color = array (
				'#7a96a4',
				'#cba952',
				'#667b16',
				'#a26642',
				'#349898',
				'#c04f51',
				'#5c315e',
				'#445a2b',
				'#adae50',
				'#14638a',
				'#b56367',
				'#a399bb',
				'#070dfa',
				'#47ff07',
				'#f809b7' 
		);
		
		foreach ( $stat_arr ['series'] as $series_k => $series_v ) {
			foreach ( $series_v ['data'] as $data_k => $data_v ) {
				if (! $data_v ['color']) {
					$data_v ['color'] = $color [$data_k % 15];
				}
				$series_v ['data'] [$data_k] = $data_v;
			}
			$stat_arr ['series'] [$series_k] ['data'] = $series_v ['data'];
		}
		return json_encode ( $stat_arr );
	}
	
	/**
	 * 计算环比
	 */
	static public function getHb($updata, $currentdata) {
		if ($updata != 0) {
			$mtomrate = round ( ($currentdata - $updata) / $updata * 100, 2 ) . '%';
		} else {
			$mtomrate = '-';
		}
		return $mtomrate;
	}
	
	/**
	 * 计算同比
	 */
	static public function getTb($updata, $currentdata) {
		if ($updata != 0) {
			$ytoyrate = round ( ($currentdata - $updata) / $updata * 100, 2 ) . '%';
		} else {
			$ytoyrate = '-';
		}
		return $ytoyrate;
	}
	
	/**
	 * 地图统计图
	 */
	static public function getStatData_Map($stat_arr) {
		// $color_arr = array('#f63a3a','#ff5858','#ff9191','#ffc3c3','#ffd5d5');
		$color_arr = array (
				'#fd0b07',
				'#ff9191',
				'#f7ba17',
				'#fef406',
				'#25aae2' 
		);
		$stat_arrnew = array ();
		foreach ( $stat_arr as $k => $v ) {
			$stat_arrnew [] = array (
					'cha' => $v ['cha'],
					'name' => $v ['name'],
					'des' => $v ['des'],
					'color' => $color_arr [$v ['level']] 
			);
		}
		return json_encode ( $stat_arrnew );
	}
	/**
	 * 获得饼形图数据
	 */
	static public function getStatData_Pie($data) {
		$stat_arr ['chart'] ['type'] = 'pie';
		$stat_arr ['credits'] ['enabled'] = false;
		$stat_arr ['title'] ['text'] = $data ['title'];
		$stat_arr ['tooltip'] ['pointFormat'] = '{series.name}: <b>{point.y}</b>';
		$stat_arr ['plotOptions'] ['pie'] = array (
				'allowPointSelect' => true,
				'cursor' => 'pointer',
				'dataLabels' => array (
						'enabled' => $data ['label_show'],
						'color' => '#000000',
						'connectorColor' => '#000000',
						'format' => '<b>{point.name}</b>: {point.percentage:.1f} %' 
				) 
		);
		$stat_arr ['series'] [0] ['name'] = $data ['name'];
		$stat_arr ['series'] [0] ['data'] = array ();
		foreach ( $data ['series'] as $k => $v ) {
			$stat_arr ['series'] [0] ['data'] [] = array (
					$v ['p_name'],
					$v ['allnum'] 
			);
		}
		// exit(json_encode($stat_arr));
		return json_encode ( $stat_arr );
	}
	static public function get_height($image) {
		$size = getimagesize ( $image );
		$height = $size [1];
		return $height;
	}
	static public function get_width($image) {
		$size = getimagesize ( $image );
		$width = $size [0];
		return $width;
	}
	static public function resize_thumb($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale) {
		$newImageWidth = ceil ( $width * $scale );
		$newImageHeight = ceil ( $height * $scale );
		if (C ( 'thumb.cut_type' ) == 'im') {
			$exec_str = rtrim ( C ( 'thumb.impath' ), '/' ) . '/convert -quality 100 -crop ' . $width . 'x' . $height . '+' . $start_width . '+' . $start_height . ' -resize ' . $newImageWidth . 'x' . $newImageHeight . ' ' . $image . ' ' . $thumb_image_name;
			exec ( $exec_str );
		} else {
			list ( $imagewidth, $imageheight, $imageType ) = getimagesize ( $image );
			$imageType = image_type_to_mime_type ( $imageType );
			$newImage = imagecreatetruecolor ( $newImageWidth, $newImageHeight );
			$white = imagecolorallocate ( $newImage, 255, 255, 255 );
			imagefill ( $newImage, 0, 0, $white );
			switch ($imageType) {
				case "image/gif" :
					$source = imagecreatefromgif ( $image );
					break;
				case "image/pjpeg" :
				case "image/jpeg" :
				case "image/jpg" :
					$source = imagecreatefromjpeg ( $image );
					break;
				case "image/png" :
				case "image/x-png" :
					$source = imagecreatefrompng ( $image );
					break;
			}
			$dst_w = $dst_h = 0;
			if ($newImageWidth > $width) {
				$dst_w = ($newImageWidth - $width) / 2;
			}
			if ($newImageHeight > $height) {
				$dst_h = ($newImageHeight - $height) / 2;
			}
			if ($dst_w > 0) {
				imagecopyresampled ( $newImage, $source, $dst_w, $dst_h, $start_width, $start_height, $width, $height, $width, $height );
			} else {
				imagecopyresampled ( $newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height );
			}
			
			switch ($imageType) {
				case "image/gif" :
					imagegif ( $newImage, $thumb_image_name );
					break;
				case "image/pjpeg" :
				case "image/jpeg" :
				case "image/jpg" :
					imagejpeg ( $newImage, $thumb_image_name, 100 );
					break;
				case "image/png" :
				case "image/x-png" :
					imagepng ( $newImage, $thumb_image_name );
					break;
			}
		}
		return $thumb_image_name;
	}
	
	/**
	 * 获得系统年份数组
	 */
	static public function getSystemYearArr() {
		$year_arr = array (
				'2010' => '2010',
				'2011' => '2011',
				'2012' => '2012',
				'2013' => '2013',
				'2014' => '2014',
				'2015' => '2015',
				'2016' => '2016',
				'2017' => '2017',
				'2018' => '2018',
				'2019' => '2019',
				'2020' => '2020' 
		);
		return $year_arr;
	}
	
	/**
	 * 获得系统月份数组
	 */
	static public function getSystemMonthArr() {
		$month_arr = array (
				'1' => '01',
				'2' => '02',
				'3' => '03',
				'4' => '04',
				'5' => '05',
				'6' => '06',
				'7' => '07',
				'8' => '08',
				'9' => '09',
				'10' => '10',
				'11' => '11',
				'12' => '12' 
		);
		return $month_arr;
	}
	
	/**
	 * 获得系统周数组
	 */
	static public function getSystemWeekArr() {
		$week_arr = array (
				'1' => '周一',
				'2' => '周二',
				'3' => '周三',
				'4' => '周四',
				'5' => '周五',
				'6' => '周六',
				'7' => '周日' 
		);
		return $week_arr;
	}
	
	/**
	 * 获取某月的最后一天
	 */
	static public function getMonthLastDay($year, $month) {
		$t = mktime ( 0, 0, 0, $month + 1, 1, $year );
		$t = $t - 60 * 60 * 24;
		return $t;
	}
	
	/**
	 * 获得系统某月的周数组，第一周不足的需要补足
	 */
	static public function getMonthWeekArr($current_year, $current_month) {
		// 该月第一天
		$firstday = strtotime ( $current_year . '-' . $current_month . '-01' );
		// 该月的第一周有几天
		$firstweekday = (7 - date ( 'N', $firstday ) + 1);
		// 计算该月第一个周一的时间
		$starttime = $firstday - 3600 * 24 * (7 - $firstweekday);
		// 该月的最后一天
		$lastday = strtotime ( $current_year . '-' . $current_month . '-01' . " +1 month -1 day" );
		// 该月的最后一周有几天
		$lastweekday = date ( 'N', $lastday );
		// 该月的最后一个周末的时间
		$endtime = $lastday - 3600 * 24 * ($lastweekday % 7);
		$step = 3600 * 24 * 7; // 步长值
		$week_arr = array ();
		for($i = $starttime; $i < $endtime; $i = $i + 3600 * 24 * 7) {
			$week_arr [] = array (
					'key' => date ( 'Y-m-d', $i ) . '|' . date ( 'Y-m-d', $i + 3600 * 24 * 6 ),
					'val' => date ( 'Y-m-d', $i ) . '~' . date ( 'Y-m-d', $i + 3600 * 24 * 6 ) 
			);
		}
		return $week_arr;
	}
	
	/**
	 * 获取本周的开始时间和结束时间
	 */
	static public function getWeek_SdateAndEdate($current_time) {
		$current_time = strtotime ( date ( 'Y-m-d', $current_time ) );
		$return_arr ['sdate'] = date ( 'Y-m-d', $current_time - 86400 * (date ( 'N', $current_time ) - 1) );
		$return_arr ['edate'] = date ( 'Y-m-d', $current_time + 86400 * (7 - date ( 'N', $current_time )) );
		return $return_arr;
	}
	static public function getRefUrl() {
		return request_uri () ;
	}
	static public function getLoadingImage() {
		return CMS_TEMPLATES_URL . DS . 'images/loading.gif';
	}
	
	/**
	 * 画报图片列表
	 */
	static public function getPictureImageUrl($picture_id) {
		return CMS_SITE_URL . '&c=Picture&a=picture_detail_image&picture_id=' . $picture_id;
	}
	
	/**
	 * 获取商品URL
	 */
	static public function getGoodsUrl($goods_id) {
		return SHOP_SITE_URL . '&c=Goods&goods_id=' . $goods_id;
	}
	
	/**
	 * 返回图片居中显示的样式字符串
	 *
	 * @param $image_width 图片宽度
	 *        	$image_height 图片高度
	 *        	$box_width 目标图片尺寸宽度
	 *        	$box_height 目标图片尺寸高度
	 *        	
	 * @return string 图片居中显示style字符串
	 *        
	 */
	static public function getMiddleImgStyleString($image_width, $image_height, $box_width, $box_height) {
		$image_style = array ();
		$image_style ['width'] = $box_width;
		$image_style ['height'] = $box_height;
		$image_style ['left'] = 0;
		$image_style ['top'] = 0;
		
		if (($image_width - $box_width) > ($image_height - $box_height)) {
			if ($image_width > $box_width) {
				$image_style ['width'] = $box_height / $image_height * $image_width;
				$image_style ['left'] = ($box_width - $image_style ['width']) / 2;
			}
		} else {
			if ($image_height > $box_height) {
				$image_style ['height'] = $box_width / $image_width * $image_height;
				$image_style ['top'] = ($box_height - $image_style ['height']) / 2;
			}
		}
		
		$style_string = 'style="';
		$style_string .= 'height: ' . $image_style ['height'] . 'px;';
		$style_string .= ' width: ' . $image_style ['width'] . 'px;';
		$style_string .= ' left: ' . $image_style ['left'] . 'px;';
		$style_string .= 'top: ' . $image_style ['top'] . 'px;';
		$style_string .= '"';
		
		return $style_string;
	}
	
	/**
	 * Recently two voters
	 */
	static public function recentlyTwoVoters($str) {
		$str = explode ( ' ', $str, 3 );
		$rs = '';
		if (isset ( $str [0] ) && ! empty ( $str [0] ))
			$rs .= $str [0];
		if (isset ( $str [1] ) && ! empty ( $str [1] ))
			$rs .= ', ' . $str [1];
		return $rs;
	}
	
	/**
	 * member rank html
	 */
	static public function memberLevelHtml($param) {
		if ($param ['is_identity'] == 1) {
			return "<div class=\"spdmember-rank spdmember-rank-m\" title=\"圈主\">圈主<i></i></div>";
		} else if ($param ['is_identity'] == 2) {
			return "<div class=\"spdmember-rank spdmember-rank-a\" title=\"管理员\">管理员<i></i></div>";
		} else {
			return "<div class=\"spdmember-rank spdmember-rank-" . $param ['cm_level'] . "\" title=\"" . L ( 'circle_level_introduction_page' ) . "\"><a href=\"" . CIRCLE_SITE_URL . "&c=Group&a=level_intr&c_id=" . $param ['circle_id'] . "\" target=\"_blank\">" . ($param ['cm_levelname'] == '' ? L ( 'circle_violation' ) : $param ['cm_levelname']) . "</a><i></i><em>" . $param ['cm_level'] . "</em></div>";
		}
	}
	/**
	 * 文本过滤
	 *
	 * @param $param string
	 *        	$subject
	 * @return string
	 */
	static public function circleCenterCensor($subject) {
		$replacement = '***';
		if (C ( 'circle_wordfilter' ) == '')
			return $subject;
		$find = explode ( ',', C ( 'circle_wordfilter' ) );
		foreach ( $find as $val ) {
			if (preg_match ( '/^\/(.+?)\/$/', $val, $a )) {
				$subject = preg_replace ( $val, $replacement, $subject );
			} else {
				$val = preg_replace ( "/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote ( $val, '/' ) );
				$subject = preg_replace ( "/" . $val . "/", $replacement, $subject );
			}
		}
		return $subject;
	}
	
	/**
	 * tidy theme goods information
	 *
	 * @param array $array        	
	 * @param string $key        	
	 * @param int $deep
	 *        	1 one-dimensional array 2 two dimension array
	 * @param string $type        	
	 * @return array
	 */
	static public function tidyThemeGoods($array, $key, $deep = 1, $type = 60) {
		if (is_array ( $array )) {
			$tmp = array ();
			foreach ( $array as $v ) {
				if ($v ['thg_type'] == 0) {
					$v ['image'] = thumb ( $v, $type );
					$v ['thg_url'] = urlShop ( 'goods', 'index', array (
							'goods_id' => $v ['goods_id'] 
					) );
				} else {
					$v ['image'] = $v ['goods_image'];
				}
				if ($deep === 1) {
					$tmp [$v [$key]] = $v;
				} elseif ($deep === 2) {
					$tmp [$v [$key]] [] = $v;
				}
			}
			return $tmp;
		} else {
			return $array;
		}
	}
	/**
	 * The editor
	 *
	 * @param string $cname
	 *        	The content of the editor 'id' and the 'name' of the name
	 * @param string $content
	 *        	The editor content
	 * @param string $type
	 *        	The toolbar type
	 * @param array $affix
	 *        	The affix content
	 * @param string $gname
	 *        	The name of the goods content
	 * @param array $goods
	 *        	The goods content
	 * @param array $readperm
	 *        	Optional permissions array
	 * @param int $rpvalue
	 *        	Has chosen the permissions
	 */
	static public function showMiniEditor($cname, $content = '', $type = 'all', $affix = array(), $gname = '', $goods = array(), $readperm = array(), $rpvalue = 0) {
		switch ($type) {
			case 'manage' :
				$items = array (
						'font',
						'size',
						'line',
						'bold',
						'italic',
						'underline',
						'color',
						'line',
						'url',
						'flash',
						'image',
						'line',
						'smilier' 
				);
				break;
			case 'quickReply' :
				$items = array (
						'font',
						'size',
						'line',
						'bold',
						'italic',
						'underline',
						'color',
						'line',
						'url',
						'flash',
						'line',
						'smilier' 
				);
				break;
			case 'hQuickReply' :
				$items = array (
						'font',
						'size',
						'line',
						'bold',
						'italic',
						'underline',
						'color',
						'line',
						'url',
						'flash',
						'line',
						'smilier',
						'highReply' 
				);
				break;
			default :
				$items = array (
						'font',
						'size',
						'line',
						'bold',
						'italic',
						'underline',
						'color',
						'line',
						'affix',
						'line',
						'url',
						'flash',
						'image',
						'goods',
						'line',
						'smilier' 
				);
				break;
		}
		
		// toolbar items
		$_line = "<span class=\"line\"></span>";
		$_font = "<a href=\"javascript:void(0);\" datype=\"font-family\" class=\"font-family\">" . L ( 'spd_font' ) . "
                    <div class=\"ubb-layer font-family-layer\">
                        <div class=\"arrow\"></div>
                        <span class=\"ff01\" data-param=\"Microsoft YaHei\">" . L ( 'spd_Microsoft_YaHei' ) . "</span><span class=\"ff02\" data-param=\"simsun\">" . L ( 'spd_simsun' ) . "</span><span class=\"ff03\" data-param=\"simhei\">" . L ( 'spd_simhei' ) . "</span><span class=\"ff04\" data-param=\"Arial\">Arial</span><span class=\"ff05\" data-param=\"Verdana\">Verdana</span><span class=\"ff06\" data-param=\"Helvetica\">Helvetica</span><span class=\"ff07\" data-param=\"Tahoma\">Tahoma</span>
                    </div>
                </a>";
		$_size = "<a href=\"javascript:void(0);\" datype=\"font-size\" class=\"font-size\">" . L ( 'spd_font_size' ) . "
                    <div class=\"ubb-layer font-size-layer\">
                        <div class=\"arrow\"></div>
                        <span class=\"s12\">12px</span><span class=\"s14\">14px</span><span class=\"s16\">16px</span><span class=\"s18\">18px</span><span class=\"s20\">20px</span><span class=\"s22\">22px</span><span class=\"s24\">24px</span>
                    </div>
                </a>";
		$_bold = "<a href=\"javascript:void(0);\" datype=\"b\" title=\"" . L ( 'spd_font_bold' ) . "\"><i class=\"font-b\"></i></a>";
		$_italic = "<a href=\"javascript:void(0);\" datype=\"i\" title=\"" . L ( 'spd_font_italic' ) . "\"><i class=\"font-i\"></i></a>";
		$_underline = "<a href=\"javascript:void(0);\" datype=\"u\" title=\"" . L ( 'spd_font_underline' ) . "\"><i class=\"font-u\"></i></a>";
		$_color = "<a href=\"javascript:void(0);\" datype=\"color\" title=\"" . L ( 'spd_font_color' ) . "\" class=\"font-color-handle\"><i class=\"font-color\"></i>
                    <div class=\"ubb-layer font-color-layer\">
                        <div class=\"arrow\"></div>
                        <span class=\"c-000000\"></span><span class=\"c-A0522D\"></span><span class=\"c-556B2F\"></span><span class=\"c-006400\"></span><span class=\"c-483D8B\"></span><span class=\"c-000080\"></span><span class=\"c-4B0082\"></span><span class=\"c-2F4F4F\"></span> <span class=\"c-8B0000\"></span><span class=\"c-FF8C00\"></span><span class=\"c-808000\"></span><span class=\"c-008000\"></span><span class=\"c-008080\"></span><span class=\"c-0000FF\"></span><span class=\"c-708090\"></span><span class=\"c-696969\"></span><span class=\"c-FF0000\"></span><span class=\"c-F4A460\"></span><span class=\"c-9ACD32\"></span><span class=\"c-2E8B57\"></span><span class=\"c-48D1CC\"></span><span class=\"c-4169E1\"></span><span class=\"c-800080\"></span><span class=\"c-808080\"></span><span class=\"c-FF00FF\"></span><span class=\"c-FFA500\"></span><span class=\"c-FFFF00\"></span><span class=\"c-00FF00\"></span><span class=\"c-00FFFF\"></span><span class=\"c-00BFFF\"></span><span class=\"c-9932CC\"></span><span class=\"c-C0C0C0\"></span><span class=\"c-FFC0CB\"></span><span class=\"c-F5DEB3\"></span><span class=\"c-FFFACD\"></span><span class=\"c-98FB98\"></span><span class=\"c-AFEEEE\"></span><span class=\"c-ADD8E6\"></span><span class=\"c-DDA0DD\"></span>
                    </div>
                </a>";
		$_affix = "<div class=\"upload-btn\" title=\"" . L ( 'spd_upload_image_affix' ) . "\">
                    <span><i class=\"upload-img\"></i>
                        <div class=\"upload-button\">" . L ( 'spd_upload_affix' ) . "</div>
                    </span>
                    <input type=\"file\" name=\"test_file\" id=\"test_file\" multiple=\"multiple\"  file_id=\"0\" class=\"upload-file\" size=\"1\" hidefocus=\"true\" style=\"cursor: pointer;\" />
                    <input id=\"submit_button\" style=\"display:none\" type=\"button\" value=\"&nbsp;\" onClick=\"submit_form($(this))\" />
                </div>";
		$_url = "<a href=\"javascript:void(0);\" datype=\"url\" title=\"" . L ( 'spd_insert_link_address' ) . "\" class=\"mr5 url-handle\"><i class=\"url\"></i>" . L ( 'spd_line' ) . "
                    <div class=\"ubb-layer url-layer\" style=\"display: none;\">
                        <div class=\"arrow\"></div>
                        <label>" . L ( 'spd_link_content' ) . "</label>
                        <input name=\"content\" type=\"text\" class=\"text w180\" />
                        <label>" . L ( 'spd_link_address' ) . "</label>
                        <input name=\"url\" type=\"text\" class=\"text w180\" placeholder=\"http://\" />
                        <input name=\"" . L ( 'spd_submit' ) . "\" type=\"submit\" class=\"button\" value=\"" . L ( 'spd_submit' ) . "\"/>
                    </div>
                </a>";
		$_flash = "<a href=\"javascript:void(0);\" datype=\"flase\" title=\"" . L ( 'spd_video_address' ) . "\" class=\"mr5 flash-handle\"><i class=\"flash\"></i>" . L ( 'spd_video' ) . "
                    <div class=\"ubb-layer flash-layer\" style=\"display: none;\">
                        <div class=\"arrow\"></div>
                        <label>" . L ( 'spd_video_address' ) . "</label>
                        <input name=\"flash\" type=\"text\" class=\"text w180\" placeholder=\"http://\" />
                        <input name=\"" . L ( 'spd_submit' ) . "\" type=\"submit\" class=\"button\" value=\"" . L ( 'spd_submit' ) . "\"/>
                    </div>
                </a>";
		$_image = "<a href=\"javascript:void(0);\" datype=\"uploadImage\" title=\"" . L ( 'spd_insert_network_image' ) . "\" class=\"mr5\"><i class=\"url-img\"></i>" . L ( 'spd_image' ) . "</a>";
		$_goods = "<a href=\"javascript:void(0);\" datype=\"chooseGoods\" title=\"" . L ( 'spd_insert_relevance_goods' ) . "\"><i class=\"url-goods\"></i>" . L ( 'spd_goods' ) . "</a>";
		$_smilier = "<a href=\"javascript:void(0);\" datype=\"smilier\" title=\"" . L ( 'spd_insert_smilier' ) . "\" class=\"smilier-handle\"><i class=\"smilier\"></i>" . L ( 'spd_smilier' ) . "
                        <div class=\"ubb-layer smilier-layer\">
                            <div class=\"arrow\"></div>
                            <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/adore.png\" data-param=\"adore\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/after_boom.png\" data-param=\"after_boom\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/ah.png\" data-param=\"ah\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/amazing.png\" data-param=\"amazing\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/anger.png\" data-param=\"anger\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/angry.png\" data-param=\"angry\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/baffle.png\" data-param=\"baffle\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/batman.png\" data-param=\"batman\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/beat_brick.png\" data-param=\"beat_brick\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/bigsmile.png\" data-param=\"bigsmile\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/bye_bye.png\" data-param=\"bye_bye\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/confuse.png\" data-param=\"confuse\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/cool.png\" data-param=\"cool\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/crazy.png\" data-param=\"crazy\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/crazy_rabbit.png\" data-param=\"crazy_rabbit\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/cry.png\" data-param=\"cry\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/dead.png\" data-param=\"dead\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/devil.png\" data-param=\"devil\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/diver.png\" data-param=\"diver\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/doubt.png\" data-param=\"doubt\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/evilgrin.png\" data-param=\"evilgrin\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/exciting.png\" data-param=\"exciting\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/flower_dead.png\" data-param=\"flower_dead\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/go.png\" data-param=\"go\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/greedy.png\" data-param=\"greedy\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/haha.png\" data-param=\"haha\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/hand_flower.png\" data-param=\"hand_flower\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/happy.png\" data-param=\"happy\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/horror.png\" data-param=\"horror\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/hypnotized.png\" data-param=\"hypnotized\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/kiss.png\" data-param=\"kiss\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/love.png\" data-param=\"love\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/mad.png\" data-param=\"mad\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/matrix.png\" data-param=\"matrix\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/misdoubt.png\" data-param=\"misdoubt\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/money.png\" data-param=\"money\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/nerd.png\" data-param=\"nerd\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/ninja.png\" data-param=\"ninja\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/nosebleed.png\" data-param=\"nosebleed\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/pirate.png\" data-param=\"pirate\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/question.png\" data-param=\"question\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/sad.png\" data-param=\"sad\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/shame.png\" data-param=\"shame\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/shocked.png\" data-param=\"shame\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/silent.png\" data-param=\"silent\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/sleep.png\" data-param=\"sleep\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/sweat.png\" data-param=\"sweat\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/star.png\" data-param=\"star\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/whist.png\" data-param=\"whist\"></span> <span><img src=\"" . CIRCLE_TEMPLATES_URL . "/images/smilier/surrender.png\" data-param=\"surrender\"></span>
                        </div>
                    </a>";
		$_highReply = "<a href=\"javascript:void(0);\" datype=\"highReply\" class=\"high-reply\"><i class=\"high\"></i>" . L ( 'spd_advanced_reply' ) . "</a>";
		
		// Spell the editor contents
		$__content = '';
		$__content .= "<div class=\"content\">
            <div class=\"ubb-bar\">";
		foreach ( $items as $val ) {
			$val = '_' . $val;
			$__content .= $$val;
		}
		$__content .= "</div>
            <div class=\"textarea\">
                <textarea id=\"" . $cname . "\" name=\"" . $cname . "\">" . $content . "</textarea>
            </div>
            <div class=\"smilier\"></div>
        </div>";
		
		// The attachment part
		$__affix = '';
		$__affix .= "<div class=\"affix\">
              <h3><i></i>" . L ( 'spd_relevance_adjunct' ) . "</h3>
              <div class=\"help\" datype=\"affix\" " . (empty ( $affix ) ? "" : "style=\"display: none;\"") . ">
                <p>" . L ( 'spd_relevance_adjunct_help_one' ) . "</p>
                <p>" . L ( 'spd_relevance_adjunct_help_two' ) . "</p>
              </div>
              <div id=\"scrollbar\">
              <ul>";
		if (! empty ( $affix )) {
			foreach ( $affix as $val ) {
				$__affix .= "<li>
                  <p><img src=\"" . themeImageUrl ( $val ['affix_filethumb'] ) . "\"> </p>
                  <div class=\"handle\"> <a data-param=\"" . themeImageUrl ( $val ['affix_filename'] ) . "\" datype=\"affix_insert\" href=\"javascript:void(0);\"><i class=\"c\"></i>" . L ( 'spd_insert' ) . "</a> <a data-param=\"" . $val ['affix_id'] . "\" datype=\"affix_delete\" href=\"javascript:void(0);\"><i class=\"d\"></i>" . L ( 'spd_delete' ) . "</a> </div>
                </li>";
			}
		}
		$__affix .= "</ul>
              </div>
            </div>";
		
		$__maffix = str_replace ( "datype=\"affix_delete\"", "datype=\"maffix_delete\"", $__affix );
		
		// After insert part of goods
		$__goods = '';
		$__goods .= "<div class=\"insert-goods\" " . (empty ( $goods ) ? "style=\"display:none;\"" : "") . ">
              <h3><i></i>" . L ( 'spd_select_insert_goods,spd_colon' ) . "</h3>";
		if (! empty ( $goods )) {
			foreach ( $goods as $val ) {
				$__goods .= "<dl>
                <dt class=\"spdgoods-name\">" . $val ['goods_name'] . "</dt>
                <dd class=\"spdgoods-pic\"><a href=\"javascript:void(0);\"><img src=\"" . $val ['image'] . "\"></a></dd>
                <dd class=\"spdgoods-price\"><em>" . $val ['goods_price'] . "</em></dd>
                <dd class=\"spdgoods-del\">" . L ( 'spd_delete' ) . "</dd>
                <input type=\"hidden\" value=\"" . $val ['goods_id'] . "\" name=\"" . $gname . "[" . $val ['themegoods_id'] . "][id]\">
                <input type=\"hidden\" value=\"" . $val ['goods_name'] . "\" name=\"" . $gname . "[" . $val ['themegoods_id'] . "][name]\">
                <input type=\"hidden\" value=\"" . $val ['goods_price'] . "\" name=\"" . $gname . "[" . $val ['themegoods_id'] . "][price]\">
                <input type=\"hidden\" value=\"" . $val ['goods_image'] . "\" name=\"" . $gname . "[" . $val ['themegoods_id'] . "][image]\">
                <input type=\"hidden\" value=\"" . $val ['store_id'] . "\" name=\"" . $gname . "[" . $val ['themegoods_id'] . "][storeid]\">
                <input type=\"hidden\" value=\"" . $val ['thg_type'] . "\" name=\"" . $gname . "[" . $val ['themegoods_id'] . "][type]\">
                <input type=\"hidden\" value=\"" . $val ['thg_url'] . "\" name=\"" . $gname . "[" . $val ['themegoods_id'] . "][uri]\">
              </dl>";
			}
		}
		$__goods .= "</div>";
		
		// Part read permissions
		$__readperm = '';
		if (! empty ( $readperm )) {
			$__readperm .= "<div class=\"readperm\"><span>" . L ( 'spd_read_perm,spd_colon' ) . "</span><span><select name=\"readperm\">";
			foreach ( $readperm as $key => $val ) {
				$__readperm .= "<option value=\"" . $key . "\" " . (($rpvalue == $key) ? "selected=\"selected\"" : "") . ">" . $val . "&nbsp;lv" . $key . "</option>";
			}
			$__readperm .= "</select></span></div>";
		}
		
		switch ($type) {
			case 'manage' :
				$return = $__content . $__maffix . $__goods . $__readperm;
				break;
			case 'quickReply' :
				$return = $__content;
				break;
			case 'hQuickReply' :
				$return = $__content;
				break;
			default :
				$return = $__content . $__affix . $__goods . $__readperm;
				break;
		}
		return $return;
	}
	
	/**
	 * 成员身份
	 */
	static public function memberIdentity($identity) {
		switch (intval ( $identity )) {
			case 1 :
				return '<em class="c">' . L ( 'circle_manager' ) . '</em>';
				break;
			case 2 :
				return '<em class="a">' . L ( 'circle_administrate' ) . '</em>';
				break;
			case 3 :
			default :
				break;
		}
	}
	
	/**
	 * 买家秀图像
	 */
	static public function showImgUrl($param) {
		return UPLOAD_SITE_URL . '/' . ATTACH_MALBUM . '/' . $param ['member_id'] . '/' . str_ireplace ( '.', '_240.', $param ['ap_cover'] );
	}
	/**
	 * 根据会员id生成部分附件路径
	 */
	static public function themePartPath($id) {
		$a = $id % 20;
		$b = $id % 10;
		return $a . '/' . $b . '/' . $id;
	}
	/**
	 * Inform Url link
	 */
	static public function spellInformUrl($param) {
		if ($param ['reply_id'] == 0)
			return $url = "{$GLOBALS['_PAGE_URL']}&c=Theme&a=theme_detail&c_id=" . $param ['circle_id'] . '&t_id=' . $param ['theme_id'];
		
		$where = array ();
		$where ['circle_id'] = $param ['circle_id'];
		$where ['theme_id'] = $param ['theme_id'];
		$where ['reply_id'] = array (
				'elt',
				$param ['reply_id'] 
		);
		$count = Model ()->table ( 'circle_threply' )->where ( $where )->count ();
		$page = ceil ( $count / 15 );
		return $url = "{$GLOBALS['_PAGE_URL']}&c=Theme&a=theme_detail&c_id=" . $param ['circle_id'] . '&t_id=' . $param ['theme_id'] . '&curpage=' . $page . '#f' . $param ['reply_id'];
	}
	/**
	 * Replace the UBB tag
	 *
	 * @param string $ubb        	
	 * @param int $video_sign        	
	 * @return string
	 */
	static public function replaceUBBTag($ubb, $video_sign = 1) {
		if ($video_sign) {
			$flash_sign = preg_match ( "/\[FLASH\](.*)\[\/FLASH\]/iU", $ubb );
		}
		$ubb = str_replace ( array (
				'[B]',
				'[/B]',
				'[I]',
				'[/I]',
				'[U]',
				'[/U]',
				'[/FONT]',
				'[/FONT-SIZE]',
				'[/FONT-COLOR]' 
		), array (
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'' 
		), preg_replace ( array (
				"/\[URL=(.*)\](.*)\[\/URL\]/iU",
				"/\[FONT=([A-Za-z ]*)\]/iU",
				"/\[FONT-SIZE=([0-9]*)\]/iU",
				"/\[FONT-COLOR=([A-Za-z0-9]*)\]/iU",
				"/\[SMILIER=([A-Za-z_]*)\/\]/iU",
				"/\[IMG\](.*)\[\/IMG\]/iU",
				"/\[FLASH\](.*)\[\/FLASH\]/iU",
				"<img class='pi' src=\"$1\"/>" 
		), array (
				'[' . L ( 'spd_link' ) . ']',
				"",
				"",
				"",
				"",
				'[' . L ( 'spd_img' ) . ']',
				($video_sign == 1 ? '' : '[' . L ( 'spd_video' ) . ']'),
				"" 
		), $ubb ) );
		
		if ($video_sign && ! empty ( $flash_sign )) {
			$ubb .= "<span datype=\"theme_read\"><img src=\"" . BASE_CIRCLE_STATIC_URL . "/images/default_play.gif\"></span>";
		}
		return $ubb;
	}
	
	/**
	 * 压缩PHP代码
	 *
	 * @param string $content
	 *        	压缩内容
	 * @return string
	 */
	static public function compress_code($content) {
		$strip_str = '';
		// 分析php源码
		$tokens = token_get_all ( $content );
		$last_space = false;
		for($i = 0, $j = count ( $tokens ); $i < $j; $i ++) {
			if (is_string ( $tokens [$i] )) {
				$last_space = false;
				$strip_str .= $tokens [$i];
			} else {
				switch ($tokens [$i] [0]) {
					// 过滤各种PHP注释
					case T_COMMENT :
					case T_DOC_COMMENT :
						break;
					// 过滤空格
					case T_WHITESPACE :
						if (! $last_space) {
							$strip_str .= ' ';
							$last_space = true;
						}
						break;
					default :
						$last_space = false;
						$strip_str .= $tokens [$i] [1];
				}
			}
		}
		return $strip_str;
	}
	static public function advshow($ap_id, $type = 'js') {
		if ($ap_id < 1)
			return;
		$time = time ();
		
		$ap_info = Model ( 'adv' )->getApById ( $ap_id );
		if (! $ap_info)
			return;
		
		$list = $ap_info ['adv_list'];
		unset ( $ap_info ['adv_list'] );
		extract ( $ap_info );
		if ($is_use !== '1') {
			return;
		}
		$adv_list = array ();
		$adv_info = array (); // 异步调用的数组格式
		foreach ( ( array ) $list as $k => $v ) {
			if ($v ['adv_start_date'] < $time && $v ['adv_end_date'] > $time && $v ['is_allow'] == '1') {
				$adv_list [] = $v;
			}
		}
		
		if (empty ( $adv_list )) {
			if ($ap_class == '1') { // 文字广告
				$content .= "<a href=''>";
				$content .= $default_content;
				$content .= "</a>";
			} else {
				$width = $ap_width;
				$height = $ap_height;
				$content .= "<a href='' title='" . $ap_name . "'>";
				$content .= "<img style='width:{$width}px;height:{$height}px' border='0' src='";
				$content .= UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $default_content;
				$content .= "' alt=''/>";
				$content .= "</a>";
				$adv_info ['adv_title'] = $ap_name;
				$adv_info ['adv_img'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $default_content;
				$adv_info ['adv_url'] = '';
			}
		} else {
			$select = 0;
			if ($ap_display == '1') { // 多广告展示
				$select = array_rand ( $adv_list );
			}
			$adv_select = $adv_list [$select];
			extract ( $adv_select );
			// 图片广告
			if ($ap_class == '0') {
				$width = $ap_width;
				$height = $ap_height;
				$pic_content = unserialize ( $adv_content );
				$pic = $pic_content ['adv_pic'];
				$url = $pic_content ['adv_pic_url'];
				$content .= "<a href='http://" . $pic_content ['adv_pic_url'] . "' target='_blank' title='" . $adv_title . "'>";
				$content .= "<img style='width:{$width}px;height:{$height}px' border='0' src='";
				$content .= UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $pic;
				$content .= "' alt='" . $adv_title . "'/>";
				$content .= "</a>";
				$adv_info ['adv_title'] = $adv_title;
				$adv_info ['adv_img'] = UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $pic;
				$adv_info ['adv_url'] = 'http://' . $pic_content ['adv_pic_url'];
			}
			// 文字广告
			if ($ap_class == '1') {
				$word_content = unserialize ( $adv_content );
				$word = $word_content ['adv_word'];
				$url = $word_content ['adv_word_url'];
				$content .= "<a href='http://" . $pic_content ['adv_word_url'] . "' target='_blank'>";
				$content .= $word;
				$content .= "</a>";
			}
			// Flash广告
			if ($ap_class == '3') {
				$width = $ap_width;
				$height = $ap_height;
				$flash_content = unserialize ( $adv_content );
				$flash = $flash_content ['flash_swf'];
				$url = $flash_content ['flash_url'];
				$content .= "<a href='http://" . $url . "' target='_blank'><button style='width:" . $width . "px; height:" . $height . "px; border:none; padding:0; background:none;' disabled><object id='FlashID' classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width='" . $width . "' height='" . $height . "'>";
				$content .= "<param name='movie' value='";
				$content .= UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $flash;
				$content .= "' /><param name='quality' value='high' /><param name='wmode' value='opaque' /><param name='swfversion' value='9.0.45.0' /><!-- 此 param 标签提示使用 Flash Player 6.0 r65 和更高版本的用户下载最新版本的 Flash Player。如果您不想让用户看到该提示，请将其删除。 --><param name='expressinstall' value='";
				$content .= RESOURCE_SITE_URL . "/js/expressInstall.swf'/><!-- 下一个对象标签用于非 IE 浏览器。所以使用 IECC 将其从 IE 隐藏。 --><!--[if !IE]>--><object type='application/x-shockwave-flash' data='";
				$content .= UPLOAD_SITE_URL . "/" . ATTACH_ADV . "/" . $flash;
				$content .= "' width='" . $width . "' height='" . $height . "'><!--<![endif]--><param name='quality' value='high' /><param name='wmode' value='opaque' /><param name='swfversion' value='9.0.45.0' /><param name='expressinstall' value='";
				$content .= RESOURCE_SITE_URL . "/js/expressInstall.swf'/><!-- 浏览器将以下替代内容显示给使用 Flash Player 6.0 和更低版本的用户。 --><div><h4>此页面上的内容需要较新版本的 Adobe Flash Player。</h4><p><a href='http://www.adobe.com/go/getflashplayer'><img src='http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif' alt='获取 Adobe Flash Player' width='112' height='33' /></a></p></div><!--[if !IE]>--></object><!--<![endif]--></object></button></a>";
			}
		}
		
		if ($type == 'array' && $ap_class == '0') {
			return $adv_info;
		}
		
		if ($type == 'js') {
			$content = "document.write(\"" . $content . "\");";
		}
		return $content;
	}
	
	static public function getClientIp()
	{
		$ret = "";
		if (isset($_SERVER['REMOTE_ADDR']))
		{
			$ret = $_SERVER['REMOTE_ADDR'];
		}
		else if (getenv("REMOTE_ADDR"))
		{
			$ret = getenv("REMOTE_ADDR");
		}
		return $ret;
	}
}