<?php
/**
 * 平台咨询类型管理
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


class mall_consult_typeModel extends Model{
    public function __construct() {
        parent::__construct('mall_consult_type');
    }

    /**
     * 咨询类型列表
     *
     * @param array $condition
     * @param string $field
     * @param string $key
     * @param string $order
     */
    public function getMallConsultTypeList($condition, $field = '*', $key = '', $order = 'mct_sort asc,mct_id asc') {
        return $this->where($condition)->field($field)->key($key)->order($order)->select();
    }

    /**
     * 单条咨询类型
     *
     * @param unknown $condition
     * @param string $field
     */
    public function getMallConsultTypeInfo($condition, $field = '*') {
        return $this->where($condition)->field($field)->find();
    }

    /**
     * 添加咨询类型
     * @param array $insert
     * @return int
     */
    public function addMallConsultType($insert) {
        return $this->insert($insert);
    }

    /**
     * 编辑咨询类型
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editMallConsultType($condition, $update) {
        return $this->where($condition)->update($update);
    }

    /**
     * 删除咨询类型
     *
     * @param array $condition
     * @return boolean
     */
    public function delMallConsultType($condition) {
        return $this->where($condition)->delete();
    }
}
