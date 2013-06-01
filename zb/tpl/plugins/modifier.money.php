<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty money modifier plugin
 *
 * Type:     modifier<br>
 * Name:     money<br>
 * Date:     Dec 18th, 2012
 * Purpose:  format money like xxx.xx
 * Input:    decimal
 * Example:  {$price|money}
 * @version 1.0
 * @param 	(String)	$price 价格
 * @return 	(String)	格式化后的价格
 */
function smarty_modifier_money($money) {
	return sprintf("%01.2f", $money);
}

