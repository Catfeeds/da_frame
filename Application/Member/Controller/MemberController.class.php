<?php
/**
 * 会员中心——账户概览
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Member\Controller;
use Member\Controller\BaseMemberController;
use Common\Lib\Language;
use Common\Lib\Model;


class MemberController extends BaseMemberController{

    /**
     * 我的商城
     */
    public function home() {
        $model_consume = Model('consume');
        $consume_list = $model_consume->getConsumeList(array('member_id' => $_SESSION['member_id']), '*', 0, 10);
        $this->assign('consume_list', $consume_list);
        $this->render('member_home');
    }
}
