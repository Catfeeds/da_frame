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
namespace Circle\Controller;
use Circle\Controller\BaseCircleController;
use Common\Lib\Language;
use Common\Lib\Model;


 
class MemberCardController extends BaseCircleController {
	
	public function __construct()
	{
		parent::init_view();
	}
	
    public function mcard_info(){
        $uid    = intval($_GET['uid']);
        $member_list = Model()->table('circle_member')->field('member_id,circle_id,circle_name,cm_level,cm_exp')->where(array('member_id'=>$uid,'cm_state'=>1))->select();
        if(empty($member_list)){
            echo 'false';exit;
        }
        echo json_encode($member_list);exit;
    }
}
