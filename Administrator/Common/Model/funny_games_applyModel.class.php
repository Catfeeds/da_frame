<?php
/**
 * album_class
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Common\Model;
use Common\Lib\Language;
use Common\Lib\Model;

class funny_games_applyModel extends Model {
	public function __construct() {
		parent::__construct('funny_games_apply');
	}
	/**
	 * 游戏申请列表
	 *
	 * @param array $condition 条件
	 * @param array $field 字段
	 * @param string $page 分页
	 * @param string $order 排序
	 * @return array
	 */
	public function getApplyList($condition, $field = '*', $page = 10, $order = "id desc",  $limit = '') {
		$ret = $this->field($field)->where($condition)->order($order)->limit($limit)->page($page)->select();
		return $ret;
	}
	
	//批量获取店铺信息
	public function batchGetStoreInfoForHash($store_id_list)
	{
		$ret = array();
		if (empty($store_id_list))
		{
			return $ret;
		}
		
		$cond = array( "store_id" => array("exp" , $this->buildInStr("store_id", $store_id_list)) );
		$list = $this->table("store")->where($cond)->select();
		
		foreach ($list as $item)
		{
			$ret[$item['store_id']] = $item;
		}
		return $ret;
	}
	
	//批量获取管理员信息
	public function batchGetAdminInfoForHash($admin_id_list)
	{
		$ret = array();
		if (empty($admin_id_list))
		{
			return $ret;
		}
		
		$cond = array( "admin_id" => array("exp" , $this->buildInStr("admin_id", $admin_id_list)) );
		$list = $this->table("admin")->where($cond)->select();
		
		foreach ($list as $item)
		{
			$ret[$item['admin_id']] = $item;
		}
		return $ret;
	}
	
	//获取审批详情
	public function getApplyDetailForCheck($apply_id)
	{
		$ret = array();
		if (empty($apply_id))
		{
			return $ret;
		}
		
		$apply_item = $this->table("funny_games_apply")->where(array("id" => $apply_id))->find();
		if (empty($apply_item))
		{
			return $ret;
		}
		
		$store_id = $apply_item['store_id'];
		$game_key = $apply_item['game_key'];
		$admin_id = $apply_item['admin_id'];
		
		$store = $this->table("store")->where(array("store_id" => $store_id))->find();
		$game = $this->table("funny_games")->where(array("game_key" => $game_key))->find();
		$admin = $this->table("admin")->where(array("admin_id" => $admin_id))->find();

		$ret = $apply_item;
		$ret['game_name'] = $game['game_name'];
		$ret['game_desc'] = $game['game_desc'];
		$ret['admin_name'] = $admin['admin_name'];
		$ret['store_id'] = $store['store_id'];
		$ret['store_name']  = $store['store_name'];
		$ret['game_icon'] = Model("funny_games")->getGameIcon($game_key);
		
		return $ret;
	}
}

?>