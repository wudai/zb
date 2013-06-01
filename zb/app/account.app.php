<?php

class AccountApp extends FrontendApp {
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
			if (!array_key_exists($type, $type_list)) {
				$this->show_warning('错误的账号类型');
			}
			$account_name = trim($_POST['account_name']);
			if (!strlen($account_name)) {
				$this->show_warning('请填写账号名');
			}
			if ($exist = $this->_amod->get(array('conditions' => "user_id={$this->_user_id} AND account_name='{$account_name}'"))) {
				$this->show_warning('该账户名已存在，请更换另外的账户名');
			}
			$sort = intval($_POST['sort']);
			$data = array(
				'user_id'		=> $this->_user_id,
				'account_name'	=> $account_name,
				'type'			=> $type,
				'sort'			=> $sort,
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
		$list = $this->_oumod->findAll(array(
			'conditions'	=> 'user_id='.$this->_user_id,
			'include'		=> array(
				'has_oa'	=> array('fields' => 'account_name,expense,income,balance', 'order' => '`sort` DESC'),
			),
			'order' => '`sort` DESC',
		));
		$this->assign('list', $list);
		$this->display('account/out.html');
	}

	function outadd() {
		$user_list = $this->_oumod->get_options('user_id='.$this->_user_id, 'user_name', 'outer_user_id');
		if (!IS_POST) {
			$this->assign('user_list', $user_list);
			$this->display('account/outadd.html');
		} else {
			$ou_id = intval($_POST['outer_user_id']);
			if ($ou_id > 0) {
				if (!array_key_exists($ou_id, $user_list)) {
					$this->show_warning('你选择了错误的用户');
				}
				$is_new = false;
			} else {
				$user_name = trim($_POST['user_name']);
				if (!strlen($user_name)) {
					$this->show_warning('请填写用户名');
				}
				if ($this->_oumod->get(array('conditions' => "user_id={$this->_user_id} AND user_name='$user_name'"))) {
					$this->show_warning('该用户已存在，请从下拉列表中选择或填写不同的姓名');
				}
				$ou_data = array(
					'user_id'	=> $this->_user_id,
					'user_name' => $user_name,
					'create_time' => TIME,
				);
				$ou_id = $this->_oumod->add($ou_data);
				if (!$ou_id) {
					$this->show_warning('添加用户失败');
				}
				$is_new = true;
			}
			$account_name = trim($_POST['account_name']);
			if ($is_new){
				if (!strlen($account_name)) {
					$account_name = $user_name;
				}
			} else {
				if (strlen($account_name)) {
					if ($this->_oamod->get(array('conditions' => "outer_user_id=$ou_id AND account_name='$account_name'"))) {
						$this->show_warning('该账户名已存在，请填写不同的账户名', '返回', '/index.php?app=account&act=outadd&ou_id='.$ou_id);
					}
				} else {
					if ($this->_oamod->get(array('conditions' => "outer_user_id=$ou_id AND account_name='$user_name'"))) {
						$this->show_warning('请填写账户名', '返回', '/index.php?app=account&act=outadd&ou_id='.$ou_id);
					} else {
						$account_name = $user_name;
					}
				}
			}
			$data = array(
				'user_id'		=> $this->_user_id,
				'outer_user_id'	=> $ou_id,
				'account_name'	=> $account_name,
				'create_time'	=> TIME,
			);
			if ($this->_oamod->add($data)) {
				$this->show_message('添加成功',
					'返回列表', '/index.php?app=account&act=out',
					'继续添加', '/index.php?app=account&act=outadd'
				);
			}
		}
	}
}
