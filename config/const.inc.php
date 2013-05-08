<?php
mb_regex_encoding('UTF-8');
mb_internal_encoding("UTF-8"); 
define('DB_CHARSET', 'utf-8');
define('DB_PREFIX', '51edu_');
define('DB_CACHEDIR', ROOT_PATH . '/temp/query_caches/');
define('TPL_PLUGINS_DIR', ROOT_PATH . '/core/view/plugins');
define('LANG', 'sc-utf-8');
define('CHARSET', 'utf-8');
define('ASSET_COMBO', 1);
//{{{session配置
ini_set('session.name', 'SOOKER_ID');
ini_set('session.save_handler', 'memcached');
ini_set('session.save_path', implode(',',$GLOBALS['session_memcache_config']['servers']));
//}}}
