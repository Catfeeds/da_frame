<?php
/**
 * 趣味游戏
*
* @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
* @license    http://www.shopda.cn
* @link       交流群号：387110194
* @since      大商城荣誉出品
*/


namespace Shop\Controller;
use Shop\Controller\BaseController;
use Common\Lib\Language;
use Common\Lib\QueueClient;
use Common\Lib\Cache;
use Common\Lib\Db;
use Common\Lib\Email;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;
use Common\Lib\Process;
use Common\Model\funny_gamesModel;

class FunnyGamesController extends BaseSellerController {
	public function __construct(){
		parent::__construct();
		Language::read ('funny_games');
		
	}
	
	public function index() {
		$this->game_lists();
	}
	
	/**
	 * 游戏列表
	 * */
	public function game_lists()
	{
		$mod = Model("funny_games");
		
		$store_id = $_SESSION['store_id'];
		$where = array();
 
		if (trim($_GET['keyword']) != '') {
			$where['game_name'] = array('like', '%' . trim($_GET['keyword']) . '%');
		}
		
		if (!empty($_GET['game_class']))
		{
			$where['game_class_id'] = $_GET['game_class'];
		}
		
		//游戏list
		$game_hash = array();
		$game_list = $mod->getGameList($where);
 
		foreach ($game_list as $game_item)
		{
			$game_hash[$game_item['game_key']]['config'] = unserialize($game_hash[$game_item['game_key']]['config']);
			$game_hash[$game_item['game_key']] = $game_item;
		}
		$game_key_list = array_keys($game_hash);
 
		//批量分类HASH
		$class_hash = array();
		$class_mod = Model("funny_games_class");
		$class_list = $class_mod->where(array(1=>1))->select();
		foreach ($class_list as $class_item)
		{
			$class_hash[$class_item['id']] = $class_item;
		}
		
		//批量权限hash
		$privilege_hash = (array) $this->batch_check_privilege($store_id, $game_key_list);

		//批量个性化配置hash
		$own_game_config_hash = (array) $this->batch_get_config($store_id, $game_key_list);
		
		//批量申请数据
		$apply_hash = (array) $this->batch_get_apply_info($store_id, $game_key_list);
 
 
		//conbine数据
		foreach ($game_hash as $game_key => $game_item)
		{
			$game_hash[$game_key]['game_class'] = $class_hash[$game_item['game_class_id']];
			
			if (isset($privilege_hash[$game_key]) && ($privilege_hash[$game_key] == true))
			{
				$game_hash[$game_key]['game_privilege'] = true;
			}
			else 
			{
				$game_hash[$game_key]['game_privilege'] = false;
			}
			
			if (isset($own_game_config_hash[$game_key]))
			{
				$game_hash[$game_key]['own_config'] =  $own_game_config_hash[$game_key]['config'];
			}
			else 
			{
				$game_hash[$game_key]['own_config'] =  array();
			}
			
			$game_hash[$game_key]['game_icon'] = $mod->getGameIcon($game_key);
			
			//申请信息
			if (isset($apply_hash[$game_key]))
			{
				$game_hash[$game_key]['apply_info'] =  $apply_hash[$game_key];
			}
			else
			{
				$game_hash[$game_key]['apply_info'] =  array();
			}
		}

		$this->assign('show_page', $mod->showpage());
		
		$game_list = array_values($game_hash);
		$this->assign('game_list', $game_list);

		$this->assign("games_class_list", $class_list);

		//var_dump($game_list);
		
		$this->profile_menu('game_lists', 'game_lists');
        $this->render('funny_games.game_list');
	}
	
	/**
	 * 游戏权限申请
	 * */
	public function game_apply()
	{
		$store_info = $this->store_info;
		$store_id = $store_info['store_id'];
		$game_key = $_GET['game_key'];

		$check_privilege = $this->check_privilege($store_id, $game_key);

		if ($check_privilege)
		{
			showDialog("您已经申请过权限了", 'reload');
		}
		
		$mod = Model("funny_games_apply");
		$game_mod = Model("funny_games");
		$cond = array("game_key" => $game_key, "store_id" => $store_id);
		$apply_arr = $mod->table("funny_games_apply")->where($cond)->find();
 
		if (empty($apply_arr))
		{
			$apply_arr = array(
					"game_key" => $game_key,
					"store_id" => $store_id,
					"create_time" => CUR_TIME,
					"update_time" => CUR_TIME,
					"is_open" => 0,
					"ver" => 1,
					"check_status" => funny_gamesModel::CHECK_STATUS_WAIT,
					"check_status_desc"  => $game_mod->getCheckStatusDesc(funny_gamesModel::CHECK_STATUS_WAIT),
			);

			$ret = $mod->table("funny_games_apply")->insert($apply_arr);
		}
		else
		{
			$apply_arr["check_status"] = funny_gamesModel::CHECK_STATUS_WAIT;
			$apply_arr["check_status_desc"]  = $game_mod->getCheckStatusDesc(funny_gamesModel::CHECK_STATUS_WAIT);
			
			$apply_arr["ver"] = $apply_arr['ver'] + 1;
			$apply_arr["update_time"] = CUR_TIME;
			
			$mod->table("funny_games_apply")->where($cond)->update($apply_arr);
		}
 
		showDialog("提交成功，等待审核", 'reload', 'succ');
	}
	
	/**
	 * 游戏预览
	 * */
	public function game_preview()
	{
		$game_key = $_GET['game_key'];
		$store_id = $_SESSION['store_id'];
		
		if (empty($game_key) || empty($store_id))
		{
			showMessage("参数错误");
		}
		
		$mod = Model("funny_games");
		$game_arr = $mod->getGameByKey($game_key);
		
		$game_name = $game_arr['game_name'];
		$game_url =  $mod->getGameUrl($game_key, $store_id);
		
		$this->assign("game_url", $game_url);
		$this->assign("game_name", $game_name);
		$this->profile_menu('game_preview', 'game_preview', $game_name);
		$this->render("funny_games.game_preview");
	}
	
	/**
	 * 游戏配置
	 * */
	public function game_config()
	{
		$store_info = $this->store_info;
		$store_id = $store_info['store_id'];
		$game_key = $this->getParam('game_key');
	
		$game_arr = Model("funny_games")->getGameByKey($game_key, $store_id);
		$this->assign("game_info", $game_arr);
 
// 		var_dump($game_key, $store_id, $game_arr);
// 		exit;
		
		$default_config = $game_arr['config'];
		$default_config = array_values($default_config);
		
		$own_config = isset($game_arr['own_config']) ? $game_arr['own_config'] : array();
		$own_config = array_values($own_config);
 
		$this->assign("default_config", $default_config);
		$this->assign("own_config", $own_config);
		
		//提交表单标识
		$formFieldStr = Model("funny_games")->getConfFormKeyStr($default_config);
		$this->assign("shopda_form_fields_str", $formFieldStr);
		
		//获取已有配置
		$default_config_str = serialize($default_config);
		$own_config_str = serialize($own_config);
		$current_conf_list = Model("funny_games")->getConfigList($default_config_str, $own_config_str);

		$current_conf_list_str =  Model("funny_games")->getConfigListFormStr($current_conf_list);
// 		var_dump($current_conf_list_str);
// 		exit;
		
		$this->assign("current_conf_list_str", $current_conf_list_str);
		
		if (!$game_arr['check_privilege'])
		{
			showDialog("没有权限");
		}
		
		$this->assign("game_arr", $game_arr);

		if (chksubmit())
		{
			$config = '';
			$config_array = explode(',',$_POST["form_fields_str"]);//配置参数
			if(is_array($config_array) && (!empty($config_array))) {

				$config_info_arr = Model("funny_games")->getConfFormData($default_config);

// 				var_dump($config_info_arr);
// 				exit;
				
				$game_config = serialize($config_info_arr);
	 
				$config_mod = Model("funny_games_config");
				$conf = $config_mod->table("funny_games_config")->where(array("store_id" => $store_id, "game_key" => $game_key))->find();
				if (empty($conf))
				{
					$conf = array("store_id" => $store_id, "config" => $game_config, "game_key" => $game_key, "create_time" => CUR_TIME);
					$config_mod->table("funny_games_config")->insert($conf);
				}
				else 
				{
					$conf['update_time'] = CUR_TIME;
					$conf['config'] = $game_config;
					$config_mod->table("funny_games_config")->where(array("store_id" => $store_id, "game_key" => $game_key))->update($conf);
				}
				
				showDialog("更新成功", urlShop("FunnyGames", "game_lists"), 'succ');
			}			
		}
		
		$this->profile_menu("game_config", "game_config", $game_arr['game_name']);
		$this->render("funny_games.game_config");
	}
	
	//批量获取配置
	private function batch_get_config($store_id, $game_key_list)
	{
		$ret = Model("funny_games")->batchGetConfig($store_id, $game_key_list);
		return $ret;
	}
	
	//批量获取申请信息
	private function batch_get_apply_info($store_id, $game_key_list)
	{
		$ret = Model("funny_games")->batchGetApplyInfo($store_id, $game_key_list);
		return $ret;
	}

	//批量检查游戏权限
	private function batch_check_privilege($store_id, $game_key_list)
	{
		$ret = Model("funny_games")->batchCheckPrivilege($store_id, $game_key_list);
		return $ret;
	}
	
	//检查游戏权限
	private function check_privilege($store_id, $game_key)
	{
		$ret = Model("funny_games")->checkGamePrivilege($store_id, $game_key);
		return $ret;
	}

	/**
	 * 用户中心右边，小导航
	 *
	 * @param string    $menu_type  导航类型
	 * @param string    $menu_key   当前导航的menu_key
	 * @return
	 */
	private function profile_menu($menu_type,$menu_key='', $menu_extend_str = '') {
		$menu_array = array();
		switch ($menu_type) {
			
			case "game_preview":
				$menu_array = array(
						1=>array('menu_key'=>'game_preview', 'menu_name'=>'游戏预览-' . $menu_extend_str, 'menu_url'=>"#")
				);
				break;
			
			case 'game_lists':
				$menu_array = array(
				1=>array('menu_key'=>'game_lists', 'menu_name'=>'游戏列表', 'menu_url'=>urlShop('FunnyGames', 'game_lists')),
				);
				break;
				
			case 'game_config':
				$menu_array = array(
				1=>array('menu_key'=>'game_config', 'menu_name'=>'游戏配置-' . $menu_extend_str, 'menu_url'=>urlShop('FunnyGames', 'game_config')),
				);
				break;
		}
		$this->assign('member_menu',$menu_array);
		$this->assign('menu_key',$menu_key);
	}
}