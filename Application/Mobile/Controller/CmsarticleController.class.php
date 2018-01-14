<?php
/**
 * CMS手机版
 *
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Mobile\Controller;
use Mobile\Controller\MobileHomeController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;
use Common\Lib\Seccode;
use Common\Lib\Sms;

class CmsarticleController extends MobileHomeController {
	
	//文章状态草稿箱
	const ARTICLE_STATE_DRAFT = 1;
	//文章状态待审核
	const ARTICLE_STATE_VERIFY = 2;
	//文章状态已发布
	const ARTICLE_STATE_PUBLISHED = 3;
	//文章状态回收站
	const ARTICLE_STATE_RECYCLE = 4;
	//推荐
	const COMMEND_FLAG_TRUE = 1;

	
	
	public function __construct() {
		parent::__construct();
	}
	
	public function index()
	{
		$this->article_class();
	}
	
	public function article_class() {
		$model_article_class = Model('cms_article_class');
		$article_class_list = $model_article_class->getList(true, null, 'class_sort asc');
		//$article_class_list = array_under_reset($article_class_list, 'class_id');
		
		$cond = array("is_show_on_mobile" => 1,);
		$show_article_class_list = Model("cms_article")
		->field(array("article_class_id"))
		->where($cond)
		->group("article_class_id")
		->select();

		$show_class_id_list = array();
		foreach ($show_article_class_list as $item)
		{
			$show_class_id_list[] = $item['article_class_id'];
		}
		
		$temp_article_class_list = array();
		foreach ($article_class_list as $item)
		{
			if (in_array($item['class_id'], $show_class_id_list))
			{
				$temp_article_class_list[] = $item;
			}
		}
		$article_class_list = $temp_article_class_list;
		
// 		var_dump($show_article_class_list);
// 		exit;
		
		$total_class = array("class_id" => "0", "class_name" => "全部", 
				"class_sort" => 1, "is_show_wap" => 1, "is_show_pc" => 1);
		array_unshift($article_class_list, $total_class);
		$ret = array("class_list" => $article_class_list);
		output_data($ret);
	}
	
	/**
	 * 文章列表
	 */
	public function article_list() {
		
		//获取文章列表
		$pagesize = $_GET['page'];
		$condition = array();
		if(!empty($_GET['class_id'])) {
			$condition['article_class_id'] = intval($_GET['class_id']);
		}
		$condition['article_state'] = self::ARTICLE_STATE_PUBLISHED;
		$model_article = Model('cms_article');
		
		$fields = "*";
		$order = "article_sort asc, article_id desc";
		$article_list = $model_article->field($fields)->where($condition)->order($order)->page($pagesize)->select();
		
		$temp_list = array();
		foreach ($article_list as $item)
		{
			if (empty($item['article_image']))
			{
				$item['article_cover_image'] = UPLOAD_SITE_URL . DS . "cms" . DS . "no_cover.png";
			}
			else
			{
				 $cover_img_arr = unserialize($item['article_image']);
				 
				 
				 $item['article_cover_image'] = UPLOAD_SITE_URL . DS . "cms/article" . DS  . $cover_img_arr['path']  . DS . $cover_img_arr['name'];
				 $item['article_image'] = $cover_img_arr;
				
				$item['article_image_all'] = unserialize($item['article_image_all']);
			}
			unset($item['article_content'], $item['article_goods']);
			$temp_list[] = $item;
		}
		$article_list = $temp_list;
		
		$page_count = $model_article->gettotalpage();
		output_data(array('article_list' => $article_list), mobile_page($page_count));
	}
	
	/**
	 * 文章详情
	 */
	public function article_detail() {
		$article_id = intval($_GET['article_id']);
		if($article_id <= 0) {
			output_error(Language::get('wrong_argument'));
		}
	
		$model_article = Model('cms_article');
		$article_detail = $model_article->getOne(array('article_id'=>$article_id));
		
		
		if(empty($article_detail)) {
			output_error(L("article_not_exists"));
		}
 
		//相关商品
		$article_goods_list = unserialize($article_detail['article_goods']);

		//计数加1
		$model_article->modify(array('article_click'=>array('exp','article_click+1')),array('article_id'=>$article_id));
 
		$article_image = getCMSArticleImageUrl($article_detail['article_attachment_path'], $article_detail['article_image'], 'max');;
		$article_detail['article_image'] = $article_image;
		
		$article_detail['article_publish_time'] = date("Y-m-d", $article_detail['article_publish_time']);
		

		$is_curr_member_praise = 0;
		if (!empty($_SESSION['member_id']))
		{
			$model_attitude = Model("cms_article_attitude");
			$cond = array("attitude_article_id" => $article_id, "attitude_member_id" => $_SESSION['member_id'],
					"attitude_result" => 5);
			$atti_data = $model_attitude->where($cond)->find();
			if (!empty($atti_data))
			{
				$is_curr_member_praise = 1;
			}
		}
		$article_detail['is_curr_member_praise'] = $is_curr_member_praise;

		
		$data = array(
				"article_detail" => $article_detail,
				"article_goods_list" => $article_goods_list,
				"article_attitude_list" => $article_attitude_list,
		);
		
		output_data($data, array(), false, true);
	}


	/**
	 * 文章分享
	 */
	public function article_share()
	{
		$article_id = intval($_GET['article_id']);
		if($article_id <= 0) {
			output_error(Language::get('wrong_argument'));
		}
		$model_article = Model('cms_article');
		$model_article->modify(array('article_share_count'=>array('exp','article_share_count+1')),array('article_id'=>$article_id));
		$article = $model_article->where(array("article_id" => $article_id))->find();
		output_data($article);
	}
	
	/**
	 * 文章赞
	 */
	public function article_praise()
	{
		$article_id = intval($_GET['article_id']);
		if($article_id <= 0) {
			output_error(Language::get('wrong_argument'));
		}
		$model_article = Model('cms_article');
		$model_article_atti = Model("cms_article_attitude");

		$article_atti_cond = array(
				"attitude_article_id" => $article_id,
				"attitude_member_id" => $this->getMemberIdIfExists(),
		);

		$article = $model_article->where(array("article_id" => $article_id))->find();
		$article_atti_arr = $model_article_atti->where($article_atti_cond)->find();

		if (empty($article_atti_arr))
		{
			$article_atti = array();
			$article_atti['attitude_article_id'] = $article_id;
			$article_atti["attitude_member_id"] = $this->getMemberIdIfExists();
			$article_atti["attitude_time"] = time();
			$article_atti['attitude_result'] = 5;
			$insert_ret = $model_article_atti->insert($article_atti);
			
			//喜欢+1
			$update = array();
			$update['article_attitude_5'] = array('exp','article_attitude_5+1');
			$condition = array();
			$condition['article_id'] = $article_id;
			$update['article_praise_count'] = array('exp','article_praise_count+1');
			$model_article->modify($update, $condition);
			
			$is_curr_member_praise = 1;
		}
		else
		{
			$model_article_atti->where($article_atti_cond)->delete();
			$is_curr_member_praise = 0;
			
			//喜欢-1
			$like_count = $article["article_attitude_5"];
			$like_count --;
			if ($like_count <= 0)
			{
				$like_count = 0;
			}
			$condition = array();
			$condition['article_id'] = $article_id;
			$model_article->modify(array("article_attitude_5" => $like_count, 
					"article_praise_count" => $like_count), $condition);
		}
 
		$article = $model_article->where(array("article_id" => $article_id))->find();
		$article['is_curr_member_praise'] = $is_curr_member_praise;
		
		unset($article['article_content']);
		output_data($article);
	}
}