<?php
/**
 * 平台充值卡使用日志
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */

namespace Common\Model;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Db;
use Common\Lib\Log;
use Common\Lib\Page;



class rcb_logModel extends Model
{
    public function __construct()
    {
        parent::__construct('rcb_log');
    }

    /**
     * 获取充值卡使用日志列表
     *
     * @param array $condition 条件数组
     * @param int $pageSize 分页长度
     *
     * @return array 充值卡使用日志列表
     */
    public function getRechargeCardBalanceLogCount($condition)
    {
        return $this->where($condition)->count();
    }

    /**
     * 获取充值卡使用日志列表
     *
     * @param array $condition 条件数组
     * @param int $pageSize 分页长度
     *
     * @return array 充值卡使用日志列表
     */
    public function getRechargeCardBalanceLogList($condition, $pageSize = 20, $limit = null, $sort = 'id desc')
    {
        if ($condition) {
            $this->where($condition);
        }

        if ($sort) {
            $this->order($sort);
        }

        if ($limit) {
            $this->limit($limit);
        } else {
            $this->page($pageSize);
        }

        return $this->select();
    }
}
