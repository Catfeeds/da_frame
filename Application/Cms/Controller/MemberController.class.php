<?php
/**
 * APP会员
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Cms\Controller;
use Common\Lib\Language;
use Think\Controller;


class MemberController extends Controller{

    public function __construct(){
		parent::__construct();
    }

    public function info(){
        if (!empty($_GET['uid'])){
            $member_info = spd_member_info($_GET['uid'],'uid');
        }elseif(!empty($_GET['user_name'])){
            $member_info = spd_member_info($_GET['user_name'],'user_name');
        }
        return $member_info;
    }
}
