<?php
/**
 * 操作中心
 * @author Administrator
 *
 */

namespace Delivery\Controller;
use Delivery\Controller\BaseDeliveryController;
use Common\Lib\Language;
use Common\Lib\Log;


class BaseDeliveryCenterController extends BaseDeliveryController{
	public function __construct() {
		parent::__construct();
		if ($_SESSION['delivery_login'] != 1) {
			@header("location: {$GLOBALS['_PAGE_URL']}&c=Login");die;
		}
	}
}