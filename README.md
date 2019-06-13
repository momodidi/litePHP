
精简了框架内容，删除了很多日常api项目用不到的东西

路由配置 

###map 模式

Home\Route\route.php 定义：

    return array(
        array(
            
            'prefix' => '', //前缀
            'suffix' => '',  //后缀
            'namespace' => 'n1\n2\n3',//目标类namespace
            'maps' => array(    //路由对应
                'x1/x2/test' => 'class@method'
            )
        )
    );


访问 http://xxxx.com/x1/x2/test ,将执行  
    n1\n2\n3\class -> method()
    


###auto 模式

Controller(任意层级)

    Home/Controller/x1/x2Controller.class.php

访问 http://xxxx.com/x1/x2/test ,将执行  

    Home\Controller\x1\x2 -> test()
    
#####优先判断是否存在map，若不存在则执行auto模式，使用$_SERVER['PATH_INFO']来查找相应的类
