<?php

class DefaultApp extends FrontendApp{
    function index() {
		$this->login();
		$e_mod = &m('event');
		$conditions = array(
			'e.user_id='.$this->_user_id,
			'type='.EventModel::TYPE_EXPENSES,
		);
		$list = $e_mod->find(array(
			'conditions'	=> implode(' AND ', $conditions),
			'join'			=> 'belongs_to_position',
			'fields'		=> 'this.*, pos.position_name',
			'limit'			=> 20,
			'count'			=> true,
			'order'			=> 'event_date DESC, event_id DESC',
		));
		$page = $this->_get_page();
		$this->_format_page($page);
		$this->assign('list', $list);
		$this->assign('page_info', $page);
        $this->display('index.html');
    }

	function register() {
		$user_name = trim($_POST['user_name']);
		$password = trim($_POST['password']);
		$repassword = trim($_POST['repassword']);
		if (!strlen($user_name)) {
			$this->show_warning('请填写账户名');
		}
		if (!strlen($password)) {
			$this->show_warning('请填写密码');
		}
		if (!strlen($repassword)) {
			$this->show_warning('请重复填写密码');
		}
		if ($password != $repassword) {
			$this->show_warning('两次填写的密码不一致，请重新填写');
		}
		if (!$this->_umod->unique($user_name)) {
			$this->show_warning('该用户名已注册，请换用其他用户名或尝试登录');
		}
		$data = array(
			'user_name'		=> $user_name,
			'password'		=> $password,
			'register_ip'	=> real_ip(),
			'create_time'	=> TIME,
		);
		if ($user_id = $this->_umod->add($data)) {
			$this->_do_login($user_id);
			$this->show_message('注册成功', '进入首页', '/');
		} else {
			$this->show_warning('注册失败');
		}
	}
}
