<?php
/**
 * 手机端令牌模型
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



class mb_user_tokenModel extends Model{
    public function __construct(){
        parent::__construct('mb_user_token');
    }

    /**
     * 查询
     *
     * @param array $condition 查询条件
     * @return array
     */
    public function getMbUserTokenInfo($condition) {
        return $this->where($condition)->find();
    }

    public function getMbUserTokenInfoByToken($token) {
        if(empty($token)) {
            return null;
        }
        return $this->getMbUserTokenInfo(array('token' => $token));
    }

    public function updateMemberOpenId($token, $openId)
    {
        return $this->where(array(
            'token' => $token,
        ))->update(array(
            'openid' => $openId,
        ));
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addMbUserToken($param){
        return $this->insert($param);
    }

    /**
     * 删除
     *
     * @param int $condition 条件
     * @return bool 布尔类型的返回结果
     */
    public function delMbUserToken($condition){
        return $this->where($condition)->delete();
    }
    
    /**
     * 获取用户信息
     * */
    public function getByTpUnionId($unionId)
    {
    	$condition = array("tp_unionid" => $unionId);
    	$ret = $this->where($condition)->find();
    	return $ret;
    }
}
