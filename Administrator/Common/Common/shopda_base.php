<?php
use Common\Lib\ShopdaBase;
function hookGuard($tag, $params) {
	return ShopdaBase::hookGuard ( $tag, $params );
}
function buildSimpleSign($params, $signKey, $filter = array(), $sort = 'asc') {
	return ShopdaBase::buildSimpleSign ( $params, $signKey, $filter, $sort );
}
function payNotify() {
	return ShopdaBase::payNotify ();
}
function payCallback() {
	return ShopdaBase::payCallback ();
}
function callOnce($url, $args = null, $method = "post", $withCookie = false, $timeout = 5, $headers = array(), $proxy = '') {
	return ShopdaBase::callOnce ( $url, $args, $method, $withCookie, $timeout, $headers, $proxy );
}
function _convert($args) {
	return ShopdaBase::_convert ( $args );
}
function getFullUrl($url, $args) {
	return ShopdaBase::getFullUrl ( $url, $args );
}
function viewRender($templateFile = '', $layout = '', $time = 2000, $charset = '', $contentType = '', $content = '', $prefix = '') {
	return ShopdaBase::viewRender ( $templateFile, $layout, $time, $charset, $contentType, $content, $prefix );
}
function viewAssign($name, $value) {
	return ShopdaBase::viewAssign ( $name, $value );
}
function viewGet($name) {
	return ShopdaBase::viewGet ( $name );
}
function viewSetDir($dir) {
	return ShopdaBase::viewSetDir ( $dir );
}
function requireTpl($tplName, $outputData, $lang = array(), $extraData = array(), $moduleName = '', $controllerName = '') {
	return ShopdaBase::requireTpl ( $tplName, $outputData, $lang, $extraData, $moduleName, $controllerName );
}

/**
 * 取得商品默认大小图片
 *
 * @param string $key
 *        	small tiny
 * @return string
 */
function defaultGoodsImage($key) {
	return ShopdaBase::defaultGoodsImage ( $key );
}

/**
 * 取得用户头像图片
 *
 * @param string $member_avatar        	
 * @return string
 */
function getMemberAvatar($member_avatar) {
	return ShopdaBase::getMemberAvatar ( $member_avatar );
}

/**
 * 取得用户头像图片
 *
 * @param string $member_avatar        	
 * @return string
 */
function getMemberAvatarHttps($member_avatar) {
	return ShopdaBase::getMemberAvatarHttps ( $member_avatar );
}

/**
 * 成员头像
 *
 * @param string $member_id        	
 * @return string
 */
function getMemberAvatarForID($id) {
	return ShopdaBase::getMemberAvatarForID ( $id );
}

/**
 * 取得店铺标志
 *
 * @param string $img
 *        	图片名
 * @param string $type
 *        	查询类型 store_logo/store_avatar
 * @return string
 */
function getStoreLogo($img, $type = 'store_avatar') {
	return ShopdaBase::getStoreLogo ( $img, $type );
}

/**
 * 获取文章URL
 */
function getCMSArticleUrl($article_id) {
	return ShopdaBase::getCMSArticleUrl ( $article_id );
}

/**
 * 获取画报URL
 */
function getCMSPictureUrl($picture_id) {
	return ShopdaBase::getCMSPictureUrl ( $picture_id );
}
/**
 * 获取文章图片URL
 */
function getCMSArticleImageUrl($image_path, $image_name, $type = 'list') {
	return ShopdaBase::getCMSArticleImageUrl ( $image_path, $image_name, $type );
}
/**
 * 获取文章图片URL
 */
function getCMSImageName($image_name_string) {
	return ShopdaBase::getCMSImageName ( $image_name_string );
}
/**
 * 获取CMS专题图片
 */
function getCMSSpecialImageUrl($image_name = '') {
	return ShopdaBase::getCMSSpecialImageUrl ( $image_name );
}
/**
 * 获取CMS专题路径
 */
function getCMSSpecialImagePath($image_name = '') {
	return ShopdaBase::getCMSSpecialImagePath ( $image_name );
}

/**
 * 获取CMS首页图片
 */
function getCMSIndexImageUrl($image_name = '') {
	return ShopdaBase::getCMSIndexImageUrl ( $image_name );
}
/**
 * 获取CMS首页图片路径
 */
function getCMSIndexImagePath($image_name = '') {
	return ShopdaBase::getCMSIndexImagePath ( $image_name );
}
/**
 * 获取CMS专题Url
 */
function getCMSSpecialUrl($special_id) {
	return ShopdaBase::getCMSSpecialUrl ( $special_id );
}
/**
 * 获取商城专题Url
 */
function getShopSpecialUrl($special_id) {
	return ShopdaBase::getShopSpecialUrl ( $special_id );
}
/**
 * 获取CMS专题静态文件
 */
function getCMSSpecialHtml($special_id) {
	return ShopdaBase::getCMSSpecialHtml ( $special_id );
}
/**
 * 获取微商城个人秀图片地址
 */
function getMicroshopPersonalImageUrl($personal_info, $type = '') {
	return ShopdaBase::getMicroshopPersonalImageUrl ( $personal_info, $type );
}
function getMicroshopDefaultImage() {
	return ShopdaBase::getMicroshopDefaultImage ();
}
/**
 * 获取开店申请图片
 */
function getStoreJoininImageUrl($image_name = '') {
	return ShopdaBase::getStoreJoininImageUrl ( $image_name );
}

/**
 * 获取开店装修图片地址
 */
function getStoreDecorationImageUrl($image_name = '', $store_id = null) {
	return ShopdaBase::getStoreDecorationImageUrl ( $image_name, $store_id );
}
/**
 * 获取运单图片地址
 */
function getWaybillImageUrl($image_name = '') {
	return ShopdaBase::getWaybillImageUrl ( $image_name );
}
/**
 * 获取运单图片地址
 */
function getMbSpecialImageUrl($image_name = '') {
	return ShopdaBase::getMbSpecialImageUrl ( $image_name );
}

/**
 * 返回模板文件所在完整目录
 *
 * @param str $tplpath        	
 * @return string
 */
function template($tplpath) {
	return ShopdaBase::template ( $tplpath );
}

/**
 * 延时加载分页功能，判断是否有更多连接和limitstart值和经过验证修改的$delay_eachnum值
 *
 * @param int $delay_eachnum
 *        	延时分页每页显示的条数
 * @param int $delay_page
 *        	延时分页当前页数
 * @param int $count
 *        	总记录数
 * @param bool $ispage
 *        	是否在分页模式中实现延时分页(前台显示的两种不同效果)
 * @param int $page_nowpage
 *        	分页当前页数
 * @param int $page_eachnum
 *        	分页每页显示条数
 * @param int $page_limitstart
 *        	分页初始limit值
 * @return array array('hasmore'=>'是否显示更多连接','limitstart'=>'加载的limit开始值','delay_eachnum'=>'经过验证修改的$delay_eachnum值');
 */
function lazypage($delay_eachnum, $delay_page, $count, $ispage = false, $page_nowpage = 1, $page_eachnum = 1, $page_limitstart = 1) {
	return ShopdaBase::lazypage ( $delay_eachnum, $delay_page, $count, $ispage, $page_nowpage, $page_eachnum, $page_limitstart );
}
/**
 * 内容写入文件
 *
 * @param string $filepath
 *        	待写入内容的文件路径
 * @param string/array $data
 *        	待写入的内容
 * @param string $mode
 *        	写入模式，如果是追加，可传入“append”
 * @return bool
 */
function write_file($filepath, $data, $mode = null) {
	return ShopdaBase::write_file ( $filepath, $data, $mode );
}
/**
 * 循环创建目录
 *
 * @param string $dir
 *        	待创建的目录
 * @param $mode 权限        	
 * @return boolean
 */
function mk_dir($dir, $mode = '0777') {
	return ShopdaBase::mk_dir ( $dir, $mode );
}

/**
 * 抛出异常
 *
 * @param string $error
 *        	异常信息
 */
function throw_exception($error) {
	return ShopdaBase::throw_exception ( $error );
}
/**
 * 输出错误信息
 *
 * @param string $error
 *        	错误信息
 */
function halt($error) {
	return ShopdaBase::halt ( $error );
}

/**
 * 返回以原数组某个值为下标的新数据
 *
 * @param array $array        	
 * @param string $key        	
 * @param int $type
 *        	1一维数组2二维数组
 * @return array
 */
function array_under_reset($array, $key, $type = 1) {
	return ShopdaBase::array_under_reset ( (array) $array, $key, $type );
}

/**
 * 加载广告
 *
 * @param $ap_id 广告位ID        	
 * @param $type 广告返回类型
 *        	html,js
 */
function loadadv($ap_id = null, $type = 'html') {
	return ShopdaBase::loadadv ( $ap_id, $type );
}

/**
 * 话题图片绝对路径
 *
 * @param $param string
 *        	文件名称
 * @return string
 */
function themeImagePath($param) {
	return ShopdaBase::themeImagePath ( $param );
}
/**
 * 话题图片url
 *
 * @param $param string        	
 * @return string
 */
function themeImageUrl($param) {
	return ShopdaBase::themeImageUrl ( $param );
}
/**
 * 圈子logo
 *
 * @param $param string
 *        	圈子id
 * @return string
 */
function circleLogo($id) {
	return ShopdaBase::circleLogo ( $id );
}
/**
 * sns 来自
 *
 * @param $param string
 *        	$trace_from
 * @return string
 */
function snsShareFrom($sign) {
	return ShopdaBase::snsShareFrom ( $sign );
}

/**
 * 拼接动态URL
 *
 * 调用示例
 *
 * 若指向网站首页，可以传空:
 * url() => 表示c和a均为index，返回当前站点网址
 *
 * url('search,'index','array('cate_id'=>2)); 实际指向 index.php?c=search&a=index&cate_id=2
 * 传递数组参数时，若c（或a）值为index,则可以省略
 * 上面示例等同于
 * url('search','',array('c'=>'search','cate_id'=>2));
 *
 * @param string $c
 *        	control文件名
 * @param string $a
 *        	a方法名
 * @param array $args
 *        	URL其它参数
 * @param string $site_url
 *        	生成链接的网址，默认取当前网址
 * @return string
 */
function url($c = '', $a = '', $args = array(), $site_url = '') {
	return ShopdaBase::url ( $c, $a, $args, $site_url );
}

/**
 * 商城会员中心使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $c
 *        	c文件名
 * @param string $a
 *        	a方法名
 * @param array $args
 *        	URL其它参数
 * @param string $store_domian
 *        	店铺二级域名
 * @return string
 */
function urlShop($c = '', $a = '', $args = array(), $store_domain = '') {
	return ShopdaBase::urlShop ( $c, $a, $args, $store_domain );
}

/**
 * 商城后台使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $c
 *        	control文件名
 * @param string $a
 *        	a方法名
 * @param array $args
 *        	URL其它参数
 * @return string
 */
function urlAdmin($c = '', $a = '', $args = array()) {
	return ShopdaBase::urlAdmin ( $c, $a, $args );
}
function urlAdminShop($c = '', $a = '', $args = array()) {
	return ShopdaBase::urlAdminShop ( $c, $a, $args );
}
function urlAdminCms($c = '', $a = '', $args = array()) {
	return ShopdaBase::urlAdminCms ( $c, $a, $args );
}
function urlAdminMobile($c = '', $a = '', $args = array()) {
	return ShopdaBase::urlAdminMobile ( $c, $a, $args );
}
function urlAdminCircle($c = '', $a = '', $args = array()) {
	return ShopdaBase::urlAdminCircle ( $c, $a, $args );
}
/**
 * CMS使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $c
 *        	control文件名
 * @param string $a
 *        	a方法名
 * @param array $args
 *        	URL其它参数
 * @return string
 */
function urlCMS($c = '', $a = '', $args = array()) {
	return ShopdaBase::urlCMS ( $c, $a, $args );
}
/**
 * 圈子使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $c
 *        	control文件名
 * @param string $a
 *        	a方法名
 * @param array $args
 *        	URL其它参数
 * @return string
 */
function urlCircle($c = '', $a = '', $args = array()) {
	return ShopdaBase::urlCircle ( $c, $a, $args );
}
/**
 * 微商城使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $c
 *        	control文件名
 * @param string $a
 *        	a方法名
 * @param array $args
 *        	URL其它参数
 * @return string
 */
function urlMicroshop($c = '', $a = '', $args = array()) {
	return ShopdaBase::urlMicroshop ( $c, $a, $args );
}
/**
 * 会员中心使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $c
 *        	control文件名
 * @param string $a
 *        	a方法名
 * @param unknown $args
 *        	URL其它参数
 * @return string
 */
function urlMember($c = '', $a = '', $args = array()) {
	return ShopdaBase::urlMember ( $c, $a, $args );
}
/**
 * 会员登录使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $c
 *        	control文件名
 * @param string $a
 *        	a方法名
 * @param unknown $args
 *        	URL其它参数
 * @return string
 */
function urlLogin($c = '', $a = '', $args = array()) {
	return ShopdaBase::urlLogin ( $c, $a, $args );
}
/**
 * 门店使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $c
 *        	control文件名
 * @param string $a
 *        	a方法名
 * @param unknown $args
 *        	URL其它参数
 * @return string
 */
function urlChain($c = '', $a = '', $args = array()) {
	return ShopdaBase::urlChain ( $c, $a, $args );
}


/**
 * 验证是否为平台店铺 并且绑定了全部商品类目
 *
 * @return boolean
 */
function checkPlatformStoreBindingAllGoodsClass($store_id = 0, $bind_all_gc = 0) {
	return ShopdaBase::checkPlatformStoreBindingAllGoodsClass ( $store_id, $bind_all_gc );
}
/**
 * 将字符部分加密并输出
 *
 * @param unknown $str        	
 * @param unknown $start
 *        	从第几个位置开始加密(从1开始)
 * @param unknown $length
 *        	连续加密多少位
 * @return string
 */
function encryptShow($str, $start, $length) {
	return ShopdaBase::encryptShow ( $str, $start, $length );
}
/**
 * 规范数据返回函数
 *
 * @param unknown $state        	
 * @param unknown $msg        	
 * @param unknown $data        	
 * @return multitype:unknown
 */
function callback($state = true, $msg = '', $data = array()) {
	return ShopdaBase::callback ( $state, $msg, $data );
}
/**
 * flexigrid.js返回的数组
 *
 * @param array $in_array
 *        	需要进行赋值的数据（提供给页面中JS使用）
 * @param array $fields_array
 *        	赋值下标的数组
 * @param array $data
 *        	从数据库读出的未处理数据
 * @param array $format_array
 *        	格式化价格下标的数组
 * @return array 处理后的数据
 */
function getFlexigridArray($in_array, $fields_array, $data, $format_array = array()) {
	return ShopdaBase::getFlexigridArray ( $in_array, $fields_array, $data, $format_array );
}
/**
 * flexigrid.js返回的数组列表
 *
 * @param array $list
 *        	从数据库读出的未处理列表
 * @param array $fields_array
 *        	赋值下标的数组
 * @param array $format_array
 *        	格式化价格下标的数组
 * @return array 处理后的数据
 */
function getFlexigridList($list, $fields_array, $format_array = array()) {
	return ShopdaBase::getFlexigridList ( $list, $fields_array, $format_array );
}
/**
 * 会员标签图片
 *
 * @param unknown $img        	
 * @return string
 */
function getMemberTagimage($img) {
	return ShopdaBase::getMemberTagimage ( $img );
}
/**
 * 门店图片
 *
 * @param string $image        	
 * @param int $store_id        	
 * @return string
 */
function getChainImage($image, $store_id) {
	return ShopdaBase::getChainImage ( $image, $store_id );
}

function scan_dir($dir) {
	return ShopdaBase::scan_dir($dir);
}

function daSafetyCheck() {
	return ShopdaBase::daSafetyCheck();
}

function diffTime($dateTime1, $dateTime2) {
	return ShopdaBase::diffTime($dateTime1, $dateTime2);
}

function getVersionData() {
	return ShopdaBase::getVersionData();
}

function getSiteHbrpUrl() {
	return ShopdaBase::getSiteHbrpUrl();
}

