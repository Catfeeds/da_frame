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
use Common\Lib\Model;
use Common\Lib\Page;


class PromotionController extends BaseHomeController {
    const PAGESIZE = 16;
    public function index() {
        $model_xianshi_goods = Model('p_xianshi_goods');
        $model_goods = Model('goods');

        $condition = array();
        $condition['state'] = 1;
        $condition['start_time'] = array('elt',TIMESTAMP);
        $condition['end_time'] = array('gt',TIMESTAMP);
        if ($_GET['gc_id']) {
            $condition['gc_id_1'] = intval($_GET['gc_id']);
        }

        $goods_list = $model_xianshi_goods->getXianshiGoodsExtendList($condition,self::PAGESIZE,'xianshi_goods_id desc');
        $total_page = pagecmd('gettotalpage');
        if (intval($_GET['curpage'] > $total_page)) {
            exit();
        }
        $xs_goods_list = array();
        foreach ($goods_list as $k => $goods_info) {
            $xs_goods_list[$goods_info['goods_id']] = $goods_info;
            $xs_goods_list[$goods_info['goods_id']]['image_url_240'] = cthumb($goods_info['goods_image'], 240, $goods_info['store_id']);
            $xs_goods_list[$goods_info['goods_id']]['down_price'] = $goods_info['goods_price'] - $goods_info['xianshi_price'];
        }
        $condition = array('goods_id' => array('in',array_keys($xs_goods_list)));
        $goods_list = $model_goods->getGoodsOnlineList($condition, 'goods_id,gc_id_1,evaluation_good_star,store_id,store_name', 0, '', self::PAGESIZE, null, false);
        foreach ($goods_list as $k => $goods_info) {
            $xs_goods_list[$goods_info['goods_id']]['evaluation_good_star'] = $goods_info['evaluation_good_star'];
            $xs_goods_list[$goods_info['goods_id']]['store_name'] = $goods_info['store_name'];
            if ($xs_goods_list[$goods_info['goods_id']]['gc_id_1'] != $goods_info['gc_id_1']) {
                //兼容以前版本，如果限时商品表没有保存一级分类ID，则马上保存
                $model_xianshi_goods->editXianshiGoods(array('gc_id_1'=>$goods_info['gc_id_1']),array('xianshi_goods_id'=>$xs_goods_list[$goods_info['goods_id']]['xianshi_goods_id']));
            }
        }

        //查询商品评分信息
        $goodsevallist = Model("evaluate_goods")->getEvaluateGoodsList(array('geval_goodsid'=>array('in',array_keys($xs_goods_list))));
        $eval_list = array();
        if (!empty($goodsevallist)) {
            foreach ($goodsevallist as $v) {
                if($v['geval_content'] == '' || count($eval_list[$v['geval_goodsid']]) >=2) continue;
                $eval_list[$v['geval_goodsid']][] = $v;
            }
        }
        $this->assign('goodsevallist',$eval_list);

        $this->assign('goods_list', $xs_goods_list);
        if (!empty($_GET['curpage'])) {
            $this->render('promotion.item','null_layout');
        } else {

            //导航
            $nav_link = array(
                    0=>array(
                            'title'=>Language::get('homepage'),
                            'link'=>SHOP_SITE_URL,
                    ),
                    1=>array(
                            'title'=>'限时折扣'
                    )
            );
            $this->assign('nav_link_list',$nav_link);

            //查询商品分类
            $goods_class = Model('goods_class')->getGoodsClassListByParentId(0);
            $this->assign('goods_class', $goods_class);

            $this->assign('total_page',pagecmd('gettotalpage'));
            $this->render('promotion');
        }
    }

}