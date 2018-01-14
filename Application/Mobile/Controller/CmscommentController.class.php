<?php
namespace Mobile\Controller;
use Mobile\Controller\MobileHomeController;
use Common\Lib\Language;

class CmscommentController extends MobileHomeController {
	// 文章评论类型
	const ARTICLE = 1;
	const PICTURE = 2;
	public function __construct() {
		parent::__construct ();
		$_SESSION ['member_id'] = $this->member_info ['member_id'];
	}
	
	/**
	 * 评论保存
	 */
	public function comment_save() {
		$data = array ();
		$data ['result'] = 'true';
		$comment_object_id = intval ( $_POST ['comment_object_id'] );
		$comment_type = strtolower ( $_POST ['comment_type'] );
		$model_name = '';
		$count_field = '';
		switch ($comment_type) {
			case 'article' :
				$comment_type = self::ARTICLE;
				$model_name = 'cms_article';
				$count_field = 'article_comment_count';
				$comment_object_key = 'article_id';
				break;
			case 'picture' :
				$comment_type = self::PICTURE;
				$model_name = 'cms_picture';
				$count_field = 'picture_comment_count';
				$comment_object_key = 'picture_id';
				break;
			default :
				$comment_type = 0;
				break;
		}
		
		if ($comment_object_id <= 0 || empty ( $comment_type ) || empty ( $_POST ['comment_message'] )) {
			$data ['result'] = 'false';
			$data ['message'] = Language::get ( 'wrong_argument' );
			output_data ( $data );
		}
		
		if (! empty ( $_SESSION ['member_id'] )) {
			
			$param = array ();
			$param ['comment_type'] = $comment_type;
			$param ["comment_object_id"] = $comment_object_id;
			if (strtoupper ( CHARSET ) == 'GBK') {
				$param ['comment_message'] = Language::getGBK ( trim ( $_POST ['comment_message'] ) );
			} else {
				$param ['comment_message'] = trim ( $_POST ['comment_message'] );
			}
			$param ['comment_member_id'] = $_SESSION ['member_id'];
			$param ['comment_time'] = time ();
			
			$model_comment = Model ( 'cms_comment' );
			
			if (! empty ( $_POST ['comment_id'] )) {
				$comment_detail = $model_comment->getOne ( array (
						'comment_id' => $_POST ['comment_id'] 
				) );
				if (empty ( $comment_detail ['comment_quote'] )) {
					$param ['comment_quote'] = $_POST ['comment_id'];
				} else {
					$param ['comment_quote'] = $comment_detail ['comment_quote'] . ',' . $_POST ['comment_id'];
				}
			} else {
				$param ['comment_quote'] = '';
			}
			
			$result = $model_comment->save ( $param );
			if ($result) {
				
				// 评论计数加1
				$model = Model ( $model_name );
				$update = array ();
				$update [$count_field] = array (
						'exp',
						$count_field . '+1' 
				);
				$condition = array ();
				$condition [$comment_object_key] = $comment_object_id;
				$model->modify ( $update, $condition );
				
				// 返回信息
				$data ['result'] = 'true';
				$data ['message'] = Language::get ( 'spd_common_save_succ' );
				$data ['member_name'] = $_SESSION ['member_name'] . Language::get ( 'spd_colon' );
				$data ['member_avatar'] = getMemberAvatar ( $_SESSION ['avatar'] );
				$data ['member_link'] = urlShop("MemberSnshome", "index", array("mid" => $_SESSION ['member_id']));
				$data ['comment_message'] = parsesmiles ( stripslashes ( $param ['comment_message'] ) );
				$data ['comment_time'] = date ( 'Y-m-d H:i:s', $param ['comment_time'] );
				$data ['comment_id'] = $result;
			} else {
				$data ['result'] = 'false';
				$data ['message'] = Language::get ( 'spd_common_save_fail' );
			}
		} else {
			$data ['result'] = 'false';
			$data ['message'] = Language::get ( 'no_login' );
		}
		output_data ( $data );
	}
	
	/**
	 * 评论列表
	 */
	public function comment_list() {
		
		$order = 'comment_id desc';
		if ($_GET ['comment_all'] === 'all') {
			$order = 'comment_up desc, comment_id desc';
		}
		$comment_object_id = intval ( $_GET ['comment_object_id'] );
		$comment_type = 0;
		$_GET ['type'] = strtolower ( $_GET ['type'] );
		switch ($_GET ['type']) {
			case 'article' :
				$comment_type = self::ARTICLE;
				break;
			case 'picture' :
				$comment_type = self::PICTURE;
				break;
		}
		
		if ($comment_object_id > 0 && $comment_type > 0) {
			$condition = array ();
			$page_count = 2000;
			$condition ["comment_object_id"] = $comment_object_id;
			$condition ["comment_type"] = $comment_type;
			$model_cms_comment = Model ( 'cms_comment' );
			$comment_list = $model_cms_comment->getListWithUserInfo ( $condition, $page_count, $order );
 
			$comment_hash = array();
			$member_id_list = array();
			foreach ($comment_list as $item)
			{
				$comment_hash[$item['comment_id']] = $item;
				$member_id_list[] = $item['comment_member_id'];
			}
			
			$member_hash = Model("member")->batchGetMemberInfo($member_id_list, true);

			$temp_comment_list = array();
			foreach ($comment_list as $item)
			{
				$temp_quote_list = array();
				$reply_member_id = "";
				$reply_member_name = "";
				if (!empty($item['comment_quote']))
				{
					$comment_quote_id_list = explode(",", $item['comment_quote']);
					$reply_item_id = $comment_quote_id_list[count($comment_quote_id_list) - 1];
					//var_dump($reply_item_id);
					$reply_member_id = $comment_hash[$reply_item_id]['comment_member_id'];
					if ($reply_member_id != $item['comment_member_id'])
					{
						$reply_member_name = $member_hash[$reply_member_id]['member_name'];
					}
				}
				
				$item['reply_member_name'] = empty($reply_member_name) ? "" : $reply_member_name;
 				$item['comment_quote_list'] = $temp_quote_list;
				$item['comment_time'] = date("Y-m-d H:i", $item['comment_time']);
				$item['member_avatar'] = getMemberAvatar($item['member_avatar']);
				$temp_comment_list[] = $item;
			}
			$comment_list = $temp_comment_list;
 
			output_data(array('comment_list' => $comment_list));
		}
	}
	
	/**
	 * 评论删除
	 */
	public function comment_drop() {
		$_GET ['type'] = strtolower ( $_GET ['type'] );
		
		$data ['result'] = 'false';
		$data ['message'] = Language::get ( 'spd_common_del_fail' );
		$comment_id = intval ( $_POST ['comment_id'] );
		if ($comment_id > 0) {
			$model_comment = Model ( 'cms_comment' );
			$comment_info = $model_comment->getOne ( array (
					'comment_id' => $comment_id 
			) );
			if ($comment_info ['comment_member_id'] == $_SESSION ['member_id']) {
				$result = $model_comment->drop ( array (
						'comment_id' => $comment_id 
				) );
				if ($result) {
					
					$comment_type = $_GET ['type'];
					switch ($comment_type) {
						case 'article' :
							$comment_type = self::ARTICLE;
							$model_name = 'cms_article';
							$count_field = 'article_comment_count';
							$comment_object_key = 'article_id';
							break;
						case 'picture' :
							$comment_type = self::PICTURE;
							$model_name = 'cms_picture';
							$count_field = 'picture_comment_count';
							$comment_object_key = 'picture_id';
							break;
						default :
							$comment_type = 0;
							break;
					}
					
					// 评论计数减1
					$model = Model ( $model_name );
					$update = array ();
					$update [$count_field] = array (
							'exp',
							$count_field . '-1' 
					);
					$condition = array ();
					$condition [$comment_object_key] = $comment_object_id;
					$model->modify ( $update, $condition );
					
					$data ['result'] = 'true';
					$data ['message'] = Language::get ( 'spd_common_del_succ' );
				}
			}
		}
		output_data ( $data );
	}
	
	/**
	 * 评论顶
	 */
	public function comment_up() {
		$data = array ();
		$data ['result'] = 'true';
		
		$comment_id = intval ( $_POST ['comment_id'] );
		if ($comment_id > 0) {
			$model_comment_up = Model ( 'cms_comment_up' );
			$param = array ();
			$param ['comment_id'] = $comment_id;
			$param ['up_member_id'] = $_SESSION ['member_id'];
			$is_exist = $model_comment_up->isExist ( $param );
			if (! $is_exist) {
				$param ['up_time'] = time ();
				$model_comment_up->save ( $param );
				
				$model_comment = Model ( 'cms_comment' );
				$model_comment->modify ( array (
						'comment_up' => array (
								'exp',
								'comment_up+1' 
						) 
				), array (
						'comment_id' => $comment_id 
				) );
			} else {
				$data ['result'] = 'false';
				$data ['message'] = '顶过了';
			}
		} else {
			$data ['result'] = 'false';
			$data ['message'] = Language::get ( 'wrong_argument' );
		}
		output_data ( $data );
	}
}