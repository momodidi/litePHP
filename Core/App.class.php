<?php
namespace Core;
/**
 * 应用程序类 执行应用过程管理
 */
class App {

    /**
     * 应用程序初始化
     * @access public
     * @return void
     */
    static public function init() {
        // 加载动态应用公共文件和配置
        load_ext_file(COMMON_PATH);
        
        // 定义当前请求的系统常量
        define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
        define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']);
        define('IS_GET',        REQUEST_METHOD =='GET' ? true : false);
        define('IS_POST',       REQUEST_METHOD =='POST' ? true : false);
        define('IS_PUT',        REQUEST_METHOD =='PUT' ? true : false);
        define('IS_DELETE',     REQUEST_METHOD =='DELETE' ? true : false);
        define('IS_AJAX',       ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')])) ? true : false);

        // URL调度
        Dispatcher::dispatch();

        // URL调度结束
        // 日志目录转换为绝对路径
        C('LOG_PATH',   realpath(LOG_PATH).'/'.MODULE_NAME.'/');
        // TMPL_EXCEPTION_FILE 改为绝对地址
        C('TMPL_EXCEPTION_FILE',realpath(C('TMPL_EXCEPTION_FILE')));
        return ;
    }

    /**
     * 执行应用程序
     * @access public
     * @return void
     */
    static public function exec() {
    
        if(!preg_match('/^[A-Za-z](\\\|\w)*$/',CONTROLLER_NAME)){ // 安全检测
            $module  =  false;
        }else{
            //创建控制器实例
            $module  =  controller(CONTROLLER_NAME);                
        }
        if(!$module) {
            // 是否定义Empty控制器
            $module = A('Empty');
            if(!$module){
                E(L('_CONTROLLER_NOT_EXIST_').':'.CONTROLLER_NAME);
            }
        }

        // 获取当前操作名 支持动态路由
        if(!isset($action)){
            $action    =   ACTION_NAME.C('ACTION_SUFFIX');  
        }
        try{
            if(!preg_match('/^[A-Za-z](\w)*$/',$action)){
                // 非法操作
                throw new \ReflectionException();
            }
            //执行当前操作
            $method =   new \ReflectionMethod($module, $action);
            //自动路由需要 public 且非 static 属性；map路由无需判断
            if(($method->isPublic() && !$method->isStatic())||Route::$route_type==1) {
                $class  =   new \ReflectionClass($module);
                // 前置操作
                if($class->hasMethod('_before_'.$action)) {
                    $before =   $class->getMethod('_before_'.$action);
                    if($before->isPublic()) {
                        $before->invoke($module);
                    }
                }
                
                //执行
                
                $method->invoke($module);
                
                // 后置操作
                if($class->hasMethod('_after_'.$action)) {
                    $after =   $class->getMethod('_after_'.$action);
                    if($after->isPublic()) {
                        $after->invoke($module);
                    }
                }
            }else{
                // 操作方法不是Public 抛出异常
                throw new \ReflectionException();
            }
        } catch (\ReflectionException $e) { 
            // 方法调用发生异常后 引导到__call方法处理
            $method = new \ReflectionMethod($module,'__call');
            $method->invokeArgs($module,array($action,''));
        }
        return ;
    }

    /**
     * 运行应用实例 入口文件使用的快捷方法
     * @access public
     * @return void
     */
    static public function run() {
        G('T_START');
        // 应用初始化
        App::init();
        G('T_END2');
        Log::record("【app_init运行时间 】".G('T_START','T_END2'));
        // 应用开始
        // Session初始化
        if(!IS_CLI){
            session(C('SESSION_OPTIONS'));
        }
        G('T_END2-2');
        Log::record("【应用运行时间 T_END2】".G('T_START','T_END2-2'));
        // 记录应用初始化时间
        G('initTime');
        App::exec();
        // 应用结束
        G('T_END3');
        Log::record("【应用运行时间 T_END3】".G('T_START','T_END3'));
        G('T_END1');
        Log::record("【应用运行时间T_END1】".G('T_START','T_END1'));
        return ;
    }

}