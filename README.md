# LeonPHP

##框架安装与使用

1、使用composer加载框架

    composer require esunphp/mvc

2、创建入口文件

    <?php
    define('ROOT_PATH', __DIR__.'/');

    include ROOT_PATH.'/vendor/autoload.php';

    //创建引导类
    class Bootstrap extends Esunphp\Mvc\Bootstrap
    {
        private $_config = [
            'DEBUG'           => TRUE, //调试模式
            'CONTROLLER_PATH' => ROOT_PATH.'controller/',
            'VIEW_PATH'       => ROOT_PATH.'views/',
            'LIBRARY_PATH'    => ROOT_PATH.'lib/',
        ];

        //初始化操作
        public function init()
        {
            $this->setConfig($this->_config);
        }
    }
    //异常捕获
    try{
        Esunphp\Mvc\Base::start(new Bootstrap());
    }catch(Exception $e){
        $code = $e->getCode();
        switch ($code) {
            case 404: header("HTTP/1.1 404"); break;
            case 513: header("HTTP/1.1 513 Server busy"); break;
            default:  header("HTTP/1.1 500 Internal Server Error"); break;
        }
    }

3、创建目录（目录名称可自定义）

    controller
    views
    lib

4、默认路由（home/main）

创建控制器文件 controller/Home.php

    <?php
    class Home extends Esunphp\Mvc\Controller
    {
        public function main()
        {
            $id = $this->input('get')->get_int('id');
            $this->assign('id',$id);
            $this->render();
        }
    }

创建视图文件 view/home/main.php

    <?=$_controller;?> <!--输出当前控制器-->
    <?=$_action;?> <!--输出当前操作-->
    <?=$id;?> <!--输出$_GET['id']-->
    <?=$this->input('get')->get_string('title');?> <!--输出$_GET['title']-->


访问测试

    #伪静态模式：
    /home/main?id=1&title=HelloWorld
    #常规模式：
    index.php?c=home&a=main&id=1&title=HelloWorld


##框架抛异常错误码

- 404 #页面不存在
- 500 #内部服务器错误,代码错误
- 501 #配置错误，文件不存在
- 502 #网关连接失败，含数据库连接失败
- 513 #网关请求超时
