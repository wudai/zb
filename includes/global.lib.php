<?php

function &cache_server()
{
    import('cache.lib');
    static $CS = null;
    if ($CS === null)
    {
        switch (CACHE_SERVER)
        {
            case 'memcached':
                $CS = new MemcacheServer(array(
                    'host'  => MEMCACHE_HOST,
                    'port'  => MEMCACHE_PORT,
                ));
            break;
            default:
                $CS = new PhpCacheServer;
                $CS->set_cache_dir(ROOT_PATH . '/temp/caches');
            break;
        }
    }

    return $CS;
}

/**
 *    获取数组文件对象
 *
 *    @author    Garbin
 *    @param     string $type
 *    @param     array  $params
 *    @return    void
 */
function &af($type, $params = array())
{
    static $types = array();
    if (!isset($types[$type]))
    {
        /* 加载数据文件基础类 */
        include_once(ROOT_PATH . '/includes/arrayfile.base.php');
        include(ROOT_PATH . '/includes/arrayfiles/' . $type . '.arrayfile.php');
        $class_name = ucfirst($type) . 'Arrayfile';
        $types[$type]   =   new $class_name($params);
    }

    return $types[$type];
}

/**
 *    获取环境变量
 *
 *    @author    Garbin
 *    @param     string $key
 *    @param     mixed  $val
 *    @return    mixed
 */
function &env($key, $val = null)
{
    $vkey = $key ? strtokey("{$key}", '$GLOBALS[\'EC_ENV\']') : '$GLOBALS[\'EC_ENV\']';
    if ($val === null)
    {
        /* 返回该指定环境变量 */
        $v = eval('return ' . $vkey . ';');

        return $v;
    }
    else
    {
        /* 设置指定环境变量 */
        eval($vkey . ' = $val;');

        return $val;
    }
}

function check_phone($phone) {
	return preg_match('/1(3|4|5|8)\d{9}/', $phone);
}

function check_tel($tel) {
	return preg_match('/^1(3|4|5|8)\d{9}|\d{3,4}-\d{7,8}$/', $tel);
}

function check_email($email) {
	return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email);
}

function check_money($money) {
	return preg_match('/(^[1-9]\d*(.\d{1,2})?$)|(^0(.\d{1,2})?$)/', $money);
}
function location($url) {
	header('Location: '. $url);
	die;
}
function header404() {
	header("HTTP/1.0 404 Not Found");
	header("Status: 404 Not Found");
	die;
}
function mt_log($msg) {
	if (is_array($msg)) {
		error_log(date("c\t").print_r($msg, 1)."\n", 3, '/tmp/matao.log');
	} else {
		error_log(date("c\t").$msg."\n", 3, '/tmp/matao.log');
	}
}

function array_find_deep($arr, $colname) {
	$ids = array();
	if (!is_array($arr)) return array();
	foreach ($arr as $k => $one) {
		if (is_array($one)) {
			$ids = array_merge($ids, array_find_deep($one, $colname));
		} elseif ($colname === $k) {
			$ids[] = $one;
		}
	}
	return array_unique($ids);
}

function cron_log($cron_name, $status='', $data='') {
	error_log(date('Y-m-d H:i:s')."\t$cron_name\t$action\t$status\t$data\n", 3, CRON_LOG);
}
