<?php

class PositionApp extends FrontendApp {
	var $_pmod = null;
	function __construct() {
		parent::__construct();
		$this->_pmod = &m('position');
	}

	function ajax_get() {
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
}
