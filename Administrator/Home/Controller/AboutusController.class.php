<?php
/**
 * 控制台
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Home\Controller;
use Common\Lib\Language;



class AboutusController extends SystemController {
    public function __construct(){
        parent::__construct();
        Language::read('dashboard', 'Home');
    }

    public function index() {
        $this->aboutus();
    }

    /**
     * 关于我们
     */
    public function aboutus(){
        $version = C('version');
        $v_date = substr($version,0,4).".".substr($version,4,2);
        $s_date = substr(C('setup_date'),0,10);
        $this->assign('v_date',$v_date);
        $this->assign('s_date',$s_date);
        $this->render('aboutus', 'null_layout');
    }

}
