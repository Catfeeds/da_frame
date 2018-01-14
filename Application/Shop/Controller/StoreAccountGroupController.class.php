<?php
/**
 * 卖家账号组管理
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Tpl;

class StoreAccountGroupController extends BaseSellerController {
    public function __construct() {
        parent::__construct();
        Language::read('member_store_index');
    }

    public function group_list() {
        $model_seller_group = Model('seller_group');
        $seller_group_list = $model_seller_group->getSellerGroupList(array('store_id' => $_SESSION['store_id']));
        $this->assign('seller_group_list', $seller_group_list);
        $this->profile_menu('group_list');
        $this->render('store_account_group.list');
    }

    public function group_add() {
        // 店铺消息模板列表
        $smt_list = Model('store_msg_tpl')->getStoreMsgTplList(array(), 'smt_code,smt_name');
        $this->assign('smt_list', $smt_list);

        //取得商品分类权限列表
        $this->_get_goods_class_list();

        $this->profile_menu('group_add');
        $this->render('store_account_group.add');
    }

    public function group_edit() {
        $group_id = intval($_GET['group_id']);
        if ($group_id <= 0) {
            showMessage('参数错误', '', '', 'error');
        }
        $model_seller_group = Model('seller_group');
        $seller_group_info = $model_seller_group->getSellerGroupInfo(array('group_id' => $group_id));
        if (empty($seller_group_info)) {
            showMessage('组不存在', '', '', 'error');
        }
        $this->assign('group_info', $seller_group_info);
        $this->assign('group_limits', explode(',', $seller_group_info['limits']));
        $this->assign('smt_limits', explode(',', $seller_group_info['smt_limits']));

        // 店铺消息模板列表
        $smt_list = Model('store_msg_tpl')->getStoreMsgTplList(array(), 'smt_code,smt_name');
        $this->assign('smt_list', $smt_list);

        //取得商品分类权限列表
        $this->_get_goods_class_list($group_id);

        $this->profile_menu('group_edit');
        $this->render('store_account_group.add');
    }

    public function group_save() {
        $seller_info = array();
        $seller_info['group_name'] = $_POST['seller_group_name'];
        $seller_info['limits'] = implode(',', $_POST['limits']);
        $seller_info['smt_limits'] = empty($_POST['smt_limits']) ? '' : implode(',', $_POST['smt_limits']);
        $seller_info['gc_limits'] = $_POST['gc_select_all'] ? 1 : 0;
        $seller_info['store_id'] = $_SESSION['store_id'];
        $model_seller_group = Model('seller_group');
        if (empty($_POST['group_id'])) {
            $result = $model_seller_group->addSellerGroup($seller_info);
            $this->_get_goods_class_save($result);
            $this->recordSellerLog('添加组成功，组编号'.$result);
            showDialog('添加成功', urlShop('store_account_group', 'group_list'),'succ');
        } else {
            $condition = array();
            $condition['group_id'] = intval($_POST['group_id']);
            $condition['store_id'] = $_SESSION['store_id'];
            $model_seller_group->editSellerGroup($seller_info, $condition);
            $this->_get_goods_class_save(intval($_POST['group_id']));
            $this->recordSellerLog('编辑组成功，组编号'.$_POST['group_id']);
            showDialog('编辑成功', urlShop('store_account_group', 'group_list'),'succ');
        }
    }

    public function group_del() {
        $group_id = intval($_POST['group_id']);
        if($group_id > 0) {
            $condition = array();
            $condition['group_id'] = $group_id;
            $condition['store_id'] = $_SESSION['store_id'];
            $model_seller_group = Model('seller_group');
            $result = $model_seller_group->delSellerGroup($condition);
            if($result) {
                $this->recordSellerLog('删除组成功，组编号'.$group_id);
                showDialog(Language::get('spd_common_op_succ'),'reload','succ');
            } else {
                $this->recordSellerLog('删除组失败，组编号'.$group_id);
                showDialog(Language::get('spd_common_save_fail'),'reload','error');
            }
        } else {
            showDialog(Language::get('wrong_argument'),'reload','error');
        }
    }

    /**
     * 取得商品分类列表
     */
    private function _get_goods_class_list($group_id = null) {
        $model_goods_class = Model('goods_class');
        if (checkPlatformStoreBindingAllGoodsClass()) {
            $class_list = $model_goods_class->get_all_category();
        } else {
            $class_list = array();
            $model_store_bind_class = Model('store_bind_class');
            $bind_class = $model_store_bind_class->getStoreBindClassList(array('store_id'=>$_SESSION['store_id']),'','','*',false);
            $goods_class = $model_goods_class->getGoodsClassIndexedListAll();
            for($i = 0, $j = count($bind_class); $i < $j; $i++) {
                $cur = $bind_class[$i];
                if (!isset($class_list[$cur['class_1']])) {
                    $class_list[$cur['class_1']] = array(
                        'gc_id' => $cur['class_1'],
                        'gc_name' => $goods_class[$cur['class_1']]['gc_name'],
                        'gc_parent_id' => $goods_class[$cur['class_1']]['gc_parent_id']
                    );
                }

                if (empty($cur['class_2'])) continue;
                if (!isset($class_list[$cur['class_1']]['class2'])) {
                    $class_list[$cur['class_1']]['class2'] = array();
                }
                $tmp_2 = & $class_list[$cur['class_1']]['class2'];
                if (!isset($tmp_2[$cur['class_2']])) {
                    $tmp_2[$cur['class_2']] = array(
                            'gc_id' => $cur['class_2'],
                            'gc_name' => $goods_class[$cur['class_2']]['gc_name'],
                            'gc_parent_id' => $goods_class[$cur['class_2']]['gc_parent_id']
                    );
                }

                if (empty($cur['class_3'])) continue;
                if (!isset($tmp_2[$cur['class_2']]['class3'])) {
                    $tmp_2[$cur['class_2']]['class3'] = array();
                }
                $tmp_3 = & $tmp_2[$cur['class_2']]['class3'];
                if (!isset($tmp_3[$cur['class_3']])) {
                    $tmp_3[$cur['class_3']] = array(
                            'gc_id' => $cur['class_3'],
                            'gc_name' => $goods_class[$cur['class_3']]['gc_name'],
                            'gc_parent_id' => $goods_class[$cur['class_3']]['gc_parent_id']
                    );
                }
            }
        }
        $this->assign('bind_class_list', $class_list);
        //输出JSON形式，模板JS调用需要
        $this->assign('bind_class_list_json',json_encode($class_list));

        if (!empty($group_id)) {
            $model_seller_group_bclass = Model('seller_group_bclass');
            //最低级ID列表
            $gc_list_useing = $model_seller_group_bclass->getSellerGroupBclasList(array('group_id'=>$group_id),'','','gc_id','gc_id');
            $this->assign('gc_id_use_list',array_keys($gc_list_useing));
            //处理哪些二级分类需要选中
            $gc_list_useing = $model_seller_group_bclass->getSellerGroupBclasList(array('group_id'=>$group_id,'class_2'=>array(neq,0)),'','','count(bid) as ccount,class_2','class_2','class_2');
            $this->assign('class_2_use_list',$gc_list_useing);
        } else {
            $this->assign('gc_id_use_list',array());
        }
    }

    private function _get_goods_class_save($group_id = null) {
        if (!is_array($_POST['cate']) || isset($_POST['gc_select_all'])) $_POST['cate'] = array();
        $input = array();
        foreach($_POST['cate'] as $cate1_id => $cate1_array) {
            if (!is_array($cate1_array)) {
                $tmp = array();
                $tmp['class_1'] = $cate1_id;
                $tmp['class_2'] = $tmp['class_3'] = 0;
                $tmp['gc_id'] = $cate1_id;
                $tmp['group_id'] = $group_id;
                $input[] = $tmp;
            } else {
                foreach($cate1_array as $cate2_id => $cate2_array) {
                    if (!is_array($cate2_array)) {
                        $tmp = array();
                        $tmp['class_1'] = $cate1_id;
                        $tmp['class_2'] = $cate2_id;
                        $tmp['class_3'] = 0;
                        $tmp['gc_id'] = $cate2_id;
                        $tmp['group_id'] = $group_id;
                        $input[] = $tmp;
                    } else {
                        foreach($cate2_array as $cate3_id => $cate3_array) {
                            $tmp = array();
                            $tmp['class_1'] = $cate1_id;
                            $tmp['class_2'] = $cate2_id;
                            $tmp['class_3'] = $cate3_id;
                            $tmp['gc_id'] = $cate3_id;
                            $tmp['group_id'] = $group_id;
                            $input[] = $tmp;
                        }
                    }
                }
            }
        }
        $model_seller_group_bclass = Model('seller_group_bclass');
        $a = $model_seller_group_bclass->delSellerGroupBclass(array('group_id'=>$group_id));
        $model_seller_group_bclass->addSellerGroupBclass($input);
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string    $menu_type  导航类型
     * @param string    $menu_key   当前导航的menu_key
     * @param array     $array      附加菜单
     * @return
     */
    private function profile_menu($menu_key='') {
        $menu_array = array();
        $menu_array[] = array(
            'menu_key'=>'group_list',
            'menu_name' => '组列表',
            'menu_url' => urlShop('store_account_group', 'group_list')
        );
        if($menu_key === 'group_add') {
            $menu_array[] = array(
                'menu_key'=>'group_add',
                'menu_name' => '添加组',
                'menu_url' => urlShop('store_account_group', 'group_add')
            );
        }
        if($menu_key === 'group_edit') {
            $menu_array[] = array(
                'menu_key'=>'group_edit',
                'menu_name' => '编辑组',
                'menu_url' => urlShop('store_account_group', 'group_edit')
            );
        }
        $this->assign('member_menu', $menu_array);
        $this->assign('menu_key', $menu_key);
    }

}
