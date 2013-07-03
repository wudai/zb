<?php

class OutApp extends FrontendApp {
	var $_amod = null;
	var $_oumod = null;
	var $_eamod = null;
	function __construct() {
		parent::__construct();
		$this->_amod = &m('account');
		$this->_oumod = &m('outer_user');
		$this->_eamod = &m('event_account');
	}

	function index() {
		if ($_GET['md5']) {
			if (!$info = $this->_oumod->auth($_GET['md5'])) {
				$this->show_warning('密码错误');
			} else {
				$this->visitor->assign(array(
					'ou_id' => $info['outer_user_id'],
					'user_name' => $info['user_name'],
				));
				$ou_id = $info['outer_user_id'];
			}
		} elseif (!$this->visitor->has_out) {
			$this->show_warning('请使用正确的链接访问');
		} else {
			$ou_id = $this->visitor->info['ou_id'];
		}

		$info = $this->_oumod->get_info($ou_id);
		if (!$info) {
			$this->show_warning('账号不存在');
		}
		$account_all = $this->_amod->getList($info['user_id']);
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
		$filtered = count($conditions) > 1;
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
		$this->display('out/index.html');
	}

}
