<?php

/**
 * User_expense_typeModel 
 *
 * 自定义消费类型
 * 
 * @uses BaseModel
 * @package sooker
 * @copyright www.sooker.com Inc
 * @author matao <matao@sooker.com> 
 */
class User_expense_typeModel extends BaseModel {
	var $table	= 'user_expense_type';
	var $prikey	= 'type_id';
	var $_name	= 'user_expense_type';
	var $alias	= 'uet';
}
