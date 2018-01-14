<?php
namespace Shop\Controller;
use Think\Controller;
use Common\Lib\JavascriptPacker;
class FunnyGamesToolController extends Controller
{
	public function fg_js()
	{
		$referer_url = $_SERVER['HTTP_REFERER'];
		$parse_arr = da_parse_url($referer_url);

		$game_key = $parse_arr['query']['game_key'];
		$store_id = isset($parse_arr['query']['store_id']) ? $parse_arr['query']['store_id'] : '';

		if (empty($game_key))
		{
			$game_key = $this->getParam("game_key");
		}
		if (empty($store_id))
		{
			$store_id = $this->getParam("store_id");
		}

		if (empty($game_key))
		{
			echo "alert('参数错误');";
			exit;
		}

		$mod = Model("funny_games");
		$game_info = $mod->getGameByKey($game_key, $store_id);
 
		
		//读取config
		$config_str = "";
		
		$default_config_str = serialize($game_info['config']);
		$own_config_str = serialize($game_info['own_config']);

		$config_list = Model("funny_games")->getConfigList($default_config_str, $own_config_str);
		
// 		var_dump($config_list);
// 		exit;
		
		$config_js = $this->build_conf_into_js($config_list);
		
		$shopda_fg_base_url = FG_BASE_URL;
	
		$js_tpl = file_get_contents(FG_BASE_PATH . "/config/common/resource/js/shopda.js");
		$fg_game_template_str = file_get_contents(FG_BASE_PATH . "/config/common/resource/template/index.html");
		$fg_game_template_str = str_replace(array("\n", "\r"), array("", ""), $fg_game_template_str);
		$fg_game_float_ad_close_bg = FG_BASE_URL . "/config/common/resource/image/close.png";
 
		$js_tpl = str_replace(array("##shopda_fg_base_url##", 
				"##shopda_fg_config_js##", 
				"##shopda_fg_game_template_str##",
				"##shopda_fg_game_float_ad_close_bg##",//浮动广告关闭按钮
				), 
				array($shopda_fg_base_url, 
						$config_js, 
						$fg_game_template_str,
						$fg_game_float_ad_close_bg,
				), $js_tpl);

// 		if (!C("APP_DEBUG"))
// 		{
// 			$packer = new JavaScriptPacker($js_tpl);
// 			$js_tpl = $packer->pack();
// 		}

		header ('Content-type: application/x-javascript;charset=utf-8');
		header ('Cache-Control: no-cache');
		echo $js_tpl;
		exit;
	}
	
	/**
	 * 组装输出js
	 * */
	public function build_conf_into_js($conf_arr)
	{
		$ret = "";
		if (empty($conf_arr))
		{
			return $ret;
		}
		foreach ($conf_arr as $item)
		{
			$json_str = json_encode($item);
			$ret .= "\n      " . $item['key'] . " = shopdaKit.parseJSON('" .  $json_str . "');\n";
		}
	
		return $ret;
	}

}