<?php
namespace Member\Controller;
use Member\Controller\BaseController;
use Common\Lib\Language;
use Common\Lib\Cache;
use Common\Lib\Log;


class BaseLoginController extends BaseController{
    /**
     * 构造函数
     */
    public function __construct(){
    	parent::__construct();
        /**
         * 读取通用、布局的语言包
         */
        Language::read('common,core_lang_index');
        /**
         * 设置布局文件内容
         */
        $this->setLayout('login_layout');

        /**
         * 获取导航
         */
        $this->assign('nav_list', rkcache('nav',true));
        
        /**
         * 自动登录
         */
        $this->auto_login();
    }

}

