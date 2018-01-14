<?php
/**
 * 趣味游戏
*
* @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
* @license    http://www.shopda.cn
* @link       交流群号：387110194
* @since      大商城荣誉出品
*/


namespace Mobile\Controller;
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
use Home\Controller\SystemController;

class FunnyGamesController extends SystemController {
	public function __construct(){
		parent::__construct();
		$this->links = array(
				array('url'=>$GLOBALS['_PAGE_URL'] . '&c=FunnyGames&a=game_lists','text'=>'游戏列表'),
				array('url'=>$GLOBALS['_PAGE_URL'] . '&c=FunnyGames&a=game_apply_lists','text'=>'游戏申请列表'),
				//array('url'=>$GLOBALS['_PAGE_URL'] . '&c=FunnyGames&a=game_config','text'=>'游戏配置'),
				
		);
		
		$this->setDirquna('mobile');
	}
	
	public function index() {
		$this->game_lists();
	}
	
	/**
	 * 游戏列表
	 * */
	public function game_lists()
	{
		//输出子菜单
		$this->assign('top_link',$this->sublink($this->links,'game_lists'));
        $this->render('funny_games.game_list');
	}
	
	/**
	 * 游戏权限申请列表
	 * */
	public function game_apply_lists()
	{
		$this->assign('top_link',$this->sublink($this->links,'game_apply_lists'));
		$this->render('funny_games.game_apply_list');
	}

	/**
	 * 游戏预览
	 * */
	public function game_preview()
	{
		$game_key = $_GET['game_key'];

		if (empty($game_key))
		{
			showMessage("参数错误");
		}
		
		$mod = Model("funny_games");
		$game_arr = $mod->adminGetGameByKey($game_key);
		
		$game_name = $game_arr['game_name'];
		$game_url =  $mod->getGameUrl($game_key);
		
		$this->assign("game_arr", $game_arr);
		$this->assign("game_url", $game_url);
		$this->assign("game_name", $game_name);
		$this->render("funny_games.game_preview");
	}
	
	/**
	 * 游戏配置
	 * */
	public function game_config()
	{
		$game_key = $_GET['game_key'];
	
		$game_arr = Model("funny_games")->adminGetGameByKey($game_key);
		$this->assign("game_arr", $game_arr);
		$this->render("funny_games.game_config");
	}
 

	/**
	 * 申请审核
	 * */
	public function check_apply()
	{
		$apply_id = $_GET['apply_id'];
		$apply_item = Model("funny_games_apply")->getApplyDetailForCheck($apply_id);
 		
// 		var_dump($this->admin_info);
// 		exit;
		
		if (chksubmit()){

			$reason = $_POST['check_reason'];
			$result = $_POST['check_result'];
			
 
			
			$cond = array("id" => $apply_id);
 
			if ($result == 1)
			{
				$arr = array("is_open" => 1,
						"check_status" => funny_gamesModel::CHECK_STATUS_SUCCESS,
						"check_status_desc" => Model("funny_games")->getCheckStatusDesc(funny_gamesModel::CHECK_STATUS_SUCCESS),
						"update_time" => CUR_TIME,
						"check_time" => CUR_TIME,
						"admin_id" => $this->admin_info['id'],
						"check_reason" => $reason,
				);
				$res = Model("funny_games_apply")->table("funny_games_apply")->where($cond)->update($arr);
			}
			else
			{
				$arr = array("is_open" => 0,
						"check_status" => funny_gamesModel::CHECK_STATUS_FAIL,
						"check_status_desc" => Model("funny_games")->getCheckStatusDesc(funny_gamesModel::CHECK_STATUS_FAIL),
						"update_time" => CUR_TIME,
						"check_time" => CUR_TIME,
						"admin_id" => $this->admin_info['id'],
						"check_reason" => $reason,
				);
				$res =  Model("funny_games_apply")->table("funny_games_apply")->where($cond)->update($arr);
			}
			
// 			var_dump($cond, $arr, $this->admin_info['id'], $res);
// 			exit;
			
			showDialog("审核成功!", $GLOBALS['_PAGE_URL'] . '&c=FunnyGames&a=game_apply_lists', "succ");
		}
		
		$this->assign("apply_detail", $apply_item);
		$this->render("funny_games.game_apply_check");
	}
	
	/**
	 * 删除申请
	 * */
	public function del_apply()
	{
		$apply_id = $_GET['apply_id'];
		$mod = Model("funny_games_apply");
		$cond = array("id" => $apply_id);
		$mod->where($cond)->delete();
	
		showDialog("删除成功", "reload", "succ");
	}


	/**
	 * 输出XML数据
	 */
	const LIST_TYPE_GAMES = 1;
	const LIST_TYPE_APPLY = 2;
	
	public function get_xml() {
		$list_type = $_GET['list_type'];

		if ($list_type == self::LIST_TYPE_GAMES)
		{
			$ret = $this->get_game_list_xml();
		}
		else if ($list_type == self::LIST_TYPE_APPLY)
		{
			$ret = $this->get_game_apply_list_xml();
		}
		echo $this->flexigridXML($ret);exit();
	}
	
	//游戏列表
	private function get_game_list_xml()
	{
		$game_model = Model('funny_games');
		$game_class_list = $game_model->getGameClassList();

		$game_class_hash = $game_model->transGameClassList2Hash($game_class_list);

		$condition = array();
		if ($_POST['query'] != '') {
			$condition[$_POST['qtype']] = array('like', '%' . $_POST['query'] . '%');
		}
		$order = '';
		$param = array('id','game_key','game_name','game_desc','game_class_id',);
		if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
			$order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
		}
		$page = $_POST['rp'];
 
		$game_list = $game_model->adminGetGameList($condition, '*', $page, $order);
 
		$data = array();
		$data['now_page'] = $game_model->shownowpage();
		$data['total_num'] = $game_model->gettotalnum();

		foreach ($game_list as $value) {
			$param = array();
            $param['operation'] = "<a style='display:none;' class='btn blue' href='{$GLOBALS['_PAGE_URL']}&c=FunnyGames&a=game_config&game_key=" . $value['game_key'] . "'><i class='fa fa-pencil-square-o'></i>配置</a>" .
			"<a href='javascript:game_preview (\"" . $value['game_key'] ."\" );' class='btn red'><i class='fa fa-eye'></i>游戏预览</a>";
			$param['game_key'] = $value['game_key'];
			$param['game_name'] = $value['game_name'];
			$param['game_desc'] =   $value['game_desc'];
			//$param['game_class_id'] = $value['game_class_id'];
			$param['game_class_name'] = $game_model->getGameClassName($value['game_class_id'], $game_class_hash);
			$data['list'][$value['game_key']] = $param;
		}

		return $data;
	}
	
	//申请列表
	private function get_game_apply_list_xml()
	{
		$game_model = Model("funny_games");
		$game_apply_model = Model('funny_games_apply');
		
		$game_class_list = $game_model->getGameClassList();
		$game_class_hash = $game_model->transGameClassList2Hash($game_class_list);
		
		$condition = array();

		$order = '';
		$param = array('id','game_key','store_id','is_open','check_reason', 'check_status', 'check_status_desc', 'admin_id');
		if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
			$order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
		}
		$page = $_POST['rp'];
		$game_apply_list = $game_apply_model->getApplyList($condition, '*', $page, $order);
		
		$game_key_list = array();
		foreach ($game_apply_list as $item)
		{
			$game_key_list[] = $item['game_key'];
		}
		$game_list = Model("funny_games")->batchGetGameList($game_key_list);
		$game_list_hash = Model("funny_games")->transGameList2Hash($game_list);
		
// 		var_dump($game_key_list, $game_list, $game_list_hash);
// 		exit;
		
		$store_id_list = array();
		$admin_id_list = array();
		foreach ($game_apply_list as $item)
		{
			$store_id_list[] = $item['store_id'];
			$admin_id_list[] = $item['admin_id'];
		}
		
		$store_hash = $game_apply_model->batchGetStoreInfoForHash($store_id_list);
		$admin_hash = $game_apply_model->batchGetAdminInfoForHash($admin_id_list);
		
// 		var_dump($admin_hash, $admin_id_list);
// 		exit;
		
		$data = array();
		$data['now_page'] = $game_model->shownowpage();
		$data['total_num'] = $game_model->gettotalnum();
		foreach ($game_apply_list as $value) {
			$param = array();
			$param['operation'] = "<a class='btn blue' href='{$GLOBALS['_PAGE_URL']}&c=FunnyGames&a=check_apply&apply_id=" . $value['id'] . "'>" .
			"<i class='fa fa-pencil-square-o'></i>审批</a>" . 
			
			"<a  href='javascript:submit_delete(". $value['id'] .");' class='btn red'><i class='fa fa-trash-o'></i>删除</a>";
			
			$param['id'] = $value['id'];
			$param['game_key'] = $value['game_key'];
			$param['game_name'] = $game_list_hash[$value['game_key']]['game_name'];
			
			$game_icon = $game_list_hash[$value['game_key']]['game_icon'];
			
			$param['game_icon'] = "<a href='javascript:void(0);' class='pic-thumb-tip' onMouseOut='toolTip()' onMouseOver='toolTip(\"<img src={$game_icon}>\")'><i class='fa fa-picture-o'></i></a>";
				
			$param['game_desc'] = $game_list_hash[$value['game_key']]['game_desc'];
			
			if ($value['check_status'] == funny_gamesModel::CHECK_STATUS_SUCCESS)
			{
				$value['check_status_desc'] = "<font color='green'>" . $value['check_status_desc'] . "</font>";
			}
			else if ($value['check_status'] == funny_gamesModel::CHECK_STATUS_FAIL)
			{
				$value['check_status_desc'] = "<font color='red'>" . $value['check_status_desc'] . "</font>";
			}
			$param['check_status_desc'] = $value['check_status_desc'];
			
			$param['store_id'] = $value['store_id'];
			$param['store_name'] = $store_hash[$value['store_id']]['store_name'];
			
			$param['open_info'] = ($value['is_open'] == 1) ? "开启" : "关闭";
			$param['game_class_name'] = $game_model->getGameClassName($value['game_class_id'], $game_class_hash);
			$param['admin_name'] = $admin_hash[$value['admin_id']]['admin_name'];

			$param['check_reason'] = $value['check_reason'];
			
			$data['list'][$value['id']] = $param;
		}
		return $data;
	}

}