
<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @author Anakin Sun
 */


/**
 * Smarty redirect modifier plugin
 *
 * Type:     modifier<br>
 * Name:     redirect<br>
 * Date:     Aug 13, 2012
 * Purpose:  Redirect url
 * Input:    string to format
 * Example:  {$var|redirect}
 * @version 1.0
 * @param 	(String)	$url		| 要跳转的网址
 * @return 	(String) null	
 */
function smarty_modifier_redirect($url)
{
    if(false === strpos($url, 'http://')) $url = 'http://'.$url;
    if(false === strpos($url,'sooker')) return '/?act=redirect&url='.urlencode($url);
	return $url;
}
