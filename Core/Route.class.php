<?php
namespace Core;
/**
 * 路由解析类
 * 依赖 $_SERVER['PATH_INFO']
 */
class Route {
    
    static private $route_maps = array();
    static $route_type = 1;//1,map类型；2，自动路由
    
    static function parseRoute(){
        
        if (!self::parseMap()){
            self::parseAuto();
            self::$route_type = 2;
        }
    }
    
    static function importRoute(){
        
    }
    
    //TODO 解析预定义路由
    private static function parseMap(){
        $path_info = $_SERVER['PATH_INFO'];
        if (in_array($path_info, self::$route_maps)){
            $route_info = self::$route_maps[$path_info];
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
        
//         $depr_pos = strrpos ($pat_info, $depr);
        
//         if ($depr_pos  === false){
//             $contorller = $pat_info;
//         }else{
//             $contorller = substr($pat_info, 0,$depr_pos);
//             $action  = substr($pat_info,strlen($pat_info)-$depr_pos);
//         }

        if ($controller){
            $controller = MODULE_NAME.'\\'.C('DEFAULT_C_LAYER').'\\'.$controller;
        }
        
        define('CONTROLLER_NAME',   $controller);
        define('ACTION_NAME',       $action);
        
    }

}