<?php

class Outer_userModel extends BaseModel {
	var $table	= 'outer_user';
	var $prikey = 'outer_user_id';
	var $_name	= 'outer_user';
	var $alias	= 'ou';
	var $_relation = array(
		'belongs_to_user' => array(
			'model'		=> 'user',
			'type'		=> BELONGS_TO,
			'reverse'	=> 'has_outer_user',
		),
		'has_account'		=> array(
			'model'			=> 'account',
			'type'			=> HAS_MANY,
			'foreign_key'	=> 'outer_user_id',
			'refer_key'		=> 'outer_user_id',
		),
	);

	function get_group_options($user_id) {
		$a_cond = array(
			'user_id='.$user_id,
			'outer_user_id>0'
		);
		$amod = &m('account');
		$user_list = $this->find(array(
			'conditions'	=> 'user_id='.$user_id,
			'fields'		=> 'outer_user_id, user_name',
			'order'			=> 'sort_order',
		));
		$account_list = $amod->find(array(
			'conditions'	=> implode(' AND ', $a_cond),
			'fields'		=> 'outer_user_id, account_id, account_name',
			'order'			=> 'sort_order',
		));
		$res = $account_group = array();
		foreach ($account_list as $account) {
			$account_group[$account['outer_user_id']][$account['account_id']] = $account['account_name'];
		}
		foreach ($user_list as $ou) {
			if ($account_group[$ou['outer_user_id']]) {
				$res[$ou['user_name']] = $account_group[$ou['outer_user_id']];
			}
		}
		return $res;
	}
}
