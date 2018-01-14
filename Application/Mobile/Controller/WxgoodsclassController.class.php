<?php
/**
 * 商品分类
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Mobile\Controller;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\Model;

class WxgoodsclassController extends WxbaseController {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        if(!empty($_GET['gc_id']) && intval($_GET['gc_id']) > 0) {
            $date = $this->_get_class_list($_GET['gc_id']);
            output_data($date);
        } else {
            $this->_get_root_class();
        }
    }
	

    /**
     * 返回一级分类列表
     */
    private function _get_root_class() {
        $model_goods_class = Model('goods_class');
        $model_mb_category = Model('mb_category');

        $goods_class_array = Model('goods_class')->getGoodsClassForCacheModel();

        $class_list = $model_goods_class->getGoodsClassListByParentId(0);
        $mb_categroy = $model_mb_category->getLinkList(array());
        $mb_categroy = array_under_reset($mb_categroy, 'gc_id');
        foreach ($class_list as $key => $value) {
            if(!empty($mb_categroy[$value['gc_id']])) {
                $class_list[$key]['image'] = UPLOAD_SITE_URL.DS.ATTACH_MOBILE.DS.'category'.DS.$mb_categroy[$value['gc_id']]['gc_thumb'];
            } else {
                $class_list[$key]['image'] = '';
            }

            $class_list[$key]['text'] = '';
            $child_class_string = $goods_class_array[$value['gc_id']]['child'];
            $child_class_array = explode(',', $child_class_string);
            foreach ($child_class_array as $child_class) {
                $class_list[$key]['text'] .= $goods_class_array[$child_class]['gc_name'] . '/';
            }
            $class_list[$key]['text'] = rtrim($class_list[$key]['text'], '/');
        }
 
        $ret = array('class_list' => $class_list, 'default_cat_id' => $class_list[0]['gc_id']);
        $ret['default_cat_list'] = $this->_get_class_list($class_list[0]['gc_id']);
        
        $ret['class_list'] = $this->fix_class_list($ret['class_list']);
        $ret['default_cat_list']['class_list'] = $this->fix_class_list($ret['default_cat_list']['class_list']);
        
        output_data($ret);
    }
    
    public function fix_class_list($class_list)
    {
    	$ret = array();
    	foreach ($class_list as $item)
    	{
    		if (empty($item['image']))
    		{
    			$item['image'] = '';//WAP_SITE_URL . "/images/degault.png";
    			$item['logo_word'] = mb_substr($item['gc_name'], 0, 1);
    		}
    		$ret[] = $item;
    	}
    	return $ret;
    }

    /**
     * 根据分类编号返回下级分类列表
     */
    private function _get_class_list($gc_id) {
        $goods_class_array = Model('goods_class')->getGoodsClassForCacheModel();

        $goods_class = $goods_class_array[$gc_id];

        if(empty($goods_class['child'])) {
            //无下级分类返回0
            return array('class_list' => array());
        } else {
            //返回下级分类列表
            $class_list = array();
            $child_class_string = $goods_class_array[$gc_id]['child'];
            $child_class_array = explode(',', $child_class_string);
            foreach ($child_class_array as $child_class) {
                $class_item = array();
                $class_item['gc_id'] .= $goods_class_array[$child_class]['gc_id'];
                $class_item['gc_name'] .= $goods_class_array[$child_class]['gc_name'];
                $class_item['logo_word'] = mb_substr($class_item['gc_name'], 0, 1);
                $class_list[] = $class_item;
            }
            return array('class_list' => $class_list);
        }
    }
    
    /**
     * 获取全部子集分类
     */
    public function get_child_all() {
        $gc_id = intval($_GET['gc_id']);
        $data = array();
        if ($gc_id > 0) {
            $data = $this->_get_class_list($gc_id);
            if (!empty($data['class_list'])) {
                foreach ($data['class_list'] as $key => $val) {
                     $d = $this->_get_class_list($val['gc_id']);
                     $data['class_list'][$key]['child'] = $d['class_list'];
                }
            }
        }
        $data['class_list'] = $this->fix_class_list($data['class_list']);
        output_data($data);
    }
}
