<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @authoer matao
 */


/**
 * Smarty enumtostr modifier plugin
 *
 * Type:     modifier<br>
 * Name:     enumtostr<br>
 * Date:     Sept 13, 2012
 * Purpose:  transfer enum value to string description
 * Input:    enum value to transfer, bmodel name, col name
 * Example:  {$num|enumtostr:"school":"status"}
 * @version 1.0
 * @param 	(String)	$num		| 要翻译的枚举值
 * @param 	(String)	$model     | 字段所在的业务模型名
 * @param 	(String)	$col		| 字段名
 * @return 	(String)	枚举值的文字描述
 */

function smarty_modifier_enumtostr($num, $model, $col, $ext1=null) {
	$model = &m($model);
	$method = 'get'.ucfirst($col)."List";
	if (!is_object($model) || !method_exists($model, $method)) return '未知';
	$list = $model->$method($ext1);
	return array_key_exists($num, $list) ? $list[$num] : '未知';
}
