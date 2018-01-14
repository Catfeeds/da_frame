<?php
namespace Circle\Controller;
use Circle\Controller\BaseCircleController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;


class BaseCircleThemeController extends BaseCircleController {
	protected $circle_info = array();   // 圈子详细信息
	protected $t_id = 0;        // 话题id
	protected $theme_info = array();    // 话题详细信息
	protected $r_id = 0;        // 回复id
	protected $reply_info = array();    // reply info
	protected $cm_info = array();       // Members of the information
	public function __construct(){
		parent::__construct();
		Language::read('circle');

		$this->c_id = intval($_GET['c_id']);
		if($this->c_id <= 0){
			@header("location: ".CIRCLE_SITE_URL);
		}
		$this->assign('c_id', $this->c_id);
	}
	/**
	 * 话题信息
	 */
	protected function themeInfo(){
		$this->t_id = intval($_GET['t_id']);
		if($this->t_id <= 0){
			@header("location: ".CIRCLE_SITE_URL);
		}
		$this->assign('t_id', $this->t_id);

		$this->theme_info = Model()->table('circle_theme')->where(array('circle_id'=>$this->c_id, 'theme_id'=>$this->t_id))->find();
		if(empty($this->theme_info)){
			showMessage(L('circle_theme_not_exists'), '', '', 'error');
		}
		$this->assign('theme_info', $this->theme_info);
	}
	/**
	 * 验证回复
	 */
	protected function checkReplySelf(){
		$this->t_id = intval($_GET['t_id']);
		if($this->t_id <= 0){
			showDialog(L('wrong_argument'));
		}
		$this->assign('t_id', $this->t_id);

		$this->r_id = intval($_GET['r_id']);
		if($this->r_id <= 0){
			showDialog(L('wrong_argument'));
		}
		$this->assign('r_id', $this->r_id);

		$this->reply_info = Model()->table('circle_threply')->where(array('theme_id'=>$this->t_id, 'reply_id'=>$this->r_id, 'member_id'=>$_SESSION['member_id']))->find();
		if(empty($this->reply_info)){
			showDialog(L('wrong_argument'));
		}
		$this->assign('reply_info', $this->reply_info);
	}
	/**
	 * 验证话题
	 */
	protected function checkThemeSelf(){
		$this->t_id = intval($_GET['t_id']);
		if($this->t_id <= 0){
			showDialog(L('wrong_argument'));
		}
		$this->assign('t_id', $this->t_id);

		$this->theme_info = Model()->table('circle_theme')->where(array('theme_id'=>$this->t_id, 'member_id'=>$_SESSION['member_id']))->find();
		if(empty($this->theme_info)){
			showDialog(L('wrong_argument'));
		}
		$this->assign('theme_info', $this->theme_info);
	}
}