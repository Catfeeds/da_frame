<?php
use Common\Lib\ShopdaCore;

/**
 * 验证是否为平台店铺
 *
 * @return boolean
 */
function checkPlatformStore($store_id = 0) {
	return ShopdaCore::checkPlatformStore ( $store_id );
}

/**
 * 输出validate的验证信息
 *
 * @param array/string $error
 */
function showValidateError($error) {
	return ShopdaCore::showValidateError ( $error );
}

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
function recursionSpec($len, $sign) {
	return ShopdaCore::recursionSpec ( $len, $sign );
}

/**
 * 格式化ubb标签
 *
 * @param string $theme_content/$reply_content
 *        	话题内容/回复内容
 * @return string
 */
function ubb($ubb) {
	return ShopdaCore::ubb ( $ubb );
}
/**
 * 去掉ubb标签
 *
 * @param string $theme_content/$reply_content
 *        	话题内容/回复内容
 * @return string
 */
function removeUBBTag($ubb) {
	return ShopdaCore::removeUBBTag ( $ubb );
}

/**
 * 输出聊天信息
 *
 * @return string
 */
function getChat($layout) {
	return ShopdaCore::getChat ( $layout );
}


/**
 * sns表情标示符替换为html
 */
function parsesmiles($message) {
	return ShopdaCore::parsesmiles ( $message );
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
function rkcache($key, $callback = false) {
	return ShopdaCore::rkcache ( $key, $callback );
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
function wkcache($key, $value, $expire = null) {
	return ShopdaCore::wkcache ( $key, $value, $expire );
}
/**
 * KV缓存 删
 *
 * @param string $key
 *        	缓存名称
 * @return boolean
 */
function dkcache($key) {
	return ShopdaCore::dkcache ( $key );
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
function rcache($key = null, $prefix = '', $fields = '*') {
	return ShopdaCore::rcache ( $key, $prefix, $fields );
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
function wcache($key = null, $data = array(), $prefix, $period = 0) {
	return ShopdaCore::wcache ( $key, $data, $prefix, $period );
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
function dcache($key = null, $prefix = '') {
	return ShopdaCore::dcache ( $key, $prefix );
}

/**
 * 获取token
 *
 * @return string
 */
function securityGetToken() {
	return ShopdaCore::securityGetToken ();
}
function securityGetTokenValue() {
	return ShopdaCore::securityGetTokenValue ();
}

/**
 * 二级域名解析
 *
 * @return int 店铺id
 */
function subdomain() {
	return ShopdaCore::subdomain ();
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
function daReplaceText($message, $param) {
	return ShopdaCore::daReplaceText ( $message, $param );
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
function str_cut($string, $length, $dot = '') {
	return ShopdaCore::str_cut ( $string, $length, $dot );
}
/**
 * unicode转为utf8
 *
 * @param string $str
 *        	待转的字符串
 * @return string
 */
function unicodeToUtf8($str, $order = "little") {
	return ShopdaCore::unicodeToUtf8 ( $str, $order );
}
/*
 * 重写$_SERVER['REQUREST_URI']
 */
function request_uri() {
	return ShopdaCore::request_uri ();
}

/**
 * 产生验证码
 *
 * @param string $shopdamap
 *        	哈希数
 * @return string
 */
function makeSeccode($shopdamap) {
	return ShopdaCore::makeSeccode ( $shopdamap );
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
function checkSeccode($shopdamap, $value) {
	return ShopdaCore::checkSeccode ( $shopdamap, $value );
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
function setDaCookie($name, $value, $expire = '3600', $path = '', $domain = '', $secure = false) {
	return ShopdaCore::setDaCookie ( $name, $value, $expire, $path, $domain, $secure );
}
/**
 * 取得COOKIE的值
 *
 * @param string $name        	
 * @return unknown
 */
function cookie($name = '') {
	return ShopdaCore::cookie ( $name );
}
/**
 * 当访问的URL不存在时调用此函数并退出脚本
 *
 * @param string $c        	
 * @param string $a        	
 * @return void
 */
function requestNotFound($c = null, $a = null) {
	return ShopdaCore::requestNotFound ( $c, $a );
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
function showMessage($msg, $url = '', $show_type = 'html', $msg_type = 'succ', $is_show = 1, $time = 2000) {
	return ShopdaCore::showMessage ( $msg, $url, $show_type, $msg_type, $is_show, $time );
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
function showDialog($message = '', $url = '', $alert_type = 'error', $extrajs = '', $time = 2) {
	return ShopdaCore::showDialog ( $message, $url, $alert_type, $extrajs, $time );
}
/**
 * 取验证码hash值
 *
 * @param        	
 *
 * @return string 字符串类型的返回结果
 */
function getShopdaHash($controller = '', $action = '') {
	return ShopdaCore::getShopdaHash ( $controller, $action );
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
function encrypt($txt, $key = '') {
	return ShopdaCore::encrypt ( $txt, $key );
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
function decrypt($txt, $key = '', $ttl = 0) {
	return ShopdaCore::decrypt ( $txt, $key, $ttl );
}
/**
 * 取得IP
 *
 *
 * @return string 字符串类型的返回结果
 */
function getIp() {
	return ShopdaCore::getIp ();
}

/**
 * 读取目录列表
 * 不包括 .
 *
 *
 *
 *
 * .. 文件 三部分
 *
 * @param string $path
 *        	路径
 * @return array 数组格式的返回结果
 */
function readDirList($path) {
	return ShopdaCore::readDirList ( $path );
}
/**
 * 转换特殊字符
 *
 * @param string $string
 *        	要转换的字符串
 * @return string 字符串类型的返回结果
 */
function replaceSpecialChar($string) {
	return ShopdaCore::rreplaceSpecialChar ( $string );
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
function showEditor($id, $value = '', $width = '700px', $height = '300px', $style = 'visibility:hidden;', $upload_state = "true", $media_open = false, $type = 'all') {
	return ShopdaCore::showEditor ( $id, $value, $width, $height, $style, $upload_state, $media_open, $type );
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
function getDirSize($path, $size = 0) {
	return ShopdaCore::getDirSize ( $path, $size );
}

/**
 * 价格格式化
 *
 * @param int $price        	
 * @return string
 *
 */
function daPriceFormat($price) {
	return ShopdaCore::daPriceFormat ( $price );
}

/**
 * 价格格式化
 *
 * @param int $price        	
 * @return string
 *
 */
function daPriceFormatForList($price) {
	return ShopdaCore::daPriceFormatForList ( $price );
}

/**
 * 取得结算文字输出形式
 *
 * @param array $bill_state        	
 * @return string 描述输出
 */
function billState($bill_state) {
	return ShopdaCore::billState ( $bill_state );
}
/**
 * 取得订单商品销售类型文字输出形式
 *
 * @param array $goods_type        	
 * @return string 描述输出
 */
function orderGoodsType($goods_type) {
	return ShopdaCore::orderGoodsType ( $goods_type );
}
/**
 * 取得订单支付类型文字输出形式
 *
 * @param array $payment_code        	
 * @return string
 */
function orderPaymentName($payment_code) {
	return ShopdaCore::orderPaymentName ( $payment_code );
}

/**
 * 取得订单状态文字输出形式
 *
 * @param array $order_info
 *        	订单数组
 * @return string $order_state 描述输出
 *        
 */
function orderState($order_info) {
	return ShopdaCore::orderState ( $order_info );
}

/**
 * 取得品牌图片
 *
 * @param string $image_name        	
 * @return string
 */
function brandImage($image_name = '') {
	return ShopdaCore::brandImage ( $image_name );
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
function pointprodThumb($image_name = '', $type = '') {
	return ShopdaCore::pointprodThumb ( $image_name, $type );
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
function snsThumb($image_name = '', $type = '') {
	return ShopdaCore::snsThumb ( $image_name, $type );
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
function gthumb($image_name = '', $type = '') {
	return ShopdaCore::gthumb ( $image_name, $type );
}

/**
 * 店铺二维码
 *
 * @param array $store_id        	
 * @return string
 */
function storeQRCode($store_id) {
	return ShopdaCore::storeQRCode ( $store_id );
}
/**
 * 商品二维码
 *
 * @param array $goods_info        	
 * @return string
 */
function goodsQRCode($goods_info) {
	return ShopdaCore::goodsQRCode ( $goods_info );
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
function cthumb($file, $type = '', $store_id = false) {
	return ShopdaCore::cthumb ( $file, $type, $store_id );
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
function thumb($goods = array(), $type = '') {
	return ShopdaCore::thumb ( $goods, $type );
}
function getMicroshopImageSize($image_url, $max_width = 238) {
	return ShopdaCore::getMicroshopImageSize ( $image_url, $max_width );
}
function http_get($url) {
	return ShopdaCore::http_get ( $url );
}
function get_server_ip() {
	return ShopdaCore::get_server_ip ();
}
function mobile_page($page_count) {
	return ShopdaCore::mobile_page ( $page_count );
}
function output_error($message, $extend_data = array()) {
	return ShopdaCore::output_error ( $message, $extend_data );
}
function output_data($datas, $extend_data = array(), $error = false, $with_site_setting = false) {
	return ShopdaCore::output_data ( $datas, $extend_data, $error, $with_site_setting );
}
function getParam() {
	return ShopdaCore::getParam ();
}

/**
 * 删除部分地址参数（适用于商品搜索品牌、属性部分）
 * 例：参数c=1_2_3_4
 * 如需要删除act中的3可使用该函数 removeRaram(array('c' => 3))
 * 该函数只能删除一个参数
 *
 * @param array $param        	
 */
function removeParam($param) {
	return ShopdaCore::removeParam ( $param );
}

/**
 * 替换并删除地址参数
 *
 * @param array $param        	
 */
function replaceAndDropParam($paramToReplace, $paramToDrop) {
	return ShopdaCore::replaceAndDropParam ( $paramToReplace, $paramToDrop );
}

/**
 * 替换地址参数(替换参数值)
 *
 * @param array $param        	
 */
function replaceParam($param) {
	return ShopdaCore::replaceParam ( $param );
}

/**
 * 删除地址参数(把参数的值赋值为0)
 *
 * @param array $param        	
 */
function dropParam($param) {
	return ShopdaCore::dropParam ( $param );
}

/**
 * 获得饼形图数据
 */
function getStatData_Pie($data) {
	return ShopdaCore::getStatData_Pie ( $data );
}
/**
 * 地图统计图
 */
function getStatData_Map($stat_arr) {
	return ShopdaCore::getStatData_Map ( $stat_arr );
}
/**
 * 计算同比
 */
function getTb($updata, $currentdata) {
	return ShopdaCore::getTb ( $updata, $currentdata );
}
/**
 * 计算环比
 */
function getHb($updata, $currentdata) {
	return ShopdaCore::getHb ( $updata, $currentdata );
}
/**
 * 获得Basicbar统计图数据
 */
function getStatData_Basicbar($stat_arr) {
	return ShopdaCore::getStatData_Basicbar ( $stat_arr );
}
/**
 * 获得Column2D统计图数据
 */
function getStatData_Column2D($stat_arr) {
	return ShopdaCore::getStatData_Column2D ( $stat_arr );
}
/**
 * 获得折线图统计图数据
 * param $statarr 图表需要的设置项
 */
function getStatData_LineLabels($stat_arr) {
	return ShopdaCore::getStatData_LineLabels ( $stat_arr );
}
function resize_thumb($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale) {
	return ShopdaCore::resize_thumb ( $thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale );
}
function get_width($image) {
	return ShopdaCore::get_width ( $image );
}
function get_height($image) {
	return ShopdaCore::get_height ( $image );
}

/**
 * 获得系统年份数组
 */
function getSystemYearArr() {
	return ShopdaCore::getSystemYearArr ();
}
/**
 * 获得系统月份数组
 */
function getSystemMonthArr() {
	return ShopdaCore::getSystemMonthArr ();
}
/**
 * 获得系统周数组
 */
function getSystemWeekArr() {
	return ShopdaCore::getSystemWeekArr ();
}

/**
 * 获取某月的最后一天
 */
function getMonthLastDay($year, $month) {
	return ShopdaCore::getMonthLastDay ( $year, $month );
}

/**
 * 获得系统某月的周数组，第一周不足的需要补足
 */
function getMonthWeekArr($current_year, $current_month) {
	return ShopdaCore::getMonthWeekArr ( $current_year, $current_month );
}

/**
 * 获取本周的开始时间和结束时间
 */
function getWeek_SdateAndEdate($current_time) {
	return ShopdaCore::getWeek_SdateAndEdate ( $current_time );
}
function getRefUrl() {
	return ShopdaCore::getRefUrl ();
}
function getLoadingImage() {
	return ShopdaCore::getLoadingImage ();
}

/**
 * 画报图片列表
 */
function getPictureImageUrl($picture_id) {
	return ShopdaCore::getPictureImageUrl ( $picture_id );
}

/**
 * 获取商品URL
 */
function getGoodsUrl($goods_id) {
	return ShopdaCore::getGoodsUrl ( $goods_id );
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
function getMiddleImgStyleString($image_width, $image_height, $box_width, $box_height) {
	return ShopdaCore::getMiddleImgStyleString ( $image_width, $image_height, $box_width, $box_height );
}

/**
 * Recently two voters
 */
function recentlyTwoVoters($str) {
	return ShopdaCore::recentlyTwoVoters ( $str );
}

/**
 * member rank html
 */
function memberLevelHtml($param) {
	return ShopdaCore::memberLevelHtml ( $param );
}

/**
 * 文本过滤
 *
 * @param $param string
 *        	$subject
 * @return string
 */
function circleCenterCensor($subject) {
	return ShopdaCore::circleCenterCensor ( $subject );
}
function advshow($ap_id, $type = 'js') {
	return ShopdaCore::advshow ( $ap_id, $type );
}

/**
 * 压缩PHP代码
 *
 * @param string $content
 *        	压缩内容
 * @return string
 */
function compress_code($content) {
	return ShopdaCore::compress_code ( $content );
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
function tidyThemeGoods($array, $key, $deep = 1, $type = 60) {
	return ShopdaCore::tidyThemeGoods ( $array, $key, $deep, $type );
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
function showMiniEditor($cname, $content = '', $type = 'all', $affix = array(), $gname = '', $goods = array(), $readperm = array(), $rpvalue = 0) {
	return ShopdaCore::showMiniEditor ( $cname, $content, $type, $affix, $gname, $goods, $readperm, $rpvalue );
}
/**
 * 成员身份
 */
function memberIdentity($identity) {
	return ShopdaCore::memberIdentity ( $identity );
}
/**
 * 买家秀图像
 */
function showImgUrl($param) {
	return ShopdaCore::showImgUrl ( $param );
}

/**
 * 根据会员id生成部分附件路径
 */
function themePartPath($id) {
	return ShopdaCore::themePartPath ( $id );
}

/**
 * Inform Url link
 */
function spellInformUrl($param) {
	return ShopdaCore::spellInformUrl ( $param );
}

/**
 * Replace the UBB tag
 *
 * @param string $ubb        	
 * @param int $video_sign        	
 * @return string
 */
function replaceUBBTag($ubb, $video_sign = 1) {
	return ShopdaCore::replaceUBBTag ( $ubb, $video_sign );
}


function getClientIp()
{
	return ShopdaCore::getClientIp();
}

