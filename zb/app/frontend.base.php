<?php

/**
 *    前台控制器基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class FrontendApp extends MallBaseApp
{
	var $_tagmod;//标签模型(header中用到)
	function __construct()
	{
		$this->FrontendApp();
	}
	function FrontendApp()
	{
		parent::__construct();
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
					'id'	=> $this->visitor->has_login ? $this->visitor->info['user_id'] : 0,
					'name'	=> $this->visitor->has_login ? $this->visitor->info['user_name'] : '搜课网友',
					'school_id'	=> $this->visitor->has_login ? intval($this->visitor->info['school_id']) : 0,
				),
				'region'	=> array(
					'id'	=> $this->visitor->region['region_id'],
					'name'	=> $this->visitor->region['region_name'],
				),
			)
		);
		$this->assign('app', APP);
		$this->assign('act', ACT);
		$tag_mod = &bm('tag');//标签模型(header中用到)
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
