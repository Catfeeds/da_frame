<?php
/**
 * 操作中心
 * @author Administrator
 *
 */
namespace Chain\Controller;
use Chain\Controller\BaseChainController;
use Common\Lib\Language;
use Common\Lib\Log;



class BaseChainCenterController extends BaseChainController {
	public function __construct() {
		parent::__construct();
		if ($_SESSION['chain_login'] != 1) {
			$page_url = $GLOBALS['_PAGE_URL'];
			@header("location: $page_url&c=Login");die;
		}
	}
}