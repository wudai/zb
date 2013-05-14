<?php
mb_regex_encoding('UTF-8');
mb_internal_encoding("UTF-8"); 
define('CHARSET', 'utf-8');
//{{{session配置
if (array_key_exists('session_memcache_config', $GLOBALS)) {
	ini_set('session.name', 'ZB_SID');
	ini_set('session.save_handler', 'memcache');
	ini_set('session.save_path', implode(',',$GLOBALS['session_memcache_config']['servers']));
}
//}}}
