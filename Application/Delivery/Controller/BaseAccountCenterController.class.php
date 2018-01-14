<?php
/**
 * 物流自提服务站父类
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

/**
 * 操作中心
 * @author Administrator
 *
 */
namespace Delivery\Controller;
use Delivery\Controller\BaseDeliveryController;
use Common\Lib\Language;
use Common\Lib\Log;

 
class BaseAccountCenterController extends BaseDeliveryController {
    public function __construct() {
        parent::__construct();

        $this->setLayout('login_layout');
    }
}
