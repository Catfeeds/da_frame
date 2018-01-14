<?php
/**
 * cms用户中心画报
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Cms\Controller;
use Common\Lib\Language;
use Cms\Controller\CMSMemberController;
use Common\Lib\Model;
use Common\Lib\Page;


class MemberPictureController extends CMSMemberController {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->picture_list();
    }

    /**
     * 画报列表
     */
    public function picture_list() {
        $condition = array();
        if(!empty($_GET['picture_state'])) {
            $condition['picture_state'] = intval($_GET['picture_state']);
        } else {
            $condition['picture_state'] = array('in',array(self::ARTICLE_STATE_PUBLISHED, self::ARTICLE_STATE_VERIFY)) ;
        }
        $this->get_picture_list($condition);
    }

    /**
     * 草稿列表
     */
    public function draft_list() {
        $condition = array();
        $condition['picture_state'] = self::ARTICLE_STATE_DRAFT;
        $this->get_picture_list($condition);
    }

    /**
     * 草稿列表
     */
    public function recycle_list() {
        $condition = array();
        $condition['picture_state'] = self::ARTICLE_STATE_RECYCLE;
        $this->get_picture_list($condition);
    }

    /**
     * 获得画报列表
     */
    private function get_picture_list($condition = array()) {
        if(!empty($_GET['keyword'])) {
            $condition['picture_title'] = array('like', '%'.$_GET['keyword'].'%');
        }
        $condition['picture_type'] = $this->publisher_type;
        $condition['picture_publisher_id'] = $this->publisher_id;
        $model_picture = Model('cms_picture');
        $picture_list = $model_picture->getList($condition, 20, 'picture_id desc');
        $this->assign('show_page',$model_picture->showpage(2));
        $this->assign('picture_list', $picture_list);

        //获取画报图片
        $picture_ids = '';
        if(!empty($picture_list)) {
            foreach ($picture_list as $value) {
                $picture_ids .= $value['picture_id'].',';
            }
            $picture_ids = rtrim($picture_ids, ',');
        }
        $model_picture_image = Model('cms_picture_image');
        $picture_image_array = $model_picture_image->getList(array('image_picture_id'=>array('in', $picture_ids)));
        $picture_image_list = array();
        if(!empty($picture_image_array)) {
            foreach($picture_image_array as $value) {
                $image = array('name'=>$value['image_name'], 'path'=>$value['image_path']);
                $picture_image_list[$value['image_picture_id']][] = serialize($image);
            }
        }
        $this->assign('picture_image_list', $picture_image_list);

        $this->assign('picture_state_list', $this->get_article_state_list());
        $this->assign('index_sign', 'picture');
        $this->render('member_picture_list', 'cms_member_layout');
    }

    /**
     * 画报编辑
     */
    public function picture_edit() {
        $picture_id = intval($_GET['picture_id']);
        $picture_detail = $this->check_picture_auth($picture_id);
        if($picture_detail) {
            $model_picture_class = Model('cms_picture_class');
            $picture_class_list = $model_picture_class->getList(TRUE, NULL, 'class_sort asc');
            $this->assign('picture_class_list', $picture_class_list);

            $model_tag = Model('cms_tag');
            $tag_list = $model_tag->getList(TRUE, NULL, 'tag_sort asc');
            $this->assign('tag_list', $tag_list);

            $model_picture_image = Model('cms_picture_image');
            $picture_image_list = $model_picture_image->getList(array('image_picture_id'=>$picture_id), NULL);
            $this->assign('picture_image_list', $picture_image_list);

            $this->assign('picture_detail', $picture_detail);

            $this->render('publish_picture','cms_member_layout');
        } else {
            showMessage(Language::get('wrong_argument'),'','','error');
        }
    }

    /**
     * 发布
     */
    public function picture_publish() {
        $this->picture_state_change($this->publish_state);
    }

    /**
     * 移到回收站
     */
    public function picture_recycle() {
        $this->picture_state_change(self::ARTICLE_STATE_RECYCLE);
    }

    /**
     * 移到草稿箱
     */
    public function picture_draft() {
        $this->picture_state_change(self::ARTICLE_STATE_DRAFT);
    }

    /**
     * 删除
     */
    public function picture_drop() {
        $picture_id = intval($_GET['picture_id']);
        $picture_auth = $this->check_picture_auth($picture_id);
        if($picture_auth) {
            $model_picture = Model('cms_picture');
            $result = $model_picture->drop(array('picture_id'=>$picture_id));
            if($result) {
                showMessage(Language::get('spd_common_del_succ'),'');
            } else {
                showMessage(Language::get('spd_common_del_fail'),'','','error');
            }
        } else {
            showMessage(Language::get('wrong_argument'),'','','error');
        }
    }

    /**
     * 改变画报状态
     */
    private function picture_state_change($picture_state_new) {
        $picture_id = intval($_GET['picture_id']);
    $picture_auth = $this->check_picture_auth($picture_id);
    if($picture_auth) {
        $model_picture = Model('cms_picture');
        $result = $model_picture->modify(array('picture_state'=>$picture_state_new),array('picture_id'=>$picture_id));
        showMessage(Language::get('spd_common_op_succ'),'');
    } else {
        showMessage(Language::get('spd_common_op_fail'),'','','error');
    }
}
}
