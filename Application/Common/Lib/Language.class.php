<?php
/**
 * 语言调用类
 *
 * 语言调用类，为静态使用
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Common\Lib;

final class Language {
    private static $language_content = array();

    /**
     * 得到数组变量的GBK编码
     *（因系统不有GBK版本，为兼容之前程序原样返回）
     * @param array $key 数组
     * @return array 数组类型的返回结果
     */
    public static function getGBK($key){
        return $key;
    }
    /**
     * 得到数组变量的UTF-8编码
     *（因系统不有GBK版本，为兼容之前程序原样返回）
     * @param array $key GBK编码数组
     * @return array 数组类型的返回结果
     */
    public static function getUTF8($key){
        return $key;
    }
    /**
     * 取指定下标的数组内容
     *
     * @param string $key 数组下标
     * @return string 字符串形式的返回结果
     */
    public static function get($key,$charset = ''){
        $result = self::$language_content[$key] ? self::$language_content[$key] : '';
        return $result;
    }
    /**
     * 设置指定下标的数组内容
     *
     * @param string $key 数组下标
     * @param string $value 值
     * @return bool 字符串形式的返回结果
     */
    public static function set($key,$value){
        self::$language_content[$key] = $value;
        return true;
    }
    /**
     * 通过语言包文件设置语言内容
     *
     * @param string $file 语言包文件，可以按照逗号(,)分隔
     * @return bool 布尔类型的返回结果
     */
    public static function read($file, $moduleName = ''){
    	if (empty($moduleName)) 
    	{
    		$moduleName = MODULE_NAME;
    	}
        $file = str_replace('，',',',$file);
        $tmp = explode(',',$file);
        foreach ($tmp as $v){
            $tmp_file = BASE_ROOT_PATH . DS . trim(APP_PATH, '.') . $moduleName . DS . 'Lang' . DS . LANG_TYPE . DS . $v . '.php';
            if (file_exists($tmp_file)){
                require($tmp_file);
                //var_dump($ret);
                if (!empty($lang) && is_array($lang)){
                    self::$language_content = array_merge(self::$language_content,$lang);
                }
                unset($lang);
            }
        }
        L(self::$language_content);
        return true;
    }
    /**
     * 取语言包全部内容
     *
     * @return array 数组类型的返回结果
     */
    public static function getLangContent($charset = ''){
        $result = self::$language_content;
        return $result;
    }

	public static function appendLanguage($lang){
		if (!empty($lang) && is_array($lang)){
			self::$language_content = array_merge(self::$language_content,$lang);
		}
	}
}