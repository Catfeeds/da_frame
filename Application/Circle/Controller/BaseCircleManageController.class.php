<?php
namespace Circle\Controller;
use Circle\Controller\BaseCircleController;
use Common\Lib\Language;
use Common\Lib\Model;


class BaseCircleManageController extends BaseCircleController {
	protected $circle_info = array();   // 圈子详细信息
	protected $t_id = 0;        // 话题id
	protected $theme_info = array();    // 话题详细信息
	protected $identity = 0;    // 身份 0游客 1圈主 2管理 3成员
	protected $cm_info = array();   // 会员信息
	public function __construct(){
		parent::__construct();
		$this->c_id = intval($_GET['c_id']);
		if($this->c_id <= 0){
			@header("location: ".CIRCLE_SITE_URL);
		}
		$this->assign('c_id', $this->c_id);
	}
	/**
	 * 圈子信息
	 */
	protected function circleInfo(){
		// 圈子信息
		$this->circle_info = Model()->table('circle')->where(array('circle_id'=>$this->c_id))->find();
		if(empty($this->circle_info)) @header("location: ".CIRCLE_SITE_URL);
		$this->assign('circle_info', $this->circle_info);
	}
	/**
	 * 会员信息
	 */
	protected function circleMemberInfo(){
		// 会员信息
		$this->cm_info = Model()->table('circle_member')->where(array('circle_id'=>$this->c_id, 'member_id'=>$_SESSION['member_id']))->find();
		if(!empty($this->cm_info)){
			$this->identity = $this->cm_info['is_identity'];
			$this->assign('cm_info', $this->cm_info);
		}
		if(in_array($this->identity, array(0,3))){
			@header("location: ".CIRCLE_SITE_URL);
		}
		$this->assign('identity', $this->identity);
	}
	/**
	 * 去除圈主
	 */
	protected function removeCreator($array){
		return array_diff($array, array($this->cm_info['member_id']));
	}
	/**
	 * 去除圈主和管理
	 */
	protected function removeManager($array){
		$where = array();
		$where['is_identity']   = array('in', array(1,2));
		$where['circle_id']     = $this->c_id;
		$cm_info = Model()->table('circle_member')->where($where)->select();
		if(empty($cm_info)){
			return $array;
		}
		foreach ($cm_info as $val){
			$array = array_diff($array, array($val['member_id']));
		}
		return $array;
	}
	/**
	 * 身份验证
	 */
	protected function checkIdentity($type){        // c圈主 m管理 cm圈主和管理
		$this->cm_info = Model()->table('circle_member')->where(array('circle_id'=>$this->c_id, 'member_id'=>$_SESSION['member_id']))->find();
		$identity = intval($this->cm_info['is_identity']); $sign = false;
		switch ($type){
			case 'c':
				if($identity != 1) $sign = true;
				break;
			case 'm':
				if($identity != 2) $sign = true;
				break;
			case 'cm':
				if($identity != 1 && $identity != 2) $sign = true;
				break;
			default:
				$sign = true;
				break;
		}
		if ($this->super) {
			$sign = false;
		}
		if($sign){
			return L('circle_permission_denied');
		}
	}
	/**
	 * 会员加入的圈子
	 */
	protected function memberJoinCircle(){
		// 所属圈子信息
		$circle_array = Model()->table('circle,circle_member')->field('circle.*,circle_member.is_identity')
		->join('inner')->on('circle.circle_id=circle_member.circle_id')
		->where(array('circle_member.member_id'=>$_SESSION['member_id']))->select();
		$this->assign('circle_array', $circle_array);
	}
	/**
	 * Top Navigation
	 */
	protected  function sidebar_menu($sign, $child_sign=''){
		$menu = array(
				'index'=>array('menu_name'=>L('circle_basic_setting'), 
				'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=Manage&c_id='.$this->c_id),
				'member'=>array('menu_name'=>L('circle_member_manage'), 
				'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=Manage&a=member_manage&c_id='.$this->c_id),
				'applying'=>array('menu_name'=>L('circle_wait_apply'), 'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=Manage&a=applying&c_id='.$this->c_id),
				'level'=>array('menu_name'=>L('circle_member_level'), 'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=ManageLevel&a=level&c_id='.$this->c_id),
				'class'=>array('menu_name'=>L('circle_tclass'), 'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=Manage&a=classes&c_id='.$this->c_id),
				'inform'=>array(
						'menu_name'=>L('circle_inform'),
						'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=ManageInform&a=inform&c_id='.$this->c_id,
						'menu_child'=>array(
								'untreated'=>array('name'=>L('circle_inform_untreated'), 'url'=>$GLOBALS['_PAGE_URL'] . '&c=ManageInform&a=inform&c_id='.$this->c_id),
								'treated'=>array('name'=>L('circle_inform_treated'), 'url'=>$GLOBALS['_PAGE_URL'] . '&c=ManageInform&a=inform&type=treated&c_id='.$this->c_id)
						),
				),
				'managerapply'=>array('menu_name'=>L('circle_mapply'), 'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=ManageMapply&c_id='.$this->c_id),
				'friendship'=>array('menu_name'=>L('fcircle'), 'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=Manage&a=friendship&c_id='.$this->c_id)
		);
		if($this->identity == 2){
			unset($menu['index']);unset($menu['member']);unset($menu['level']);unset($menu['class']);unset($menu['friendship']);
			unset($menu['inform']['menu_child']['untreated']);unset($menu['managerapply']);
		}
		$this->assign('sidebar_menu', $menu);
		$this->assign('sidebar_sign', $sign);
		$this->assign('sidebar_child_sign', $child_sign);
	}
}