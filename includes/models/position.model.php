<?php

/**
 * PositionModel 
 *
 * 地点
 * 
 * @uses BaseModel
 * @package sooker
 * @copyright www.sooker.com Inc
 * @author matao <matao@sooker.com> 
 */
class PositionModel extends  BaseModel {
	var $table	= 'position';
	var $prikey = 'position_id';
	var $_name	= 'position';
	var $alias	= 'pos';

	function getPositionByName($q, $user_id, $limit=5) {
		$res = $this->find(array(
			'conditions'	=> "position_id<100000 AND position_name like '%$q%'",
			'limit'			=> $limit,
			'fields'		=> 'position_id, position_name',
		));
		if (count($res) == $limit) {
			return $res;
		}
		if (!$res) $res = array();
		$limit = $limit - count($res);
		$personal_res = $this->find(array(
			'conditions'	=> "user_id=$user_id AND position_name like '%$q%'",
			'limit'			=> $limit,
			'fields'		=> 'position_id, position_name',
		));
		if ($personal_res) {
			$res = array_merge($res, $personal_res);
		}
		return $res;
	}
}
