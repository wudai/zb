<?php

class Outer_accountModel extends BaseModel {
	var $table	= 'outer_account';
	var $prikey = 'oa_id';
	var $_name	= 'outer_account';
	var $alias	= 'oa';
	var $_relation = array(
		'belongs_to_user' => array(
			'model'		=> 'user',
			'type'		=> BELONGS_TO,
			'reverse'	=> 'has_outer_account',
		),
		'belongs_to_ou'		=> array(
			'model'			=> 'outer_user',
			'type'			=> BELONGS_TO,
			'reverse'		=> 'has_oa',
		),
	);
}
