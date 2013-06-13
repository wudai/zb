<?php

class Event_accountModel extends BaseModel {
	var $table	= 'event_account';
	var $prikey	= 'id';
	var $_name	= 'event_account';
	var $alias	= 'ea';

	const TYPE_TRANSFER_OUT			= 1;
	const TYPE_TRANSFER_IN			= 2;
	const TYPE_EXPENSES				= 3;
	const TYPE_INCOME				= 4;
	const TYPE_COMPLEX				= 5;

	var $_relation = array(
		'belongs_to_event' => array(
			'model'		=> 'event',
			'type'		=> BELONGS_TO,
			'reverse'	=> 'has_ea',
		),
	);

	function getTypeList() {
		return array(
			self::TYPE_TRANSFER_OUT		=> '转出',
			self::TYPE_TRANSFER_IN		=> '转入',
			self::TYPE_EXPENSES			=> '花费',
			self::TYPE_INCOME			=> '收入',
			self::TYPE_COMPLEX			=> '复合支出',
		);
	}
}
