<?php
/**
 * 商家注销
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Mobile\Controller;
use Mobile\Controller\MobileSellerController;
use Common\Lib\Language;
use Common\Lib\Log;


class SellerAlbumController extends MobileSellerController {

    public function __construct(){
        parent::__construct();
    }

    public function image_upload() {
        $logic_goods = Logic('goods');

        $result =  $logic_goods->uploadGoodsImage(
            $_POST['name'],
            $this->seller_info['store_id'],
            $this->store_grade['sg_album_limit']
        );

        if(!$result['state']) {
            output_error($result['msg']);
        }
		output_data($result['data']);
        //output_data(array('image_name' => $result['data']['name']));
    }

}
