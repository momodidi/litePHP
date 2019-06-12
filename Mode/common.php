<?php

/**
 * 普通模式定义
 */
return array(
    // 配置文件
    'config'    =>  array(
        CORE_PATH.'Conf/convention.php',   // 系统惯例配置
        CONF_PATH.'config'.CONF_EXT,      // 应用公共配置
    ),

    // 函数和类文件
    'core'      =>  array(
        CORE_PATH.'Common/functions.php',
        COMMON_PATH.'Common/function.php',
        LIB_PATH . 'App'.EXT,
        LIB_PATH . 'Dispatcher'.EXT,
        LIB_PATH . 'Log'.EXT,
        LIB_PATH . 'Route'.EXT,
        LIB_PATH . 'Controller'.EXT,
    ),
);
