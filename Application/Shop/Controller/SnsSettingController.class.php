<?php
/**
 * 图片空间操作
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */



namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Model;

class SnsSettingController extends BaseSNSController {
    public function __construct() {
        parent::__construct();
        /**
         * 读取语言包
         */
        Language::read('sns_setting');
    }
    public function change_skin(){
        $this->render('sns_changeskin', 'null_layout');
    }
    public function skin_save(){
        $insert = array();
        $insert['member_id']    = $_SESSION['member_id'];
        $insert['setting_skin'] = $_GET['skin'];

        Model()->table('sns_setting')->insert($insert,true);
    }
}
