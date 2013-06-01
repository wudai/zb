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
		'has_oa'		=> array(
			'model'			=> 'outer_account',
			'type'			=> HAS_MANY,
			'foreign_key'	=> 'outer_user_id',
			'refer_key'		=> 'outer_user_id',
		),
	);
}
