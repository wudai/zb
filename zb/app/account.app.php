<?php

class AccountApp extends FrontendApp {
	var $_amod = null;
	var $_oamod = null;
	function __construct() {
		$this->_amod = &m('account');
		$this->_oamod = &m('out_account');
	}
	function index() {
		$list = $this->_amod->find(array(
			'conditions'	=> 'user_id='.$this->_user_id,
		));
		$this->assign('list', $list);
		$this->display('account/index.html');
	}

	function add() {
		$type_list = $this->_amod->getTypeList();
		if (!IS_POST) {
			$this->assign('type_list', $type_list);
			$this->display('account/add.html');
		} else {
			$type = intval($_POST['type']);
			if (!in_array($type, $type_list)) {
				$this->show_warning('错误的账号类型');
			}
			$name = trim($_POST['name']);
			if (!strlen($name)) {
				$this->show_warning('请填写账号名');
			}
			$order = intval($_POST['order']);
			$data = array(
				'user_id'		=> $this->_user_id,
				'account_name'	=> $name,
				'type'			=> $type,
				'order'			=> $order,
				'create_time'	=> TIME,
			);
			if ($this->_amod->add($data)) {
				$this->show_message('添加成功',
					'返回列表', '/index.php?app=account',
					'继续添加', '/index.php?app=account&act=add'
				);
			}
		}
	}

	function out() {
		$list = $this->_oamod->find(array(
			'conditions'	=> 'user_id='.$this->_user_id,
		));
		$this->assign('list', $list);
		$this->display('account/out.html');
	}

	function addout() {
		$type_list = $this->_oamod->getTypeList();
		if (!IS_POST) {
			$this->assign('type_list', $type_list);
			$this->display('account/addout.html');
		} else {
			$type = intval($_POST['type']);
			if (!in_array($type, $type_list)) {
				$this->show_warning('错误的账号类型');
			}
			$name = trim($_POST['name']);
			if (!strlen($name)) {
				$this->show_warning('请填写账号名');
			}
			$order = intval($_POST['order']);
			$data = array(
				'user_id'		=> $this->_user_id,
				'account_name'	=> $name,
				'type'			=> $type,
				'order'			=> $order,
				'create_time'	=> TIME,
			);
			if ($this->_oamod->add($data)) {
				$this->show_message('添加成功',
					'返回列表', '/index.php?app=account&act=out',
					'继续添加', '/index.php?app=account&act=addout'
				);
			}
		}
	}
}
