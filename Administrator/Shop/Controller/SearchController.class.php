<?php
/**
 * 搜索设置
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Model;



class SearchController extends SystemController {

    private $_links;

    public function __construct(){
        parent::__construct();
        $this->_links =  array(
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Search&a=index','text'=>'默认搜索'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Search&a=hot','text'=>'热门搜索')
        );
    }

    /**
     * 默认搜索
     */
    public function index() {
        if (chksubmit()){
            $model_setting = Model('setting');
            $comma = '，';
            if (strtoupper(CHARSET) == 'GBK'){
                $comma = Language::getGBK($comma);
            }
            $result = $model_setting->updateSetting(array(
                    'hot_search'=>str_replace($comma,',',$_POST['hot_search'])));
            if ($result){
                showMessage('保存成功');
            }else {
                showMessage('保存失败');
            }
        }
        $model_setting = Model('setting');
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);

        $this->assign('top_link',$this->sublink($this->_links,'index'));
		$this->setDirquna('shop');

        $this->render('search.index');
    }

    /**
     * 热门搜索词列表
     */
    public function hot() {
        $model_setting = Model('setting');
        $search_info = $model_setting->getRowSetting('rec_search');
        if ($search_info !== false) {
            $search_list = @unserialize($search_info['value']);
        }
        if (!$search_list && !is_array($search_list)) {
            $search_list = array();
        }
        $this->assign('search_list',$search_list);
        $this->assign('top_link',$this->sublink($this->_links,'hot'));
		$this->setDirquna('shop');
        $this->render('search.hot');
    }

    /**
     * 热搜词添加
     */
    public function hot_add() {
        $model_setting = Model('setting');
        $search_info = $model_setting->getRowSetting('rec_search');
        if ($search_info !== false) {
            $search_list = @unserialize($search_info['value']);
        }
        if (!$search_list && !is_array($search_list)) {
            $search_list = array();
        }
        if (chksubmit()) {
            if (count($search_list) >= 10) {
                showMessage('最多可设置10个热搜词',$GLOBALS['_PAGE_URL'] . '&c=Search&a=hot');
            }
            if ($_POST['s_name'] != '' && $_POST['s_value'] != '') {
                $data = array('name'=>stripslashes($_POST['s_name']),'value'=>stripslashes($_POST['s_value']));
                array_unshift($search_list, $data);
            }
            $result = $model_setting->updateSetting(array('rec_search'=>serialize($search_list)));
            if ($result){
                showMessage('保存成功',$GLOBALS['_PAGE_URL'] . '&c=Search&a=hot');
            }else {
                showMessage('保存失败');
            }
        }
		$this->setDirquna('shop');

        $this->render('search.hot_add');
    }

    /**
     * 删除
     */
    public function hot_del() {
        $model_setting = Model('setting');
        $search_info = $model_setting->getRowSetting('rec_search');
        if ($search_info !== false) {
            $search_list = @unserialize($search_info['value']);
        }
        if (!empty($search_list) && is_array($search_list) && intval($_GET['id']) >= 0) {
            unset($search_list[intval($_GET['id'])]);
        }
        if (!is_array($search_list)) {
            $search_list = array();
        }
        $result = $model_setting->updateSetting(array('rec_search'=>serialize(array_values($search_list))));
        if ($result){
            showMessage('删除成功');
        }
        showMessage('删除失败');
    }

    /**
     * 编辑
     */
    public function hot_edit() {
        $model_setting = Model('setting');
        $search_info = $model_setting->getRowSetting('rec_search');
        if ($search_info !== false) {
            $search_list = @unserialize($search_info['value']);
        }
        if (!is_array($search_list)) {
            $search_list = array();
        }
        if (!chksubmit()) {
            if (!empty($search_list) && is_array($search_list) && intval($_GET['id']) >= 0) {
                $current_info = $search_list[intval($_GET['id'])];
            }
            $this->assign('current_info',is_array($current_info) ? $current_info : array());
			$this->setDirquna('shop');
            $this->render('search.hot_add');
        } else {
            if ($_POST['s_name'] != '' && $_POST['s_value'] != '' && $_POST['id'] != '' && intval($_POST['id']) >= 0) {
                $search_list[intval($_POST['id'])] = array('name'=>stripslashes($_POST['s_name']),'value'=>stripslashes($_POST['s_value']));
            }
            $result = $model_setting->updateSetting(array('rec_search'=>serialize($search_list)));
            if ($result){
                showMessage('编辑成功',$GLOBALS['_PAGE_URL'] . '&c=Search&a=hot');
            }
            showMessage('编辑失败');
        }


    }
}