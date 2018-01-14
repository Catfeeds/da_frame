<?php
/**
 * 消息通知
 *
 *
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
use Common\Lib\Tpl;


class MessageController extends SystemController {

    public function __construct(){
        parent::__construct();
		
		    $this->links = array(
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Message&a=seller_tpl', 'lang'=>'seller_tpl'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Message&a=member_tpl', 'lang'=>'member_tpl'),
    );
		
        Language::read('setting,message', 'Home');
    }

    public function index() {
        $this->seller_tpl();
    }

    /**
     * 商家消息模板
     */
    public function seller_tpl() {
        $mstpl_list = Model('store_msg_tpl')->getStoreMsgTplList(array());
        $this->assign('mstpl_list', $mstpl_list);
        $this->assign('top_link',$this->sublink($this->links,'seller_tpl'));
		$this->setDirquna('shop');
        $this->render('message.seller_tpl');
    }

    /**
     * 商家消息模板编辑
     */
    public function seller_tpl_edit() {
        if (chksubmit()) {
            $code = trim($_POST['code']);
            $type = trim($_POST['type']);
            if (empty($code) || empty($type)) {
                showMessage(L('param_error'));
            }
            switch ($type) {
                case 'message':
                    $this->seller_tpl_update_message();
                    break;
                case 'short':
                    $this->seller_tpl_update_short();
                    break;
                case 'mail':
                    $this->seller_tpl_update_mail();
                    break;
            }
        }
        $code = trim($_GET['code']);
        if (empty($code)) {
            showMessage(L('param_error'));
        }

        $where = array();
        $where['smt_code'] = $code;
        $smtpl_info = Model('store_msg_tpl')->getStoreMsgTplInfo($where);
        $this->assign('smtpl_info', $smtpl_info);
        $this->links[] = array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Message&a=seller_tpl_edit','lang'=>'seller_tpl_edit');
        $this->assign('top_link',$this->sublink($this->links,'seller_tpl_edit'));
		$this->setDirquna('shop');
        $this->render('message.seller_tpl.edit');
    }

    /**
     * 商家消息模板更新站内信
     */
    private function seller_tpl_update_message() {
        $message_content = trim($_POST['message_content']);
        if (empty($message_content)) {
            showMessage('请填写站内信模板内容。');
        }
        // 条件
        $where = array();
        $where['smt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['smt_message_switch'] = intval($_POST['message_switch']);
        $update['smt_message_content'] = $message_content;
        $update['smt_message_forced'] = intval($_POST['message_forced']);
        $result = Model('store_msg_tpl')->editStoreMsgTpl($where, $update);
        $this->seller_tpl_update_showmessage($result);
    }

    /**
     * 商家消息模板更新短消息
     */
    private function seller_tpl_update_short() {
        $short_content = trim($_POST['short_content']);
        if (empty($short_content)) {
            showMessage('请填写短消息模板内容。');
        }
        // 条件
        $where = array();
        $where['smt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['smt_short_switch'] = intval($_POST['short_switch']);
        $update['smt_short_content'] = $short_content;
        $update['smt_short_forced'] = intval($_POST['short_forced']);
        $result = Model('store_msg_tpl')->editStoreMsgTpl($where, $update);
        $this->seller_tpl_update_showmessage($result);
    }

    /**
     * 商家消息模板更新邮件
     */
    private function seller_tpl_update_mail() {
        $mail_subject = trim($_POST['mail_subject']);
        $mail_content = trim($_POST['mail_content']);
        if ((empty($mail_subject) || empty($mail_content))) {
            showMessage('请填写邮件模板内容。');
        }
        // 条件
        $where = array();
        $where['smt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['smt_mail_switch'] = intval($_POST['mail_switch']);
        $update['smt_mail_subject'] = $mail_subject;
        $update['smt_mail_content'] = $mail_content;
        $update['smt_mail_forced'] = intval($_POST['mail_forced']);
        $result = Model('store_msg_tpl')->editStoreMsgTpl($where, $update);
        $this->seller_tpl_update_showmessage($result);
    }

    private function seller_tpl_update_showmessage($result) {
        if ($result) {
            showMessage(L('spd_common_op_succ'), urlAdminShop('message', 'seller_tpl'));
        } else {
            showMessage(L('spd_common_op_fail'));
        }
    }

    /**
     * 用户消息模板
     */
    public function member_tpl() {
        $mmtpl_list = Model('member_msg_tpl')->getMemberMsgTplList(array());
        $this->assign('mmtpl_list', $mmtpl_list);
        $this->assign('top_link',$this->sublink($this->links,'member_tpl'));
		$this->setDirquna('shop');
        $this->render('message.member_tpl');
    }

    /**
     * 用户消息模板编辑
     */
    public function member_tpl_edit() {
        if (chksubmit()) {
            $code = trim($_POST['code']);
            $type = trim($_POST['type']);
            if (empty($code) || empty($type)) {
                showMessage(L('param_error'));
            }
            switch ($type) {
                case 'message':
                    $this->member_tpl_update_message();
                    break;
                case 'short':
                    $this->member_tpl_update_short();
                    break;
                case 'mail':
                    $this->member_tpl_update_mail();
                    break;
            }
        }
        $code = trim($_GET['code']);
        if (empty($code)) {
            showMessage(L('param_error'));
        }

        $where = array();
        $where['mmt_code'] = $code;
        $mmtpl_info = Model('member_msg_tpl')->getMemberMsgTplInfo($where);
        $this->assign('mmtpl_info', $mmtpl_info);
        $this->links[] = array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Message&a=member_tpl_edit','lang'=>'member_tpl_edit');
        $this->assign('top_link',$this->sublink($this->links,'member_tpl_edit'));
		$this->setDirquna('shop');
        $this->render('message.member_tpl.edit');
    }

    /**
     * 商家消息模板更新站内信
     */
    private function member_tpl_update_message() {
        $message_content = trim($_POST['message_content']);
        if (empty($message_content)) {
            showMessage('请填写站内信模板内容。');
        }
        // 条件
        $where = array();
        $where['mmt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['mmt_message_switch'] = intval($_POST['message_switch']);
        $update['mmt_message_content'] = $message_content;
        $result = Model('member_msg_tpl')->editMemberMsgTpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }

    /**
     * 商家消息模板更新短消息
     */
    private function member_tpl_update_short() {
        $short_content = trim($_POST['short_content']);
        if (empty($short_content)) {
            showMessage('请填写短消息模板内容。');
        }
        // 条件
        $where = array();
        $where['mmt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['mmt_short_switch'] = intval($_POST['short_switch']);
        $update['mmt_short_content'] = $short_content;
        $result = Model('member_msg_tpl')->editMemberMsgTpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }

    /**
     * 商家消息模板更新邮件
     */
    private function member_tpl_update_mail() {
        $mail_subject = trim($_POST['mail_subject']);
        $mail_content = trim($_POST['mail_content']);
        if ((empty($mail_subject) || empty($mail_content))) {
            showMessage('请填写邮件模板内容。');
        }
        // 条件
        $where = array();
        $where['mmt_code'] = trim($_POST['code']);
        // 数据
        $update = array();
        $update['mmt_mail_switch'] = intval($_POST['mail_switch']);
        $update['mmt_mail_subject'] = $mail_subject;
        $update['mmt_mail_content'] = $mail_content;
        $result = Model('member_msg_tpl')->editMemberMsgTpl($where, $update);
        $this->member_tpl_update_showmessage($result);
    }

    private function member_tpl_update_showmessage($result) {
        if ($result) {
            showMessage(L('spd_common_op_succ'), urlAdminShop('message', 'member_tpl'));
        } else {
            showMessage(L('spd_common_op_fail'));
        }
    }
}
