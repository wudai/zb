<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @author Rejoy.li
 */


/**
 * Smarty formaturl modifier plugin
 *
 * Type:     modifier<br>
 * Name:     formaturl<br>
 * Date:     JUNE 11, 2008
 * Purpose:  Format url as purpose
 * Input:    string to format
 * Example:  {$var|formaturl}
 * @version 1.0
 * @param 	(String)	$url		| 要格式化的网址
 * @param 	(Boolean)	$mtime		| 可选,文件创建时间
 * @return 	(String)	格式化后的网址
 */
function smarty_modifier_formaturl( $url , $mtime=true , $type=1)
{
	return Asset::getUrl( $url , $mtime , $type) ;
}
