<?php
/**
 * 默认展示页面
 *
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */


namespace Shop\Controller;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Page;

class LinkController extends BaseHomeController{
    public function index(){

         //友情链接
                $model_link = Model('link');
                $link_list = $model_link->getLinkList($condition,$page);
                /**
                 * 整理图片链接
                 */
                if (is_array($link_list)){
                        foreach ($link_list as $k => $v){
                                if (!empty($v['link_pic'])){
                                        $link_list[$k]['link_pic'] = UPLOAD_SITE_URL.'/'.ATTACH_PATH.'/common/'.DS.$v['link_pic'];
                                }
                        }
                }
                $this->assign('$link_list',$link_list);
        Model('seo')->type('index')->show();
        $this->render('link');
    }
   
}
