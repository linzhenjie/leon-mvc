<?php
/**
 * @Author: linzj@500wan.com
 * @Desc: 插件类
 * @Date:   2015-12-28 14:39:14
 * @Last Modified by:   linzj
 * @Last Modified time: 2018-06-26 18:10:43
 */
namespace Esunphp\Mvc;

class Task
{
    const START             = 'start';
    const PREROUTER         = 'preRouter';
    const FINISHROUTER      = 'finishRouter';
    const PRECONTROLLER     = 'preController';
    const PREACTION         = 'preAction';
    const FINISHACTION      = 'finishAction';
    const FINISHCONTROLLER  = 'finishController';
    const STOP              = 'stop';


    private static $_task = [];

    //添加插件
    public static function addPlugin(Plugin $plugin)
    {
        array_push(self::$_task,$plugin);
    }

    //执行插件
    public static function doPlugin()
    {
        $params = func_get_args();
        $func = array_shift($params);
        foreach (self::$_task as $plugin) {
            call_user_func_array(array($plugin,$func),$params);
        }
    }
}
