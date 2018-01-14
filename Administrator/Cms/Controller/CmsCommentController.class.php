<?php
/**
 * cms评论
 *
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Cms\Controller;
use Home\Controller\SystemController;
use Common\Lib\Language;
use Common\Lib\Tpl;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Page;


class CmsCommentController extends SystemController {


    public function __construct(){
        parent::__construct();
        Language::read('cms', 'Home');
    }

    public function index() {
        $this->comment_manage();
    }


    /**
     * 评论管理
     */
    public function comment_manage()
    {
        $this->get_type_array();
        $this->setDirquna('cms');
$this->render('cms_comment.manage');
    }

    /**
     * 评论管理
     */
    public function comment_manage_xml()
    {
        $condition = array();

        if ($_REQUEST['advanced']) {
            if (strlen($q = trim((string) $_REQUEST['comment_id']))) {
                $condition['comment_id'] = (int) $q;
            }
            if (strlen($q = trim((string) $_REQUEST['member_name']))) {
                $condition['member_name'] = $q;
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

        $model_comment = Model("cms_comment");
        $list = (array) $model_comment->getListWithUserInfo($condition, $_REQUEST['rp'], 'comment_time desc');

        $data = array();
        $data['now_page'] = $model_comment->shownowpage();
        $data['total_num'] = $model_comment->gettotalnum();

        $channel_array = $this->get_type_array();

        foreach ($list as $val) {
            $channel = $channel_array[$val['comment_type']];
            $o = '<a class="btn red confirm-del-on-click" href="' . $GLOBALS['_PAGE_URL'] . '&c=CmsComment&a=comment_drop&comment_id=' .
                $val['comment_id'] .
                '"><i class="fa fa-trash"></i>删除</a>';
            $o .= '<a class="btn green" target="_blank" href="' .
                CMS_SITE_URL . '&c=' .
                $channel['key'] .
                '&op=' .
                $channel['key'] .
                '_detail&' .
                $channel['key'] .
                '_id=' .
                $val['comment_object_id'] .
                '"><i class="fa fa-list-alt"></i>查看</a>';


            $i = array();
            $i['operation'] = $o;
            $i['comment_id'] = $val['comment_id'];

            $i['member_name'] = $val['member_name'];

            $i['comment_type'] = $channel['name'];
            $i['comment_object_id'] = $val['comment_object_id'];
            $i['comment_message'] = parsesmiles($val['comment_message']);

            $data['list'][$val['comment_id']] = $i;
        }

        echo $this->flexigridXML($data);
        exit;
    }

    /**
     * 获取类型数组
     */
    private function get_type_array() {
        $type_array = array();
        $type_array[1] = array('name'=>Language::get('cms_text_artcile'),'key'=>'article');
        $type_array[2] = array('name'=>Language::get('cms_text_picture'),'key'=>'picture');
        $this->assign('type_array', $type_array);

        return $type_array;
    }


    /**
     * 评论删除
     */
    public function comment_drop() {
        $model = Model('cms_comment');
        $condition = array();
        $condition['comment_id'] = array('in',trim($_REQUEST['comment_id']));
        $result = $model->drop($condition);
        if($result) {
            $this->log(Language::get('cms_log_comment_drop').$_REQUEST['comment_id'], 1);
            showMessage(Language::get('spd_common_del_succ'),'');
        } else {
            $this->log(Language::get('cms_log_comment_drop').$_REQUEST['comment_id'], 0);
            showMessage(Language::get('spd_common_del_fail'),'','','error');
        }
    }

}
