<?php
/**
 * 默认展示页面
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Shop\Controller;
use Shop\Controller\BaseHomeController;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\Db;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;
use Think\Template\Driver\Mobile;


class IndexController extends BaseHomeController{
    public function index(){
        Language::read('home_index_index');
        $this->assign('index_sign','index');

	//把加密的用户id写入cookie 已换另一个方式，临时去掉此方法
	$uid = intval(base64_decode($_COOKIE['uid']));
        //抢购专区
        Language::read('member_groupbuy');
        $model_groupbuy = Model('groupbuy');
        $group_list = $model_groupbuy->getGroupbuyCommendedList(4);
        $this->assign('group_list', $group_list);
		
		//专题获取

        $model_special = Model('cms_special');
        $special_list = $model_special->getShopindexList($conition);
        $this->assign('special_list', $special_list);
	
	//友情链接
	$model_link = Model('link');
	$link_list = $model_link->getLinkList($condition,$page);
	if (is_array($link_list)){
		foreach ($link_list as $k => $v){
			if (!empty($v['link_pic'])){
				$link_list[$k]['link_pic'] = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/common/'.DS.$v['link_pic'];
			}
		}
	}
	$this->assign('$link_list',$link_list);
		
        //限时折扣
        $model_xianshi_goods = Model('p_xianshi_goods');
        $xianshi_item = $model_xianshi_goods->getXianshiGoodsCommendList(6);
        $this->assign('xianshi_item', $xianshi_item);
		
		//直达楼层信息
		 if (C('shopda_lc') != '') {
            $lc_list = @unserialize(C('shopda_lc'));
        }
        $this->assign('lc_list',is_array($lc_list) ? $lc_list : array());
		
		//首页推荐词链接
		 if (C('shopda_rc') != '') {
            $rc_list = @unserialize(C('shopda_rc'));
        }
        $this->assign('rc_list',is_array($rc_list) ? $rc_list : array());

        //推荐品牌
        $brand_r_list = Model('brand')->getBrandPassedList(array('brand_recommend'=>1) ,'brand_id,brand_name,brand_pic,brand_xbgpic,brand_tjstore', 0, 'brand_sort asc, brand_id desc', 16);
        $this->assign('brand_r',$brand_r_list);
		
		
		//评价信息
        $goods_evaluate_info = Model('evaluate_goods')->getEvaluateGoodsList(8);
        $this->assign('goods_evaluate_info', $goods_evaluate_info);
	
	//当前城市  
		$city_id = cookie('cityid') ? cookie('cityid') : 0;
		if ($city_id != 0) {
			$this->assign('now_city',Model('area')->getAreaInfoById($city_id));
		}else{
			$this->assign('now_city','全国');
		}

        //板块信息
        $model_web_config = Model('web_config');
        $web_html = $model_web_config->getWebHtml('index');
        $this->assign('web_html',$web_html);
        Model('seo')->type('index')->show();
  
        $this->render('index');
    }
    
    //选择城市  
	public function set_city() {
		$city_id = intval($_GET['cityid']);
		if ($city_id != 0) {
			$city_info = Model('area')->getAreaInfoById($city_id);
			if (empty($city_info)) {
				showMessage('该城市不存在，请选择其他城市');
			}
	        
		}
		setDaCookie('cityid', $city_id);
		$ref_url=$_GET['ref_url'];
		$isExist = strstr($ref_url, 'c=SelectCity&');
		if($ref_url==''||$isExist)
		{
			header('Location:'.BASE_SITE_URL);
			return;
		}
        header('Location:'.$ref_url);
	}
	//选择城市的页面  
	public function select_city() {
		$area_list = Model('area')->getAreaArrayForJson();
		//省份数据
		$province = $area_list[0];
		foreach ($province as $key => $value) {
			//市的数据
			$city_list[][$key] = $area_list[$value[0]];
		}
		$this->assign('province_list',$province);
		$this->assign('city_list',$city_list);
		$this->render('select_city');
	}

    //json输出商品分类
    public function josn_class() {
        /**
         * 实例化商品分类模型
         */
        $model_class        = Model('goods_class');
        $goods_class        = $model_class->getGoodsClassListByParentId(intval($_GET['gc_id']));
        $array              = array();
        if(is_array($goods_class) and count($goods_class)>0) {
            foreach ($goods_class as $val) {
                $array[$val['gc_id']] = array('gc_id'=>$val['gc_id'],'gc_name'=>htmlspecialchars($val['gc_name']),'gc_parent_id'=>$val['gc_parent_id'],'commis_rate'=>$val['commis_rate'],'gc_sort'=>$val['gc_sort']);
            }
        }
        /**
         * 转码
         */
        if (strtoupper(CHARSET) == 'GBK'){
            $array = Language::getUTF8(array_values($array));//网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
        } else {
            $array = array_values($array);
        }
        echo $_GET['callback'].'('.json_encode($array).')';
    }

    /**
     * json输出地址数组 原data/resource/js/area_array.js
     */
    public function json_area()
    {
        $_GET['src'] = $_GET['src'] != 'db' ? 'cache' : 'db';
        echo $_GET['callback'].'('.json_encode(Model('area')->getAreaArrayForJson($_GET['src'])).')';
    }

    /**
     * 根据ID返回所有父级地区名称
     */
    public function json_area_show()
    {
        $area_info['text'] = Model('area')->getTopAreaName(intval($_GET['area_id']));
        echo $_GET['callback'].'('.json_encode($area_info).')';
    }

    //判断是否登录
    public function login(){
        echo ($_SESSION['is_login'] == '1')? '1':'0';
    }

    /**
     * 头部最近浏览的商品
     */
    public function viewed_info(){
        $info = array();
        if ($_SESSION['is_login'] == '1') {
            $member_id = $_SESSION['member_id'];
            $info['m_id'] = $member_id;
            if (C('voucher_allow') == 1) {
                $time_to = time();//当前日期
                $info['voucher'] = Model()->table('voucher')->where(array('voucher_owner_id'=> $member_id,'voucher_state'=> 1,
                'voucher_start_date'=> array('elt',$time_to),'voucher_end_date'=> array('egt',$time_to)))->count();
            }
            $time_to = strtotime(date('Y-m-d'));//当前日期
            $time_from = date('Y-m-d',($time_to-60*60*24*7));//7天前
            $info['consult'] = Model()->table('consult')->where(array('member_id'=> $member_id,
            'consult_reply_time'=> array(array('gt',strtotime($time_from)),array('lt',$time_to+60*60*24),'and')))->count();
        }
        $goods_list = Model('goods_browse')->getViewedGoodsList($_SESSION['member_id'],5);
        if(is_array($goods_list) && !empty($goods_list)) {
            $viewed_goods = array();
            foreach ($goods_list as $key => $val) {
                $goods_id = $val['goods_id'];
                $val['url'] = urlShop('goods', 'index', array('goods_id' => $goods_id));
                $val['goods_image'] = thumb($val, 60);
                $viewed_goods[$goods_id] = $val;
            }
            $info['viewed_goods'] = $viewed_goods;
        }
        if (strtoupper(CHARSET) == 'GBK'){
            $info = Language::getUTF8($info);
        }
        echo json_encode($info);
    }
    /**
     * 查询每月的周数组
     */
    public function getweekofmonth(){
 
        $year = $_GET['y'];
        $month = $_GET['m'];
        $week_arr = getMonthWeekArr($year, $month);
        echo json_encode($week_arr);
        die;
    }
}
