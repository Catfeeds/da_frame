<?php
/**
 * 系统文章
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



class documentModel extends Model {
    /**
     * 查询所有系统文章
     */
    public function getList(){
        $param  = array(
            'table' => 'document'
        );
        return Db::select($param);
    }
    /**
     * 根据编号查询一条
     *
     * @param unknown_type $id
     */
    public function getOneById($id){
        $param  = array(
            'table' => 'document',
            'field' => 'doc_id',
            'value' => $id
        );
        return Db::getRow($param);
    }
    /**
     * 根据标识码查询一条
     *
     * @param unknown_type $id
     */
    public function getOneByCode($code){
        $param  = array(
            'table' => 'document',
            'field' => 'doc_code',
            'value' => $code
        );
        return Db::getRow($param);
    }
    /**
     * 更新
     *
     * @param unknown_type $param
     */
    public function updates($param){
        return Db::update('document',$param,"doc_id='{$param['doc_id']}'");
    }
}
