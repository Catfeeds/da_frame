<?php
/**
 * 手机短信记录
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
use Common\Lib\Log;
use Common\Lib\Page;
use Common\Lib\Sms;



class sms_logModel extends Model{

    public function __construct() {
        parent::__construct();
    }

    /**
     * 增加短信记录
     *
     * @param
     * @return int
     */
    public function addSms($log_array) {
        $log_id = $this->table('sms_log')->insert($log_array);
        return $log_id;
    }

    /**
     * 查询单条记录
     *
     * @param
     * @return array
     */
    public function getSmsInfo($condition) {
        if (empty($condition)) {
            return false;
        }
        $result = $this->table('sms_log')->where($condition)->order('log_id desc')->find();
        return $result;
    }

    /**
     * 查询记录
     *
     * @param
     * @return array
     */
    public function getSmsList($condition = array(), $page = '', $limit = '', $order = 'log_id desc') {
        $result = $this->table('sms_log')->where($condition)->page($page)->limit($limit)->order($order)->select();
        return $result;
    }
    
    /**
     * 取得记录数量
     *
     * @param
     * @return int
     */
    public function getSmsCount($condition) {
        return $this->table('sms_log')->where($condition)->count();
    }
}
