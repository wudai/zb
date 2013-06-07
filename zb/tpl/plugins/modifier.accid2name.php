<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @authoer matao
 */


/**
 * Smarty accid2name modifier plugin
 *
 * Type:     modifier<br>
 * Name:     accid2name<br>
 * Date:     June 7, 2013
 * Purpose:  transfer account_id to account_name
 * Input:    user_id, account_id
 * Example:  {$account_id|accid2name:$user.user_id}
 * @version 1.0
 * @param 	(String)	$account_id | 账号id
 * @param 	(String)	$user_id	| 账号所有者id
 * @return 	(String)	账号名称
 */

function smarty_modifier_accid2name($account_id, $user_id) {
	$model = &m('account');
	$info = $model->getById($user_id, $account_id);
	return $info['account_name'] ? $info['account_name'] : '未知账户';
}
