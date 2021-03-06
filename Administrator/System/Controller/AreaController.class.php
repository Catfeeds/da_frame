<?php
/**
 * 系统操作日志
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
use Common\Lib\Tpl;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;


class AreaController extends SystemController {

    public function __construct(){
        parent::__construct();
    }

    public function index() {
		$this->setDirquna('system');
        $this->render('area.index');
    }

    public function get_xml() {
        $model_area = Model('area');
        $condition  = array();
        if ($_POST['query'] != '') {
            $condition[$_POST['qtype']] = array('like', '%' . $_POST['query'] . '%');
        } else {
            $condition['area_parent_id'] = intval($_GET['parent_id']);
        }
        $list = $model_area->getAreaList($condition,'*','',$_POST['rp']);
        $data = array();
        $data['now_page'] = $model_area->shownowpage();
        $data['total_num'] = $model_area->gettotalnum();
        foreach ($list as $k => $info) {
            $list = array();$operation_detail = '';
            $list['operation'] = "<a class='btn red' onclick=\"fg_delete({$info['area_id']})\"><i class='fa fa-trash-o'></i>删除</a>";
            $operation_detail .= "<li><a href=\"{$GLOBALS['_PAGE_URL']}&c=Area&a=edit&area_id={$info['area_id']}\">编辑地区</a></li>";
            if ($info['area_deep'] <= 3) {
                $operation_detail .= "<li><a href=\"{$GLOBALS['_PAGE_URL']}&c=Area&a=add&parent_id={$info['area_id']}\">新增下级</a></li>";
                $operation_detail .= "<li><a href=\"javascript:void(0);\" onclick=\"fg_show_children({$info['area_id']},{$info['area_parent_id']})\">查看下级</a></li>";
            }
            $list['operation'] .= "<span class='btn'><em><i class='fa fa-cog'></i>设置 <i class='arrow'></i></em><ul>{$operation_detail}</ul>";
            $list['area_name'] = $info['area_name'];
            $list['area_region'] = $info['area_region'];
            $list['area_deep'] = $info['area_deep'];
            $list['area_parent_id'] = $info['area_parent_id'];
            $data['list'][$info['area_id']] = $list;
        }
        exit($this->flexigridXML($data));
    }

    public function add() {
        $info = array();
        $model_area = Model('area');
        if (isset($_GET['parent_id'])) {
            $info = $model_area->getAreaInfo(array('area_id'=>intval($_GET['parent_id'])));
            $data = array();
            $data['area_parent_id'] = $info['area_id'];
            $data['area_deep'] = $info['area_deep']+1;
            $data['area_parent_name'] = $model_area->getTopAreaName($_GET['parent_id']);
            $this->assign('info',$data);
        }
		$this->setDirquna('system');
        $this->render('area.add');
    }

    public function save() {
        if (!chksubmit()) return ;
        $model_area = Model('area');
        $data = array();
        $data['area_name'] = $_POST['area_name'];
        $data['area_region'] = $_POST['area_region'];
        if ($_GET['area_id']) {
            $result = $model_area->editArea($data,array('area_id'=>intval($_GET['area_id'])));
        } else {
            $data['area_parent_id'] = $_POST['parent_id'];
            $data['area_deep'] = intval($_POST['area_deep']);
            $result = $model_area->addArea($data);
        }
        if ($result) {
            showMessage('操作成功',$_GET['area_id'] ? ($GLOBALS['_PAGE_URL'] . '&c=Area') : '');
        } else {
            showMessage('操作失败','','html','error');
        }
    }

    public function edit() {
        $area_id = intval($_GET['area_id']);
        if ($area_id <= 0) {
            showMessage('参数错误','','html','error');
        }

        $model_area = Model('area');
        $info = $model_area->getAreaInfo(array('area_id'=>$area_id));
        if (!$info) {
            showMessage('该地区不存在','','html','error');
        }
        $info['area_parent_name'] = $model_area->getTopAreaName($info['area_parent_id']);

        $this->assign('info',$info);
		$this->setDirquna('system');
        $this->render('area.edit');
    }

    public function del(){
        $model_area = Model('area');
        $condition = array();
        if (preg_match('/^[\d,]+$/', $_GET['area_id'])) {
            $area_ids = explode(',',trim($_GET['area_id'],','));
            foreach ($area_ids as $v) {
                $area_ids = array_merge($area_ids,$model_area->getChildrenIDs($v));
            }
            $condition['area_id'] = array('in',$area_ids);
        }else{
            $condition['area_id'] = intval($_GET['area_id']);
        }
        $result = $model_area->delArea($condition);
        if ($result) {
            $this->log('删除地区',1);
            exit(json_encode(array('state'=>true,'msg'=>'删除成功')));
        }
        exit(json_encode(array('state'=>false,'msg'=>'删除失败')));
    }

}