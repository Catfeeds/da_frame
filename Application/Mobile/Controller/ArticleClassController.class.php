<?php
/**
 * 文章 
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Mobile\Controller;
use Mobile\Controller\MobileHomeController;
use Common\Lib\Language;
use Common\Lib\Model;

class ArticleClassController extends MobileHomeController {
	public function __construct() {
		parent::__construct ();
	}
	public function index() {
		$article_class_model = Model ( 'article_class' );
		$article_model = Model ( 'article' );
		$condition = array ();
		
		$article_class = $article_class_model->getClassList ( $condition );
		output_data ( array (
				'article_class' => $article_class));		
    }
}
