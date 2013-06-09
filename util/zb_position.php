<?php
/**
 * 用于更新频道页数据的定时脚本 
 */
ini_set('memory_limit', '-1');
date_default_timezone_set('PRC');//时区
require(dirname(__FILE__).'/../config/zb_config.inc.php');//依环境不同的配置文件
require(ROOT_PATH . '/config/const.inc.php');//宏定义配置文件
require(ROOT_PATH . '/core/core.php');//核心定义
require(ROOT_PATH . '/includes/global.lib.php');//包含文件
/* 加载初始化文件 */
require_once(ROOT_PATH . '/core/model/model.base.php');   //模型基础类
$pmod = &m('position');
define('CRON_NAME', 'add_zb_position');
cron_log(CRON_NAME, 'start');
$arr = array(
	1 => '淘宝网',
	2 => '天猫',
	3 => '京东商城',
	4 => '亚马逊',
	5 => '苏宁易购',
	6 => '易迅网',
	7 => '新蛋中国',
	8 => '美国亚马逊',
	9 => '1号店',
	10 => '当当网',
	11 => '库巴网',
	12 => '为为网',
	13 => '国美在线',
	14 => '高鸿商城',
	15 => '飞虎乐购',
	16 => '卓美网',
	17 => '天极商城',
	18 => '绿森数码',
	19 => '锐意网',
	20 => '唯品会',
	21 => '俏物悄语',
	22 => '聚尚网',
	23 => '猛买网',
	24 => '美团网',
	25 => '糯米网',
	26 => '走秀网',
	27 => '邦购网',
	28 => '凡客诚品',
	29 => '优购时尚商城',
	30 => '银泰网',
	31 => '好乐买',
	32 => '韩都衣舍',
	33 => '梦芭莎',
	34 => '玛萨玛索',
	35 => '乐淘网',
	36 => '麦包包',
	37 => '聚美优品',
	38 => '知我药妆',
	39 => '乐蜂网',
	40 => '蔚蓝网',
	41 => '99网上书城',
	42 => '中国图书网',
	43 => '博库网',
	44 => '我买网',
	45 => '酒仙网',
	46 => '酒美网',
	47 => '嘀嗒猫',
	48 => '中国零食网',
	49 => '顺丰优选',
	50 => '沱沱工社',
	51 => '红孩子商城',
	52 => '金象网',
	53 => '百洋健康网',
	54 => '趣玩网',
	1001 => '家乐福-中关村广场店',
	1002 => '家乐福-国展店',
	1003 => '家乐福-马连道店',
	1500 => '沃尔玛-知春路店',
	1501 => '沃尔玛-建国路店',
);
foreach ($arr as $id => $name) {
	$pmod->add(array(
		'position_id'	=> $id,
		'position_name' => $name,
	));
}
cron_log(CRON_NAME, 'ok');
