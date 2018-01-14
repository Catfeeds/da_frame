<?php

/**
 * 我知图
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Common\Lib;

class Wozhitu {

	private $AppKey; // 开发者平台分配的AppKey
	private $Saveimg = 0; // 0 正式 1 测试
	
	/**
	 * 参数初始化
	 * @param $AppKey
	 * @param $AppSecret
	 * @param $RequestType [选择php请求方式，fsockopen或curl,若为curl方式，请检查php配置是否开启]
	 **/
	public function __construct($AppKey, $Saveimg) {
		$this->AppKey = $AppKey;
		$this->Saveimg = $Saveimg;
	}
	
	/**
	 * 添加图片接口
	 * @param $data 数据
	 * @return array
	 **/
	public function additem($data) {
		$url = 'http://112.16.170.183:8084/vsearchtech/api/v1.0/apisim_additem';
		$data ['apikey'] = $this->AppKey;
		if ($this->Saveimg == 1) {
			$url .= '?saveimg=1';
		}
		$result = $this->postDataCurl ( $url, $data );
		return $result;
	}
	
	/*
	 * 搜索图片接口
	 * @param $data
	 */
	public function search($data) {
		$url = 'http://112.16.170.183:8084/vsearchtech/api/v1.0/apisim_search';
		$data ['apikey'] = $this->AppKey;
		$result = $this->postDataCurl ( $url, $data );
		return $result;
	}
	
	/*
	 * 删除图片接口
	 * @param $data
	 */
	public function deleteitem($data) {
		$url = 'http://112.16.170.183:8084/vsearchtech/api/v1.0/apisim_deleteitem';
		$data ['apikey'] = $this->AppKey;
		$result = $this->postDataCurl ( $url, $data );
		return $result;
	}
	
	/*
	 * 查询图片接口
	 * @param $data
	 */
	public function detail($data) {
		$url = 'http://112.16.170.183:8084/vsearchtech/api/v1.0/apisim_detail';
		$data ['apikey'] = $this->AppKey;
		$result = $this->postDataCurl ( $url, $data );
		return $result;
	}
	
	/*
	 * 浏览搜索图片接口
	 * @param $data
	 */
	public function browse($data) {
		$url = 'http://112.16.170.183:8084/vsearchtech/api/v1.0/apipnp_browse';
		$data ['apikey'] = $this->AppKey;
		$result = $this->postDataCurl ( $url, $data );
		return $result;
	}
	
	/*
	 * 接口错误码
	 * @param $string 错误码
	 */
	public function error_code($string) {
		switch ($string) {
			/* 限制类错误 */
			case 'API_CODE_ERROR_exceeded_access_total' :
				$msg = '用户总调用量超限';
				break;
			case 'API_CODE_ERROR_exceeded_access_frequency' :
				$msg = '用户调用频度超限';
				break;
			case 'API_CODE_ERROR_exceeded_access_frequency_min' :
				$msg = '服务每分钟调用量超限';
				break;
			case 'API_CODE_ERROR_exceeded_access_frequency_hr' :
				$msg = '服务每小时调用量超限';
				break;
			case 'API_CODE_ERROR_exceeded_access_frequency_day' :
				$msg = '用户日调用量超限';
				break;
			case 'API_CODE_ERROR_exceeded_access_frequency_month' :
				$msg = '服务每月调用量超限';
				break;
			case 'API_CODE_ERROR_two_API_calls_time_too_close' :
				$msg = '两次调用时间太近';
				break;
			case 'IMAGE_INDEX_CODE_ERROR_IMAGE_EXCEED_MAX_DOCS_PER_UID' :
				$msg = '用户添加图像量超限';
				break;
			case 'API_CODE_ERROR_not_allow_write_index' :
				$msg = '用户添加图像功能暂停(服务器很可能在维护中)';
				break;
				/* 调用方法错误 */
			case 'API_CODE_ERROR_invalid_apikey' :
				$msg = 'Apikey错误';
				break;
			case 'IMAGE_INDEX_ITEM_CODE_ERROR_INVALID_imgName' :
				$msg = '图片名错误';
				break;
			case 'IMAGE_INDEX_ITEM_CODE_ERROR_INVALID_imgUrl' :
				$msg = '图片URL错误';
				break;
			case 'IMAGE_INDEX_ITEM_CODE_ERROR_INVALID_catid_must_be_1_to_3000' :
				$msg = '图片类别错误(应是1-3000整数)';
				break;
			case 'IMAGE_INDEX_ITEM_CODE_ERROR_INVALID_labels_must_be_positive_integer' :
				$msg = '标签错误(应是正整数)';
				break;
			case 'IMAGE_INDEX_ITEM_CODE_ERROR_INVALID_FIELDS_any_field_must_not_have_double_quote' :
				$msg = '图片数据错误, 不应有双引号';
				break;
			case 'IMAGE_INDEX_ITEM_CODE_ERROR_INVALID_LANG' :
				$msg = '语言参数错误(中文zh 或英语en)';
				break;
			case 'IMAGE_INDEX_CODE_ERROR_DOWNLOAD_IMAGE' :
				$msg = '下载图片错误';
				break;
			case 'IMAGE_INDEX_CODE_ERROR_IMAGE_SIZE_TOO_BIG' :
				$msg = '图片太大(最好在400 – 800PIX JPG 图片)';
				break;
			case 'IMAGE_INDEX_DELETE_IMG_CODE_ERROR_NOT_FOUND_ITEM' :
				$msg = '删除图片错误, 没找到图片';
				break;
		}
		return $msg;
	}
	
	/**
	 * 将json字符串转化成php数组
	 * @param $json_str
	 * @return $json_arr
	 **/
	public function json_to_array($json_str) {
		if (is_array ( $json_str ) || is_object ( $json_str )) {
			$json_str = $json_str;
		} else if (is_null ( json_decode ( $json_str ) )) {
			$json_str = $json_str;
		} else {
			$json_str = strval ( $json_str );
			$json_str = json_decode ( $json_str, true );
		}
		$json_arr = array ();
		foreach ( $json_str as $k => $w ) {
			if (is_object ( $w )) {
				$json_arr [$k] = $this->json_to_array ( $w ); // 判断类型是不是object
			} else if (is_array ( $w )) {
				$json_arr [$k] = $this->json_to_array ( $w );
			} else {
				$json_arr [$k] = $w;
			}
		}
		return $json_arr;
	}
	
	/**
	 * 使用CURL方式发送post请求
	 * @param $url [请求地址]
	 * @param $data [array格式数据]
	 * @return $请求返回结果(array)
	 */
	public function postDataCurl($url, $data) {
		$timeout = 5000;
		$http_header = array (
				'Content-Type:application/x-www-form-urlencoded;charset=utf-8'
		);
		$postdataArray = array ();
		foreach ( $data as $key => $value ) {
			array_push ( $postdataArray, $key . '=' . urlencode ( $value ) );
		}
		$postdata = join ( '&', $postdataArray );
	
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postdata );
		curl_setopt ( $ch, CURLOPT_HEADER, false );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $http_header );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false ); // 处理http证书问题
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	
		$result = curl_exec ( $ch );
		if (false === $result) {
			$result = curl_errno ( $ch );
		}
		curl_close ( $ch );
		return $this->json_to_array ( $result );
	}
}