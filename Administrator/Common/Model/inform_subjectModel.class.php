<?php
/**
 * 举报主题模型
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


class inform_subjectModel extends Model {

    /*
     * 构造条件
     */
    private function getCondition($condition){
        $condition_str = '' ;
        if(!empty($condition['inform_subject_state'])) {
            $condition_str .= " and inform_subject_state = '{$condition['inform_subject_state']}'";
        }
        if(!empty($condition['inform_subject_type_id'])) {
            $condition_str .= " and inform_subject_type_id = '{$condition['inform_subject_type_id']}'";
        }
        if(!empty($condition['in_inform_subject_id'])) {
            $condition_str .= " and inform_subject_id in (".$condition['in_inform_subject_id'].')';
        }
        if(!empty($condition['in_inform_subject_type_id'])) {
            $condition_str .= " and inform_subject_type_id in (".$condition['in_inform_subject_type_id'].')';
        }
        return $condition_str;
    }

    /*
     * 增加
     * @param array $param
     * @return bool
     */
    public function saveInformSubject($param){

        return Db::insert('inform_subject',$param) ;

    }

    /*
     * 更新
     * @param array $update_array
     * @param array $where_array
     * @return bool
     */
    public function updateInformSubject($update_array, $where_array){

        $where = $this->getCondition($where_array) ;
        return Db::update('inform_subject',$update_array,$where) ;

    }

    /*
     * 删除
     * @param array $param
     * @return bool
     */
    public function dropInformSubject($param){

        $where = $this->getCondition($param) ;
        return Db::delete('inform_subject', $where) ;

    }

    /*
     *  获得列表
     *  @param array $condition
     *  @param obj $page    //分页对象
     *  @return array
     */
    public function getInformSubject($condition='',$page='',$field=''){

        $param = array() ;
        $param['table'] = 'inform_subject' ;
        $param['field'] = $field;
        $param['where'] = $this->getCondition($condition);
        $param['order'] = $condition['order'] ? $condition['order']: ' inform_subject_id desc ';
        return Db::select($param,$page) ;

    }

}