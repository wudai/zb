<?php

class ExpenseModel extends BaseModel {
	var $table	= 'expense';
	var $prikey	= 'expense_id';
	var $_name	= 'expense';
	var $alias	= 'ex';

	var $_relation = array(
		'belongs_to_event' => array(
			'model'		=> 'event',
			'type'		=> BELONGS_TO,
			'reverse'	=> 'has_ex',
		),
	);

	var $_cache = array();

	function getTypeList($user_id) {
		$common = array(
			1	=> '衣',
			2	=> '食',
			3	=> '住',
			4	=> '行',
			99	=> '其他',
		);
		if ($user_id <= 0) return $common;
		if (array_key_exists($user_id, $this->_cache)) return $this->_cache[$user_id];
		$et_mod = &m('user_expense_type');
		$custom_list = $et_mod->get_options("user_id=$user_id", "type_name", "type_id", "sort_order");
		if ($custom_list) {
			$this->_cache[$user_id] = $custom_list;
			return $custom_list;
		}
		$this->_cache[$user_id] = $common;
		return $common;
	}
}
