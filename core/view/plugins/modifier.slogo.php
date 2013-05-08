<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty ulogo modifier plugin
 *
 * Type:     modifier<br>
 * Name:     ulogo<br>
 * Date:     Nov 5th, 2008
 * Purpose:  get user avatar full path by id
 * Input:    array $userInfo
 * Input:	 string size need
 * Example:  {$var|ulogo:'48'}
 * @version 1.0
 * @param 	(String)	$store_logo | 学校logo地址 
 * @param 	(String)	$size		| 可选,(S,L)
 * @return 	(String)	full path
 */
function smarty_modifier_slogo($store_logo, $size='L') {
	switch (strtoupper($size)) {
		case 'S':
			$defaultImg = ASSET_SERVER . '/core/img/school_logo_S.png';
			break;
		case 'L':
		default:
			$defaultImg = ASSET_SERVER . '/core/img/school_logo_L.png';
			break;
	}
	if( !$store_logo) return $defaultImg;
	return  SITE_URL . '/' . $store_logo;
}

