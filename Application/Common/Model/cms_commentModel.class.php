<?php
/**
 * CMS评论模型
 *
 *
 *
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Common\Model;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Db;
use Common\Lib\Page;


class cms_commentModel extends Model{

    public function __construct(){
        parent::__construct('cms_comment');
    }

    /**
     * 读取列表
     * @param array $condition
     *
     */
    public function getList($condition,$page= 5000,$order='',$field='*'){
        $result = $this->table('cms_comment')->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
    }

    /**
     * 读取用户信息
     *
     */
    public function getListWithUserInfo($condition, $page=5000, $order='', $field='*'){
        
    	//$on = 'cms_comment.comment_member_id = member.member_id';
        //$result = $this->table('cms_comment,member')->field($filed)->join('left')->on($on)->where($condition)->page($page)->order($order)->select();
        
    	$result = array();

        $comment_list = $this->table('cms_comment')->field($field)->where($condition)->page($page)->order($order)->select();
        

        if (empty($comment_list))
        {
        	return $result;
        }
        
        $comment_hash = array();
        $member_list = array();
        
        foreach ($comment_list as $item)
        {
        	if (!in_array($item['comment_member_id'], $member_list))
        	{
        		$member_list[] = $item['comment_member_id'];
        	}
        	$comment_hash[$item['comment_id']] = $item;
        }
        $member_id_str = implode(",", $member_list);

        if (!empty($member_id_str))
        {
        	$member_list = F($member_id_str);
        }
        
        if (empty($member_list))
        {
        	$cond = "member_id in ($member_id_str)";
        	$member_list = Model("member")->getMemberList($cond);
        	F($member_id_str, $member_list);
        }
        
        foreach ($member_list as $member_item)
        {
        	$member_hash[$member_item['member_id']] = $member_item;
        }
 
        foreach ($comment_hash as $id => $comment_item)
        {
        	if (isset($member_hash[$comment_item['comment_member_id']]))
        	{
        		$item = array_merge($comment_item, $member_item);
        		$result[] = $item;
        	}
        }
        
        return $result;
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getOne($condition){
        $result = $this->where($condition)->find();
        return $result;
    }

    /*
     *  判断是否存在
     *  @param array $condition
     *
     */
    public function isExist($condition) {
        $result = $this->getOne($condition);
        if(empty($result)) {
            return FALSE;
        }
        else {
            return TRUE;
        }
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function save($param){
        return $this->insert($param);
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function saveAll($param){
        return $this->insertAll($param);
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */
    public function modify($update, $condition){
        return $this->where($condition)->update($update);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */
    public function drop($condition){
        return $this->where($condition)->delete();
    }

}
