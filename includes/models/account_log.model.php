<?php

/**
 * Account_logModel 
 *
 * 账户流水表
 * 
 * @uses BaseModel
 * @package sooker
 * @copyright www.sooker.com Inc
 * @author matao <matao@sooker.com> 
 */
class Account_logModel extends BaseModel {
	var $table	= 'account_log';
	var $prikey	= 'id';
	var $_name	= 'account_log';
	var $alias	= 'al';
	var $_relation = array(
		'belongs_to_account'	=> array(
			'model'		=> 'account',
			'type'		=> BELONGS_TO,
			'reverse'	=> 'has_log',
		),
	);
}
