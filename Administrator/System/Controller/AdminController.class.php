<?php
/**
 * 权限管理
 *
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace System\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;



class AdminController extends SystemController {
    private $links;
	
    public function __construct(){
        parent::__construct();
        Language::read('admin', 'Home');
		
		$this->links = array(
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Admin&a=admin','lang'=>'limit_admin'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Admin&a=gadmin','lang'=>'limit_gadmin'),
         );
    }

    public function index() {
        $this->admin();
    }

    /**
     * 管理员列表
     */
    public function admin(){
        $model = Model();
        $admin_list = $model->table('admin,gadmin')
        ->join('left join')
        ->on('gadmin.gid=admin.admin_gid')
        ->limit(200)
        ->select();

        $this->assign('admin_list',$admin_list);
        $this->assign('page',$model->showpage());

        $this->assign('top_link',$this->sublink($this->links,'admin'));
		$this->setDirquna('system');
        $this->render('admin.index');
    }

    /**
     * 管理员删除
     */
    public function admin_del(){
        if (!empty($_GET['admin_id'])){
            if ($_GET['admin_id'] == 1){
                showMessage(L('spd_common_save_fail'));
            }
            Model()->table('admin')->where(array('admin_id'=>intval($_GET['admin_id'])))->delete();
            $this->log(L('spd_delete,limit_admin').'[ID:'.intval($_GET['admin_id']).']',1);
            showMessage(L('spd_common_del_succ'));
        }else {
            showMessage(L('spd_common_del_fail'));
        }
    }

    /**
     * 管理员添加
     */
    public function admin_add(){
        if (chksubmit()){
            $limit_str = '';
            $model_admin = Model('admin');
            $param['admin_name'] = $_POST['admin_name'];
            $param['admin_gid'] = $_POST['gid'];
            $param['admin_password'] = md5($_POST['admin_password']);
            $rs = $model_admin->addAdmin($param);
            if ($rs){
                $this->log(L('spd_add,limit_admin').'['.$_POST['admin_name'].']',1);
                showMessage(L('spd_common_save_succ'),$GLOBALS['_PAGE_URL'] . '&c=Admin&a=admin');
            }else {
                showMessage(L('spd_common_save_fail'));
            }
        }

        //得到权限组
        $gadmin = Model('gadmin')->field('gname,gid')->select();
        $this->assign('gadmin',$gadmin);
        $this->assign('top_link',$this->sublink($this->links,'admin_add'));
        $this->assign('limit',$this->permission());
		$this->setDirquna('system');
        $this->render('admin.add');
    }

    /**
     * ajax操作
     */
    public function ajax(){
        switch ($_GET['branch']){
            //管理人员名称验证
            case 'check_admin_name':
                $model_admin = Model('admin');
                $condition['admin_name'] = $_GET['admin_name'];
                $list = $model_admin->infoAdmin($condition);
                if (!empty($list)){
                    exit('false');
                }else {
                    exit('true');
                }
                break;
            //权限组名称验证
            case 'check_gadmin_name':
                $condition = array();
                if (is_numeric($_GET['gid'])){
                    $condition['gid'] = array('neq',intval($_GET['gid']));
                }
                $condition['gname'] = $_GET['gname'];
                $info = Model('gadmin')->where($condition)->find();
                if (!empty($info)){
                    exit('false');
                }else {
                    exit('true');
                }
                break;
        }
    }

    /**
     * 设置管理员权限
     */
    public function admin_edit(){
        if (chksubmit()){
            //没有更改密码
            if ($_POST['new_pw'] != ''){
                $data['admin_password'] = md5($_POST['new_pw']);
            }
            $data['admin_id'] = intval($_GET['admin_id']);
            $data['admin_gid'] = intval($_POST['gid']);
            //查询管理员信息
            $admin_model = Model('admin');
            $result = $admin_model->updateAdmin($data);
            if ($result){
                $this->log(L('spd_edit,limit_admin').'[ID:'.intval($_GET['admin_id']).']',1);
                showMessage(Language::get('admin_edit_success'),$GLOBALS['_PAGE_URL'] . '&c=Admin&a=admin');
            }else{
                showMessage(Language::get('admin_edit_fail'),$GLOBALS['_PAGE_URL'] . '&c=Admin&a=admin');
            }
        }else{
            //查询用户信息
            $admin_model = Model('admin');
            $admininfo = $admin_model->getOneAdmin(intval($_GET['admin_id']));
            if (!is_array($admininfo) || count($admininfo)<=0){
                showMessage(Language::get('admin_edit_admin_error'),$GLOBALS['_PAGE_URL'] . '&c=Admin&a=admin');
            }
            $this->assign('admininfo',$admininfo);
            $this->assign('top_link',$this->sublink($this->links,'admin'));

            //得到权限组
            $gadmin = Model('gadmin')->field('gname,gid')->select();
            $this->assign('gadmin',$gadmin);
			$this->setDirquna('system');
            $this->render('admin.edit');
        }
    }

    /**
     * 取得所有权限项
     *
     * @return array
     */
    private function permission() {
        return rkcache('admin_menu', true);
    }

    /**
     * 权限组
     */
    public function gadmin(){
        $model = Model('gadmin');
        if (chksubmit()){
            if (@in_array(1,$_POST['del_id'])){
                showMessage(L('admin_index_not_allow_del'));
            }

            if (!empty($_POST['del_id'])){
                if (is_array($_POST['del_id'])){
                    foreach ($_POST['del_id'] as $k => $v){
                        $model->where(array('gid'=>intval($v)))->delete();
                    }
                }
                $this->log(L('spd_delete,limit_gadmin').'[ID:'.implode(',',$_POST['del_id']).']',1);
                showMessage(L('spd_common_del_succ'));
            }else {
                showMessage(L('spd_common_del_fail'));
            }
        }
        $list = $model->limit(100)->select();

        $this->assign('list',$list);
        $this->assign('page',$model->showpage());

        $this->assign('top_link',$this->sublink($this->links,'gadmin'));
		$this->setDirquna('system');
        $this->render('gadmin.index');
    }

    /**
     * 添加权限组
     */
    public function gadmin_add(){
        if (chksubmit()){
            $model = Model('gadmin');
            $data['limits'] = encrypt(serialize($_POST['permission']),MD5_KEY.md5($_POST['gname']));
            $data['gname'] = $_POST['gname'];
            if ($model->insert($data)){
                $this->log(L('spd_add,limit_gadmin').'['.$_POST['gname'].']',1);
                showMessage(L('spd_common_save_succ'),$GLOBALS['_PAGE_URL'] . '&c=Admin&a=gadmin');
            }else {
                showMessage(L('spd_common_save_fail'));
            }
        }
        $this->assign('top_link',$this->sublink($this->links,'gadmin_add'));
        $this->assign('limit',$this->permission());
		$this->setDirquna('system');
        $this->render('gadmin.add');
    }

    /**
     * 设置权限组权限
     */
    public function gadmin_edit(){
        $model = Model('gadmin');
        $gid = intval($_GET['gid']);

        $ginfo = $model->getby_gid($gid);
        if (empty($ginfo)){
            showMessage(L('admin_set_admin_not_exists'));
        }
        if (chksubmit()){
            $limit_str = '';
            $limit_str = encrypt(serialize($_POST['permission']),MD5_KEY.md5($_POST['gname']));
            $data['limits'] = $limit_str;
            $data['gname']  = $_POST['gname'];
            $update = $model->where(array('gid'=>$gid))->update($data);
            if ($update){
                $this->log(L('spd_edit,limit_gadmin').'['.$_POST['gname'].']',1);
                showMessage(L('spd_common_save_succ'),$GLOBALS['_PAGE_URL'] . '&c=Admin&a=gadmin');
            }else {
                showMessage(L('spd_common_save_succ'));
            }
        }

        //解析已有权限
        $hlimit = decrypt($ginfo['limits'],MD5_KEY.md5($ginfo['gname']));
        $ginfo['limits'] = unserialize($hlimit);
        $this->assign('ginfo',$ginfo);
        $this->assign('limit',$this->permission());
        $this->assign('top_link',$this->sublink($this->links,'gadmin'));
		$this->setDirquna('system');
        $this->render('gadmin.edit');
    }

    /**
     * 组删除
     */
    public function gadmin_del(){
        if (is_numeric($_GET['gid'])){
            Model('gadmin')->where(array('gid'=>intval($_GET['gid'])))->delete();
            $this->log(L('spd_delete,limit_gadmin').'[ID'.intval($_GET['gid']).']',1);
            showMessage(Language::get('spd_common_op_succ'),$GLOBALS['_PAGE_URL'] . '&c=Admin&a=gadmin');
        }else {
            showMessage(L('spd_common_op_fail'));
        }
    }
}
