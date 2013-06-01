<?php

class BillApp extends FrontendApp {
	var $_amod = null;
	var $_oamod = null;
	var $_oumod = null;
	function __construct() {
		parent::__construct();
		$this->login();
		$this->_amod = &m('account');
		$this->_oumod = &m('outer_user');
		$this->_oamod = &m('outer_account');
	}
	function addsingle() {
		if (!IS_POST) {
			$this->display('bill/addsingle.html');
		} else {
		}
	}

	function transfer_out() {
		if (!IS_POST) {
			$account_options = $this->_amod->get_options('user_id='.$this->_user_id, 'user_name', 'outer_user_id');
			$outer_users = $this->_oumod->findAll(array(
				'conditions'	=> 'ou.user_id='.$this->_user_id,
				'include'	=> array(
					'has_oa'	=> array(
						'fields' => 'account_name',
					),
				)
			));
			$outer_options = array();
			foreach ($outer_users as $user) {
				$outer_options[$user['user_name']] = array();
				foreach ($user['has_oa'] as $oa) {
					$outer_options[$user['user_name']][$oa['oa_id']] = $oa['account_name'];
				}
			}
			$this->assign(array(
				'account_options'	=> $account_options,
				'outer_options'	=> $outer_options,
			));
			$this->display('bill/transfer_out.html');
		} else {
		}
	}

	function transfer_out() {
		if (!IS_POST) {
			$this->display('bill/transfer_out.html');
		} else {
		}
	}
}
