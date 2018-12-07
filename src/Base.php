<?php

/**
 * @Author: linzj
 * @Date:   2018-06-20 09:54:50
 * @Last Modified by:   linzj
 * @Last Modified time: 2018-06-27 14:53:38
 */
namespace Esunphp\Mvc;

use Exception;
use Esunphp\Pattern\Singleton;

class Base
{
    use Singleton;

    /**
     * @desc 构造函数
     *
     * @return
     */
    private function __construct()
    {
        //注册异常类
        set_exception_handler(array('Esunphp\Mvc\Exception', 'exceptionHandler'));
        set_error_handler(array('Esunphp\Mvc\Exception','errorHandler'));
        register_shutdown_function(array('Esunphp\Mvc\Exception','fatalErrorHandler'));
    }

    /**
     * @desc 加载框架
     *
     * @param Bootstrap $bootstrap 引导类
     * @return
     */
    public static function start(Bootstrap $bootstrap)
    {
        //实例化对象
        $obj = self::getInstance();
        $bootstrap->initPlugin();
        //钩子
        Task::doPlugin(Task::START);
        //分发路由
        $obj->dispatch();
    }

    /**
     * @desc 调度程序
     *
     * @return void
     */
    public function dispatch()
    {
        // 加载路由
        $router = Router::getInstance();

        //钩子
        Task::doPlugin(Task::PRECONTROLLER,$router);

        //加载控制器
        $ctrl = Controller::getInstance($router->controller);
        $ctrl->forward($router->action,$router->params);
    }


    public function __destruct()
    {
        //钩子
        Task::doPlugin(Task::STOP);
    }
}
