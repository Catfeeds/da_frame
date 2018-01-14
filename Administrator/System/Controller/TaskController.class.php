<?php
/**
 * 计划任务
 * 大商城   by shopda.cn
 **/
namespace System\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Validate;


class TaskController extends SystemController {
    public function __construct()
    {
        parent::__construct();
        Language::read('task', 'Home');
    }
	
	public function index() {
        $this->lists();
    }
    public function lists()
    {
        $a = Model()->table('task')->select();
        $this->assign('task_list', $a);
		$this->setDirquna('system');
        $this->render('task.index');
    }
    public function del()
    {
        Model()->table('task')->where(array('id' => $_GET['id']))->delete();
        showMessage(Language::get('spd_common_del_succ'));
    }
    public function add()
    {
        if (chksubmit()) {
            $b = new Validate();
            $b->validateparam = array(array('input' => $_POST['taskname'], 'require' => 'true', 'message' => '请填写任务名称！'), array('input' => $_POST['dourl'], 'require' => 'true', 'message' => '请填写运行程序！'));
            $c = $b->validate();
            if ($c != '') {
                showMessage($c);
            } else {
                $d = $_POST['h'] . ':' . $_POST['i'] . ':' . $_POST['s'];
                $e = empty($_POST['starttime']) ? 0 : strtotime($_POST['starttime']);
                $f = empty($_POST['endtime']) ? 0 : strtotime($_POST['endtime']);
                $g = array();
                $g['taskname'] = $_POST['taskname'];
                $g['dourl'] = $_POST['dourl'];
                $g['islock'] = trim($_POST['islock']);
                $g['description'] = $_POST['description'];
                $g['runtype'] = trim($_POST['runtype']);
                $g['runtime'] = $d;
                $g['starttime'] = $e;
                $g['endtime'] = $f;
                $g['freq'] = $_POST['freq'];
                $g['parameter'] = $_POST['parameter'];
                $g['lastrun'] = '0';
                $g['settime'] = time();
                $h = Model('task')->insert($g);
                if ($h) {
                    showMessage(Language::get('spd_common_save_succ'));
                } else {
                    showMessage('增加任务失败!');
                }
            }
        }
		$this->setDirquna('system');
        $this->render('task_add');
    }
    public function edit()
    {
        if (chksubmit()) {
  
        	
            $b = new Validate();
            $b->validateparam = array(array('input' => $_POST['taskname'], 'require' => 'true', 'message' => '请填写任务名称！'), 
            		array('input' => $_POST['dourl'], 'require' => 'true', 'message' => '请填写运行程序！'));
            $c = $b->validate();
            if ($c != '') {
                showMessage($c);
            } else {
                $d = $_POST['h'] . ':' . $_POST['i'] . ':' . $_POST['s'];
                $e = empty($_POST['starttime']) ? 0 : strtotime($_POST['starttime']);
                $f = empty($_POST['endtime']) ? 0 : strtotime($_POST['endtime']);
                $g = array();
                $g['taskname'] = $_POST['taskname'];
                $g['dourl'] = $_POST['dourl'];
                $g['islock'] = trim($_POST['islock']);
                $g['description'] = $_POST['description'];
                $g['runtype'] = trim($_POST['runtype']);
                $g['runtime'] = $d;
                $g['starttime'] = $e;
                $g['endtime'] = $f;
                $g['freq'] = $_POST['freq'];
                $g['parameter'] = $_POST['parameter'];
 
                $h = Model('task')->where(array('id' => $_GET['id']))->update($g);
//                 var_dump($h);
//                 exit;
                
                if ($h) {
                    showMessage(Language::get('spd_common_save_succ'));
                } else {
                    showMessage('修改任务失败!');
                }
            }
        }
        $i = Model()->table('task')->where(array('id' => $_GET['id']))->find();
        $this->assign('task', $i);
		$this->setDirquna('system');
        $this->render('task_edit');
    }
}
