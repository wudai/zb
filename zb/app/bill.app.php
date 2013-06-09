<?php

class BillApp extends FrontendApp {
	var $_amod = null;
	var $_oumod = null;
	var $_tlmod = null;
	var $_emod = null;
	var $_eamod = null;
	var $_pmod = null;
	var $_eemod = null;
	function __construct() {
		parent::__construct();
		$this->login();
		$this->_amod = &m('account');
		$this->_oumod = &m('outer_user');
		$this->_tlmod = &m('transfer_log');
		$this->_emod = &m('event');
		$this->_eamod = &m('event_account');
		$this->_pmod = &m('position');
		$this->_exmod = &m('expense');
	}

	//{{{ 转账操作
	/**
	 * transfer 
	 * 
	 * @access public
	 * @return void
	 */
	function transfer() {
		if (!IS_POST) {
			$cond = array(
				'user_id='.$this->_user_id,
				'outer_user_id=0'
			);
			$account_options = $this->_amod->get_options(implode(' AND ', $cond), 'account_name', 'account_id', 'sort_order');
			$outer_options = $this->_oumod->get_group_options($this->_user_id);
			$this->assign(array(
				'account_options'	=> $account_options,
				'outer_options'	=> $outer_options,
			));
			$this->display('bill/transfer.html');
		} else {
			$out_id = intval($_POST['out_id']);
			$in_id = intval($_POST['in_id']);
			$amount = trim($_POST['amount']);
			$comment = trim($_POST['comment']);
			if ($out_id <= 0 || $in_id <= 0 || !check_money($amount)) {
				$this->show_warning('信息不完整');
			}
			$out_info = $this->_amod->get_info($out_id);
			$in_info = $this->_amod->get_info($in_id);
			if (!$out_info || $out_info['user_id'] != $this->_user_id) {
				$this->show_warning('转出账号错误');
			}
			if (!$in_info || $in_info['user_id'] != $this->_user_id) {
				$this->show_warning('转入账号错误');
			}
			$date = $_POST['bill_date'];
			if ($out_info['outer_user_id'] > 0) {
				if ($in_info['outer_user_id'] > 0) {
					$event_type = EventModel::TYPE_TRANSFER_OUTER;
				} else {
					$event_type = EventModel::TYPE_TRANSFER_IN;
				}
			} else {
				if ($in_info['outer_user_id'] > 0) {
					$event_type = EventModel::TYPE_TRANSFER_OUT;
				} else {
					$event_type = EventModel::TYPE_TRANSFER_INNER;
				}
			}
			$this->_db_begin();
			//将转账当作一个事件
			//创建事件
			$event_data = array(
				'user_id'		=> $this->_user_id,
				'type'			=> $event_type,
				'amount'		=> $amount,
				'event_date'	=> $date,
				'create_time'	=> TIME,
				'comment'		=> $comment,
			);
			$event_id = $this->_emod->add($event_data);
			if (!$event_id) {
				$this->show_warning('转账事件记录失败');
			}
			//记录支出事件详情
			$event_account_out_data = array(
				'event_id'			=> $event_id,
				'account_id'		=> $out_id,
				'type'				=> Event_accountModel::TYPE_TRANSFER_OUT,
				'amount'			=> 0 - $amount,
				'event_date'		=> $date,
				'comment'			=> $comment,
				'extra'				=> $in_id,
			);
			$event_account_out_id = $this->_eamod->add($event_account_out_data);
			if (!$event_account_out_id) {
				$this->_db_rollback();
				$this->show_warning('支出详细记录失败');
			}
			//记录转入事件详情
			$event_account_in_data = array(
				'event_id'			=> $event_id,
				'account_id'		=> $in_id,
				'type'				=> Event_accountModel::TYPE_TRANSFER_IN,
				'amount'			=> $amount,
				'event_date'		=> $date,
				'comment'			=> $comment,
				'extra'				=> $out_id,
			);
			$event_account_in_id = $this->_eamod->add($event_account_in_data);
			if (!$event_account_in_id) {
				$this->_db_rollback();
				$this->show_warning('转入详细记录失败');
			}
			//修改转出账号余额
			$out_data = array(
				'expense'	=> $out_info['expense'] + $amount,
				'balance'	=> $out_info['balance'] - $amount,
			);
			if (!$this->_amod->edit($out_id, $out_data)) {
				$this->_db_rollback();
				$this->show_warning('转出账户余额变更失败');
			}
			//修改转入账号余额
			$in_data = array(
				'income'	=> $in_info['income'] + $amount,
				'balance'	=> $in_info['balance'] + $amount,
			);
			if (!$this->_amod->edit($in_id, $in_data)) {
				$this->_db_rollback();
				$this->show_warning('转入账户余额变更失败');
			}
			$this->_db_commit();
			$this->show_message('记录添加成功',
				'继续添加', '/index.php?app=bill&act=transfer&tab='.$_GET['tab'].'&ou_id='.$out_id,
				'返回账户', '/index.php?app=account'
			);
		}
	}
	//}}}
	
	//{{{ 消费操作
	function expense() {
		if (!IS_POST) {
			$cond = array(
				'user_id='.$this->_user_id,
				'outer_user_id=0'
			);
			$account_options = $this->_amod->get_options(implode(' AND ', $cond), 'account_name', 'account_id', 'sort_order');
			$expense_type_list = $this->_exmod->getTypeList(0);
			$this->assign(array(
				'account_options'	=> $account_options,
				'outer_options'		=> $outer_options,
				'expense_type_list'	=> $expense_type_list,
			));
			$this->display('bill/expense.html');
		} else {
			$date = $_POST['bill_date'];
			//处理地点
			if ($_POST['position_id']) {
				$position = $this->_pmod->get_info($_POST['position_id']);
				if (!$position || ($position['user_id'] != 0 && $position['user_id'] != $this->_user_id)) {
					$this->show_warning('请选择正确的地点');
				}
			} elseif ($_POST['position_name']) {
				$position_name = trim($_POST['position_name']);
				if ($position = $this->_pmod->get(array('conditions' => "user_id=0 AND position_name='$position_name'"))) {
					$position_id = $position['position_id'];
				} elseif ($position = $this->_pmod->get(array('conditions' => "user_id={$this->_user_id} AND position_name='$position_name'"))) {
					$position_id = $position['position_id'];
				} else {
					$pos_data = array(
						'user_id' => $this->_user_id,
						'position_name' => $position_name,
					);
					$position_id = $this->_pmod->add($pos_data);
					if (!$position_id) {
						$this->show_warning('消费地点添加失败，请重新尝试');
					}
				}
			} else {
				$this->show_warning('请填写地点');
			}
			$amount = $_POST['amount'];
			if (!check_money($amount)) {
				$this->show_warning('请填写正确的金额');
			}
			$comment = trim($_POST['comment']);
			if (!strlen($comment)) {
				$this->show_warning('请填写事由');
			}
			//{{{支付账号处理
			$account_list = array();
			if (!is_array($_POST['account_id'])) {
				$account_id = intval($_POST['account_id']);
				if ($account_id <= 0) {
					$this->show_warning('请选择支付账号');
				}
				if ($_POST['inter_mediate'] > 0) {
					$account_list[] = array(
						'account_id'	=> $account_id,
						'type'			=> Event_accountModel::TYPE_TRANSFER_OUT,
						'amount'		=> 0 - $amount,
						'comment'		=> $comment,
						'extra'			=> $_POST['inter_mediate'],
					);
					$account_list[] = array(
						'account_id'	=> $_POST['inter_mediate'],
						'type'			=> Event_accountModel::TYPE_TRANSFER_IN,
						'amount'		=> $amount,
						'comment'		=> $comment,
						'extra'			=> $account_id,
					);
					$account_list[] = array(
						'account_id'	=> $_POST['inter_mediate'],
						'type'			=> Event_accountModel::TYPE_EXPENSES,
						'amount'		=> 0 - $amount,
						'comment'		=> $comment,
					);
				} else {
					$account_list[] = array(
						'account_id'	=> $account_id,
						'type'			=> Event_accountModel::TYPE_EXPENSES,
						'amount'		=> 0 - $amount,
						'comment'		=> $comment,
					);
				}
			} else {
				foreach ($_POST['account_id'] as $index => $account_id) {
					if ($_POST['inter_mediate'][$index]) {
						$account_list[$account_id] = array(
							'account_id'	=> $account_id,
							'type'			=> Event_accountModel::TYPE_TRANSFER_OUT,
							'amount'		=> 0 - $_POST['account_amount'][$index],
							'comment'		=> $comment,
							'extra'			=> $_POST['inter_mediate'][$index],
						);
						$account_list[$_POST['inter_mediate'][$index].'_in'] = array(
							'account_id'	=> $_POST['inter_mediate'][$index],
							'type'			=> Event_accountModel::TYPE_TRANSFER_IN,
							'amount'		=> $_POST['account_amount'][$index],
							'comment'		=> $comment,
							'extra'			=> $account_id,
						);
						if ($account_list[$_POST['inter_mediate'][$index].'_out']) {
							$account_list[$_POST['inter_mediate'][$index].'_out']['amount'] -= $_POST['account_amount'][$index];
						} else {
							$account_list[$_POST['inter_mediate'][$index].'_out'] = array(
								'account_id'	=> $_POST['inter_mediate'][$index],
								'type'			=> Event_accountModel::TYPE_EXPENSES,
								'amount'		=> 0 - $_POST['account_amount'][$index],
								'comment'		=> $comment,
							);
						}
					} else {
						$account_list[] = array(
							'account_id'	=> $account_id,
							'type'			=> Event_accountModel::TYPE_EXPENSES,
							'amount'		=> $_POST['account_amount'][$index],
							'comment'		=> $comment,
						);
					}
					$tmp_amount += $_POST['account_amount'][$index];
				}
				if ($tmp_amount != $amount) {
					$this->show_warning('总支付金额和支付账户的支付总金额不等');
				}
			}
			$account_ids = array_column($account_list, 'account_id');
			$account_infos = $this->_amod->find(array('conditions' => db_create_in($account_ids, 'account_id')));
			foreach ($account_ids  as $account_id) {
				if (!$account_infos[$account_id] || $account_infos[$account_id]['user_id'] != $this->_user_id) {
					$this->show_warning('错误的账号');
				}
			}//}}}
			//{{{消费详情处理
			$item_list = array();
			if (count($_POST['item_name'])) {
				foreach ($_POST['item_name'] as $index => $item_name) {
					if (!$item_name) continue;
					$item_list[] = array(
						'name'	=> $item_name,
						'type'	=> $_POST['item_type'][$index],
						'count' => $_POST['item_count'][$index] ? intval($_POST['item_count'][$index]) : 1,
						'price' => $_POST['item_price'][$index],
						'comment' => $_POST['item_comment'][$index],
					);
				}
			} else {
				$item_list[] = array(
					'name'		=> $comment,
					'type'		=> 0,
					'count'		=> 1,
					'price'		=> $amount,
					'comment'	=> $comment,
				);
			}//}}}
			//开始记录
			$this->_db_begin();
			//记录事件
			$event_data = array(
				'user_id'		=> $this->_user_id,
				'position_id'	=> $position_id,
				'type'			=> EventModel::TYPE_EXPENSES,
				'amount'		=> $amount,
				'event_date'	=> $date,
				'create_time'	=> TIME,
				'comment'		=> $comment,
			);
			$event_id = $this->_emod->add($event_data);
			if (!$event_id) {
				$this->show_warning('消费事件记录失败');
			}
			//记录支出详情和处理账户余额
			foreach ($account_list as $account) {
				$event_account_data = array(
					'event_id'			=> $event_id,
					'account_id'		=> $account['account_id'],
					'type'				=> $account['type'],
					'amount'			=> $account['amount'],
					'comment'			=> $account['comment'],
					'extra'				=> $account['extra'],
					'event_date'		=> $date,
				);
				$event_account_id = $this->_eamod->add($event_account_data);
				if (!$event_account_id) {
					$this->_db_rollback();
					$this->show_warning('支出详细记录失败');
				}
				if ($account['amount'] > 0) {
					$account_data = array(
						'income'	=> $account_infos[$account['account_id']]['expense'] + $account['amount'],
						'balance'	=> $account_infos[$account['account_id']]['balance'] + $account['amount'],
					);
				} else {
					$account_data = array(
						'expense'	=> $account_infos[$account['account_id']]['expense'] - $account['amount'],
						'balance'	=> $account_infos[$account['account_id']]['balance'] + $account['amount'],
					);
				}
				if (!$this->_amod->edit($account['account_id'], $account_data)) {
					$this->_db_rollback();
					$this->show_warning('支出账户余额变更失败');
				}
			}
			//记录消费详情
			foreach ($item_list as $item) {
				$expense_data = array(
					'user_id'		=> $this->_user_id,
					'event_id'		=> $event_id,
					'expense_name'	=> $item['name'],
					'type'			=> $item['type'],
					'amount'		=> $item['price'],
					'buy_date'		=> $date,
					'comment'		=> $item['comment'],
				);
				$expense_id = $this->_exmod->add($expense_data);
				if (!$expense_id) {
					$this->_db_rollback();
					$this->show_warning('消费详细记录失败');
				}
			}
			$this->_db_commit();
			$this->show_message('记录添加成功',
				'继续添加', '/index.php?app=bill&act=expense',
				'返回账户', '/index.php?app=account'
			);
		}
	}
	//}}}
}
