<?php
namespace Core;
/**
 * 日志处理类
 */
class Log {

    // 日志级别 从上到下，由低到高
    const EMERG     = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT     = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const CRIT      = 'CRIT';  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR       = 'ERR';  // 一般错误: 一般性错误
    const WARN      = 'WARN';  // 警告性错误: 需要发出警告的错误
    const NOTICE    = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO      = 'INFO';  // 信息: 程序输出信息
    const DEBUG     = 'DEBUG';  // 调试: 调试信息
    const SQL       = 'SQL';  // SQL：SQL语句 注意只在调试模式开启时有效

    // 日志信息
    static protected $log       =  array();

    // 日志存储
    static protected $storage   =   null;

    // 日志初始化
    static public function init($config=array()){
        $type   =   isset($config['type'])?$config['type']:'File';
        $class  =   strpos($type,'\\')? $type: 'Core\\Log\\Driver\\'. ucwords(strtolower($type));           
        unset($config['type']);
        self::$storage = new $class($config);
    }

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param boolean $record  是否强制记录
     * @return void
     */
    static function record($message,$level=self::ERR,$record=false) {
        if($record || false !== strpos(C('LOG_LEVEL'),$level)) {
            $mic_time = microtime(true);
            self::$log[] =   "[{$mic_time}]{$level}: {$message}\r\n";
            if (count(self::$log) > 200) {// 未防止溢出，每200条log写入一次文件 madong
            	 self::$log[] =   "--------------每200条写入一次文件，防止内存溢出--------------\r\n";
            	 $uuid = uniqid('log-');
            	 self::$log[] =   "↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓下接相同的uuid：".$uuid."↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓\r\n";
            	 self::save();
            	 self::$log = array();
            	 self::$log[] =  "↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑上接相同的uuid：".$uuid."↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑\r\n";
            }
        }
    }

    /**
     * 日志保存
     * @static
     * @access public
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @param bool $clear_log 写入后是否清除旧数据
     * @return void
     */
    static function save($type='',$destination='',$clear_log=true) {
        if(empty(self::$log)) return ;
        if(empty($destination))
            $destination = C('LOG_PATH').date('y_m_d').'.log';
        if(!self::$storage){
            $type = $type?:C('LOG_TYPE');
            $class  =   'Core\\Log\\Driver\\'. ucwords($type);
            self::$storage = new $class();            
        }
        $message    =   implode('',self::$log);
        self::$storage->write($message,$destination);
        // 保存后清空日志缓存
        self::$log = array();
        if (PHP_SAPI === 'cli')//如果cli模式下运行的，则有可能是root用户，此时进行一次文件的 用户名、用户组修改，改为 www www
        {
        	chown($destination, 'www');
        	chgrp($destination, 'www');
        }
    }

    /**
     * 日志直接写入
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @return void
     */
    static function write($message,$level=self::ERR,$type='',$destination='') {
        if(!self::$storage){
            $type = $type?:C('LOG_TYPE');
            $class  =   'Core\\Log\\Driver\\'. ucwords($type);
            self::$storage = new $class();            
        }
        if(empty($destination))
            $destination = C('LOG_PATH').date('y_m_d').'.log';        
        self::$storage->write("{$level}: {$message}", $destination);
    }
}