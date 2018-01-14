<?php
/**
 * 圈子父类
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Circle\Controller;
use Circle\Controller\BaseCircleController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;



class BaseCirclePersonalController extends BaseCircleController {
    protected  $m_id = 0;   // memeber ID
    public function __construct(){
        parent::__construct();
        if(!$_SESSION['is_login']){
            @header("location: ".CIRCLE_SITE_URL);
        }
        $this->m_id = $_SESSION['member_id'];

        // member information
        $this->circleMemberInfo();
    }
    /**
     * member information
     */
    protected function circleMemberInfo(){
        // member information list
        $circlemember_list = Model()->table('circle_member')->where(array('member_id'=>$this->m_id))->select();

        $data = array();
        $data['cm_thcount']     = 0;
        $data['cm_comcount']    = 0;
        $data['member_id']      = $_SESSION['member_id'];
        $data['member_name']    = $_SESSION['member_name'];
        if(!empty($circlemember_list)){
            foreach ($circlemember_list as $val){
                $data['cm_thcount']     += $val['cm_thcount'];
                $data['cm_comcount']    += $val['cm_comcount'];
            }
        }
        $this->assign('cm_info', $data);
    }

}
