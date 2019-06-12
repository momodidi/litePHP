<?php
namespace Core;
/**
 * 完成URL解析、路由和调度
 */
class Dispatcher {

    /**
     * URL映射到控制器
     * @access public
     * @return void
     */
    static public function dispatch() {
        $varPath        =   C('VAR_PATHINFO');
        $varAddon       =   C('VAR_ADDON');
        $varModule      =   C('VAR_MODULE');
        $varController  =   C('VAR_CONTROLLER');
        $varAction      =   C('VAR_ACTION');
        $urlCase        =   C('URL_CASE_INSENSITIVE');
        if(isset($_GET[$varPath])) { // 判断URL里面是否有兼容模式参数
            $_SERVER['PATH_INFO'] = $_GET[$varPath];
            unset($_GET[$varPath]);
        }elseif(IS_CLI){ // CLI模式下 index.php module/controller/action/params/...
            $_SERVER['PATH_INFO'] = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
        }
        
        $_SERVER['PATH_INFO'] = trim($_SERVER['PATH_INFO'],'/');

        // 检测模块是否存在
        if( MODULE_NAME && is_dir(APP_PATH.MODULE_NAME)){
            // 定义当前模块路径
            define('MODULE_PATH', APP_PATH.MODULE_NAME.'/');
            // 定义当前模块的模版缓存路径
            C('CACHE_PATH',CACHE_PATH.MODULE_NAME.'/');

            // 加载模块配置文件
            if(is_file(MODULE_PATH.'Conf/config'.CONF_EXT))
                C(load_config(MODULE_PATH.'Conf/config'.CONF_EXT));

            // 加载预定义路由
            if(is_file(MODULE_PATH.'Route/route.php')){
               //TODO 导入预定义路由
                Route::importRoute(MODULE_PATH.'Route/route.php');
            }
                
            // 加载模块函数文件
            if(is_file(MODULE_PATH.'Common/function.php'))
                include MODULE_PATH.'Common/function.php';
            // 加载模块的扩展配置文件
            load_ext_file(MODULE_PATH);
        }else{
            E(L('_MODULE_NOT_EXIST_').':'.MODULE_NAME);
        }
        Route::parseRoute();

//         //解析path_info
//         if('' != $_SERVER['PATH_INFO'] && (!C('URL_ROUTER_ON') ||  !Route::check()) ){   // 检测路由规则 如果没有则按默认规则调度URL

//             $depr   =   C('URL_PATHINFO_DEPR');
//             $paths  =   explode($depr,trim($_SERVER['PATH_INFO'],$depr));

//             if(C('CONTROLLER_LEVEL')>1){// 控制器层次
//                 $_GET[$varController]   =   implode('/',array_slice($paths,0,C('CONTROLLER_LEVEL')));
//                 $paths  =   array_slice($paths, C('CONTROLLER_LEVEL'));
//             }else{
//                 $_GET[$varController]   =   array_shift($paths);
//             }
            
//             // 获取操作
//             if(!defined('BIND_ACTION')){
//                 $_GET[$varAction]  =   array_shift($paths);
//             }
//         }
//         // 获取控制器的命名空间（路径）
//         define('CONTROLLER_PATH',   self::getSpace($varAddon,$urlCase));
//         // 获取控制器和操作名
//         define('CONTROLLER_NAME',   self::getController($varController,$urlCase));
//         define('ACTION_NAME',       self::getAction($varAction,$urlCase));

//         // 当前控制器的UR地址
//         $controllerName    =   defined('CONTROLLER_ALIAS')? CONTROLLER_ALIAS : CONTROLLER_NAME;

//         //保证$_REQUEST正常取值
//         $_REQUEST = array_merge($_POST,$_GET);
    }

    /**
     * 获得控制器的命名空间路径 便于插件机制访问
     */
    static private function getSpace($var,$urlCase) {
        $space  =   !empty($_GET[$var])?ucfirst($var).'\\'.strip_tags($_GET[$var]):'';
        unset($_GET[$var]);
        return $space;
    }

    /**
     * 获得实际的控制器名称
     */
    static private function getController($var,$urlCase) {
        $controller = (!empty($_GET[$var])? $_GET[$var]:C('DEFAULT_CONTROLLER'));
        unset($_GET[$var]);
        if($maps = C('URL_CONTROLLER_MAP')) {
            if(isset($maps[strtolower($controller)])) {
                // 记录当前别名
                define('CONTROLLER_ALIAS',strtolower($controller));
                // 获取实际的控制器名
                return   ucfirst($maps[CONTROLLER_ALIAS]);
            }elseif(array_search(strtolower($controller),$maps)){
                // 禁止访问原始控制器
                return   '';
            }
        }
        if($urlCase) {
            // URL地址不区分大小写
            // 智能识别方式 user_type 识别到 UserTypeController 控制器
            $controller = parse_name($controller,1);
        }
        return strip_tags(ucfirst($controller));
    }

    /**
     * 获得实际的操作名称
     */
    static private function getAction($var,$urlCase) {
        $action   = !empty($_POST[$var]) ?
            $_POST[$var] :
            (!empty($_GET[$var])?$_GET[$var]:C('DEFAULT_ACTION'));
        unset($_POST[$var],$_GET[$var]);
        if($maps = C('URL_ACTION_MAP')) {
            if(isset($maps[strtolower(CONTROLLER_NAME)])) {
                $maps =   $maps[strtolower(CONTROLLER_NAME)];
                if(isset($maps[strtolower($action)])) {
                    // 记录当前别名
                    define('ACTION_ALIAS',strtolower($action));
                    // 获取实际的操作名
                    if(is_array($maps[ACTION_ALIAS])){
                        parse_str($maps[ACTION_ALIAS][1],$vars);
                        $_GET   =   array_merge($_GET,$vars);
                        return $maps[ACTION_ALIAS][0];
                    }else{
                        return $maps[ACTION_ALIAS];
                    }
                    
                }elseif(array_search(strtolower($action),$maps)){
                    // 禁止访问原始操作
                    return   '';
                }
            }
        }
        return strip_tags( $urlCase? strtolower($action) : $action );
    }

    /**
     * 获得实际的模块名称
     */
    static private function getModule($var) {
        $module   = (!empty($_GET[$var])?$_GET[$var]:C('DEFAULT_MODULE'));
        unset($_GET[$var]);
        if($maps = C('URL_MODULE_MAP')) {
            if(isset($maps[strtolower($module)])) {
                // 记录当前别名
                define('MODULE_ALIAS',strtolower($module));
                // 获取实际的模块名
                return   ucfirst($maps[MODULE_ALIAS]);
            }elseif(array_search(strtolower($module),$maps)){
                // 禁止访问原始模块
                return   '';
            }
        }
        return strip_tags(ucfirst($module));
    }

}
