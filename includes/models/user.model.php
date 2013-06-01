<?php

/**
 * UserModel 
 *
 * 用户数据模型
 * 
 * @uses BaseModel
 * @package zb
 * @copyright wudai9.net
 * @author wudai<wudai9net@qq.com> 
 */
class UserModel extends BaseModel {
	var $table	= 'user';
	var $prikey = 'user_id';
	var $_name	= 'user';
	var $alias  = 'u';

	var $_relation = array(
		'has_account'	=> array(
			'model'		=> 'account',
			'type'		=> HAS_MANY,
			'foreign_key'	=> 'user_id',
			'refer_key'		=> 'user_id',
		),
		'has_outer_user'	=> array(
			'model'		=> 'outer_user',
			'type'		=> HAS_MANY,
			'foreign_key'	=> 'user_id',
			'refer_key'		=> 'user_id',
		),
		'has_outer_account'	=> array(
			'model'		=> 'outer_account',
			'type'		=> HAS_MANY,
			'foreign_key'	=> 'user_id',
			'refer_key'		=> 'user_id',
		),
	);
	
	function add($data, $compatible = false) {
		if ($data['password']) {
			$data['password'] = $this->_encrypt($data['password']);
		}
		return parent::add($data, $compatible);
	}

	function edit($conditions, $edit_data) {
		if ($edit_data['password']) {
			$edit_data['password'] = $this->_encrypt($edit_data['password']);
		}
		return parent::edit($conditions, $edit_data);
	}

	function auth($user_name, $password) {
		$info = $this->get(array(
			'conditions' => "user_name='$user_name' AND password = '".$this->_encrypt($password) . "'",
		));
		if (!$info) {
			$this->_error('用户名或密码不正确');
		}
		return $info;
	}

	function unique($user_name) {
		return ! (bool) $this->getCol(array('conditions' => "user_name='$user_name'"), 'user_id');
	}

	private function _encrypt($password) {
		return md5($password . "zb@wudai9");
	}
}
