<?php
ini_set('memory_limit', '-1');
date_default_timezone_set('PRC');//时区
require(dirname(__FILE__).'/../config/zb_config.inc.php');//依环境不同的配置文件
require(ROOT_PATH . '/config/const.inc.php');//宏定义配置文件
require(ROOT_PATH . '/core/core.php');//核心定义
require(ROOT_PATH . '/includes/global.lib.php');//包含文件
/* 加载初始化文件 */
require_once(ROOT_PATH . '/core/model/model.base.php');   //模型基础类
$emod = &m('event');
$eamod = &m('event_account');
define('CRON_NAME', 'import_old');
cron_log(CRON_NAME, 'start');
$file = fopen(ROOT_PATH . '/log/subject_143150.log', 'r');
$accounts = array(
	'光大阳光卡' => array(0,7),
	'马涛的现金' => array(0,1),
	'支付宝' => array(0,2),
	'招行一卡通' => array(0,20),
	'麻辣诱惑会员卡' => array(0,32),
	'中信信用卡' => array(0,5),
	'深发展金卡' => array(0,42),
	'广发信用卡' => array(0,19),
	'招行信用卡' => array(0,9),
	'林雪' => array(1,27),
	'王宇航' => array(1,31),
	'孙宇' => array(1,16),
	'马涛的公交一卡通' => array(0,43),
	'郭建奇' => array(1,44),
	'杨凡' => array(1,45),
	'王伟森' => array(1,36),
	'交行信用卡' => array(0,6),
	'民生信用卡' => array(0,26),
	'浦发信用卡' => array(0,35),
	'李远' => array(1,46),
	'桂娜' => array(1,30),
	'中友卡' => array(0,48),
	'深发展信用卡' => array(0,8),
	'单鑫波' => array(1,47),
	'半亩园会员卡' => array(0,49),
	'华联储值卡' => array(0,50),
	'我的现金' => array(0,1),
);
$account_id = 4;
while (!feof($file)) {
	$line = trim(fgets($file));
	if (!$line) break;
	list($date, $out, $in, $e_id, $comment)  = explode("\t", $line);
	if ($out) {
		$pattern = '/备注：此款还到 \[([^]]*)\]/';
		if (preg_match($pattern, $comment, $matches)) {
			$target = $accounts[$matches[1]];
			$emod->begin();
			$event_data = array(
				'user_id'		=> 1,
				'type'			=> $target[0] == 0 ? EventModel::TYPE_TRANSFER_IN : EventModel::TYPE_TRANSFER_OUTER,
				'amount'		=> $out,
				'event_date'	=> $date,
				'create_time'	=> TIME,
				'comment'		=> $matches[2],
			);
			$event_id = $emod->add($event_data);
			if (!$event_id) {
				cron_log(CRON_NAME, "event failed\t$line");
				$emod->rollback();
				continue;
			}
			$out_data = array(
				'event_id'			=> $event_id,
				'account_id'		=> $account_id,
				'type'				=> Event_accountModel::TYPE_TRANSFER_OUT,
				'amount'			=> 0 - $out,
				'event_date'		=> $date,
				'comment'			=> $matches[2],
				'extra'				=> $target[1],
				'zb_id'				=> $e_id,
			);
			if (!$eamod->add($out_data)) {
				cron_log(CRON_NAME, "out_out failed\t$line");
				$emod->rollback();
				continue;
			}
			$in_data = array(
				'event_id'			=> $event_id,
				'account_id'		=> $target[1],
				'type'				=> Event_accountModel::TYPE_TRANSFER_IN,
				'amount'			=> $out,
				'event_date'		=> $date,
				'comment'			=> $matches[2],
				'extra'				=> $account_id,
				'zb_id'				=> $e_id,
			);
			if (!$eamod->add($in_data)) {
				cron_log(CRON_NAME, "out_in failed\t$line");
				$emod->rollback();
				continue;
			}
			$emod->commit();
		} else {
			cron_log(CRON_NAME, "out not match\t$line");
		}
	} else {
		$pattern = '/备注：从 \[([^]]*)\] 中借出(.*)/';
		if (preg_match($pattern, $comment, $matches)) {
			$target = $accounts[$matches[1]];
			$emod->begin();
			$event_data = array(
				'user_id'		=> 1,
				'type'			=> $target[0] == 0 ? EventModel::TYPE_TRANSFER_OUT : EventModel::TYPE_TRANSFER_OUTER,
				'amount'		=> $in,
				'event_date'	=> $date,
				'create_time'	=> TIME,
				'comment'		=> $matches[2],
			);
			$event_id = $emod->add($event_data);
			if (!$event_id) {
				cron_log(CRON_NAME, "event failed\t$line");
				$emod->rollback();
				continue;
			}
			$in_data = array(
				'event_id'			=> $event_id,
				'account_id'		=> $account_id,
				'type'				=> Event_accountModel::TYPE_TRANSFER_IN,
				'amount'			=> $in,
				'event_date'		=> $date,
				'comment'			=> $matches[2],
				'extra'				=> $target[1],
				'zb_id'				=> $e_id,
			);
			if (!$eamod->add($in_data)) {
				cron_log(CRON_NAME, "in_in failed\t$line");
				$emod->rollback();
				continue;
			}
			$out_data = array(
				'event_id'			=> $event_id,
				'account_id'		=> $target[1],
				'type'				=> Event_accountModel::TYPE_TRANSFER_OUT,
				'amount'			=> 0 - $in,
				'event_date'		=> $date,
				'comment'			=> $matches[2],
				'extra'				=> $account_id,
				'zb_id'				=> $e_id,
			);
			if (!$eamod->add($out_data)) {
				cron_log(CRON_NAME, "in_out failed\t$line");
				$emod->rollback();
				continue;
			}
			$emod->commit();
		} else {
			cron_log(CRON_NAME, "in not match\t$line");
		}
	}
}
cron_log(CRON_NAME, 'ok');
