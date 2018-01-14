<?php
/**
 * 显示图片
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



class ShowPicsController extends SystemController {

	public function __construct() {
        parent::__construct();
    }

	public function index(){

        $type = trim($_GET['type']);
        if(empty($_GET['pics'])) {
            $this->goto_index();
        }
        $pics = explode('|',trim($_GET['pics']));
        $pic_path = '';
        switch ($type) {
            case 'inform':
                $pic_path = UPLOAD_SITE_URL.'/shop/inform/';
                break;
            case 'complain':
                $pic_path = UPLOAD_SITE_URL.'/shop/complain/';
                break;
            default:
                $this->goto_index();
                break;
        }

        $this->assign('pic_path',$pic_path);
		$this->assign('pics',$pics);
		//输出页面
		$this->setDirquna('shop');
		$this->render('show_pics','blank_layout');
	}

    private function goto_index() {
	    @header("Location: {$GLOBALS['_PAGE_URL']}");
		exit;
    }
}
