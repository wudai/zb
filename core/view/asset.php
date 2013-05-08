<?php

/**
 * Asset Class
 */
class Asset
{
	static $split = '__';
	const ASSET_MAX = 1;

	/**
	 * Get asset url with timestamp
	 *	@param	path		the path of asset.xxx.com/$path
	 *	@return	URL			the url ( domain name & path )
	 */
	static public function getUrl($url, $version = true, $type = 1) {
		if (empty($url)) return '';
		if (preg_match('#^https?://#', $url)) return $url;
		if ($url{0} != '/') $url = '/' . $url;

		// 同一个文件，总会被分配到同一个 n 上。
		$http = 1;
		$server = self::getServer($url, $type, $http);

		if (!$version) return $server . $url;

		$tmp = explode('.', $url);
		$ext = array_pop($tmp);

		//we use more then one domain name to down load asset in parallel
		return "$server{$url}";
	}

	
	
	/**
	 * get config
	 *
	 * @return array
	 */
	static private function _getConfig()
	{
			global $sys_config;
			$config = $sys_config;
			return isset($config->global) ? $config->global : $config;
	}

	/**
	 * get big version
	 *
	 * @return int
	 */
	static function getVersion($path) {
		return ASSET_VERSION;
		$path = ASSET_PATH.$path;
		return file_exists($path) ? filemtime($path) : 0;
	}

	/**
	 * Get combo asset url
	 *
	 *	@param array path		array of the path
	 *	@return	string		the url ( domain name & path )
	 */
	static public function getComboUrl($urls, $version = true)
	{
		$url = implode(',',$urls);
		return ASSET_SERVER.'??'.$url.'?v='.ASSET_VERSION;

		$prefix = '/combo/';
		$split = self::$split;
		$url = join($split, $urls);
		$url = ltrim($url, '/');
		$server = self::getServer($url);
		if (!$version) return "$server$prefix$url";

		$suffix = 0;
		foreach((array)$urls as $v) $suffix = max(self::getVersion($v), $suffix);
		return "$server$prefix$split$suffix/$url";
	}

	/**
	 * Get asset server number
	 *
	 *	@param	path		path of the asset, asset.xxx.com/$path
	 *	@return	int 		the number
	 */
	static public function getServer($url, $type = 1, $http = true)
	{
		return ASSET_SERVER;
		/*
		global $sys_config;
		$multi_server = false;
		if ($type && $sys_config->global->asset_server_prefix) {
			$multi_server = true;
			//$n = sprintf('%u', crc32($url));
			//$n%= self::ASSET_MAX;
		}
		if (!isset($sys_config->global->asset_server_suffix)) {
			//		self::_log($e->getMessage());
			return;
		}
		$prefix = $sys_config->global->asset_server_prefix;
		$suffix = $sys_config->global->asset_server_suffix;
		//$server = $multi_server ? "$prefix$n.$suffix" : "$suffix";
		$server = $multi_server ? "$prefix.$suffix" : "$suffix";
		return $http ? "http://$server" : $server;
		*/
	}

	/**
	 * log
	 *
	 * @param string $message log message
	 */
	static private function _log($message)
	{
		error_log(__CLASS__ . ": $message");
	}
}
