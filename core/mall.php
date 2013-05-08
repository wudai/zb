<?php
/* 判断请求方式 */
define('IS_POST', (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'));
/* 定义PHP_SELF常量 */
define('PHP_SELF',  htmlentities(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']));
define('IN_MALL', 1);
define('TIME', time());//当前时间戳
class Mall {
    /* 启动 */
    function startup($config = array())
    {
        /* 加载初始化文件 */
        require_once(ROOT_PATH . '/core/controller/app.base.php');     //基础控制器类
        require_once(ROOT_PATH . '/core/model/model.base.php');   //模型基础类
        require_once(ROOT_PATH . '/core/model/mmodel.base.php');   //用户模型基础类

        if (!empty($config['external_libs']))
        {
            foreach ($config['external_libs'] as $lib)
            {
                require_once($lib);
            }
        }
        /* 数据过滤 */
        if (!get_magic_quotes_gpc())
        {
            $_GET   = addslashes_deep($_GET);
            $_POST  = addslashes_deep($_POST);
            $_COOKIE= addslashes_deep($_COOKIE);
        }

        /* 请求转发 */
        $default_app = $config['default_app'] ? $config['default_app'] : 'default';
        $default_act = $config['default_act'] ? $config['default_act'] : 'index';

        $app    = isset($_REQUEST['app']) ? trim($_REQUEST['app']) : $default_app;
        $act    = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : $default_act;
        $app_file = $config['app_root'] . "/{$app}.app.php";
        if (!is_file($app_file))
        {
            exit('Missing controller');
        }

        require($app_file);
        define('APP', $app);
        define('ACT', $act);
        $app_class_name = ucfirst($app) . 'App';

        /* 实例化控制器 */
        $app     = new $app_class_name();
        c($app);
        $app->do_action($act);        //转发至对应的Action
        $app->destruct();
    }
}
/**
 * 递归方式的对变量中的特殊字符进行转义
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function addslashes_deep($value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
}

/**
 *    所有类的基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class Object
{
    var $_errors = array();
    var $_errnum = 0;
    function __construct()
    {
        $this->Object();
    }
    function Object()
    {
        #TODO
    }
    /**
     *    触发错误
     *
     *    @author    Garbin
     *    @param     string $errmsg
     *    @return    void
     */
    function _error($msg, $obj = '')
    {
        if(is_array($msg))
        {
            $this->_errors = array_merge($this->_errors, $msg);
            $this->_errnum += count($msg);
        }
        else
        {
            $this->_errors[] = compact('msg', 'obj');
            $this->_errnum++;
        }
    }

    /**
     *    检查是否存在错误
     *
     *    @author    Garbin
     *    @return    int
     */
    function has_error()
    {
        return $this->_errnum;
    }

    /**
     *    获取错误列表
     *
     *    @author    Garbin
     *    @return    array
     */
    function get_error()
    {
        return $this->_errors;
    }
}

/**
 *    将default.abc类的字符串转为$default['abc']
 *
 *    @author    Garbin
 *    @param     string $str
 *    @return    string
 */
function strtokey($str, $owner = '')
{
    if (!$str)
    {
        return '';
    }
    if ($owner)
    {
        return $owner . '[\'' . str_replace('.', '\'][\'', $str) . '\']';
    }
    else
    {
        $parts = explode('.', $str);
        $owner = '$' . $parts[0];
        unset($parts[0]);
        return strtokey(implode('.', $parts), $owner);
    }
}

/**
 *    配置管理器
 *
 *    @author    Garbin
 *    @usage    none
 */
class Conf
{
    /**
     *    加载配置项
     *
     *    @author    Garbin
     *    @param     mixed $conf
     *    @return    bool
     */
    function load($conf)
    {
        $old_conf = isset($GLOBALS['MALL_CONFIG']) ? $GLOBALS['MALL_CONFIG'] : array();
        if (is_string($conf))
        {
            $conf = include($conf);
        }
        if (is_array($old_conf))
        {
            $GLOBALS['MALL_CONFIG'] = array_merge($old_conf, $conf);
        }
        else
        {
            $GLOBALS['MALL_CONFIG'] = $conf;
        }
    }
    /**
     *    获取配置项
     *
     *    @author    Garbin
     *    @param     string $k
     *    @return    mixed
     */
    function get($key = '')
    {
        $vkey = $key ? strtokey("{$key}", '$GLOBALS[\'MALL_CONFIG\']') : '$GLOBALS[\'MALL_CONFIG\']';

        return eval('if(isset(' . $vkey . '))return ' . $vkey . ';else{ return null; }');
    }
}
/**
 *    获取视图链接
 *
 *    @author    Garbin
 *    @param     string $engine
 *    @return    object
 */
function &v()
{
    require_once(ROOT_PATH . '/core/view/template.php');
	return Template::instance();
}

/**
 *  获取一个模型
 *
 *  @author Garbin
 *  @param  string $model_name
 *  @param  array  $params
 *  @param  book   $is_new
 *  @return object
 */
function &m($model_name, $params = array(), $is_new = false)
{
    static $models = array();
    $model_hash = md5($model_name . var_export($params, true));
    if ($is_new || !isset($models[$model_hash]))
    {
        $model_file = ROOT_PATH . '/includes/models/' . $model_name . '.model.php';
        if (!is_file($model_file))
        {
            /* 不存在该文件，则无法获取模型 */
            return false;
        }
        include_once($model_file);
        $model_name = ucfirst($model_name) . 'Model';
        if ($is_new)
        {
            return new $model_name($params, db());
        }
        $models[$model_hash] = new $model_name($params, db());
    }

    return $models[$model_hash];
}

/**
 * 获取一个业务模型
 *
 * @param string $model_name
 * @param array $params
 * @param bool $is_new
 * @return object
 */
function &bm($model_name, $params = array(), $is_new = false)
{
    static $models = array();
    $model_hash = md5($model_name . var_export($params, true));
    if ($is_new || !isset($models[$model_hash]))
    {
        $model_file = ROOT_PATH . '/includes/models/' . $model_name . '.model.php';
        if (!is_file($model_file))
        {
            /* 不存在该文件，则无法获取模型 */
            return false;
        }
        include_once($model_file);
        $model_name = ucfirst($model_name) . 'BModel';
        if ($is_new)
        {
            return new $model_name($params, db());
        }
        $models[$model_hash] = new $model_name($params, db());
    }

    return $models[$model_hash];
}

/**
 *  获取一个用户信息模型
 *
 *  @author matao
 *  @param  string $model_name
 *  @param  array  $params
 *  @param  book   $is_new
 *  @return object
 */
function &mm($model_name, $params = array(), $is_new = false)
{
    static $mmodels = array();
    $model_hash = md5($model_name . var_export($params, true));
    if ($is_new || !isset($mmodels[$model_hash]))
    {
        $model_file = ROOT_PATH . '/includes/mmodels/' . $model_name . '.model.php';
        if (!is_file($model_file))
        {
            /* 不存在该文件，则无法获取模型 */
            return false;
        }
        include_once($model_file);
        $model_name = ucfirst($model_name) . 'MModel';
        if ($is_new)
        {
            return new $model_name($params, mdb());
        }
        $mmodels[$model_hash] = new $model_name($params, mdb());
    }

    return $mmodels[$model_hash];
}
/**
 * 获取一个用户信息业务模型
 *
 * @param string $model_name
 * @param array $params
 * @param bool $is_new
 * @return object
 */
function &bmm($model_name, $params = array(), $is_new = false)
{
    static $models = array();
    $model_hash = md5($model_name . var_export($params, true));
    if ($is_new || !isset($mmodels[$model_hash]))
    {
        $model_file = ROOT_PATH . '/includes/mmodels/' . $model_name . '.model.php';
        if (!is_file($model_file))
        {
            /* 不存在该文件，则无法获取模型 */
            return false;
        }
        include_once($model_file);
        $model_name = ucfirst($model_name) . 'BMModel';
        if ($is_new)
        {
            return new $model_name($params, mdb());
        }
        $mmodels[$model_hash] = new $model_name($params, mdb());
    }

    return $mmodels[$model_hash];
}
/**
 *    获取当前控制器实例
 *
 *    @author    Garbin
 *    @return    void
 */
function c(&$app)
{
    $GLOBALS['MALL_APP'] =& $app;
}

/**
 *    获取当前控制器
 *
 *    @author    Garbin
 *    @return    Object
 */
function &cc()
{
    return $GLOBALS['MALL_APP'];
}

/**
 *    导入一个类
 *
 *    @author    Garbin
 *    @return    void
 */
function import()
{
    $c = func_get_args();
    if (empty($c))
    {
        return;
    }
    array_walk($c, create_function('$item, $key', 'include_once(ROOT_PATH . \'/includes/libraries/\' . $item . \'.php\');'));
}

/**
 * 创建MySQL数据库对象实例
 *
 * @author  wj
 * @return  object
 */
function &db()
{
    include_once(ROOT_PATH . '/core/model/mysql.php');
    static $db = null;
    if ($db === null)
    {
		if (!defined('DB_HOST') || DB_HOST == '')
		{
			trigger_error('Invalid database host.', E_USER_ERROR);
		}
		else
		{
			$dbhost = DB_HOST;
		}
		if (!defined('DB_PORT') || DB_PORT == '')
		{
			$dbport = 3306;
		}
		else
		{
			$dbport = DB_PORT;
		}
		if (!defined('DB_PW') || DB_PW == '')
		{
			$pass = '';
		}
		else
		{
			$pass = urldecode(DB_PW);
		}
		$user = urldecode(DB_USER);

		if (!defined('DB_NAME') || DB_NAME == '')
		{
			trigger_error('Invalid database name.', E_USER_ERROR);
		}
		else
		{
			$dbname = DB_NAME;
		}

		$charset = (DB_CHARSET == 'utf-8') ? 'utf8' : DB_CHARSET;
		$db = new cls_mysql();
		$db->cache_dir = DB_CACHEDIR;
		$db->connect($dbhost. ':' .$dbport, $user, $pass, $dbname, $charset);
    }

    return $db;
}

/**
 * 获得用户的真实IP地址
 *
 * @return  string
 */
function real_ip()
{
    static $realip = NULL;

    if ($realip !== NULL)
    {
        return $realip;
    }

    if (isset($_SERVER))
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr as $ip)
            {
                $ip = trim($ip);

                if ($ip != 'unknown')
                {
                    $realip = $ip;

                    break;
                }
            }
        }
        elseif (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else
        {
            if (isset($_SERVER['REMOTE_ADDR']))
            {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
            else
            {
                $realip = '0.0.0.0';
            }
        }
    }
    else
    {
        if (getenv('HTTP_X_FORWARDED_FOR'))
        {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_CLIENT_IP'))
        {
            $realip = getenv('HTTP_CLIENT_IP');
        }
        else
        {
            $realip = getenv('REMOTE_ADDR');
        }
    }

    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';

    return $realip;
}

/**
 * 获得当前的域名
 *
 * @return  string
 */
function get_domain()
{
    /* 协议 */
    $protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';

    /* 域名或IP地址 */
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
    {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    }
    elseif (isset($_SERVER['HTTP_HOST']))
    {
        $host = $_SERVER['HTTP_HOST'];
    }
    else
    {
        /* 端口 */
        if (isset($_SERVER['SERVER_PORT']))
        {
            $port = ':' . $_SERVER['SERVER_PORT'];

            if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol))
            {
                $port = '';
            }
        }
        else
        {
            $port = '';
        }

        if (isset($_SERVER['SERVER_NAME']))
        {
            $host = $_SERVER['SERVER_NAME'] . $port;
        }
        elseif (isset($_SERVER['SERVER_ADDR']))
        {
            $host = $_SERVER['SERVER_ADDR'] . $port;
        }
    }

    return $protocol . $host;
}
/**
 * 获得网站的URL地址
 *
 * @return  string
 */
function site_url()
{
    return get_domain() . substr(PHP_SELF, 0, strrpos(PHP_SELF, '/'));
}

function mt_iconv($from, $to, $string) {
	$to = str_replace('//IGNORE', '', $to);
	return mb_convert_encoding($string, $to, $from); 
}
/**
 * 对数组转码
 *
 * @param   string  $func
 * @param   array   $params
 *
 * @return  mixed
 */
function iconv_deep($source_lang, $target_lang, $value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        if (is_array($value))
        {
            foreach ($value as $k=>$v)
            {
                $value[$k] = iconv_deep($source_lang, $target_lang, $v);
            }
            return $value;
        }
        elseif (is_string($value))
        {
            return mt_iconv($source_lang, $target_lang, $value);
        }
        else
        {
            return $value;
        }
    }
}
/**
 * 创建用户MySQL数据库对象实例
 *
 * @author  matao
 * @return  object
 */
function &mdb() {
    include_once(ROOT_PATH . '/core/model/mysql.php');
    static $mdb = null;
    if ($mdb === null) {
		if (!defined('MDB_HOST') || MDB_HOST == '')
		{
			trigger_error('Invalid member database host.', E_USER_ERROR);
		}
		else
		{
			$dbhost = MDB_HOST;
		}
		if (!defined('MDB_PORT') || MDB_PORT == '')
		{
			$dbport = 3306;
		}
		else
		{
			$dbport = MDB_PORT;
		}
		if (!defined('MDB_PW') || MDB_PW == '')
		{
			$pass = '';
		}
		else
		{
			$pass = urldecode(MDB_PW);
		}
		$user = urldecode(MDB_USER);

		if (!defined('MDB_NAME') || MDB_NAME == '')
		{
			trigger_error('Invalid database name.', E_USER_ERROR);
		}
		else
		{
			$dbname = MDB_NAME;
		}

		$charset = (MDB_CHARSET == 'utf-8') ? 'utf8' : MDB_CHARSET;
		$mdb = new cls_mysql();
		$mdb->cache_dir = MDB_CACHEDIR;
		$mdb->connect($dbhost. ':' .$dbport, $user, $pass, $dbname, $charset);
    }
    return $mdb;
}

if (!function_exists('array_column')) {
	function array_column($arr, $column) {
		if (!is_array($arr)) return false;
		$result = array();
		foreach ($arr as $one) {
			if (array_key_exists($column, $one)) {
				$result[] = $one[$column];
			}
		}
		return $result;
	}
}
function array_sort_by_col($arr, $colname, $order) {
	$result = array();
	foreach ($order as $v) {
		foreach ($arr as $k => $one) {
			if ($one[$colname] == $v) {
				$result[$v] = $one;
				unset($arr[$k]);
				break;
			}
		}
	}
	return $result;
}
function mkdir_deep($absolute_path, $mode = 0777)
{
    if (is_dir($absolute_path))
    {
        return true;
    }

    $root_path      = ROOT_PATH;
    $relative_path  = str_replace($root_path, '', $absolute_path);
    $each_path      = explode('/', $relative_path);
    $cur_path       = $root_path; // 当前循环处理的路径
    foreach ($each_path as $path)
    {
        if ($path)
        {
            $cur_path = $cur_path . '/' . $path;
            if (!is_dir($cur_path))
            {
                if (@mkdir($cur_path, $mode))
                {
                    fclose(fopen($cur_path . '/index.htm', 'w'));
                }
                else
                {
                    return false;
                }
            }
        }
    }

    return true;
}
function file_ext($filename)
{
    return trim(substr(strrchr($filename, '.'), 1, 10));
}
function db_create_in($item_list, $field_name = '')
{
    if (empty($item_list))
    {
        return $field_name . " IN ('') ";
    }
    else
    {
        if (!is_array($item_list))
        {
            $item_list = explode(',', $item_list);
            foreach ($item_list as $k=>$v)
            {
                $item_list[$k] = intval($v);
            }
        }

        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list AS $item)
        {
            if ($item !== '')
            {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp))
        {
            return $field_name . " IN ('') ";
        }
        else
        {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}

/*获取访问用户唯一标识符*/

	function user_key(){
	  $user_key=md5(real_ip().$_SERVER['HTTP_USER_AGENT']);
	  return $user_key;
	}
