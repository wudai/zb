<?php
require(dirname(__FILE__).'/../config/zb_config.inc.php');//依环境不同的配置文件
require(ROOT_PATH . '/config/const.inc.php');//宏定义配置文件
require(ROOT_PATH . '/core/core.php');//核心定义
Core::startup(array(
    'default_app'   =>  'default',
    'default_act'   =>  'index',
    'app_root'      =>  ZB_PATH . '/app',
    'external_libs' =>  array(
        ROOT_PATH . '/core/controller/app.base.php',//控制器基础类
        ROOT_PATH . '/includes/global.lib.php',//基础类定义
        ROOT_PATH . '/includes/mallapp.base.php',//基础类定义
        ZB_PATH . '/app/frontend.base.php',//后台专用基础类定义
    ),
));
