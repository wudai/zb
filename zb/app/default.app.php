<?php

class DefaultApp extends FrontendApp{
    function index() {
        $this->display('index.html');
    }

	function login() {
		if (!IS_POST) {
			$this->display('login.html');
		} else {
			$user_name = trim($_POST['user_name']);
			$password = trim($_POST['password']);
			if (!$info = $this->_umod->auth($user_name, $password)) {
				$this->show_warning('账户或密码错误');
			} else {
				$this->_umod->edit($info['user_id'], array(
					'last_login_ip'		=> real_ip(),
					'last_login_time'	=> TIME,
					'login_times'		=> $info['login_times'] +1,
				));
				location('/');
			}
		}
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
			$this->show_message('注册成功');
		} else {
			$this->show_warning('注册失败');
		}
	}
}
