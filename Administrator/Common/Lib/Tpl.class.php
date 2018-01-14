<?php
/**
 * 模板驱动
 *
 * 模板驱动，商城模板引擎
 *
 *
 * @package    tpl
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Common\Lib;

class Tpl {

	static public function groupedValues($data, $cou_id, $sk_id) {
		foreach ( $data as $k => $v ) {
			$quna [$v [$cou_id]] [$v [$sk_id]] = $v [$sk_id];
		}
		return $quna;
	}
	static public function indexedValues($data, $sku_id, $cou_id) {
		if (! empty ( $data )) {
			foreach ( $data as $k => $v ) {
				$quna [$v [$sku_id]] = $v [$cou_id];
			}
		} else {
			$quna = $data;
		}
		return $quna;
	}
	static public function groupIndexed($data, $cou_id, $sk_id) {
		foreach ( $data as $k => $v ) {
			if ($sk_id == 'sku_id') {
				$quna [$v [$cou_id]] [$v [$sk_id]] = array (
						'price' => $v ['price']
				);
			} else if ($sk_id == 'xlevel') {
				$quna [$v [$cou_id]] [$v [$sk_id]] = $v;
			}
		} // print_r($quna);
		return $quna;
	}
	static public function indexed($indexed_data, $indexed_xlevel) {
		if ($indexed_xlevel == 'xlevel') {
			foreach ( $indexed_data as $indexed_k => $indexed_v ) {
				$data [$indexed_v [$indexed_xlevel]] = $indexed_v;
			}
		} elseif ($indexed_xlevel == 'goods_id') {
			foreach ( $indexed_data as $index_k => $index_v ) {
				$data [$index_v [$indexed_xlevel]] = $index_v;
			}
		}
		return $data;
	}
	static public function uniqueValues($data, $sku_id) {
		print_r ( $data );
		return $data;
	}
	
}
