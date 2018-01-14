<?php
/**
 * 手机短信类
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Common\Lib;

class Sms {
    /**
     * 发送手机短信
     * @param unknown $mobile 手机号
     * @param unknown $content 短信内容
     */
    public function send($mobile,$content) {
       $shopda_sms_type=C('shopda_sms_type');
		if($shopda_sms_type==1)
		{
			return $this->mysend_smsbao($mobile,$content);
		}
		if($shopda_sms_type==2)
		{
			return $this->mysend_yunpian($mobile,$content);
		}
		if($shopda_sms_type==3)
		{
			return $this->mysend_dayu($mobile,$content);
		}
    }
	
/*
	您于{$send_time}绑定手机号，验证码是：{$verify_code}。【{$site_name}】
	0  提交成功
	30：密码错误
	40：账号不存在
	41：余额不足
	42：帐号过期
	43：IP地址限制
	50：内容含有敏感词
	51：手机号码不正确
	http://api.smsbao.com/sms?u=USERNAME&p=PASSWORD&m=PHONE&c=CONTENT
	*/
    private function mysend_smsbao($mobile,$content){
     
	   $user_id = urlencode(C('shopda_sms_zh')); // 这里填写用户名
 	   $pass = urlencode(C('shopda_sms_pw')); // 这里填登陆密码
 	   if(!$mobile || !$content || !$user_id || !$pass) return false;
	   if(is_array($mobile)) $mobile = implode(",",$mobile);
       $mobile=urlencode($mobile);
       //$content=$content."【我的网站】";
       $content=urlencode($content);
	   $pass =md5($pass);//MD5加密
	   $url="http://api.smsbao.com/sms?u=".$user_id."&p=".$pass."&m=".$mobile."&c=".$content."";
 	   $res = file_get_contents($url);
 	   //return $res;
 	   $ok=$res=="0";
 	   if($ok)
 	   {
 	     return true;
 	   }
 	   $GLOBALS['sms_send_res'] = $res;
 	   return false;

    }
    
    /*
     * 阿里大于短信接口
     * 
     * */
    
    private function mysend_dayu($mobile,$content)
    {
    	$tpl_item = $this->getSmsTplByContent($content);
    	$dayu_tpl_id = $tpl_item['dayu_tpl_id'];

    	//TODO::
    	
    	
    }
    
	 /**
	 * http://www.yunpian.com/
     * 发送手机短信
     * @param unknown $mobile 手机号
     * @param unknown $content 短信内容
	  0 	OK 	调用成功，该值为null 	无需处理
	  1 	请求参数缺失 	补充必须传入的参数 	开发者
	  2 	请求参数格式错误 	按提示修改参数值的格式 	开发者
	  3 	账户余额不足 	账户需要充值，请充值后重试 	开发者
	  4 	关键词屏蔽 	关键词屏蔽，修改关键词后重试 	开发者
	  5 	未找到对应id的模板 	模板id不存在或者已经删除 	开发者
	  6 	添加模板失败 	模板有一定的规范，按失败提示修改 	开发者
	  7 	模板不可用 	审核状态的模板和审核未通过的模板不可用 	开发者
	  8 	同一手机号30秒内重复提交相同的内容 	请检查是否同一手机号在30秒内重复提交相同的内容 	开发者
	  9 	同一手机号5分钟内重复提交相同的内容超过3次 	为避免重复发送骚扰用户，同一手机号5分钟内相同内容最多允许发3次 	开发者
	  10 	手机号黑名单过滤 	手机号在黑名单列表中（你可以把不想发送的手机号添加到黑名单列表） 	开发者
	  11 	接口不支持GET方式调用 	接口不支持GET方式调用，请按提示或者文档说明的方法调用，一般为POST 	开发者
	  12 	接口不支持POST方式调用 	接口不支持POST方式调用，请按提示或者文档说明的方法调用，一般为GET 	开发者
	  13 	营销短信暂停发送 	由于运营商管制，营销短信暂时不能发送 	开发者
	  14 	解码失败 	请确认内容编码是否设置正确 	开发者
	  15 	签名不匹配 	短信签名与预设的固定签名不匹配 	开发者
	  16 	签名格式不正确 	短信内容不能包含多个签名【 】符号 	开发者
	  17 	24小时内同一手机号发送次数超过限制 	请检查程序是否有异常或者系统是否被恶意攻击 	开发者
	  -1 	非法的apikey 	apikey不正确或没有授权 	开发者
	  -2 	API没有权限 	用户没有对应的API权限 	开发者
	  -3 	IP没有权限 	访问IP不在白名单之内，可在后台"账户设置->IP白名单设置"里添加该IP 	开发者
	  -4 	访问次数超限 	调整访问频率或者申请更高的调用量 	开发者
	  -5 	访问频率超限 	短期内访问过于频繁，请降低访问频率 	开发者
	  -50 未知异常 	系统出现未知的异常情况 	技术支持
	  -51 系统繁忙 	系统繁忙，请稍后重试 	技术支持
	  -52 充值失败 	充值时系统出错 	技术支持
	  -53 提交短信失败 	提交短信时系统出错 	技术支持
	  -54 记录已存在 	常见于插入键值已存在的记录 	技术支持
	  -55 记录不存在 	没有找到预期中的数据 	技术支持
	  -57 用户开通过固定签名功能，但签名未设置 	联系客服或技术支持设置固定签名 	技术支持
     */
    private function mysend_yunpian($mobile,$content) {
		$yunpian='yunpian';
		$plugin = str_replace('\\', '', str_replace('/', '', str_replace('.', '',$yunpian)));
        if (!empty($plugin)) {
			
            define('PLUGIN_ROOT', BASE_ROOT_PATH . DS .'Api/smsapi');
			
            require_once(PLUGIN_ROOT . DS . $plugin . DS . 'Send.php');
			
            return send_sms($content, $mobile);
        }
		else
		{
			return false;
		}
    }
    /**
     * 亿美短信发送接口
     * @param unknown $mobile 手机号
     * @param unknown $content 短信内容
     */
    private function _sendEmay($mobile,$content) {
        set_time_limit(0);
		
		 
        define('SCRIPT_ROOT',  BASE_ROOT_PATH . DS .'Api/smsapi/emay');
        require_once SCRIPT_ROOT.'include/Client.php';
        /**
         * 网关地址
         */
        $gwUrl = C('sms.gwUrl');
        /**
         * 序列号,请通过亿美销售人员获取
         */
        $serialNumber = C('sms.serialNumber');
        /**
         * 密码,请通过亿美销售人员获取
         */
        $password = C('sms.password');
        /**
         * 登录后所持有的SESSION KEY，即可通过login方法时创建
         */
        $sessionKey = C('sms.sessionKey');
        /**
         * 连接超时时间，单位为秒
         */
        $connectTimeOut = 2;
        /**
         * 远程信息读取超时时间，单位为秒
         */
        $readTimeOut = 10;
        /**
         $proxyhost		可选，代理服务器地址，默认为 false ,则不使用代理服务器
         $proxyport		可选，代理服务器端口，默认为 false
         $proxyusername	可选，代理服务器用户名，默认为 false
         $proxypassword	可选，代理服务器密码，默认为 false
         */
        $proxyhost = false;
        $proxyport = false;
        $proxyusername = false;
        $proxypassword = false;

        $client = new Client($gwUrl,$serialNumber,$password,$sessionKey,$proxyhost,$proxyport,$proxyusername,$proxypassword,$connectTimeOut,$readTimeOut);
        /**
         * 发送向服务端的编码，如果本页面的编码为GBK，请使用GBK
        */
        $client->setOutgoingEncoding("UTF-8");
//         $statusCode = $client->login();
        if ($statusCode!=null && $statusCode=="0") {
        } else {
            //登录失败处理
        //    echo "登录失败,返回:".$statusCode;exit;
        }
        $statusCode = $client->sendSMS(array($mobile),$content);
        if ($statusCode!=null && $statusCode=="0") {
            return true;
        } else {
        	$GLOBALS['sms_send_res'] = $statusCode;
            return false;
//             print_R($statusCode);
//             echo "处理状态码:".$statusCode;
        }
    }
    
    
    /**
     * 根据短信内容获取短信模板配置，用于像大于短信接口
     * 这种，只能根据TPLID发送短信的场景
     * */
    public function getSmsTplByContent($content)
    {
    	$ret = array();
    	$mod = Model("sms_msg_tpl");
    	$tpl_list = $mod->where(array(1 => 1))->select();
    	
    	foreach ($tpl_list as $tpl_item)
    	{
    		$tpl_content = $tpl_item['tpl_content'];
    		preg_match_all("/#(.*?)#/", $tpl_content, $matches);
			$list = $matches[0];
			
			$search_arr = array();
			$replace_arr = array();
			foreach ($list as $item)
			{
				$search_arr[] = $item;
				$replace_arr[] = "(.*?)";
			}
			
			$tpl_content = str_replace($search_arr, $replace_arr, $tpl_content);
			
			$tpl_item['tpl_content'] = "/" . $tpl_content . "/";
			
			if (preg_match($tpl_item['tpl_content'] , $content))
			{
				$ret = $tpl_item;
				break;
			}
		}
		return $ret;
    }
}
