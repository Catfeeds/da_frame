<?php
/**
 * 会员店铺
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


class ShowChainController extends BaseChainController {
    public function __construct(){
        parent::__construct();
    }
    /**
     * 展示门店
     */
    public function index() {
        $chain_id = intval($_GET['chain_id']);
        $chain_info = Model('chain')->getChainInfo(array('chain_id' => $chain_id));
        $this->assign('chain_info', $chain_info);
        $this->render('show_chain');
    }
}
