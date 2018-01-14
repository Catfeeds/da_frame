<?php
/**
 * 我的反馈
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Mobile\Controller;
use Mobile\Controller\MobileMemberController;
use Common\Lib\Language;
use Common\Lib\Db;
use Common\Lib\Model;


class MemberFeedbackController extends MobileMemberController {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加反馈
     */
    public function feedback_add() {
        $model_mb_feedback = Model('mb_feedback');

        $param = array();
        $param['content'] = $_POST['feedback'];
        $param['type'] = $this->member_info['client_type'];
        $param['ftime'] = TIMESTAMP;
        $param['member_id'] = $this->member_info['member_id'];
        $param['member_name'] = $this->member_info['member_name'];

        $result = $model_mb_feedback->addMbFeedback($param);

        if($result) {
            output_data('1');
        } else {
            output_error('保存失败');
        }
    }
}
