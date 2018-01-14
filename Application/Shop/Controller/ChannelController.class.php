<?php
/**
 * 频道
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Model;

class ChannelController extends BaseHomeController {
    public function __construct() {
        parent::__construct();
    }
    /**
     * 频道页
     *
     */
    public function index() {
        $model_channel = Model('web_channel');
        $condition = array();
        $condition['channel_id'] = intval($_GET['id']);
        $condition['channel_show'] = 1;
        $channel_list = $model_channel->getChannelList($condition);
        $channel = $channel_list[0];
        $this->assign('channel',$channel);
        if ($channel['gc_id'] > 0) {
            $gc_id = $channel['gc_id'];
            $model_class = Model('goods_class');
            $class_array = $model_class->getGoodsClassInfoById($gc_id);
            $this->assign('gc_name',$class_array['gc_name']);
            $this->assign('gc_id',$gc_id);
        }
        $web_html = $model_channel->getChannelHtml($channel);
        $this->assign('web_html',$web_html);
        $this->assign('html_title',$channel['channel_name'].' - '.C('site_name'));
        $this->assign('seo_keywords',$channel['keywords'] ? $channel['keywords'] : C('site_name'));
        $this->assign('seo_description',$channel['description'] ? $channel['description'] : C('site_name'));
        $this->render('channel');
    }
    /**
     * 促销
     *
     */
    public function get_right_list(){
        $gc_id = intval($_GET['gc_id']);
        $condition = array();
        $condition['gc_id'] = $gc_id;
        $condition['goods_promotion_type'] = 1;//促销类型 0无促销，1抢购，2限时折扣
        $model_goods = Model('goods');
        $field = 'goods_commonid';
        $goods_list = array();
        $list = $model_goods->getGoodsListByColorDistinct($condition,$field,'goods_edittime desc',99);

        if(!empty($list) && is_array($list)) {
            foreach($list as $k => $v) {
                $goods_commonid = $v['goods_commonid'];
                $goods_list[$goods_commonid] = $goods_commonid;
            }
        }

        Language::read('member_groupbuy');
        $model_groupbuy = Model('groupbuy');
        $condition = array();
        $condition['goods_commonid'] = array('in',$goods_list);
        $condition['is_vr'] = 0;
        $group_list = $model_groupbuy->getGroupbuyOnlineList($condition, 5, 'recommended desc');
        $this->assign('group_list', $group_list);

        if (empty($group_list)) {//无抢购数据时调用限时折扣
            $condition = array();
            $condition['gc_id'] = $gc_id;
            $condition['goods_promotion_type'] = 2;//促销类型 0无促销，1抢购，2限时折扣
            $model_goods = Model('goods');
            $field = 'goods_id';
            $goods_list = array();
            $list = $model_goods->getGoodsOnlineList($condition,$field,99,'goods_edittime desc');

            if(!empty($list) && is_array($list)) {
                foreach($list as $k => $v) {
                    $goods_id = $v['goods_id'];
                    $goods_list[$goods_id] = $goods_id;
                }
            }
            $model_xianshi_goods = Model('p_xianshi_goods');
            $condition = array();
            $condition['goods_id'] = array('in',$goods_list);
            $xianshi_item = $model_xianshi_goods->getXianshiGoodsExtendList($condition,5,'xianshi_recommend desc');
            $this->assign('xianshi_item', $xianshi_item);
        }

        $this->render('channel_right','null_layout');
    }

}
