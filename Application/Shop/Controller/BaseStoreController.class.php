<?php
/********************************** 店铺 control父类 **********************************************/
namespace Shop\Controller;
use Shop\Controller\BaseController;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Page;


class BaseStoreController extends BaseController {

	protected $store_info;
	protected $store_decoration_only = false;

	public function __construct(){
		parent::__construct();
		Language::read('common,store_layout,store_show_store_index');

		if(!C('site_status')) halt(C('closed_reason'));

		//输出头部的公用信息
		$this->showLayout();
		$this->setDir('store');
		$this->setLayout('store_layout');

		//输出会员信息
		$this->getMemberAndGradeInfo(false);

		$store_id = intval($_GET['store_id']);
		if($store_id <= 0) {
			showMessage(L('spd_store_close'), '', '', 'error');
		}

		$model_store = Model('store');
		$store_info = $model_store->getStoreOnlineInfoByID($store_id);
		if(empty($store_info)) {
			showMessage(L('spd_store_close'), '', '', 'error');
		} else {
			$this->store_info = $store_info;
		}
		if($store_info['store_decoration_switch'] > 0 & $store_info['store_decoration_only'] == 1) {
			$this->store_decoration_only = true;
		}

		//店铺装修
		$this->outputStoreDecoration($store_info['store_decoration_switch'], $store_id);

		$this->outputStoreInfo($this->store_info);
		$this->getStoreNavigation($store_id);
		$this->outputSeoInfo($this->store_info);
	}

	/**
	 * 输出店铺装修
	 */
	protected function outputStoreDecoration($decoration_id, $store_id) {
		if($decoration_id > 0 ) {
			$model_store_decoration = Model('store_decoration');

			$decoration_info = $model_store_decoration->getStoreDecorationInfoDetail($decoration_id, $store_id);
			if($decoration_info) {
				$decoration_background_style = $model_store_decoration->getDecorationBackgroundStyle($decoration_info['decoration_setting']);
				$this->assign('decoration_background_style', $decoration_background_style);
				$this->assign('decoration_nav', $decoration_info['decoration_nav']);
				$this->assign('decoration_banner', $decoration_info['decoration_banner']);

				$html_file = BASE_UPLOAD_PATH.DS.ATTACH_STORE.DS.'decoration'.DS.'html'.DS.md5($store_id).'.html';
				if(is_file($html_file)) {
					$this->assign('decoration_file', $html_file);
				}

			}

			$this->assign('store_theme', 'default');
		} else {
			$this->assign('store_theme', $this->store_info['store_theme']);
		}
	}

	/**
	 * 检查店铺开启状态
	 *
	 * @param int $store_id 店铺编号
	 * @param string $msg 警告信息
	 */
	protected function outputStoreInfo($store_info, $goods_info = null) {
		if (!$this->store_decoration_only) {

			// 自营店设置“显示商城相关数据”
			if ($goods_info && $store_info['is_own_shop'] && $store_info['left_bar_type'] == 2) {
				$this->assign('left_bar_type_mall_related', true);

				// 推荐分类
				$mr_rel_gc = Model('goods_class')->getGoodsClassListBySiblingId($goods_info['gc_id']);
				$this->assign('mr_rel_gc', $mr_rel_gc);

				// 分类 含所有父级分类
				$gcIds = array();
				$gcIds[(int) $goods_info['gc_id_1']] = null;
				$gcIds[(int) $goods_info['gc_id_2']] = null;
				$gcIds[(int) $goods_info['gc_id_3']] = null;
				unset($gcIds[0]);
				$gcIds = array_keys($gcIds);

				// 推荐品牌
				$mr_rel_brand = null;
				if ($gcIds) {
					$mr_rel_brand = Model('brand')->getBrandPassedList(array(
							'class_id' => array('in', $gcIds),
					));
				}
				$this->assign('mr_rel_brand', $mr_rel_brand);

				// 同分类下销量排行
				$mr_hot_sales = null;
				if ($gcIds) {
					$mr_hot_sales = Model('goods')->getGoodsOnlineList(array(
							'gc_id' => array('in', $gcIds),
							'goods_id' => array('neq', $goods_info['goods_id']),
					), '*', 0, 'goods_salenum desc', 6);
				}
				$this->assign('mr_hot_sales', $mr_hot_sales);
				$gcArray = Model('goods_class')->getGoodsClassInfoById($goods_info['gc_id_1']);
				$this->assign('mr_hot_sales_gc_name', $gcArray['gc_name']);

				// 推荐商品
				$mr_rec_products = null;
				if ($gcIds) {
					$goodsIds = Model('p_booth')->getBoothGoodsIdRandList($gcIds, $goods_info['goods_id'], 6);
					if ($goodsIds) {
						$mr_rec_products = Model('goods')->getGoodsOnlineList(array(
								'goods_id' => array('in', $goodsIds),
						), '*', 0, '', 6);
					}
				}
				$this->assign('mr_rec_products', $mr_rec_products);
			} else {
				$model_store = Model('store');
				$model_seller = Model('seller');

				//热销排行
				$hot_sales = $model_store->getHotSalesList($store_info['store_id'], 5);
				$this->assign('hot_sales', $hot_sales);

				//收藏排行
				$hot_collect = $model_store->getHotCollectList($store_info['store_id'], 5);
				$this->assign('hot_collect', $hot_collect);
			}
		}

		//店铺分类
		$goodsclass_model = Model('store_goods_class');
		$goods_class_list = $goodsclass_model->getShowTreeList($store_info['store_id']);
		$this->assign('goods_class_list', $goods_class_list);

		$this->assign('store_info', $store_info);
		$this->assign('page_title', $store_info['store_name']);
	}

	protected function getStoreNavigation($store_id) {
		$model_store_navigation = Model('store_navigation');
		$store_navigation_list = $model_store_navigation->getStoreNavigationList(array('sn_store_id' => $store_id));
		$this->assign('store_navigation_list', $store_navigation_list);
	}

	protected function outputSeoInfo($store_info) {
		$seo_param = array();
		$seo_param['shopname'] = $store_info['store_name'];
		$seo_param['key']  = $store_info['store_keywords'];
		$seo_param['description'] = $store_info['store_description'];
		Model('seo')->type('shop')->param($seo_param)->show();
	}

}