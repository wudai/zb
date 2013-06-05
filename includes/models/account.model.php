<?php

/**
 * AccountModel 
 *
 * 账户表
 * 
 * @uses BaseModel
 * @package sooker
 * @copyright www.sooker.com Inc
 * @author matao <matao@sooker.com> 
 */
class AccountModel extends BaseModel {
	var $table	= 'account';
	var $prikey = 'account_id';
	var $_name	= 'account';
	var $alias	= 'a';
	var $_relation = array(
		'belongs_to_user' => array(
			'model'		=> 'user',
			'type'		=> BELONGS_TO,
			'reverse'	=> 'has_account',
		),
		'belongs_to_outer_user' => array(
			'model'		=> 'outer_user',
			'type'		=> BELONGS_TO,
			'reverse'	=> 'has_account',
		),
		'has_log'	=> array(
			'model'			=> 'account_log',
			'type'			=> HAS_MANY,
			'foreign_key'	=> 'account_id',
			'refer_key'		=> 'account_id',
		),
	);

	const TYPE_CASH = 1;//现金
	const TYPE_DEPOSIT= 2;//储蓄卡
	const TYPE_CREDIT = 3;//信用卡
	const TYPE_FINANCIAL = 4;//理财账户

	function getTypeList() {
		return array(
			self::TYPE_CASH			=> '现金账户',
			self::TYPE_DEPOSIT		=> '储蓄卡',
			self::TYPE_CREDIT		=> '信用卡',
			self::TYPE_FINANCIAL	=> '理财账户',
		);
	}
}
