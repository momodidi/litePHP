<?php
namespace Core;
/**
 * 路由解析类
 * 依赖 $_SERVER['PATH_INFO']
 * route.php 内容:
 * 
 * return array(
    array(
        
        'prefix' => '', //前缀
        'suffix' => '',  //后缀
        'namespace' => 'n1\n2\n3',//目标类namespace
        'maps' => array(    //路由对应
            'x1/x2/test' => 'Controller@method'
        )
    )
);
 */
class Route {
    /**
     * @var array
     * 
     */
    static private $route_maps = array();
    
    static $route_type = 1;//1,map类型；2，自动路由
    
    static function parseRoute(){
        
        if (!self::parseMap()){
            self::parseAuto();
            self::$route_type = 2;
        }
    }
    
    static function importRoute($route_file){
        $route_groups = include $route_file;
        foreach ($route_groups as $route_group_item){
            $prefix = trim($route_group_item['prefix']);
            $suffix = trim($route_group_item['suffix']);
            $namespace = trim($route_group_item['namespace'],'\\');
            foreach ($route_group_item['maps'] as $_route =>$_target){
                $_route = $prefix.$_route.$suffix;
                $_targets = explode('@', $_target);
                $controller = $_targets[0]?$_targets[0]:'';
                $action = $_targets[1]?$_targets[1]:'';
                
                if ($controller){
                    $controller = $namespace.'\\'.$controller;
                }
                self::$route_maps[$_route] = array(
                    'controller' => $controller,
                    'action' => $action
                );
            }
        }
    }
    
    //TODO 解析预定义路由
    private static function parseMap(){
        $path_info = $_SERVER['PATH_INFO'];
        if (self::$route_maps[$path_info]){
            $route_info = self::$route_maps[$path_info];
            define('CONTROLLER_NAME',   $route_info['controller']);
            define('ACTION_NAME',       $route_info['action']);
            return true;
        }
        return false;
    }

    /**
     * 自动路由
     * 规则 NameSpace/Controller/Action
     */
    private static function parseAuto(){
        $depr   =   C('URL_PATHINFO_DEPR');
        $pat_info = $_SERVER['PATH_INFO'];
        $controller = '';
        $action = '';
        $paths = explode($depr, $pat_info);
        if (count($paths) > 1){
            $action = array_pop($paths);
            $controller = implode('\\', $paths);
        }
        
        if ($controller){
            $controller = MODULE_NAME.'\\'.C('DEFAULT_C_LAYER').'\\'.$controller;
        }
        
        define('CONTROLLER_NAME',   $controller);
        define('ACTION_NAME',       $action);
        
    }

}