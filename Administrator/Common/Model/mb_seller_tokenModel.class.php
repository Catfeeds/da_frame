<?php
/**
 * 客户端商家令牌模型
 *
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Common\Model;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Db;



class mb_seller_tokenModel extends Model{
    public function __construct(){
        parent::__construct('mb_seller_token');
    }

    /**
     * 查询
     *
     * @param array $condition 查询条件
     * @return array
     */
    public function getSellerTokenInfo($condition) {
        return $this->where($condition)->find();
    }

    public function getSellerTokenInfoByToken($token) {
        if(empty($token)) {
            return null;
        }
        return $this->getSellerTokenInfo(array('token' => $token));
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addSellerToken($param){
        return $this->insert($param);
    }

    /**
     * 删除
     *
     * @param int $condition 条件
     * @return bool 布尔类型的返回结果
     */
    public function delSellerToken($condition){
        return $this->where($condition)->delete();
    }
}
