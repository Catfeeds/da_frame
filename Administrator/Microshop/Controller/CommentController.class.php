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
use Common\Lib\Model;
use Common\Lib\Page;



class CommentController extends SystemController {

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
       $this->comment_manage();
    }


    /**
     * 评论管理
     */
    public function comment_manage()
    {
        $this->get_channel_array();
        $this->setDirquna('microshop');
$this->render('microshop_comment.manage');
    }

    /**
     * 评论管理XML
     */
    public function comment_manage_xml()
    {
        $condition = array();

        if ($_REQUEST['advanced']) {
            if (strlen($q = trim((string) $_REQUEST['comment_id']))) {
                $condition['comment_id'] = (int) $q;
            }
            if (strlen($q = trim((string) $_REQUEST['member_name']))) {
                $condition['member_name'] = array('like', '%' . $q . '%');
            }
            if (strlen($q = trim((string) $_REQUEST['comment_type']))) {
                $condition['comment_type'] = (int) $q;
            }
            if (strlen($q = trim((string) $_REQUEST['comment_object_id']))) {
                $condition['comment_object_id'] = (int) $q;
            }
            if (strlen($q = trim((string) $_REQUEST['comment_message']))) {
                $condition['comment_message'] = array('like', '%' . $q . '%');
            }
        } else {
            if (strlen($q = trim($_REQUEST['query'])) > 0) {
                switch ($_REQUEST['qtype']) {
                    case 'comment_id':
                        $condition['comment_id'] = (int) $q;
                        break;
                    case 'member_name':
                        $condition['member_name'] = array('like', '%' . $q . '%');
                        break;
                    case 'comment_object_id':
                        $condition['comment_object_id'] = (int) $q;
                        break;
                    case 'comment_message':
                        $condition['comment_message'] = array('like', '%' . $q . '%');
                        break;
                }
            }
        }

        $model_comment = Model("micro_comment");
        $list = (array) $model_comment->getListWithUserInfo($condition, $_REQUEST['rp'], 'comment_time desc');

        $data = array();
        $data['now_page'] = $model_comment->shownowpage();
        $data['total_num'] = $model_comment->gettotalnum();

        $channel_array = $this->get_channel_array();

        foreach ($list as $val) {
            $channel = $channel_array[$val['comment_type']];
            $o = '<a class="btn red confirm-del-on-click" href="' . $GLOBALS['_PAGE_URL'] . '&c=Comment&a=comment_drop&comment_id=' .
                    $val['comment_id'] .
                    '"><i class="fa fa-trash"></i>删除</a>';

            $o .= '<a class="btn green" target="_blank" href="' .
                MICROSHOP_SITE_URL. '&c=' .
                $channel['key'] .
                '&a=detail&' .
                $channel['key'] .
                '_id=' .
                $val['comment_object_id'] .
                '"><i class="fa fa-list-alt"></i>查看</a>';

            $i = array();
            $i['operation'] = $o;
            $i['comment_id'] = $val['comment_id'];

            $i['member_name'] = '<a href="' .
                MICROSHOP_SITE_URL . '&c=Home&member_id=' .
                $val['comment_member_id'] .
                '" target="_blank">' .
                $val['member_name'] .
                '</a>';

            $i['comment_type'] = $channel['name'];
            $i['comment_object_id'] = $val['comment_object_id'];
            $i['comment_message'] = parsesmiles($val['comment_message']);

            $data['list'][$val['comment_id']] = $i;
        }

        echo $this->flexigridXML($data);
        exit;
    }

    /**
     * 评论删除
     */
    public function comment_drop() {
        $model = Model('micro_comment');
        $condition = array();
        $condition['comment_id'] = array('in',trim($_REQUEST['comment_id']));
        $result = $model->drop($condition);
        if($result) {
            showMessage(Language::get('spd_common_del_succ'),'');
        } else {
            showMessage(Language::get('spd_common_del_fail'),'','','error');
        }
    }

    /**
     * 获取频道数组
     */
    private function get_channel_array() {
        $channel_array = array();
        $channel_array[self::GOODS_FLAG] = array('name'=>Language::get('spd_microshop_goods'),'key'=>'goods');
        $channel_array[self::PERSONAL_FLAG] = array('name'=>Language::get('spd_microshop_personal'),'key'=>'personal');
        $channel_array[self::STORE_FLAG] = array('name'=>Language::get('spd_microshop_store'),'key'=>'store');
        $this->assign('channel_array', $channel_array);

        return $channel_array;
    }
}
