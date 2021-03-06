<?php

class AccountApp extends FrontendApp {
	var $_amod = null;
	var $_oumod = null;
	var $_eamod = null;
	function __construct() {
		parent::__construct();
		$this->login();
		$this->_amod = &m('account');
		$this->_oumod = &m('outer_user');
		$this->_eamod = &m('event_account');
	}
	function index() {
		$list = $this->_amod->find(array(
			'conditions'	=> 'user_id='.$this->_user_id.' AND outer_user_id=0',
			'order'			=> 'sort_order',
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
			$sort_order = intval($_POST['sort_order']);
			if (!in_array($sort_order, range(1, 10))) {
				$this->show_warning('排序数字填写错误，请填写1-10之间的整数');
			}
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
			$data = array(
				'user_id'		=> $this->_user_id,
				'account_name'	=> $account_name,
				'type'			=> $type,
				'sort_order'	=> $sort_order,
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

	function edit() {
		$type_list = $this->_amod->getTypeList();
		$account_id = $_GET['id'];
		if ($account_id <=0 ) {
			$this->show_warning('请选择账号操作');
		}
		$info = $this->_amod->get_info($account_id);
		if (!$info || $info['user_id'] != $this->_user_id) {
			$this->show_warning('请选择正确的账号操作');
		}
		if (!IS_POST) {
			$this->assign('info', $info);
			$this->assign('type_list', $type_list);
			$this->display('account/add.html');
		} else {
			$sort_order = intval($_POST['sort_order']);
			if (!in_array($sort_order, range(1, 10))) {
				$this->show_warning('排序数字填写错误，请填写1-10之间的整数');
			}
			$type = intval($_POST['type']);
			if (!array_key_exists($type, $type_list)) {
				$this->show_warning('错误的账号类型');
			}
			$account_name = trim($_POST['account_name']);
			if (!strlen($account_name)) {
				$this->show_warning('请填写账号名');
			}
			if ($info['account_name'] != $account_name && $exist = $this->_amod->get(array('conditions' => "user_id={$this->_user_id} AND account_name='{$account_name}'"))) {
				$this->show_warning('该账户名已存在，请更换另外的账户名');
			}
			if ($info['type'] == $type && $info['account_name'] == $account_name && $info['sort_order'] == $sort_order) {
				$this->show_message('编辑成功',
					'返回列表', '/index.php?app=account'
				);
			}
			$data = array(
				'user_id'		=> $this->_user_id,
				'account_name'	=> $account_name,
				'type'			=> $type,
				'sort_order'	=> $sort_order,
			);
			if ($this->_amod->edit($account_id, $data)) {
				$this->show_message('编辑成功',
					'返回列表', '/index.php?app=account'
				);
			}
		}
	}

	function out() {
		$list = $this->_oumod->findAll(array(
			'conditions'	=> 'user_id='.$this->_user_id,
			'include'		=> array(
				'has_account'	=> array('fields' => 'account_name,expense,income,balance', 'order' => 'sort_order'),
			),
			'order' => 'sort_order',
		));
		$this->assign('list', $list);
		$this->display('account/out.html');
	}

	function outadd() {
		$user_list = $this->_oumod->get_options('user_id='.$this->_user_id, 'user_name', 'outer_user_id');
		if (!IS_POST) {
			$arr = array();
			foreach ($user_list as $ou_id => $name) {
				$arr[] = array('id' => $ou_id, 'value' => $name);
			}
			$this->assign('user_list', $arr);
			$this->display('account/outadd.html');
		} else {
			$sort_order = intval($_POST['sort_order']);
			if (!in_array($sort_order, range(1, 10))) {
				$this->show_warning('排序数字填写错误，请填写1-10之间的整数');
			}
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
					if ($this->_amod->get(array('conditions' => "user_id={$this->_user_id} AND outer_user_id=$ou_id AND account_name='$account_name'"))) {
						$this->show_warning('该账户名已存在，请填写不同的账户名', '返回', '/index.php?app=account&act=outadd&ou_id='.$ou_id);
					}
				} else {
					if ($this->_amod->get(array('conditions' => "user_id={$this->_user_id} AND outer_user_id=$ou_id AND account_name='$user_name'"))) {
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
				'sort_order'	=> $sort_order,
				'create_time'	=> TIME,
			);
			if ($this->_amod->add($data)) {
				$this->show_message('添加成功',
					'返回列表', '/index.php?app=account&act=out',
					'继续添加', '/index.php?app=account&act=outadd'
				);
			}
		}
	}

	function outedit() {
		$user_list = $this->_oumod->get_options('user_id='.$this->_user_id, 'user_name', 'outer_user_id');
		$account_id = $_GET['oa_id'];
		if ($account_id <=0 ) {
			$this->show_warning('请选择账号操作');
		}
		$info = $this->_amod->get_info($account_id);
		if (!$info || $info['user_id'] != $this->_user_id || $info['outer_user_id'] != $_GET['ou_id']) {
			$this->show_warning('请选择正确的账号操作');
		}
		if (!IS_POST) {
			$this->assign('user_list', $user_list);
			$this->assign('info', $info);
			$this->display('account/outadd.html');
		} else {
			$sort_order = intval($_POST['sort_order']);
			if (!in_array($sort_order, 1, 10)) {
				$this->show_warning('排序数字填写错误，请填写1-10之间的整数');
			}
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
					if ($account_name != $info['account_name'] && $this->_amod->get(array('conditions' => "user_id={$this->_user_id} AND outer_user_id=$ou_id AND account_name='$account_name'"))) {
						$this->show_warning('该账户名已存在，请填写不同的账户名', '返回', '/index.php?app=account&act=outadd&ou_id='.$ou_id);
					}
				} else {
					if ($this->_amod->get(array('conditions' => "user_id={$this->_user_id} AND outer_user_id=$ou_id AND account_name='$user_name'"))) {
						$this->show_warning('请填写账户名', '返回', '/index.php?app=account&act=outadd&ou_id='.$ou_id);
					} else {
						$account_name = $user_name;
					}
				}
			}
			if ($info['outer_user_id'] == $ou_id && $info['account_name'] == $account_name && $info['sort_order'] == $sort_order) {
				$this->show_message('编辑成功',
					'返回列表', '/index.php?app=account&act=out'
				);
			}
			$data = array(
				'user_id'		=> $this->_user_id,
				'outer_user_id'	=> $ou_id,
				'account_name'	=> $account_name,
				'sort_order'	=> $sort_order,
			);
			if ($this->_amod->edit($info['account_id'], $data)) {
				$this->show_message('编辑成功',
					'返回列表', '/index.php?app=account&act=out'
				);
			}
		}
	}

	function detail() {
		$account_id = intval($_GET['id']);
		if ($account_id <= 0) {
			$this->show_warning('账号不存在');
		}
		$info = $this->_amod->getById($this->_user_id, $account_id);
		if (!$info) {
			$this->show_warning('账号不存在');
		}
		$conditions = array(
			'account_id='.$account_id,
		);
		if ($_GET['start_date']) {
			$conditions[] = "ea.event_date>='". $_GET['start_date']."'";
		}
		if ($_GET['end_date']) {
			$conditions[] = "ea.event_date<='". $_GET['end_date']."'";
		}
		$filtered = (boolean)count($conditions > 1);
		$page = $this->_get_page();
		$list = $this->_eamod->find(array(
			'conditions'	=> implode(' AND ', $conditions),
			'fields'		=> 'this.*',
			'limit'			=> $page['limit'],
			'count'			=> true,
			'order'			=> 'ea.event_date DESC, id DESC',
		));
		$page['item_count'] = $this->_eamod->getCount();
		$this->_format_page($page);
		$assign = array(
			'info'		=> $info,
			'list'		=> $list,
			'page_info'	=> $page,
			'filtered'	=> $filtered,
		);
		if ($filtered) {
			$balance = $this->_eamod->getSum(array(
				'conditions'	=> implode(' AND ', $conditions),
			), 'amount');
			$conditions[] = 'ea.amount>0';
			$income = $this->_eamod->getSum(array(
				'conditions'	=> implode(' AND ', $conditions),
			), 'amount');
			$expense = $income - $balance;
			$assign['balance'] = $balance;
			$assign['income'] = $income;
			$assign['expense'] = $expense;
		}
		$this->assign($assign);
		$this->display('account/detail.html');
	}

	function outdetail() {
		$ou_id = intval($_GET['ou_id']);
		if ($ou_id <= 0) {
			$this->show_warning('外债用户不存在');
		}
		$info = $this->_oumod->get_info($ou_id);
		if (!$info || $info['user_id'] != $this->_user_id) {
			$this->show_warning('账号不存在');
		}
		$account_all = $this->_amod->getList($this->_user_id);
		$accounts = array();
		foreach ($account_all as $account) {
			if ($account['outer_user_id'] == $ou_id) {
				$accounts[$account['account_id']] = $account;
				$info['income'] += $account['income'];
				$info['expense'] += $account['expense'];
				$info['balance'] += $account['balance'];
			}
		}
		$conditions = array(
			db_create_in(array_keys($accounts), 'account_id'),
		);
		if ($_GET['start_date']) {
			$conditions[] = "ea.event_date>='". $_GET['start_date']."'";
		}
		if ($_GET['end_date']) {
			$conditions[] = "ea.event_date<='". $_GET['end_date']."'";
		}
		$filtered = (boolean) (count($conditions) > 1);
		$page = $this->_get_page();
		$list = $this->_eamod->find(array(
			'conditions'	=> implode(' AND ', $conditions),
			'fields'		=> 'this.*',
			'limit'			=> $page['limit'],
			'count'			=> true,
			'order'			=> 'ea.event_date DESC, id DESC',
		));
		$page['item_count'] = $this->_eamod->getCount();
		$this->_format_page($page);
		$assign = array(
			'info'		=> $info,
			'list'		=> $list,
			'page_info'	=> $page,
			'accounts'	=> $accounts,
			'filtered'	=> $filtered,
		);
		if ($filtered) {
			$balance = $this->_eamod->getSum(array(
				'conditions'	=> implode(' AND ', $conditions),
			), 'amount');
			$conditions[] = 'ea.amount>0';
			$income = $this->_eamod->getSum(array(
				'conditions'	=> implode(' AND ', $conditions),
			), 'amount');
			$expense = $income - $balance;
			$assign['balance'] = $balance;
			$assign['income'] = $income;
			$assign['expense'] = $expense;
		}
		$this->assign($assign);
		$this->display('account/outdetail.html');
	}

	function ajax_getouter() {
        $q = trim($_REQUEST['q']);
		$res = $this->_pmod->getPositionByName($q, $this->_user_id);
		$data = array();
		foreach ($res as $one) {
			$data[] = array(
				'id'	=> $one['position_id'],
				'value'	=> $one['position_name'],
			);
		}
		$this->json_out(0, '', $data);
	}

	function makeoutpass() {
		$ou_id = intval($_GET['ou_id']);
		if ($ou_id <= 0) {
			$this->show_warning('外债用户不存在');
		}
		$info = $this->_oumod->get_info($ou_id);
		if (!$info || $info['user_id'] != $this->_user_id) {
			$this->show_warning('账号不存在');
		}
		if (!$info['password']) {
			while (true) {
				$password = uniqid();
				if (!$exists = $this->_oumod->get(array('conditions' => "password='$password'"))) break;
			}
			$this->_oumod->edit($ou_id, array('password' => $password));
		}
		location('/index.php?app=account&act=outdetail&ou_id='.$ou_id);
	}
}
