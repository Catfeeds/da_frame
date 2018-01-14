<?php
defined('IN_SHOPDA') || exit;
/*
 * 配置文件
 */
$options = array();
$options['apikey'] = C('shopda_sms_key'); //apikey
$options['signature'] =  C('shopda_sms_signature'); //签名
return $options;
?>