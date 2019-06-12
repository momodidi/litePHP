<?php
namespace Core\Log\Driver;

class File {

    protected $config  =   array(
        'log_time_format'   =>  ' c ',
        'log_file_size'     =>  2097152,
        'log_path'          =>  '',
    );

    // 实例化并传入参数
    public function __construct($config=array()){
        $this->config   =   array_merge($this->config,$config);
    }

    /**
     * 日志写入接口
     * @access public
     * @param string $log 日志信息
     * @param string $destination  写入目标
     * @return void
     */
    public function write($log,$destination='') {
        $now = date($this->config['log_time_format']);
        if(empty($destination))
            $destination = $this->config['log_path'].date('y_m_d').'.log';
        if(!is_dir($this->config['log_path'])) {
            mkdir($this->config['log_path'],0755,true);
        }        
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if(is_file($destination) && floor($this->config['log_file_size']) <= filesize($destination) )
              rename($destination,dirname($destination).'/'.basename($destination,'.log').'-'.date('H_i_s').'.log');
        //检查日志目录的最近一层是否存在 2014年12月1日13:33:12  add by madong
        if(!is_dir(dirname($destination)))   mkdir(dirname($destination));
        //G('LOG_START');
        error_log("[{$now}] ".$_SERVER['REMOTE_ADDR'].' '.gethostname().' '.$_SERVER['REQUEST_URI']."\r\n{$log}\r\n", 3,$destination);
        //G('LOG_END');
        //$log  = '【日志写入时间】'.G('LOG_START','LOG_END');
        //error_log("[{$now}] ".$_SERVER['REMOTE_ADDR'].' '.gethostname().' '.$_SERVER['REQUEST_URI']."\r\n{$log}\r\n", 3,$destination);
    }
}
