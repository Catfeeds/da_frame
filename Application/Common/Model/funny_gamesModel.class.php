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
use PayPal\Api\PrivateLabelCard;

class funny_gamesModel extends Model {
	
	const CHECK_STATUS_SUCCESS = 10; //审核通过
	const CHECK_STATUS_FAIL    = 9; //审核拒绝
	const CHECK_STATUS_WAIT    = 8; //等待审核
	
	public function __construct() {
		parent::__construct('funny_games');
	}
	
	/**
	 * 游戏列表
	 *
	 * @param array $condition 条件
	 * @param array $field 字段
	 * @param string $page 分页
	 * @param string $order 排序
	 * @return array
	 */
	public function getGameList($condition, $field = '*', $page = 10, $order = "id desc",  $limit = '') {
		$ret = $this->table('funny_games')->field($field)->where($condition)->order($order)->limit($limit)->page($page)->select();
		return $ret;
	}
	
	
	/**
	 * 游戏列表
	 *
	 * @param array $condition 条件
	 * @param array $field 字段
	 * @param string $page 分页
	 * @param string $order 排序
	 * @return array
	 */
	public function adminGetGameList($condition = array(), $field = '*', $page = null, $order = 'member_id desc', $limit = '') {

		$ret = array();
		
		$game_class_hash = $this->transGameClassList2Hash($this->getGameClassList());
		$game_list = $this->table("funny_games")->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();

// 		var_dump($page, $game_list);
// 		exit;
		
		if (empty($game_list))
		{
			return $ret;
		}
		foreach ($game_list as $game_item)
		{
			$game_item['game_class_name'] = $this->getGameClassName($game_item['game_class_id'], $game_class_hash);
			$game_item['game_icon'] = $this->getGameIcon($game_item['game_key']);
			$ret[] = $game_item;
		}
		return $ret;
	}
	
	/**
	 * 游戏列表
	 *
	 * @param array $condition 条件
	 * @param array $field 字段
	 * @param string $page 分页
	 * @param string $order 排序
	 * @return array
	 */
	public function getGameListWithExtendInfo($store_id, $condition, $field = '*', $page = 10, $order = "id desc",  $limit = '')
	{
		$condition['store_id'] = $store_id;
		$game_list = $this->table('funny_games')->field($field)->where($condition)->order($order)->limit($limit)->page($page)->select();
		
		//游戏list
		$game_hash = array();

		foreach ($game_list as $game_item)
		{
			$game_hash[$game_item['game_key']]['config'] = unserialize($game_hash[$game_item['game_key']]['config']);
			$game_hash[$game_item['game_key']] = $game_item;
		}
		$game_key_list = array_keys($game_hash);
		
		
		//批量分类HASH
		$class_hash = array();
		$class_list = $this->table("funny_games_class")->where(array(1=>1))->select();
		foreach ($class_list as $class_item)
		{
			$class_hash[$class_item['id']] = $class_item;
		}
		
		//批量权限hash
		$privilege_hash = $this->batchCheckPrivilege($store_id, $game_key_list);
		
		//批量个性化配置hash
		$own_game_config_hash = $this->batchGetConfig($store_id, $game_key_list);
		
		//权限申请hash
		$apply_hash = $this->batchGetApplyInfo($store_id, $game_key_list);
		
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
			
			//icon
			$game_hash[$game_key]['game_icon'] = $this->getGameIcon($game_key);
			
			//url
			$game_hash[$game_key]['url'] = $this->getGameUrl($game_key, $store_id);
			
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
		
		$ret = array_values($game_hash);
		return $ret;
	}
	
	/*获取游戏分类列表*/
	public function getGameClassList()
	{
		$ret = array();
		$ret = $this->table("funny_games_class")->where(array(1=>1))->select();
		
		return $ret;
	}
	
	/*转换game class 为hash*/
	public function transGameClassList2Hash($game_class_list)
	{
		$ret = array();
		if (empty($game_class_list))
		{
			return $ret;
		}
		foreach ($game_class_list  as $item)
		{
			$ret[strval($item['id'])] = $item;
		}
		return $ret;
	}
	
	/**
	 * 获取游戏分类名称 
	 * */
	public function getGameClassName($class_id, $class_hash)
	{
		$class_id = strval($class_id);
		$ret = "未知分类";
		if (isset($class_hash[$class_id]))
		{
			$ret = $class_hash[$class_id]['game_class_name'];
		}
// 		var_dump($class_id, $class_hash,$ret);
// 		exit;
		return $ret;
	}
	
	/**
	 * 获取单个游戏（后台）
	 * */
	public function adminGetGameByKey($game_key)
	{
		$ret = array();
		if (empty($game_key))
		{
			return $ret;
		}
		$cond = array("game_key" => $game_key);
		
		$ret = $this->table('funny_games')->where($cond)->find();
		
		if (!empty($ret))
		{
			$ret['config'] = unserialize($ret['config']);
				
			$class_id = $ret['game_class_id'];
			$class_data = $this->table("funny_games_class")->where(array("id" => $class_id))->find();
			$ret['game_class'] = $class_data;
			$ret['url'] = $this->getGameUrl($game_key, $store_id);
			$ret['game_icon'] = $this->getGameIcon($ret['game_key']);
		}
		return $ret;
	}
	
	/**
	 * 单个游戏
	 * */
	public function getGameByKey($game_key, $store_id = "")
	{
		$ret = array();
		if (empty($game_key))
		{
			return $ret;
		}
		$cond = array("game_key" => $game_key);

		$ret = $this->table('funny_games')->where($cond)->find();
		
		if (!empty($ret))
		{
			$ret['config'] = unserialize($ret['config']);
			
			$class_id = $ret['game_class_id'];
			$class_data = $this->table("funny_games_class")->where(array("id" => $class_id))->find();
			$ret['game_class'] = $class_data;

			if (empty($store_id))
			{
				return $ret;
			}
			
			$ret['check_privilege'] = false;
			
			if (!empty($store_id))
			{
				$privilege_check = $this->checkGamePrivilege($game_key, $store_id);
				if ($privilege_check)
				{
					$ret['check_privilege'] = true;
					
					$own_config = $this->table("funny_games_config")->where(array("store_id" => $store_id, "game_key" => $game_key))->find();
					$own_config = unserialize($own_config['config']);
					
					$ret['own_config'] = $own_config;
					
				}
				else
				{
					$ret['own_config'] = array();
				}
				
				$cond = array("store_id" => $store_id, "game_key" => $game_key);
				$apply_info = (array) $this->table("funny_games_apply")->where($cond)->find();
				$ret['apply_info'] = $apply_info;
				$ret['url'] = $this->getGameUrl($game_key, $store_id);
			}
			else 
			{
				$ret['check_privilege'] = false;
				$ret['own_config'] = array();
				$ret['apply_info'] = array();
				$ret['url'] = $this->getGameUrl($game_key);
			}
			
			
		}
 
		return $ret;
		
	}
	
	/**
	 * 游戏列表转HASH
	 * */
	public function transGameList2Hash($game_list)
	{
		$ret = array();
		if (empty($game_list))
		{
			return $ret;
		}
		foreach ($game_list as $item)
		{
			$ret[$item['game_key']] = $item;
		}
		return $ret;
	}
	
	/**
	 * 获取游戏地址
	 * */
	public function getGameUrl($game_key, $store_id = '')
	{
		$ret = "";
		$url = FG_BASE_URL . '/' . "games" . "/" . $game_key . "/" . "index.html";
		
		$args = array("game_key" => $game_key);
		if (!empty($store_id))
		{
			$args['store_id'] = $store_id;
		}
		$ret = getFullUrl($url, $args);
		
		return $ret;
	}
	
	/**
	 * 获取游戏ICON
	 * */
	public function getGameIcon($game_key)
	{
		$ret = "";
		$url = FG_BASE_URL . '/' . "games" . "/" . $game_key . "/" . "shopda_game_cover.jpg";
		$ret = $url;
		return $ret;
	}
	
	/**
	 * 检查游戏权限
	 * */
	
	public function checkGamePrivilege($game_key, $store_id)
	{
		$ret = false;
		if (empty($store_id) || empty($game_key))
		{
			return $ret;
		}
		
		
		$cond = array("game_key" => $game_key, "store_id" => $store_id, "is_open" => 1);
		$list = $this->table("funny_games_apply")->where($cond)->select();
		
		if ((!empty($list)))
		{
			$ret = true;
		}
		
		return $ret;
	}
	
	/**
	 * 批量获取游戏申请信息
	 * */
	public function batchGetApplyInfo($store_id, $game_key_list)
	{
		$ret = array();
		if (empty($store_id) || empty($game_key_list))
		{
			return $ret;
		}
		
		$mod = Model("funny_games_apply");

		$apply_hash = array();
		$cond = array( "store_id" => $store_id, "game_key" => array("exp" , $this->buildInStr("game_key", $game_key_list)) );
		 
		$apply_list = $mod->table('funny_games_apply')->where($cond)->select();
 
		foreach ($apply_list  as $apply_item)
		{
			$apply_hash[$apply_item['game_key']] = $apply_item;
		}
		 
		
		foreach ($game_key_list as $game_key)
		{
			if (!isset($apply_hash[$game_key]))
			{
				$apply_hash[$game_key] = array();
			}
		}

		$ret = $apply_hash;
		return $ret;
	} 
	
	
	/**
	 * 检查游戏权限
	 * */
	public function batchCheckPrivilege($store_id, $game_key_list)
	{
		$ret = array();
		if (empty($store_id) || empty($game_key_list))
		{
			return $ret;
		}
		
		$mod = Model("funny_games_apply");
 
		$apply_hash = array();
		$cond = array( "store_id" => $store_id, "game_key" => array("exp" , $this->buildInStr("game_key", $game_key_list)) );
		$apply_list = $mod->table('funny_games_apply')->where($cond)->select();
 
		foreach ($apply_list  as $apply_item)
		{
			if ($apply_item['is_open'] == '1')
			{
				$apply_hash[$apply_item['game_key']] = true;
			}
			else 
			{
				$apply_hash[$apply_item['game_key']] = false;
			}
		}
		
		foreach ($game_key_list as $game_key)
		{
			if (!isset($apply_hash[$game_key]))
			{
				$apply_hash[$game_key] = false;
			}
		}
		
		$ret = $apply_hash;
		return $ret;
	}
	
	/**
	 * 权限描述
	 * */
	public function getCheckStatusDesc($status)
	{
		$ret = "未知";
		switch ($status)
		{
			case  funny_gamesModel::CHECK_STATUS_FAIL:
				$ret = "审核拒绝";
				break;
			case funny_gamesModel::CHECK_STATUS_SUCCESS:
				$ret = "审核通过";
				break;
			case funny_gamesModel::CHECK_STATUS_WAIT:
				$ret = "等待审核";
				break;
			default :
				$ret = "初始化";
				
		}
		return $ret;
	}
	
	/**
	 * 获取配置
	 * */
	public function batchGetConfig($store_id, $game_key_list)
	{
		$configHash = array();
		if (empty($store_id) || empty($game_key_list))
		{
			return $configHash;
		}
 
		$cond = array( "store_id" => $store_id, "game_key" => array("exp" , $this->buildInStr("game_key", $game_key_list)) );
		$config_list = $this->table("funny_games_config")->where($cond)->select();
		foreach ($config_list as $item)
		{
			if (!empty($item['config']))
			{
				$item['config'] = unserialize($item['config']);
			}
			else
			{
				$item['config'] = array();
			}
			$configHash[$item['game_key']] = $item;
		}
		
		return $configHash;
	}
	
	/**
	 * 获取游戏列表
	 * */
	public function batchGetGameList($game_key_list)
	{
		$ret = array();
		if (empty($game_key_list))
		{
			return $ret;
		}
		
		$cond = array( "game_key" => array("exp" , $this->buildInStr("game_key", $game_key_list)) );
		$list = $this->table("funny_games")->where($cond)->select();

		foreach ($list as $item)
		{
			$item['game_icon'] = $this->getGameIcon($item['game_key']);
			$ret[] = $item;
		}
		
		return $ret;
	}
	
	/**
	 * 批量获取游戏
	 * */
	public function batchGetByGameKey($game_key_list)
	{
		$retHash = array();
		if (empty($game_key_list))
		{
			return $retHash;
		}
		 
		$cond = array("game_key" => array("exp" , $this->buildInStr("game_key", $game_key_list)) );

		$game_list = $this->table("funny_games")->where($cond)->select();
		
		if (empty($game_list))
		{
			return $retHash;			
		}
		
		foreach ($game_list as $game_item)
		{
			$retHash[$game_item['game_key']] = $game_item;
		}
		return $retHash;
	}
	
	/**
	 * 获取游戏KEY LIST
	 * */
	public function getGameKey($gameList)
	{
		$ret = array();
		foreach ($gameList as $item)
		{
			$ret[] = $item['game_key'];
		}
		return $ret;
	}
	
	/**
	 * 获取申请列表
	 * */
	public function listApply($condition, $field = '*', $page = 10, $order = "update_time desc",  $limit = '')
	{
		$ret = array();
		$apply_list = $this->table("funny_games_apply")->field($field)->where($condition)->order($order)->limit($limit)->page($page)->select();
		
		if (empty($apply_list))
		{
			return $ret;
		}
		
		$sotre_id_arr = array();
		foreach ($apply_list as $apply_item)
		{
			$store_id_arr[] = $apply_item['store_id'];
		}
		
		$sotre_list_hash = $this->batchGetStoreInfo($store_id_arr);
		
		foreach ($apply_list as $apply_item)
		{
			$apply_item['store_name'] = $sotre_list_hash[$apply_item['store_id']]['store_name'];
			$apply_item['seller_name'] = $sotre_list_hash[$apply_item['store_id']]['seller_name'];
			
			$ret[] = $apply_item;
		}
		
		return $ret;
	}
	
	/**
	 * 批量获取店铺信息
	 * */
	public function batchGetStoreInfo($store_id_list)
	{
		$ret = array();
		if (empty($store_id_list))
		{
			return $ret;
		}
		 
		$cond = array("store_id" => array("exp" , $this->buildInStr("store_id", $store_id_list)) );
		$list = $this->table("store")->where($cond)->select();
		if (empty($list))
		{
			return $ret;
		}
		foreach ($list as $item)
		{
			$ret[$item['store_id']] = $item;
		}
		return $ret;
	}
	
	/**
	 * 审批申请
	 * */
	public function checkGameApply($admin_id, $game_key, $store_id, $result = true, $reason = "") 
	{
		$ret = true;
		$cond = array("game_key" => $game_key, "store_id" => $store_id);
		$game_apply = $this->table("funny_games_apply")->where($cond)->find();
		
		if ($result == true)
		{
			if ($game_apply['is_open'] != 1)
			{
				$game_apply['is_open'] = 1;
				$game_apply['update_time'] = CUR_TIME;
				$game_apply['admin_id'] = $admin_id;
			}
		}
		else
		{
			$game_apply['is_open'] = 0;
			$game_apply['check_reason'] = $reason;
			$game_apply['update_time'] = CUR_TIME;
			$game_apply['admin_id'] = $admin_id;
		}
		
		$cond = array("id" => $game_apply['id']);
		$this->table("funny_games_apply")->where($cond)->save($game_apply);
		
		return $ret;	
	}
	

	//获取配置列表
	public function getConfigList($default_config_str, $own_config_str)
	{
		$default_config_arr = unserialize($default_config_str);
		$own_config_arr = unserialize($own_config_str);
	
		if (empty($own_config_arr))
		{
			$config_arr = $default_config_arr;
			$ret = $this->mergeConfHash2List($config_arr);
		}
		else
		{
			$default_config_list = $this->mergeConfHash2List($default_config_arr);
			$own_config_list = $this->mergeConfHash2List($own_config_arr);
				
			$own_config_hash = $this->transConf2Hash($own_config_list);
			$default_config_hash = $this->transConf2Hash($default_config_list);
				
			foreach ($default_config_hash as $key => $val)
			{
				if (!isset($own_config_hash[$key]))
				{
					$own_config_hash[$key] = $val;
				}
			}
			$ret = $own_config_hash;
			$ret = array_values($ret);
				
		}
		return $ret;
	}
	
	/**
	 * 将复合配置 变成1唯数组
	 * */
	public function mergeConfHash2List($config_arr)
	{
		return $config_arr;
	}
	
	/**
	 * trans conf 2 hash
	 * */
	public function transConf2Hash($conf_list)
	{
		$ret = array();
		if (empty($conf_list))
		{
			return $ret;
		}
		foreach ($conf_list as $item)
		{
			$ret[$item["key"]] = $item;
		}
		return $ret;
	}
	
	/**
	 * 从conf中获取表单KEY
	 * */
	public function getConfFormKeyStr($conf_arr)
	{
		$ret = "";
		if (empty($conf_arr))
		{
			return $ret;
		}
 
		$conf_list = $this->mergeConfHash2List($conf_arr);

		foreach ($conf_list as $item)
		{
			$ret = $ret . $item['key'] . ",";
		}
		$ret = trim($ret, ",");

		return $ret;
	}
	
	
	/**
	 * 根据用户输入获取提交的配置信息
	 * */
	public function getConfFormData($default_conf_arr)
	{
		$ret = array();
		if (empty($default_conf_arr))
		{
			return $ret;
		}
		
		$conf_list = $this->mergeConfHash2List($default_conf_arr);
		$default_conf_hash = $this->transConf2Hash($conf_list);
 
		//var_dump($default_conf_hash);
		
		$shopda_form_fields_str = $_POST['shopda_form_fields_str'];
		$shopda_form_fields_arr = explode(",", $shopda_form_fields_str);

		foreach ($shopda_form_fields_arr as $item_key)
		{
			$ret[] = $this->getConfFormItemData($item_key, $default_conf_hash);
		}

// 		var_dump($ret);
// 		exit;
		return $ret;
	}


	/**
	 * 分类型获取提交数据
	 * */
	private function getConfFormItemData($item_key, $default_conf_hash)
	{
// 		var_dump($item_key, $default_conf_hash);
// 		exit;

		$ret = array();
	
		$default_conf_data = $default_conf_hash[$item_key];
	
		if (empty($default_conf_data))
		{
			return $ret;
		}
		//var_dump($mix_key);
		switch($default_conf_data['type'])
		{
			case "image":
				$default_conf_data['value'] = $_POST[$item_key . "____value"];
				break;
	
			case "image-link":
				$default_conf_data['img_url_value'] = $_POST[$item_key . "____img_url_value"];
				$default_conf_data['direct_link_value'] = $_POST[$item_key . "____direct_link_value"];
				break;
	
			case "number":
				$default_conf_data['value'] = $_POST[$item_key . "____value"];
				break;
	
			case "switch":
				$default_conf_data['value'] = $_POST[$item_key . "____value"];
				break;
	
			case "text":
				$default_conf_data['value'] = $_POST[$item_key . "____value"];
	
			case "color-selector":
				$default_conf_data['value'] = $_POST[$item_key . "____value"];
				break;
		}
	
		$ret = $default_conf_data;
// 		var_dump($ret);
// 		exit;
		return $ret;
	}
	
	/**
	 * 获取配置表单项
	 * */
	public function getConfigListFormStr($conf_list)
	{
		$ret = "";
		foreach ($conf_list as $conf_item)
		{
			$ret .= $this->getConfigItemForTpl($conf_item) . "\n";
		}
		return $ret;
	}
	
	
	public function getConfigItemForTpl($conf_item)
	{
		$ret = "";
		if (empty($conf_item))
		{
			return $ret;
		}
		switch ($conf_item['type'])
		{
			case "image":
				$ret = $this->getImgFieldForTpl($conf_item);
				break;
			case "image-link":
				$ret = $this->getImgLinkFieldForTpl($conf_item);
				break;
			case "number":
				$ret = $this->getNumberFieldForTpl($conf_item);
				break;
			case "switch":
				$ret = $this->getSwitchFieldForTpl($conf_item);
				break;
			case "text":
				$ret = $this->getTextFieldForTpl($conf_item);
				break;
			case "color-selector":
				$ret= $this->getColorSelectorFieldForTpl($conf_item);
				break;
				
		}
		return $ret;
	}

	//图片上传
	private function getImgFieldForTpl($conf_arr)
	{
		$ret = "";
		if (empty($conf_arr))
		{
			return $ret;
		}
		$label = $conf_arr['label'];
		$form_name = $conf_arr['key'] . "____value";
		$info = $conf_arr['info'];
		$value = $conf_arr['value'];
		
		$ret = <<<EOT
    <dl>
      <dt>{$label}</dt>
      <dd>
        <div class="spdsc-upload-thumb">
 
          <img  class="thumb_{$form_name}" src="{$value}" />
 
        </div>
        <div class="spdsc-upload-btn" datype="{$form_name}"> <a href="javascript:void(0);">
        
          <span>
        
          	  <input class="{$form_name}" name="{$form_name}" value="{$value}" style="display:none;"/>
              <input type="file" hidefocus="true" size="1" class="input-file" name="file_data" id="{$form_name}" datype="{$form_name}"/>
          
          </span>
          
          <p><i class="icon-upload-alt"></i>图片上传</p>
          </a> </div>
        <p class="hint">{$info}</p>
      </dd>
    </dl>
EOT;
		
		return $ret;

	}
	
	//链接
	private function getImgLinkFieldForTpl($conf_arr)
	{
		$ret = "";
		if (empty($conf_arr))
		{
			return $ret;
		}
		$label = $conf_arr['label'];
		
		$form_name_img_url_name = $conf_arr['key'] . "____img_url_value";
		$form_name_img_link_name = $conf_arr['key'] . "____direct_link_value";
		
		$img_url_value = $conf_arr['img_url_value'];
		$img_link_value = $conf_arr['direct_link_value'];
		
		$info = $conf_arr['info'];
		
		$ret = <<<EOT
    <dl>
      <dt>{$label}</dt>
      <dd>
      
        <div class="spdsc-upload-thumb">
 
          <img class="thumb_{$form_name_img_url_name}" src="{$img_url_value}" />
 
        </div>
        
        <div class="spdsc-upload-btn" datype="{$form_name_img_url_name}"> <a href="javascript:void(0);"><span>
        
          <input class="{$form_name_img_url_name}" name="{$form_name_img_url_name}" value="{$img_url_value}" style="display:none;"/>
          <input type="file" hidefocus="true" size="1" class="input-file" name="file_data" id="{$form_name_img_url_name}" datype="{$form_name_img_url_name}"/>
          
          </span>
          <p><i class="icon-upload-alt"></i>图片上传</p>
          </a> 
        </div>
          
        <div style="">
        <ul>
        	<li><label>广告跳转地址</label></li>
        	<li><input name="{$form_name_img_link_name}" rows="2" class="text w400 valid shopda-fg-number"  maxlength="50" value="{$img_link_value}"/></li>
        </ul>
        </div>
        			
       
        <p class="hint">{$info}</p>
          
      </dd>
    </dl>
EOT;
		
		return $ret;
	}
	
	//数字
	private function getNumberFieldForTpl($conf_arr)
	{
		$ret = "";
		if (empty($conf_arr))
		{
			return $ret;
		}
		$label = $conf_arr['label'];
		$form_name = $conf_arr['key'] . "____value";
		$info = $conf_arr['info'];
		$value = $conf_arr['value'];
		
		//var_dump($info, $label);
		//var_dump($value, $conf_arr);
		
		$ret = <<<EOT
					<dl>
						<dt>{$label}</dt>
						<dd>
					        <input name="{$form_name}" rows="2" class="text w400 shopda-fg-number"  maxlength="50" value="{$value}" />
					        <p class="hint">{$info}</p>
					     </dd>
				    </dl>
EOT;
		
		return $ret;
	}
	
	//开关
	private function getSwitchFieldForTpl($conf_arr)
	{
		$ret = "";
		if (empty($conf_arr))
		{
			return $ret;
		}
		$label = $conf_arr['label'];
		$form_name = $conf_arr['key'] . "____value";
		$info = $conf_arr['info'];
		$value = $conf_arr['value'];
		
		if ($value == 1)
		{
			$selected_str = '<option value="1" selected>开</option> <option value="0">关</option>';
		}
		else
		{
			$selected_str = '<option value="1">开</option> <option selected value="0">关</option>';
		}
		
		$ret = <<<EOT
					<dl>
						<dt>{$label}</dt>
						<dd>
							<select name="{$form_name}">
								{$selected_str}
							</select>
					        <p class="hint">{$info}</p>
					     </dd>
				    </dl>
EOT;
		
		return $ret;
	}
	
	//文本
	private function getTextFieldForTpl($conf_arr)
	{
		$ret = "";
		if (empty($conf_arr))
		{
			return $ret;
		}
		$label = $conf_arr['label'];
		$form_name = $conf_arr['key'] . "____value";
		$info = $conf_arr['info'];
		$value = $conf_arr['value'];
		
		$ret = <<<EOT
					<dl>
						<dt>{$label}</dt>
						<dd>
					        <input name="{$form_name}" rows="2" class="text w400 valid shopda-fg-number"  maxlength="50" value="{$value}" />
					        <p class="hint">{$info}</p>
					     </dd>
				    </dl>
EOT;
		
		return $ret;
	}
	
	//color-selector
	private function getColorSelectorFieldForTpl($conf_arr)
	{
		$ret = "";
		if (empty($conf_arr))
		{
			return $ret;
		}
		
		$uniq_key = $conf_arr['key'];
		$label = $conf_arr['label'];
		$form_name = $conf_arr['key'] . "____value";
		$info = $conf_arr['info'];
		$value = $conf_arr['value'];
		
		$ret = <<<EOT
					<dl>
						<dt>{$label}</dt>
						<dd>
					        <input name="{$form_name}" rows="2" readonly="readonly" class="text w400 color-selector" uniq_key="uniq_class_key_{$uniq_key}"   maxlength="50" value="{$value}" />
					        <p class="hint">{$info}</p>
					     </dd>
				    </dl>
EOT;
		
		return $ret;
	}
}

?>