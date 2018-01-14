<?php
/**
 * 商品列表
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\Model;
use Common\Lib\Page;
use Common\Lib\QueueClient;


class SearchController extends BaseHomeController {

	public function __construct() {
		parent::__construct();
	}

    //每页显示商品数
    const PAGESIZE = 30;

    //模型对象
    private $_model_search;

    public function index() {
	//str V1.0显示城市
        $area_list = Model('area')->getAreaArrayForJson();
        //省份数据
        $province = $area_list[0];
       
        if (cookie('cityid') == 0) {
            $flag = 0;
        }else{
            $flag = 1;
        }

        //当前城市
        $city_id = cookie('cityid') ? cookie('cityid') : 0;
        if ($city_id != 0) {
            $this->assign('now_city',Model('area')->getAreaInfoById($city_id));
            $this->assign('now_city_id',$city_id);
            $this->assign('city_list',$area_list[$city_id]);
        }else{
            $this->assign('now_city','全国');
        }
        $this->assign('province', $province);
        $this->assign('flag', $flag);
	//end
	
	
	
        Language::read('home_goods_class_index');
        $this->_model_search = Model('search');		
        //显示左侧分类
        //默认分类，从而显示相应的属性和品牌
        $default_classid = intval($_GET['cate_id']);
        if (intval($_GET['cate_id']) > 0) {
            $goods_class_array = $this->_model_search->getChildCat($_GET['cate_id']);
        } elseif ($_GET['keyword'] != '') {
            if (cookie('his_sh') == '') {
                $his_sh_list = array();
            } else {
                $his_sh_list = explode('~', cookie('his_sh'));
            }
            if (strlen($_GET['keyword']) <= 30 && !in_array($_GET['keyword'],$his_sh_list)) {
                if (array_unshift($his_sh_list, $_GET['keyword']) > 8) {
                    array_pop($his_sh_list);
                }
            }
            setDaCookie('his_sh', implode('~', $his_sh_list),2592000);
            //从TAG中查找分类
            $goods_class_array = $this->_model_search->getTagCategory($_GET['keyword']);
            //取出第一个分类作为默认分类，从而显示相应的属性和品牌
            $default_classid = $goods_class_array[0];
            $goods_class_array = $this->_model_search->getLeftCategory($goods_class_array, 1);
        }
        $this->assign('goods_class_array', $goods_class_array);
        $this->assign('default_classid', $default_classid);

        //全文搜索搜索参数
        $indexer_searcharr = $_GET;

        //搜索消费者保障服务
        $search_ci_arr = array();
        $search_ci_str = '';
        if ($_GET['ci'] && $_GET['ci'] != 0 && is_string($_GET['ci'])) {
            //处理参数
            $search_ci= $_GET['ci'];
            $search_ci_arr = explode('_',$search_ci);
            $search_ci_str = $search_ci.'_';
            $indexer_searcharr['search_ci_arr'] = $search_ci_arr;
        }

        //优先从全文索引库里查找
        list($goods_list,$indexer_count) = $this->_model_search->indexerSearch($indexer_searcharr,self::PAGESIZE);

        //获得经过属性过滤的商品信息
        list($goods_param, $brand_array, $initial_array, $attr_array, $checked_brand, $checked_attr) = $this->_model_search->getAttr($_GET, $default_classid);
        $this->assign('brand_array', $brand_array);
        $this->assign('initial_array', $initial_array);
        $this->assign('attr_array', $attr_array);
        $this->assign('checked_brand', $checked_brand);
        $this->assign('checked_attr', $checked_attr);

        //查询消费者保障服务
        $contract_item = array();
        if (C('contract_allow') == 1) {
            $contract_item = Model('contract')->getContractItemByCache();
        }
        $this->assign('contract_item',$contract_item);

        $model_goods = Model('goods');
        if (!is_null($goods_list)) {
            //全文搜索 
            pagecmd('setEachNum',self::PAGESIZE);
            pagecmd('setTotalNum',$indexer_count);

        } else {
            //查库搜索

            //处理排序
            $order = 'is_own_shop desc,goods_id desc';
            if (in_array($_GET['key'],array('1','2','3'))) {
                $sequence = $_GET['order'] == '1' ? 'asc' : 'desc';
                $order = str_replace(array('1','2','3'), array('goods_salenum','goods_click','goods_promotion_price'), $_GET['key']);
                $order .= ' '.$sequence;
            }

            // 字段
            $fields = "goods_id,goods_commonid,goods_name,goods_jingle,gc_id,store_id,store_name,goods_price,goods_promotion_price,goods_promotion_type,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,brand_id,color_id,gc_id_3,gc_id_1,gc_id_2,goods_verify,goods_state,is_own_shop,evaluation_good_star,evaluation_count,is_virtual,is_fcode,is_presell,is_book,book_down_time,have_gift,areaid_1";
            //构造消费者保障服务字段
            if ($contract_item) {
                foreach ($contract_item as $citem_key=>$citem_val) {
                    $fields .= ",contract_{$citem_key}";
                }
            }
            
            $goods_class = Model('goods_class')->getGoodsClassForCacheModel();
            $condition = array();
            if (isset($goods_param['class'])) {
                $condition['gc_id_'.$goods_param['class']['depth']] = $goods_param['class']['gc_id'];
            }
            if (intval($_GET['b_id']) > 0) {
                $condition['brand_id'] = intval($_GET['b_id']);
            }
            if ($_GET['keyword'] != '') {
	    // 优化搜索
				$keys = explode(' ',$_GET['keyword']);
				$datakey = array();
				$pj = '';
				foreach($keys as $key=>$val){
					$datakey[] = array('like','%' . $val . '%');
				}
				$datakey[] = 'and';
				$condition['goods_name|goods_jingle'] = $datakey;
            }
            //if (intval($_GET['area_id']) > 0) {
            //    $condition['areaid_1'] = intval($_GET['area_id']);
            //}
	    
	    //省
            if (intval($_GET['areaid_1']) > 0) {
                $condition['areaid_1'] = intval($_GET['areaid_1']);
            }
            //市
            if (cookie('cityid')) {
                $condition['areaid_2'] = intval(cookie('cityid'));
            }
            //区
            if (intval($_GET['areaid_3']) > 0) {
                $condition['areaid_3'] = intval($_GET['areaid_3']);
            }
	    //end
	    
	    
            if ($_GET['type'] == 1) {
                $condition['is_own_shop'] = 1;
            }
            if ($_GET['gift'] == 1) {
                $condition['have_gift'] = 1;
            }
            //消费者保障服务
            if ($contract_item && $search_ci_arr) {
                foreach ($search_ci_arr as $ci_val) {
                    $condition["contract_{$ci_val}"] = 1;
                }
            }

            if (isset($goods_param['goodsid_array'])){
                $condition['goods_id'] = array('in', $goods_param['goodsid_array']);
            }
            if ($goods_class[$default_classid]['show_type'] == 1) {
                $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fields, $order, self::PAGESIZE);
            } else {
                $count = $model_goods->getGoodsOnlineCount($condition,"distinct goods_commonid");
                $goods_list = $model_goods->getGoodsOnlineList($condition, $fields, self::PAGESIZE, $order, 0, 'goods_commonid', false, $count);
            }
        }
        $this->assign('search_ci_str', $search_ci_str);
        $this->assign('search_ci_arr', $search_ci_arr);
        $this->assign('show_page1', $model_goods->showpage(4));
        $this->assign('show_page', $model_goods->showpage(5));

        if (!empty($goods_list)) {
            if (is_null($indexer_count)) {
                //查库搜索
                $commonid_array = array(); // 商品公共id数组
                $storeid_array = array();       // 店铺id数组
                foreach ($goods_list as $value) {
                    $commonid_array[] = $value['goods_commonid'];
                    $storeid_array[] = $value['store_id'];
                }
                $commonid_array = array_unique($commonid_array);
                $storeid_array = array_unique($storeid_array);
                // 商品多图
                $goodsimage_more = $model_goods->getGoodsImageList(array('goods_commonid' => array('in', $commonid_array)), '*', 'is_default desc,goods_image_id asc');
                // 店铺
                $store_list = Model('store')->getStoreMemberIDList($storeid_array);
                //处理商品消费者保障服务信息
                $goods_list = $model_goods->getGoodsContract($goods_list, $contract_item);
            }

            //搜索的关键字
            $search_keyword = $_GET['keyword'];
            foreach ($goods_list as $key => $value) {
                if (is_null($indexer_count)) {
                    // 商品多图
                    
                    if ($goods_class[$default_classid]['show_type'] == 1) {
                        foreach ($goodsimage_more as $v) {
                            if ($value['goods_commonid'] == $v['goods_commonid'] && $value['store_id'] == $v['store_id'] && $value['color_id'] == $v['color_id']) {
                                $goods_list[$key]['image'][] = $v['goods_image'];
                            }
                        }
                    } else {
                        foreach ($goodsimage_more as $v) {
                            if ($value['goods_commonid'] == $v['goods_commonid'] && $value['store_id'] == $v['store_id'] && $v['is_default'] == 1) {
                                $goods_list[$key]['image'][] = $v['goods_image'];
                            }
                        }
                    }
                    // 店铺的开店会员编号
                    $store_id = $value['store_id'];
                    $goods_list[$key]['member_id'] = $store_list[$store_id]['member_id'];
                    $goods_list[$key]['store_domain'] = $store_list[$store_id]['store_domain'];
		    //shopda  商家QQ
		    $goods_list[$key]['store_qq'] = $store_list[$store_id]['store_qq'];                 
                }

                //将关键字置红
                if ($search_keyword){
                    $goods_list[$key]['goods_name_highlight'] = str_replace($search_keyword,'<font style="color:#f00;">'.$search_keyword.'</font>',$value['goods_name']);
                } else {
                    $goods_list[$key]['goods_name_highlight'] = $value['goods_name'];
                }

                // 验证预定商品是否到期
                if ($value['is_book'] == 1) {
                    if ( $value['book_down_time'] < TIMESTAMP ) {
                        QueueClient::push('updateGoodsPromotionPriceByGoodsId', $value['goods_id']);
                        $goods_list[$key]['is_book'] = 0;
                    }
                }
            }
        }
		$goods_num= $model_goods->getGoodsCommonCount($condition);
		$this->assign('goods_num',  $goods_num);
        $this->assign('goods_list', $goods_list);
        if ($_GET['keyword'] != ''){
            $this->assign('show_keyword',  $_GET['keyword']);
        } else {
            $this->assign('show_keyword',  $goods_param['class']['gc_name']);
        }

        $model_goods_class = Model('goods_class');

        // SEO
        if ($_GET['keyword'] == '') {
            $seo_class_name = $goods_param['class']['gc_name'];
            if (is_numeric($_GET['cate_id']) && empty($_GET['keyword'])) {
                $seo_info = $model_goods_class->getKeyWords(intval($_GET['cate_id']));
                if (empty($seo_info[1])) {
                    $seo_info[1] = C('site_name') . ' - ' . $seo_class_name;
                }
                Model('seo')->type($seo_info)->param(array('name' => $seo_class_name))->show();
            }
        } elseif ($_GET['keyword'] != '') {
            $this->assign('html_title', (empty($_GET['keyword']) ? '' : $_GET['keyword'] . ' - ') . C('site_name') . L('spd_common_search'));
        }

        // 当前位置导航
        $nav_link_list = $model_goods_class->getGoodsClassNavsearch(intval($_GET['cate_id']));
        $this->assign('nav_link_list', $nav_link_list );

        // 得到自定义导航信息
		$is_search=1;
        $nav_id = intval($_GET['nav_id']) ? intval($_GET['nav_id']) : 0;
        $this->assign('$nav_id', $nav_id);
        $this->assign('is_search', $is_search);

        // 地区
        $province_array = Model('area')->getTopLevelAreas();
        $this->assign('province_array', $province_array);

		//分类热销
		$hot_goods_list = $model_goods->getGoodsOnlineList($condition, '*', 0, 'goods_salenum desc', 5);
		 $this->assign('hot_goods_list',$hot_goods_list);

        // 浏览过的商品
        $viewed_goods = Model('goods_browse')->getViewedGoodsList($_SESSION['member_id'],20);
        $this->assign('viewed_goods',$viewed_goods);
 
        $this->render('search');
    }

    /**
     * 获得推荐商品
     */
    public function get_booth_goods() {
        $gc_id = $_GET['cate_id'];
        if ($gc_id <= 0) {
            return false;
        }
        // 获取分类id及其所有子集分类id
        $goods_class = Model('goods_class')->getGoodsClassForCacheModel();
        if (empty($goods_class[$gc_id])) {
            return false;
        }
        $child = (!empty($goods_class[$gc_id]['child'])) ? explode(',', $goods_class[$gc_id]['child']) : array();
        $childchild = (!empty($goods_class[$gc_id]['childchild'])) ? explode(',', $goods_class[$gc_id]['childchild']) : array();
        $gcid_array = array_merge(array($gc_id), $child, $childchild);
        // 查询添加到推荐展位中的商品id
        $boothgoods_list = Model('p_booth')->getBoothGoodsList(array('gc_id' => array('in', $gcid_array)), 'goods_id', 0, 5, 'rand()');
        if (empty($boothgoods_list)) {
            return false;
        }

        $goodsid_array = array();
        foreach ($boothgoods_list as $val) {
            $goodsid_array[] = $val['goods_id'];
        }

        $fieldstr = "goods_id,goods_commonid,goods_name,goods_jingle,store_id,store_name,goods_price,goods_promotion_price,goods_promotion_type,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,color_id,evaluation_count";
        $goods_list = Model('goods')->getGoodsOnlineList(array('goods_id' => array('in', $goodsid_array)), $fieldstr);
        if (empty($goods_list)) {
            return false;
        }

        $this->assign('goods_list', $goods_list);
        $this->render('goods.booth', 'null_layout');
    }

    public function auto_complete() {
        exit(json_encode(Model('search')->autoComplete($_GET)));
    }

    /**
     * 获得猜你喜欢
     */
    public function get_guesslike(){
        $goodslist = Model('goods_browse')->getGuessLikeGoods($_SESSION['member_id'], 20);
        if(!empty($goodslist)){
            $this->assign('goodslist',$goodslist);
            $this->render('goods_guesslike','null_layout');
        }
    }

    /**
     * 商品分类推荐
     */
    public function get_gc_goods_recommend(){
        $rec_gc_id = intval($_GET['cate_id']);
        //只有最后一级才有推荐商品
        $class_info = Model('goods_class')->getGoodsClassListByParentId($rec_gc_id);
        if (!empty($class_info)) {
            return ;
        }
        $goods_list = array();
        if ($rec_gc_id > 0) {
            $rec_list = Model('goods_recommend')->getGoodsRecommendList(array('rec_gc_id'=>$rec_gc_id),'','','*','','rec_goods_id');
            if (!empty($rec_list)) {
                $goods_list = Model('goods')->getGoodsOnlineList(array('goods_id'=>array('in',array_keys($rec_list))));
                if (!empty($goods_list)) {
                    $this->assign('goods_list',$goods_list);
                    $this->render('goods_recommend','null_layout');
                }
            }
        }
    }
}