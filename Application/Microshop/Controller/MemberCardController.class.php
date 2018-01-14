<?php
/**
 * The AJAX call member information
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Microshop\Controller;
use Microshop\Controller\MircroShopController;
use Common\Lib\Language;
use Common\Lib\Model;


class MemberCardController extends MircroShopController{
    public function mcard_info(){
        $uid    = intval($_GET['uid']);
        if($uid <= 0) {
            echo 'false';exit;
        }
        $model_micro_member_info = Model('micro_member_info');
        $micro_member_info = $model_micro_member_info->getOneById($uid);
        if(empty($micro_member_info)){
            echo 'false';exit;
        }
        echo json_encode($micro_member_info);exit;
    }
}
