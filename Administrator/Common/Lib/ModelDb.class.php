<?php
/**
 * 完成模型SQL组装
 *
 */
namespace Common\Lib;
use Common\Lib\Db;

class ModelDb {

	protected $comparison      = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE','in'=>'IN','not in'=>'NOT IN');
	// 查询表达式
	protected $selectSql  =     'SELECT%DISTINCT% %FIELD% FROM %TABLE%%INDEX%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%';


	public function select($options=array()) {
		//     	static $_cache = array();
		$sql = $this->buildSelectSql($options);
		//var_dump($sql);
		if (isset($options['cache']) && ($options['cache'] !== false)){
			$key =  (isset($cache['cache_key']) && is_string($cache['cache_key'])) ? $cache['cache_key'] : md5($sql);
			if (isset($_cache[$key])){
				return $_cache[$key];
			}
		}
		//var_dump($sql);
		$result = Db::getAll($sql,($options['lock'] === true || $options['master'] === true || defined('TRANS_MASTER')) ? 'master' : 'slave');
		//         if ($options['cache'] !== false && !isset($_cache[$key])){
		//         	$_cache[$key] = $result;
		//         }
		return $result;
	}

	public function buildSelectSql($options=array()) {
		if (is_numeric($options['page'])){
			$page = pagecmd('obj');
			if ($options['limit'] !== 1){
				$options['limit'] = $page->getLimitStart().",".$page->getEachNum();
				//var_dump($options);
			}
		}
		$sql  = $this->parseSql($this->selectSql,$options);
		$sql .= $this->parseLock(isset($options['lock'])?$options['lock']:false);
		//var_dump($sql);
		return $sql;
	}

	public function parseSql($sql,$options=array()){
		
		if (isset($options['field']) && 
				(!empty($options['field'])) &&
				(strtolower($options['field']) == 'count(*) as spd_count'))
		{
			unset($options['group']);
		}
		
		$parseTableRes = $this->parseTable($options);

// 		var_dump($parseTableRes);
		
		$sql   = str_replace(
				array('%TABLE%','%DISTINCT%','%FIELD%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%','%UNION%','%INDEX%'),
				array(
						$parseTableRes,
						$this->parseDistinct(isset($options['distinct'])?$options['distinct']:false),
						$this->parseField(isset($options['field'])?$options['field']:'*'),
						$this->parseJoin(isset($options['on']) ? $options : array()),
						$this->parseWhere(isset($options['where'])?$options['where']:''),
						$this->parseGroup(isset($options['group'])?$options['group']:''),
						$this->parseHaving(isset($options['having'])?$options['having']:''),
						$this->parseOrder(isset($options['order'])?$options['order']:''),
						$this->parseLimit(isset($options['limit'])?$options['limit']:''),
						$this->parseUnion(isset($options['union'])?$options['union']:''),
						$this->parseIndex(isset($options['index'])?$options['index']:'')
				),$sql);
		
		
		if (strstr($sql, "JOIN") && (strstr($sql, "ON")))
		{
			$sql = str_replace($parseTableRes, "", $sql);
		}
		
//		var_dump($sql);
// 		echo "bbb\n";

		return $sql;
	}

	protected function parseUnion(){
		return '';
	}

	protected function parseLock($lock=false) {
		if(!$lock) return '';
		return ' FOR UPDATE ';
	}

	protected function parseIndex($value){
		return empty($value) ? '':' USE INDEX ('.$value.') ';
	}

	protected function parseValue($value) {
		if(is_string($value) || is_numeric($value)) {
			$value = '\''.$this->escapeString($value).'\'';
		}elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
			$value   =  $value[1];
		}elseif(is_array($value)) {
			$value   =  array_map(array($this, 'parseValue'),$value);
		}elseif(is_null($value)){
			$value   =  'NULL';
		}
		return $value;
	}

	protected function parseField($fields) {
		if(is_string($fields) && strpos($fields,',')) {
			$fields    = explode(',',$fields);
		}
		if(is_array($fields)) {
			//字段别名定义
			$array   =  array();
			foreach ($fields as $key=>$field){
				if(!is_numeric($key))
					$array[] =  $this->parseKey($key).' AS '.$this->parseKey($field);
				else
					$array[] =  $this->parseKey($field);
			}
			$fieldsStr = implode(',', $array);
		}elseif(is_string($fields) && !empty($fields)) {
			$fieldsStr = $this->parseKey($fields);
		}else{
			$fieldsStr = '*';
		}
		return $fieldsStr;
	}

	protected function parseTable($options) {
		//NOTICE: rm by mouyt 20171020 effection unknown
		//if ($options['on']) return null;
		$tables = $options['table'];
		if(is_array($tables)) {// 别名定义
			$array   =  array();
			foreach ($tables as $table=>$alias){
				if(!is_numeric($table))
					$array[] =  $this->parseKey($table).' '.$this->parseKey($alias);
				else
					$array[] =  $this->parseKey($table);
			}
			$tables  =  $array;
		}elseif(is_string($tables)){
			$tables  =  explode(',',$tables);
			array_walk($tables, array(&$this, 'parseKey'));
			//            if (strpos($options['table'],',') === false){
			//            	$tables = $options['table'].' AS '.$options['table'];
			//            }
			//            $tables  =  explode(',',$tables);
		}

		return implode(',',$tables);
	}

	protected function parseWhere($where) {
		$whereStr = '';
		if(is_string($where)) {
			$whereStr = $where;
		}elseif(is_array($where)){
			if(isset($where['_op'])) {
				// 定义逻辑运算规则 例如 OR XOR AND NOT
				$operate    =   ' '.strtoupper($where['_op']).' ';
				unset($where['_op']);
			}else{
				$operate	=   ' AND ';
			}
			foreach ($where as $key=>$val){
				//                $whereStr .= '( ';
				//                if(!preg_match('/^[A-Z_\|\&\-.a-z0-9]+$/',trim($key))){
				//                    $error = 'Model Error: args '.$key.' is wrong!';
				//                    throw_exception($error);
				//                }
				//                $key = trim($key);
				//                $whereStr   .= $this->parseWhereItem($this->parseKey($key),$val);
				//                $whereStr .= ' )'.$operate;
				$whereStrTemp = '';
				if(0===strpos($key,'_')) {
					// 解析特殊条件表达式
					//                    $whereStr   .= $this->parseThinkWhere($key,$val);
				}else{
					// 查询字段的安全过滤
					if(!preg_match('/^[A-Z_\|\&\-.a-z0-9]+$/',trim($key))){
						throw_exception($error);
					}
					// 多条件支持
					$multi = is_array($val) &&  isset($val['_multi']);
					$key = trim($key);
					if(strpos($key,'|')) { // 支持 name|title|nickname 方式定义查询字段
						$array   =  explode('|',$key);
						$str   = array();
						foreach ($array as $m=>$k){
							$v =  $multi?$val[$m]:$val;
							$str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
						}
						$whereStrTemp .= implode(' OR ',$str);
					}elseif(strpos($key,'&')){
						$array   =  explode('&',$key);
						$str   = array();
						foreach ($array as $m=>$k){
							$v =  $multi?$val[$m]:$val;
							$str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
						}
						$whereStrTemp .= implode(' AND ',$str);
					}else{
						$whereStrTemp   .= $this->parseWhereItem($this->parseKey($key),$val);
					}
				}
				if(!empty($whereStrTemp)) {
					$whereStr .= '( '.$whereStrTemp.' )'.$operate;
				}
			}
			$whereStr = substr($whereStr,0,-strlen($operate));
		}
		return empty($whereStr)?'':' WHERE '.$whereStr;
	}

	// where子单元分析
	protected function parseWhereItem($key,$val) {
		$whereStr = '';
		if(is_array($val)) {
			if(is_string($val[0])) {
				if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|NOTLIKE|LIKE)$/i',$val[0])) { // 比较运算
					$whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
				}elseif('exp'==strtolower($val[0])){ // 使用表达式
					//                    $whereStr .= ' ('.$key.' '.$val[1].') ';
					$whereStr .= $val[1];
				}elseif(preg_match('/IN/i',$val[0])){ // IN 运算
					if(isset($val[2]) && 'exp'==$val[2]) {
						$whereStr .= $key.' '.strtoupper($val[0]).' '.$val[1];
					}else{
						if (empty($val[1])){
							$whereStr .= $key.' '.strtoupper($val[0]).'(\'\')';
						}elseif(is_string($val[1]) || is_numeric($val[1])) {
							$val[1] =  explode(',',$val[1]);
							$zone   =   implode(',',$this->parseValue($val[1]));
							$whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
						}elseif(is_array($val[1])){
							$zone   =   implode(',',$this->parseValue($val[1]));
							$whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
						}
					}
				}elseif(preg_match('/BETWEEN/i',$val[0])){
					$data = is_string($val[1])? explode(',',$val[1]):$val[1];
					if($data[0] && $data[1]) {
						$whereStr .=  ' ('.$key.' '.strtoupper($val[0]).' '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1]).' )';
					} elseif ($data[0]) {
						$whereStr .= $key.' '.$this->comparison['gt'].' '.$this->parseValue($data[0]);
					} elseif ($data[1]) {
						$whereStr .= $key.' '.$this->comparison['lt'].' '.$this->parseValue($data[1]);
					}
				}elseif(preg_match('/TIME/i',$val[0])){
					$data = is_string($val[1])? explode(',',$val[1]):$val[1];
					if($data[0] && $data[1]) {
						$whereStr .=  ' ('.$key.' BETWEEN '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1] + 86400 -1).' )';
					} elseif ($data[0]) {
						$whereStr .= $key.' '.$this->comparison['gt'].' '.$this->parseValue($data[0]);
					} elseif ($data[1]) {
						$whereStr .= $key.' '.$this->comparison['lt'].' '.$this->parseValue($data[1] + 86400);
					}
				}else{
					$error = 'Model Error: args '.$val[0].' is error!';
					throw_exception($error);
				}
			}else {
				$count = count($val);
				if(in_array(strtoupper(trim($val[$count-1])),array('AND','OR','XOR'))) {
					$rule = strtoupper(trim($val[$count-1]));
					$count   =  $count -1;
				}else{
					$rule = 'AND';
				}
				for($i=0;$i<$count;$i++) {
					if (is_array($val[$i])){
						if (is_array($val[$i][1])){
							$data = implode(',',$val[$i][1]);
						}else{
							$data = $val[$i][1];
						}
					}else{
						$data = $val[$i];
					}
					if('exp'==strtolower($val[$i][0])) {
						$whereStr .= '('.$key.' '.$data.') '.$rule.' ';
					}else{
						$op = is_array($val[$i])?$this->comparison[strtolower($val[$i][0])]:'=';
						if(preg_match('/IN/i',$op)){
							$whereStr .= '('.$key.' '.$op.' ('.$this->parseValue($data).')) '.$rule.' ';
						}else{
							$whereStr .= '('.$key.' '.$op.' '.$this->parseValue($data).') '.$rule.' ';
						}

					}
				}
				$whereStr = substr($whereStr,0,-4);
			}
		}else {
			$whereStr .= $key.' = '.$this->parseValue($val);
		}
		return $whereStr;
	}

	protected function parseLimit($limit) {
		return !empty($limit)?   ' LIMIT '.$limit.' ':'';
	}

	protected function parseJoin($options = array()) {

		$joinStr = '';

		if (false === strpos($options['table'],',')) return null;
		$table = explode(',', $options['table']);
		$on = explode(',',$options['on']);
		$join = $options['join'];
		
		$joinStr .= $table[0];
		
		for($i=0;$i<(count($table)-1);$i++) {
			$joinStr .= ' '.($join[$i]?$join[$i]:'LEFT JOIN').' '.$table[$i+1].' ON '.($on[$i]?$on[$i]:'');
		}

		return $joinStr;
	}

	public function delete($options=array()) {
		$sql   = 'DELETE '.$this->parseAttr($options).' FROM '
				.$this->parseTable($options)
				.$this->parseWhere(isset($options['where'])?$options['where']:'')
				.$this->parseOrder(isset($options['order'])?$options['order']:'')
				.$this->parseLimit(isset($options['limit'])?$options['limit']:'');
		if (stripos($sql,'where') === false && $options['where'] !== true){
			//防止条件传错，删除所有记录
			return false;
		}
		return Db::execute($sql);
	}

	public function update($data,$options) {
		$sql   = 'UPDATE '
				.$this->parseAttr($options)
				.$this->parseTable($options)
				.$this->parseSet($data)
				.$this->parseWhere(isset($options['where'])?$options['where']:'')
				.$this->parseOrder(isset($options['order'])?$options['order']:'')
				.$this->parseLimit(isset($options['limit'])?$options['limit']:'');
		if (stripos($sql,'where') === false && $options['where'] !== true){
			//防止条件传错，更新所有记录
			return false;
		}
// 		var_dump($sql);
// 		exit;
		return Db::execute($sql);
	}

	public function parseAttr($options){
		if (isset($options['attr'])){
			if (in_array(isset($options['attr']),array('LOW_PRIORITY','QUICK','IGNORE','HIGH_PRIORITY','SQL_CACHE','SQL_NO_CACHE'))){
				return $options['attr'].' ';
			}
		}else{
			return '';
		}
	}

	public function lockAttr($options){
		if (isset($options['attr'])){
			if (in_array($options['attr'],array('FOR UPDATE'))){
				return ' '.$options['attr'].' ';
			}
		}else{
			return '';
		}
	}

	/**
	 * 清空表
	 *
	 * @param array $options
	 * @return boolean
	 */
	public function clear($options){
		$sql = 'TRUNCATE TABLE '.$this->parseTable($options);
		return Db::execute($sql);
	}
	public function insert($data,$options=array(),$replace=false) {
		$values  =  $fields    = array();
		foreach ($data as $key=>$val){
			$value   =  $this->parseValue($val);
			if(is_scalar($value)) {
				$values[]   =  $value;
				$fields[]     =  $this->parseKey($key);
			}
		}
		$sql   =  ($replace?'REPLACE ':'INSERT ').$this->parseAttr($options).' INTO '.$this->parseTable($options).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
		return Db::execute($sql);
	}

	public function getLastId() {
		return Db::getLastId();
	}

	/**
	 * 批量插入
	 *
	 * @param unknown_type $datas
	 * @param unknown_type $options
	 * @param unknown_type $replace
	 * @return unknown
	 */
	public function insertAll($datas,$options=array(),$replace=false) {
		if(!is_array($datas[0])) return false;
		$fields = array_keys($datas[0]);
		array_walk($fields, array($this, 'parseKey'));
		$values  =  array();
		foreach ($datas as $data){
			$value   =  array();
			foreach ($data as $key=>$val){
				$val   =  $this->parseValue($val);
				if(is_scalar($val)) {
					$value[]   =  $val;
				}
			}
			$values[]    = '('.implode(',', $value).')';
		}
		$sql   =  ($replace?'REPLACE':'INSERT').' INTO '.$this->parseTable($options).' ('.implode(',', $fields).') VALUES '.implode(',',$values);
		return Db::execute($sql);
	}

	protected function parseOrder($order) {
		if(is_array($order)) {
			$array   =  array();
			foreach ($order as $key=>$val){
				if(is_numeric($key)) {
					$array[] =  $this->parseKey($val);
				}else{
					$array[] =  $this->parseKey($key).' '.$val;
				}
			}
			$order   =  implode(',',$array);
		}
		return !empty($order)?  ' ORDER BY '.$order:'';
	}

	protected function parseGroup($group) {
		return !empty($group)? ' GROUP BY '.$group:'';
	}

	protected function parseHaving($having) {
		return  !empty($having)?   ' HAVING '.$having:'';
	}

	protected function parseDistinct($distinct) {
		return !empty($distinct)?   ' DISTINCT '.$distinct.',' :'';
	}

	protected function parseSet($data) {
		foreach ($data as $key=>$val){
			$value   =  $this->parseValue($val);
			if(is_scalar($value))
				$set[]    = $this->parseKey($key).'='.$value;
		}
		return ' SET '.implode(',',$set);
	}

	public function escapeString($str) {
		$str = addslashes(stripslashes($str));//重新加斜线，防止从数据库直接读取出错
		return $str;
	}

	protected function parseKey(&$key) {
		return $key;
	}

	public function checkActive($host) {
		Db::ping($host);
	}
}