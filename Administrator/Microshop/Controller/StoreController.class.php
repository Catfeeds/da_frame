<?php
/**
 * 微商城
 *
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Microshop\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Tpl;
use Common\Lib\Model;
use Common\Lib\Page;


class StoreController extends SystemController {

    const MICROSHOP_CLASS_LIST = 'admin.php?m=Microshop&c=GoodsClass&a=goodsclass_list';
    const GOODS_FLAG = 1;
    const PERSONAL_FLAG = 2;
    const ALBUM_FLAG = 3;
    const STORE_FLAG = 4;

    public function __construct(){
        parent::__construct();
        Language::read('store', 'Home');
        Language::read('microshop', 'Home');
    }

    public function index() {
       $this->store_manage();
    }



    /**
     * 店铺管理
     */
    public function store_manage()
    {
        $this->show_menu_store('store_manage');
        $this->setDirquna('microshop');
$this->render('microshop_store.manage');
    }

    /**
     * 店铺管理
     */
    public function store_manage_xml()
    {
        $condition = array();
        if (strlen($q = trim($_REQUEST['query'])) > 0) {
            switch ($_REQUEST['qtype']) {
                case 'store_name':
                    $condition['store_name'] = array('like', '%' . $q . '%');
                    break;
                case 'member_name':
                    $condition['member_name'] = array('like', '%' . $q . '%');
                    break;
            }
        }

        $model_store = Model('micro_store');
        $list = (array) $model_store->getListWithStoreInfo($condition, $_REQUEST['rp']);

        $data = array();
        $data['now_page'] = $model_store->shownowpage();
        $data['total_num'] = $model_store->gettotalnum();

        foreach ($list as $val) {
            $o = '<a class="btn red confirm-del-on-click" href="' . $GLOBALS['_PAGE_URL'] . '&c=Store&a=store_drop_save&store_id=' .
                $val['store_id'] .
                '"><i class="fa fa-trash"></i>删除</a>';

            if ($val['microshop_commend'] == 1) {
                $o .= '<a class="btn green" href="javascript:;" data-ie-column="microshop_commend" data-ie-value="0"><i class="fa fa-thumbs-o-down"></i>取消推荐</a>';
            } else {
                $o .= '<a class="btn green" href="javascript:;" data-ie-column="microshop_commend" data-ie-value="1"><i class="fa fa-thumbs-o-up"></i>推荐店铺</a>';
            }

            $i = array();
            $i['operation'] = $o;

            $i['microshop_sort'] = '<span class="editable" title="可编辑" style="width:50px;" data-live-inline-edit="microshop_sort">' .
                $val['microshop_sort'] . '</span>';

            $i['store_name'] = '<a target="_blank" href="' .
                MICROSHOP_SITE_URL .
                '&c=Store&a=detail&store_id=' .
                $val['store_id'] .
                '">' .
                $val['store_name'] .
                '</a>';

            $i['member_name'] = '<a href="' .
                MICROSHOP_SITE_URL .
                '&c=Home&member_id=' .
                $val['member_id'] .
                '">' .
                $val['member_name'] .
                '</a>';

            $i['area_info'] = $val['area_info'];

            $i['store_end_time_text'] = $val['store_end_time'] > 0
                ? date('Y-m-d H:i:s', $val['store_end_time'])
                : L('no_limit');

            $i['added_state'] = $val['microshop_commend'] == 1
                ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>'
                : '<span class="no"><i class="fa fa-ban"></i>否</span>';

            $data['list'][$val['store_id']] = $i;
        }

        echo $this->flexigridXML($data);
        exit;
    }


    /**
     * 店铺街添加列表
     */
    public function store_add()
    {
        $this->show_menu_store('store_add');
        $this->setDirquna('microshop');
$this->render('microshop_store.add');
    }

    /**
     * 店铺街添加列表
     */
    public function store_add_xml()
    {
        $model_store = Model('store');
        $model_microshop_store = Model('micro_store');

        $microshop_store_list = $model_microshop_store->getList(TRUE);
        $microshop_store_array = array();
        if (!empty($microshop_store_list)) {
            foreach ($microshop_store_list as $value) {
                $microshop_store_array[] = $value['shop_store_id'];
            }
        }

        $condition = array();
        if (strlen($q = trim($_REQUEST['query'])) > 0) {
            switch ($_REQUEST['qtype']) {
                case 'store_name':
                    $condition['store_name'] = array('like', '%' . $q . '%');
                    break;
                case 'member_name':
                    $condition['member_name'] = array('like', '%' . $q . '%');
                    break;
            }
        }

        $list = (array) $model_store->getStoreOnlineList($condition, $_REQUEST['rp']);

        $data = array();
        $data['now_page'] = $model_store->shownowpage();
        $data['total_num'] = $model_store->gettotalnum();

        foreach ($list as $val) {
            $addedState = in_array($val['store_id'], $microshop_store_array);

            if ($addedState) {
                $o = '--';
            } else {
                $o = '<a class="btn green" href="' . $GLOBALS['_PAGE_URL'] . '&c=Store&a=store_add_save&store_id=' .
                    $val['store_id'] .
                    '"><i class="fa fa-plus"></i>添加</a>';
            }

            $i = array();
            $i['operation'] = $o;
            $i['store_name'] = '<a href="' .
                urlShop('show_store', 'index', array('store_id' => $val['store_id'])) .
                '">' .
                $val['store_name'] .
                '</a>';

            $i['member_name'] = $val['member_name'];
            $i['area_info'] = $val['area_info'];

            $i['store_end_time_text'] = $val['store_end_time'] > 0
                ? date('Y-m-d H:i:s', $val['store_end_time'])
                : L('no_limit');

            $i['added_state'] = $addedState
                ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>'
                : '<span class="no"><i class="fa fa-ban"></i>否</span>';

            $data['list'][$val['store_id']] = $i;
        }

        echo $this->flexigridXML($data);
        exit;
    }

    /**
     * 店铺街添加保存
     */
    public function store_add_save()
    {
        $store_id_array = explode(',', $_REQUEST['store_id']);
        $param = array();
        if(!empty($store_id_array)) {
            foreach ($store_id_array as $value) {
                if(intval($value) > 0) {
                    $microshop_store['shop_store_id'] = $value;
                    $microshop_store['microshop_sort'] = 255;
                    $microshop_store['microshop_commend'] = 0;
                    $param[] = $microshop_store;
                }
            }
        }
        $model_store = Model('micro_store');
        $result = $model_store->saveAll($param);
        if($result) {
            showMessage(Language::get('spd_common_op_succ'),'');
        } else {
            showMessage(Language::get('spd_common_op_fail'),'','','error');
        }
    }

    /**
     * 店铺街删除保存
     */
    public function store_drop_save() {
        $model_store = Model('micro_store');
        $condition = array();
        $condition['shop_store_id'] = array('in',trim($_REQUEST['store_id']));
        $result = $model_store->drop($condition);
        if($result) {
            showMessage(Language::get('spd_common_del_succ'),'');
        } else {
            showMessage(Language::get('spd_common_del_fail'),'','','error');
        }
    }

    /**
     * 更新微商城店铺排序
     */
    public function store_sort_update() {
        if(intval($_GET['id']) <= 0) {
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('param_error')));
            die;
        }
        $new_sort = intval($_GET['value']);
        if ($new_sort > 255){
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('microshop_sort_error')));
            die;
        } else {
            $model_class = Model('micro_store');
            $result = $model_class->modify(array('microshop_sort'=>$new_sort),array('shop_store_id'=>$_GET['id']));
            if($result) {
                echo json_encode(array('result'=>TRUE,'message'=>'spd_common_op_succ'));
                die;
            } else {
                echo json_encode(array('result'=>FALSE,'message'=>Language::get('spd_common_op_fail')));
                die;
            }
        }
    }


    /**
     * ajax操作
     */
    public function ajax(){
        //店铺街推荐
        if ($_GET['branch'] == 'store_commend') {
            if(intval($_GET['id']) > 0) {
                $model= Model('micro_store');
                $condition['shop_store_id'] = intval($_GET['id']);
                $update[$_GET['column']] = trim($_GET['value']);
                $model->modify($update,$condition);
                echo 'true';die;
            } else {
                echo 'false';die;
            }
        }
    }
    private function show_menu_store($menu_key) {
        $menu_array = array(
                'store_manage'=>array('menu_type'=>'link','menu_name'=>Language::get('spd_manage'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=Store&a=store_manage'),
                'store_add'=>array('menu_type'=>'link','menu_name'=>Language::get('spd_new'),'menu_url'=>$GLOBALS['_PAGE_URL'] . '&c=Store&a=store_add'),
        );
        $menu_array[$menu_key]['menu_type'] = 'text';
        $this->assign('menu',$menu_array);
    }
}
