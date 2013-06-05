<?php

/**
 *    前台控制器基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class FrontendApp extends MallBaseApp
{
	var $_umod;//用户模型
	var $_user_id;
	var $_user_name;
	function __construct()
	{
		parent::__construct();
		$this->_umod = &m('user');
		$this->_init_view();
	}
	function _config_view()
	{
		parent::_config_view();
	}
	function _init_view()
	{
		parent::_init_view();
		$this->_view->addJsVars(array(
				'domains'	=> array(
					'www' => SITE_URL,
					'asset'=> ASSET_SERVER,
				),
				'proxyUrl'	=> Asset::getUrl('/core/js/jquery/flxproxy/flxproxy.swf'),
				'debug'		=> ASSET_DEBUG, // $.log 的配置项，生产中取消
				'crumb'		=> $newCrumb,
				'user'		=> array(
					'user_id'	=> $this->visitor->has_login ? $this->visitor->info['user_id'] : 0,
					'user_name'	=> $this->visitor->has_login ? $this->visitor->info['user_name'] : '游客',
				),
				'region'	=> array(
					'id'	=> $this->visitor->region['region_id'],
					'name'	=> $this->visitor->region['region_name'],
				),
			)
		);
		$this->assign(array(
			'app'	=> APP,
			'act'	=> ACT,
			'user'	=> array(
				'user_id'	=> $this->visitor->has_login ? $this->visitor->info['user_id'] : 0,
				'user_name'	=> $this->visitor->has_login ? $this->visitor->info['user_name'] : '游客',
			),
		));
	}
	function display($tpl)
	{
		parent::display($tpl);
	}

	function fetch($tpl)
	{
		return parent::fetch($tpl);
	}

	function _init_visitor()
	{
		$this->visitor =& env('visitor', new UserVisitor());
		$this->_user_id = $this->visitor->info['user_id'];
		$this->_user_name= $this->visitor->info['user_name'];
	}

    function _run_action()
    {
	    parent::_run_action();
	}

	function login() {
		if ($this->visitor->has_login) {
			return;
		}
		if (!IS_POST) {
			$this->display('login.html');
			die;
		} else {
			$user_name = trim($_POST['user_name']);
			$password = trim($_POST['password']);
			if (!$info = $this->_umod->auth($user_name, $password)) {
				$this->show_warning('账户或密码错误');
			} else {
				$this->_do_login($user_id);
				$this->_umod->edit($info['user_id'], array(
					'last_login_ip'		=> real_ip(),
					'last_login_time'	=> TIME,
					'login_times'		=> $info['login_times'] +1,
				));
				location('/');
			}
		}
	}

	function _do_login($user_id) {
		$info = $this->_umod->get_info($user_id);
		$this->visitor->assign(array(
			'user_id'       => $info['user_id'],
			'user_name'     => $info['user_name'],
		));
		$this->_user_id = $info['user_id'];
		$this->_user_name= $info['user_name'];
		return true;
	}

	protected function _db_begin() {
		$this->_umod->begin();
	}

	protected function _db_rollback() {
		$this->_umod->rollback();
	}
	protected function _db_commit() {
		$this->_umod->commit();
	}

}
/**
 *    前台访问者
 *
 *    @author    Garbin
 *    @usage    none
 */
class UserVisitor extends BaseVisitor
{
	var $_info_key	= 'user';
	var $region		= null;

	/**
	 *    退出登录
	 *
	 *    @author    Garbin
	 *    @param    none
	 *    @return    void
	 */

	function __construct() {
		parent::__construct();
	}
	function logout()
	{
		/* 退出登录 */
		parent::logout();
	}
}
/* 实现消息基础类接口 */
class MessageBase extends MallbaseApp {};

/* 实现模块基础类接口 */
class BaseModule  extends FrontendApp {};
/* 消息处理器 */
require(ROOT_PATH . '/core/controller/message.base.php');
