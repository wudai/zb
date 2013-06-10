<?php

/**
 * EventModel 
 *
 * 事件表
 * 
 * @uses BaseModel
 * @package sooker
 * @copyright www.sooker.com Inc
 * @author matao <matao@sooker.com> 
 */
class EventModel extends BaseModel {
	var $table	= 'event';
	var $prikey = 'event_id';
	var $_name	= 'event';
	var $alias	= 'e';

	const TYPE_TRANSFER_OUT			= 1;
	const TYPE_TRANSFER_IN			= 2;
	const TYPE_TRANSFER_INNER		= 3;
	const TYPE_TRANSFER_OUTER		= 4;
	const TYPE_EXPENSES				= 5;
	const TYPE_INCOME				= 6;
	const TYPE_DINNER				= 7;
	const TYPE_OTHER				= 8;

	var $_relation = array(
		'has_ea'	=> array(
			'model'			=> 'event_account',
			'type'			=> HAS_MANY,
			'foreign_key'	=> 'event_id',
			'refer_key'		=> 'event_id',
		),
		'has_ex'	=> array(
			'model'			=> 'expense',
			'type'			=> HAS_MANY,
			'foreign_key'	=> 'event_id',
			'refer_key'		=> 'event_id',
		),
		'belongs_to_position'	=> array(
			'model'			=> 'position',
			'type'			=> BELONGS_TO,
			'reverse'		=> 'has_event',
		),
	);

	function getTypeList() {
		return array(
			self::TYPE_TRANSFER_OUT			=> '转出',
			self::TYPE_TRANSFER_IN			=> '转入',
			self::TYPE_TRANSFER_INNER		=> '内部转账',
			self::TYPE_TRANSFER_OUTER		=> '外债账户间转账',
			self::TYPE_EXPENSES				=> '花费',
			self::TYPE_INCOME				=> '收入',
			self::TYPE_DINNER				=> '聚餐',
			self::TYPE_OTHER				=> '其它',
		);
	}
}
