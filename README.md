修改路由配置 
map 模式
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

访问 http://xxxx.com/x1/x2/test ,将执行  n1\n2\n3\class 的 method 方法

auto 模式

文件目录(任意层级)
Home/Controller/x1/x2Controller.class.php

访问 http://xxxx.com/x1/x2/test ,将执行  Home\Controller\x1\x2 中的 method 方法