<?php
/**
 * 网站设置
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
use Common\Lib\Cache;
use Common\Lib\Email;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\UploadFile;


class SettingController extends SystemController {

    public function __construct(){
        parent::__construct();
		
		   $this->links = array(
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Setting&a=base','lang'=>'web_set'),
        array('url'=> $GLOBALS['_PAGE_URL'] . '&c=Setting&a=dump','lang'=>'dis_dump')
    );
		
        Language::read('setting', 'Home');
    }

    public function index() {
        $this->base();
    }

    /**
     * 基本信息
     */
    public function base(){
        $model_setting = Model('setting');
        if (chksubmit()){
            //上传网站Logo
            if (!empty($_FILES['site_logo']['name'])){
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_COMMON);
                $result = $upload->upfile('site_logo');
                if ($result){
                    $_POST['site_logo'] = $upload->file_name;
                }else {
                    showMessage($upload->error,'','','error');
                }
            }
            if (!empty($_FILES['member_logo']['name'])){
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_COMMON);
                $result = $upload->upfile('member_logo');
                if ($result){
                    $_POST['member_logo'] = $upload->file_name;
                }else {
                    showMessage($upload->error,'','','error');
                }
            }
            if (!empty($_FILES['seller_center_logo']['name'])){
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_COMMON);
                $result = $upload->upfile('seller_center_logo');
                if ($result){
                    $_POST['seller_center_logo'] = $upload->file_name;
                }else {
                    showMessage($upload->error,'','','error');
                }
            }
            $list_setting = $model_setting->getListSetting();
            $update_array = array();
            $update_array['site_phone'] = $_POST['site_phone'];
            $update_array['site_email'] = $_POST['site_email'];
            if (!empty($_POST['site_logo'])){
                $update_array['site_logo'] = $_POST['site_logo'];
            }
            if (!empty($_POST['member_logo'])){
                $update_array['member_logo'] = $_POST['member_logo'];
            }
            if (!empty($_POST['seller_center_logo'])){
                $update_array['seller_center_logo'] = $_POST['seller_center_logo'];
            }
            $result = $model_setting->updateSetting($update_array);
            if ($result === true){
                //判断有没有之前的图片，如果有则删除
                if (!empty($list_setting['site_logo']) && !empty($_POST['site_logo'])){
                    @unlink(BASE_UPLOAD_PATH.DS.ATTACH_COMMON.DS.$list_setting['site_logo']);
                }
                if (!empty($list_setting['member_logo']) && !empty($_POST['member_logo'])){
                    @unlink(BASE_UPLOAD_PATH.DS.ATTACH_COMMON.DS.$list_setting['member_logo']);
                }
                if (!empty($list_setting['seller_center_logo']) && !empty($_POST['seller_center_logo'])){
                    @unlink(BASE_UPLOAD_PATH.DS.ATTACH_COMMON.DS.$list_setting['seller_center_logo']);
                }
                $this->log(L('spd_edit,web_set'),1);
                showMessage(L('spd_common_save_succ'));
            }else {
                $this->log(L('spd_edit,web_set'),0);
                showMessage(L('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);

        //输出子菜单
        $this->assign('top_link',$this->sublink($this->links,'base'));
		$this->setDirquna('shop');

        $this->render('setting.base');
    }

    /**
     * 防灌水设置
     */
    public function dump(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['guest_comment'] = $_POST['guest_comment'];
            $update_array['captcha_status_goodsqa'] = $_POST['captcha_status_goodsqa'];
            $result = $model_setting->updateSetting($update_array);
            if ($result === true){
                $this->log(L('spd_edit,dis_dump'),1);
                showMessage(L('spd_common_save_succ'));
            }else {
                $this->log(L('spd_edit,dis_dump'),0);
                showMessage(L('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();
        $this->assign('list_setting',$list_setting);
        $this->assign('top_link',$this->sublink($this->links,'dump'));
		$this->setDirquna('shop');
        $this->render('setting.dump');
    }

    /**
     * SEO与rewrite设置
     */
    public function seo(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $update_array = array();
            $update_array['rewrite_enabled'] = $_POST['rewrite_enabled'];
            $result = $model_setting->updateSetting($update_array);
            if ($result === true){
                $this->log(L('spd_edit,spd_seo_set'),1);
                showMessage(L('spd_common_save_succ'));
            }else {
                $this->log(L('spd_edit,spd_seo_set'),0);
                showMessage(L('spd_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();

        //读取SEO信息
        $list = Model('seo')->select();
        $seo = array();
        foreach ((array)$list as $value) {
            $seo[$value['type']] = $value;
        }

        $this->assign('list_setting',$list_setting);
        $this->assign('seo',$seo);

        $category = Model('goods_class')->getGoodsClassForCacheModel();
        $this->assign('category',$category);
		$this->setDirquna('shop');

        $this->render('setting.seo_setting');
    }

    public function ajax_category(){
        $model = Model('goods_class');
        $list = $model->field('gc_title,gc_keywords,gc_description')->where(array('gc_id'=>intval($_GET['id'])))->find();

        //转码
        if (strtoupper(CHARSET) == 'GBK'){
            $list = Language::getUTF8($list);//网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
        }
        echo json_encode($list);exit();
    }

    /**
     * SEO设置保存
     */
    public function seo_update(){
        $model_seo = Model('seo');
        if (chksubmit()){
            $update = array();
            if (is_array($_POST['SEO'][0])){
                $seo = $_POST['SEO'][0];
            }else{
                $seo = $_POST['SEO'];
            }
            foreach ((array)$seo as $key=>$value) {
                $model_seo->where(array('type'=>$key))->update($value);
            }
            dkcache('seo');
            showMessage(L('spd_common_save_succ'));
        }else{
            showMessage(L('spd_common_save_fail'));
        }
    }

    /**
     * 分类SEO保存
     *
     */
    public function seo_category(){
        if (chksubmit()){
            $where = array('gc_id' => intval($_POST['category']));
            $input = array();
            $input['gc_title'] = $_POST['cate_title'];
            $input['gc_keywords'] = $_POST['cate_keywords'];
            $input['gc_description'] = $_POST['cate_description'];
            if (Model('goods_class')->editGoodsClass($input, $where)){
                dkcache('goods_class_seo');
                showMessage(L('spd_common_save_succ'));
            }
        }
        showMessage(L('spd_common_save_fail'));
    }

    /**
     * 设置时区
     *
     * @param int $time_zone 时区键值
     */
    private function setTimeZone($time_zone){
        $zonelist = $this->getTimeZone();
        return empty($zonelist[$time_zone]) ? 'Asia/Shanghai' : $zonelist[$time_zone];
    }

    private function getTimeZone(){
        return array(
        '-12' => 'Pacific/Kwajalein',
        '-11' => 'Pacific/Samoa',
        '-10' => 'US/Hawaii',
        '-9' => 'US/Alaska',
        '-8' => 'America/Tijuana',
        '-7' => 'US/Arizona',
        '-6' => 'America/Mexico_City',
        '-5' => 'America/Bogota',
        '-4' => 'America/Caracas',
        '-3.5' => 'Canada/Newfoundland',
        '-3' => 'America/Buenos_Aires',
        '-2' => 'Atlantic/St_Helena',
        '-1' => 'Atlantic/Azores',
        '0' => 'Europe/Dublin',
        '1' => 'Europe/Amsterdam',
        '2' => 'Africa/Cairo',
        '3' => 'Asia/Baghdad',
        '3.5' => 'Asia/Tehran',
        '4' => 'Asia/Baku',
        '4.5' => 'Asia/Kabul',
        '5' => 'Asia/Karachi',
        '5.5' => 'Asia/Calcutta',
        '5.75' => 'Asia/Katmandu',
        '6' => 'Asia/Almaty',
        '6.5' => 'Asia/Rangoon',
        '7' => 'Asia/Bangkok',
        '8' => 'Asia/Shanghai',
        '9' => 'Asia/Tokyo',
        '9.5' => 'Australia/Adelaide',
        '10' => 'Australia/Canberra',
        '11' => 'Asia/Magadan',
        '12' => 'Pacific/Auckland'
        );
    }
}
