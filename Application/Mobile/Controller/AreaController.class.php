<?php
/**
 * 地区
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Mobile\Controller;
use Mobile\Controller\MobileHomeController;
use Common\Lib\Language;
use Common\Lib\Model;


class AreaController extends MobileHomeController{

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->area_list();
    }

    /**
     * 地区列表
     */
    public function area_list() {
        $area_id = intval($_GET['area_id']);

        $model_area = Model('area');

        $condition = array();
        if($area_id > 0) {
            $condition['area_parent_id'] = $area_id;
        } else {
            $condition['area_deep'] = 1;
        }
        $area_list = $model_area->getAreaList($condition, 'area_id,area_name');
        output_data(array('area_list' => $area_list));
    }

}
