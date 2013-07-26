<?php

class EventApp extends FrontendApp {
	var $_amod = null;
	var $_emod = null;
	var $_eamod = null;
	function __construct() {
		parent::__construct();
		$this->login();
		$this->_amod = &m('account');
		$this->_emod = &m('event');
		$this->_eamod = &m('event_account');
		$this->_exmod = &m('expense');
	}

	function detail() {
		$event_id = intval($_GET['id']);
		if (!$event_id) {
			$this->show_warning('请选择正确的事件');
		}
		$event = $this->_emod->get(array(
			'conditions'	=> "event_id=$event_id",
			'fields'		=> 'this.*, pos.position_name',
			'join'			=> 'belongs_to_position',
		));
		if (!$event) {
			$this->show_warning('请选择正确的事件');
		}
		$event_account = $this->_eamod->find(array(
			'conditions' => 'event_id='.$event_id,
			'fields'	=> 'this.*,a.account_name',
			'join'		=> 'belongs_to_account',
		));
		$assign = array(
			'event'		=> $event,
			'accounts'	=> $event_account,
		);
		if (in_array($event['type'], array(EventModel::TYPE_EXPENSES, EventModel::TYPE_DINNER))) {
			$expenses = $this->_exmod->find(array('conditions' => 'event_id='.$event_id));
			$assign['expenses'] = $expenses;
		}
		$this->assign($assign);
		$this->display('event/detail.html');
	}
}
