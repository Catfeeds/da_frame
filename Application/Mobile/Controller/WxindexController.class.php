<?php
namespace Mobile\Controller;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\Model;

class WxindexController extends WxbaseController {
	
	public function __construct() 
	{
		parent::__construct();	
	}
	
	public function get_union_id()
	{
		$info = $this->wxapp_jscode2session($_GET['code']);
		output_data($info);
	}
	
    /**
     * 首页
     */
    public function index() {
        $model_mb_special = Model('wxapp_special');
        $data = $model_mb_special->getMbSpecialIndex();
        
        $data = $this->arrange_data($data);
        $data['goods'] = $this->get_goods_extends_data($data['goods']);
        
        output_data($data);
    }
    
    /**
     * 整理数据
     * */
    public function arrange_data($datas)
    {

    	$ret = array(
    			"adv_list" => array("item" => array()),
    			"goods" => array(),
    	);
    	
    	if (empty($datas))
    	{
    		return $ret;
    	}
    	
    	$temp_ret = array();
    	foreach ($datas as $item)
    	{
    		if (isset($item['goods']))
    		{
    			$temp_ret['goods'] = $item['goods'];
    		}
    		if (isset($item['adv_list']))
    		{
    			$temp_ret['adv_list'] = $item['adv_list'];
    		}
    	}
    	if (!isset($temp_ret['goods']))
    	{
    		$temp_ret['goods'] = $ret['goods'];
    	}
    	if (!isset($temp_ret['adv_list']))
    	{
    		$temp_ret['adv_list'] = $ret['adv_list'];
    	}
    	$ret = $temp_ret;
    	return $ret;
    }
    
    /**
     * 获取商品附加信息
     * */
    public function get_goods_extends_data($goods_data)
    {
    	$ret = array();
    	if (empty($goods_data) || empty($goods_data['item']))
    	{
    		return $ret;
    	}
    	
    	$new_goods_list = array();
    	$goods_mod = Model("goods");
    	$goods_list = $goods_data['item'];
    	foreach ($goods_list as $goods_item)
    	{
    		$extends_data = $goods_mod->getGoodsDetail($goods_item['goods_id']);
    		$wx_goods_image = $goods_item['goods_image'];
    		$goods_item = array_merge($goods_item, $extends_data);
    		$goods_item['wx_goods_image'] = $wx_goods_image;
    		$new_goods_list[] = $goods_item;
    	}

    	$ret = $new_goods_list;
    	return $ret;
    }
}