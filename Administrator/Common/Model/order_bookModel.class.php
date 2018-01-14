<?php
/**
 * 预定订单时段模板
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


class order_bookModel extends Model{

    public function __construct(){
        parent::__construct('order_book');
    }

    /**
     * 读取列表
     * @param array $condition
     *
     */
    public function getOrderBookList($condition = array(), $page = '', $order = 'book_id asc', $field = '*', $limit = '') {
        return $this->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getOrderBookInfo($condition,$fields = '*') {
        return $this->where($condition)->field($fields)->find();
    }

    /*
     * 增加
     * @param array $data
     * @return bool
     */
    public function addOrderBook($data){
        return $this->insert($data);
    }

    /**
     * 编辑
     * @param unknown $data
     * @param unknown $condition
     */
    public function editOrderBook($data,$condition) {
        return $this->where($condition)->update($data);
    }

    public function getOrderBookCount($condition) {
        return $this->where($condition)->count();
    }

}
