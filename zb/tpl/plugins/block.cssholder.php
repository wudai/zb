<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {css} block plugin
 *
 * Name:     		css<br>
 * Purpose:  		提供css合并去重功能
 * @link 
 * @author 		Yancan <yancan@staff.139.com>
 * @param 		array
 * @param 		Smarty
 * 
 */

function smarty_block_cssholder($params, $content, &$smarty, &$repeat) {
	if($repeat)
		Template::instance()->cssHolder();

	if(empty($content))
		return '';

	foreach(explode("\n", $content) as $v) {
		if(empty($v))
			continue;

		$v = trim($v);
		$v = ltrim($v, '/');
		Template::instance()->cssHolder($v);
	}
}
