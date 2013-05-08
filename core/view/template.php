<?php
require('Smarty-3.1.11/libs/SmartyBC.class.php');
require('asset.php');

/**
 * Template Class
 * @author matao_a@staff.139.com
 */
class Template {
	private $_default_combo = false;
	private $_default_template_dir = TPL_TEMPLATE_DIR;
	private $_default_compiled_dir = TPL_COMPILED_DIR;

	private $_template = null;
	private $_jsArray = array();
	private $_cssArray = array();
	static private $_instance = null;
	private $_jsCode = array();
	private $_scriptholder = array();
	private $_cssholder = array();
	private $_jsVars = array();
	/*
	 * 获取类的唯一实例
	 * @return Template 本类的实例
	 */
	public static function instance() {
		if(!is_object(self::$_instance)) self::$_instance = new Template;
		return self::$_instance;
	}


	//{{{ display($templateFile, $assign = array())
	/**
	 * display 
	 *
	 * 根据模板文件生成页面并输出
	 * 
	 * @param	string	$templateFile	模板文件
	 * @param	array	$assign			传递给模板的参数 格式 array(key1 => value1, key2 => value2)
	 * @access public
	 * @return void
	 */
	public function display($templateFile, $assign = array()) {
		if(is_array($assign)) $this->assign($assign);
		$smarty = $this->_getTemplate();
		if($this->_jsArray) $smarty->assign('javascripts', $this->jsToString($this->_jsArray));
		if($this->_cssArray) $smarty->assign('csses', $this->cssToString($this->_cssArray));
		if($this->_jsVars) $smarty->assign('envobj', json_encode($this->_jsVars));
		$smarty->display($templateFile);
		$this->closeTemplate();
	}//}}}

	//{{{ fetch($templateFile, $assign = array())
	/**
	 * fetch 
	 * 
	 * 根据模板文件生成页面并返回
	 *
	 * @param	string	$templateFile	模板文件
	 * @param	array	$assign			可选参数，传递给模板的变量数组 格式 array(key1 => value1, key2 => value2)
	 * @access public
	 * @return	string	页面html代码
	 */
	public function fetch($templateFile, $assign = array()) {
		if(is_array($assign)) $this->Assign($assign);
		$smarty = $this->_getTemplate();
		if($this->_jsArray) $smarty->assign('javascripts', $this->jsToString($this->_jsArray));
		if($this->_cssArray) $smarty->assign('csses', $this->cssToString($this->_cssArray));
		if($this->_jsVars) $smarty->assign('envobj', json_encode($this->_jsVars));
		$content = $smarty->fetch($templateFile);
		$this->closeTemplate();
		return $content;
	}//}}}

	//{{{ closeTemplate()
	/**
	 * closeTemplate 
	 *
	 * 关闭当前模板
	 * 
	 * @
	 * @access public
	 * @return void
	 */
	public function closeTemplate() {
		$this->_template = null;
	}//}}}

	//{{{ assign($k = null, $v = null)
	/**
	 * assign 
	 * 
	 * 传递页面变量组给smarty
	 *
	 * @param	string/Array	$k	可选参数，当为数组时为变量数组，格式 array(key1 => value1, key2 => value2)，
	 *								当为字符串是表示变量名
	 * @param	string			$v	可选参数，当$k为字符串时，$v为$k对应的变量值
	 * @access public
	 * @return	Template		本类的实例
	 */
	public function assign($k = null, $v = null) {
		$smarty = $this->_getTemplate();
		if(!is_array($k)) {
			$smarty->assign($k, $v);
		} else {
			foreach($k as $key => $value) {
				$smarty->assign($key, $value);
			}
		}
		return $this;
	}//}}}

	//{{{ setTitle($title)
	/**
	 * setTitle 
	 * 
	 * 设置页面标题
	 *
	 * @param	string	$title	页面标题
	 * @access	public
	 * @return	本类的实例
	 */
	public function setTitle($title) {
		$this->assign('head_title', $title);
		return $this;
	}//}}}

	//{{{ addCss($css = array())
	/**
	 * addCss 
	 * 
	 * 添加css文件到页面
	 *
	 * @param	string/array	$css	单个css文件地址或者css文件地址的一维数组
	 * @access	public
	 * @return	Template		本类的实例
	 */
	public function addCss($css = array()) {
		if(!is_array($css)) $css = array($css);
		$this->_cssArray = array_unique(array_merge($this->_cssArray, $css));
	}//}}}

	//{{{ addJs($js = array())
	/**
	 * addJs 
	 * 
	 * 添加js文件到页面
	 *
	 * @param	string/array	$js		单个js文件地址或者js文件地址的一维数组
	 * @param array $js 
	 * @access public
	 * @return	Template		本类的实例
	 */
	public function addJs($js = array()) {
		if(!is_array($js)) $js = array(
			$js
		);
		$this->_jsArray = array_unique(array_merge($this->_jsArray, $js));
		return $this;
	}//}}}

	//{{{ addJsCode($jsCode)
	/**
	 * addJsCode
	 *
	 * 添加js代码到页尾加载完javascripts的代码后的位置 
	 * 
	 * @param string $jsCode 
	 * @access public
	 * @return 本类的一个实例
	 */
	public function addJsCode($jsCode) {
		$str = '';
		if (!is_array($jsCode)) $jsCode = array($jsCode);
		$this->_jsCode = array_merge($this->_jsCode, $jsCode);
		$str .= '<script type="text/javascript">' . "\r\n";
		$str .= implode("\r\n", $this->_jsCode);
		$str .= "\r\n</script>";
		$this->assign('js_codes', $str);
		return $this;
	}//}}}

	//{{{ jsToString($js)
	/**
	 * jsToString 
	 * 
	 * 由js文件列表返回html页面引用的字符串
	 *
	 * @param	array	$js		js文件一维数组
	 * @static
	 * @access public
	 * @return	string	html页面引用的字符串
	 */
	public static function jsToString($js) {
		if(empty($js)) return '';
		$cnt = count($js);
		if($this->isCombo()) {
			$str = '<script type="text/javascript" src="' 
				. ($cnt > 1 ? Asset::getComboUrl($js) : Asset::getUrl($js[0]))
				. '"></script>';
		} else {
			$str = '';
			for($i = 0; $i < $cnt; ++$i) {
				$str.= '<script type="text/javascript" src="' . Asset::getUrl($js[$i]) . "\"></script>\n";
			}
		}
		return $str;
	}//}}}

	//{{{ cssToString($css)
	/**
	 * cssToString 
	 * 
	 * 由css文件列表返回html页面引用的字符串
	 *
	 * @param	array	$css	css文件一维数组
	 * @access	public
	 * @return	string	html页面引用的字符串
	 */
	public function cssToString($css) {
			if(empty($css)) return '';
			$cnt = count($css);
			$str = '';
			if($this->isCombo()) {
					$str = $cnt > 1 ? Asset::getComboUrl($css) : Asset::getUrl($css[0]);
			} else {
					for($i = 0; $i < $cnt; ++$i) {
							$str .= '<link href="' . Asset::getUrl($css[$i]) . "\" rel=\"stylesheet\" type=\"text/css\" />\n";
					}
			}
			return $str;
	}//}}}

	//{{{ metaToString
	/**
	 * metaToString 
	 *
	 * 生成 <meta ... />
	 * 
	 * @param array $meta 
	 * @access public
	 * @return string
	 */
	public function metaToString(array $meta)
	{
			$str = '<meta ';
			$tmp = array();

			foreach ($meta as $k => $v) {
					$tmp[] = $k . '="' . addslashes($v) . '"';
			}

			$str .= implode(' ', $tmp);
			$str .= " />\r\n";

			return $str;
	}//}}}

	//{{{ scriptHolder($params, $content)
	/**
	 * scriptHolder 
	 * 
	 * 将js代码加入后写入数组中
	 *
	 * @param string $params 
	 * @param string $content 
	 * @access public
	 * @return void
	 */
	public function scriptHolder($params, $content) {
			if(empty($content))
					return;

			if(empty($params))
					$params = array('place' => 'scriptassembly');

			$assign = $params['place'];
			if(!isset($this->_scriptholder[$assign]) or empty($this->_scriptholder[$assign]))
					$this->_scriptholder[$assign] = $content;
			else
					$this->_scriptholder[$assign].= $content;
	}//}}}

	//{{{ getScriptHolder($params)
	/**
	 * getScriptHolder 
	 *
	 * 获取后写入js代码
	 * 
	 * @param string $params 
	 * @access public
	 * @return void
	 */
	public function getScriptHolder($params) {
			if(!isset($params['place']))
					$params = array('place' => 'scriptassembly');
			$assign = $params['place'];

			$str = isset($this->_scriptholder[$assign]) ? $this->_scriptholder[$assign] : '';
			echo preg_replace('/((<|\<\/)script.*?>)|(<script.*)|(.*><\\/script.*)/', '', $str);
			return '';
	}//}}}

	//{{{ getTemplateDir()
	/**
	 * getTemplateDir 
	 * 
	 * 获取模板目录
	 *
	 * @access public
	 * @return void
	 */
	public function getTemplateDir(){
			return defined('TPL_TEMPLATE_DIR') ? TPL_TEMPLATE_DIR: $this->_default_template_dir;
	}//}}}

	//{{{ getCompliedDir()
	/**
	 * getCompliedDir
	 * 
	 * 获取编译临时目录
	 *
	 * @access public
	 * @return void
	 */
	public function getCompliedDir(){
			return defined('TPL_COMPILED_DIR') ? TPL_COMPILED_DIR: $this->_default_compiled_dir;
	}//}}}

	//{{{ cssHolder($cssUrl = "")
	/**
	 * cssHolder 
	 * 
	 * 为cssholder插件做的封装函数
	 *
	 * @param string $cssUrl 
	 * @access public
	 * @return void
	 */
	public function cssHolder($cssUrl = "") {
		if(empty($cssUrl))  {
			return empty($this->_cssArray) ? '' : $this->cssToString(array_values($this->_cssArray));
		}
		$this->addCss($cssUrl);
	}//}}}

	//{{{ addJsVars($vars, $value = array())
	/**
	 * addJsVars 
	 * 
	 * 向页面添加 js 输出变量
	 *
	 * Example:
	 * <code>
	 * Template::addJsVars("target", 6);
	 * Template::addJsVars(array("target" => 7, "next" => true, "list" => array( 1, 2, 3, 4)));
	 * </code>
	 *
	 * @param string|array $js			变量名或变量数组
	 * @param boolean $value			变量值
	 * @access public
	 * @return void
	 */
	public function addJsVars($vars, $value = array()) {
		if (is_array($vars)) {
			foreach ($vars as $key => $value) {
				$this->_jsVars[$key] = $value;
			}
		}
		else {
			$this->_jsVars[$vars] = $value;
		}

		return $this;
	}//}}}

	//{{{ _getTemplate()
	/**
	 * _getTemplate 
	 *
	 * 获取smarty实例
	 * 
	 * @access private
	 * @return	Smarty	$smarty	smarty实例 
	 */
	private function _getTemplate() {
			if($this->_template) return $this->_template;
			$smarty = new SmartyBC();
			$smarty->template_dir = $this->getTemplateDir();
			$smarty->use_sub_dirs = defined('TPL_SUB_DIRS') ? TPL_SUB_DIRS : false;
			$smarty->compile_dir = $this->getCompliedDir();
			if (defined('TPL_PLUGINS_DIR')) $smarty->addPluginsDir(TPL_PLUGINS_DIR);
			$smarty->addPluginsDir(TPL_TEMPLATE_DIR.'/plugins');
			$smarty->left_delimiter = '<{';
			$smarty->right_delimiter = '}>';
			$smarty->load_filter('output', 'cssholder');
			$this->_template = $smarty;
			return $this->_template;
	}//}}}

	//{{{ isCombo()
	/**
	 * isCombo 
	 *
	 * 判断是否需要组合静态文件地址
	 *
	 * @access public
	 * @return boolean
	 */
	public function isCombo() {
			return defined('ASSET_COMBO') ? ASSET_COMBO : $this->_default_combo;
	}//}}}
}
