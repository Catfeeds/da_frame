<?php
/**
 * 买家相册模型
 * * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Common\Model;
use Common\Lib\Language;
use Common\Lib\Model;
use Common\Lib\Db;


class sns_albumModel extends Model {

    public function __construct(){
        parent::__construct('sns_albumpic');
    }

    public function getSnsAlbumClassDefault($member_id) {
        if(empty($member_id)) {
            return null;
        }

        $condition = array();
        $condition['member_id'] = $member_id;
        $condition['is_default'] = 1;
        $info = $this->table('sns_albumclass')->where($condition)->find();

        if(!empty($info)) {
            return $info['ac_id'];
        } else {
            return null;
        }
    }
}
