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
	    /* 先判断是否登录 */
	    if (!$this->visitor->has_login && !in_array(ACT, array('login', 'register'))) {
			location('/index.php?act=login');
		    return;
	    }
	    parent::_run_action();
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
