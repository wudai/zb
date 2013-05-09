<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {js} block plugin
 *
 * Name:     		js<br>
 * Purpose:  		提供js合并去重功能
 * @link 
 * @author 		Yancan <yancan@staff.139.com>
 * @param 		array
 * @param 		Smarty
 * 
 */

define('SMARTY_JSHOLDER_FILE_PER_URL', 30);
function smarty_block_jsholder($params, $content, &$smarty, &$repeat) {
	if($repeat)
		return;

	static $cont = array();

	$output	= (isset($params['output']) and $params['output']);
	if($output) {
		if(empty($cont))
			return '""';

		if(Template::isCombo()) {
			$files = array_keys($cont);
			if ((count($files) > SMARTY_JSHOLDER_FILE_PER_URL)) {
				$js = array();
				while ($list = array_splice($files, 0, SMARTY_JSHOLDER_FILE_PER_URL)) {
					$js[] = "'" . Asset::getComboUrl($list) . "'";
				}
				$url = '[' . implode(',', $js). ']';
			} else {
				$url .= "'" . Asset::getComboUrl($files) . "'";
			}
		}
		else {
			/*foreach ($cont as $key => $value) {
				$url .= '<script type="text/javascript" src="' . Asset::getUrl($key). '"></script>';
			}*/
			$tmpArray = array();
			foreach ($cont as $key => $value) {
                                $tmpArray[] = '"'.Asset::getUrl($key).'"';
                        }
			$url = '['.implode(',',$tmpArray).']';
		}

		return $url;
	}

	if(empty($content))
		return '';

	foreach(explode("\n", $content) as $v) {
		if(empty($v))
			continue;

		$v = trim($v);
		$v = ltrim($v, '/');
		$cont[$v] = 1;
	}
}
