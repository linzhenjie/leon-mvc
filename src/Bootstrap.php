<?php

/**
 * @Author: linzj
 * @Date:   2018-06-20 09:54:50
 * @Last Modified by:   linzj
 * @Last Modified time: 2018-06-27 15:11:11
 */
namespace Esunphp\Mvc;

abstract class Bootstrap
{
    public  $charset = 'utf-8';
    private $_loadDir  = [];
    private $_config = [
        'DEBUG'            => FALSE,
        'TIMEZONE'         => 'PRC',
        'CONTROLLER_LIST'  => FALSE,                       //配置控制器路径 | boolean,string,array
        'DEFAULT_ROUTER'   => '',                          //默认控制器,初始值 home/main
        'URL_SUFFIX_ALLOW' => ['html','json','php'],       //允许的URL后缀  | string,array
        'ROUTER_RULES'     => [],                          //路由规则
        'CONTROLLER_PATH'  => '',
        'VIEW_PATH'        => '',
        'LIBRARY_PATH'     => '',
    ];

    final public function __construct()
    {
        header('Content-type:text/html;charset='.$this->charset);
        header('X-Powered-By:'); //安全修复

        $this->setConfig();

        //开始DEBUG模式
        if($this->_config['DEBUG']) {
            ini_set('display_errors','On');
            error_reporting(E_ALL);
        }

        //设置时区
        date_default_timezone_set($this->_config['TIMEZONE']);

        //注册加载类
        spl_autoload_register(array($this, 'loadClass'), true, true);

        //注册插件
        $this->setPlugin();

    }

    public function initConfig()
    {
        return [];
    }
    public function initPlugin()
    {
        return [];
    }

    private function setConfig()
    {
        $config = $this->initConfig();

        foreach ($config as $key => $value) {
            $this->_config[$key] = $value;
        }
        //格式化路径
        $this->_config['CONTROLLER_PATH'] = rtrim($this->_config['CONTROLLER_PATH'],'/').'/';

        //类路径
        $libraryPath = $this->_config['LIBRARY_PATH'];
        if(is_array($libraryPath)){
            $this->_loadDir = array_map(function($v){
                return rtrim($v,'/').'/';
            },$libraryPath);
        }else if(!empty($libraryPath)){
            array_push($this->_loadDir,rtrim($libraryPath,'/').'/');
        }
        //存储配置项
        Store::set($_SERVER);
        Store::set($this->_config);
    }
    private function setPlugin()
    {
        $plugins = $this->initPlugin();
        if(!is_array($plugins)){
            $plugins = [
                $plugins
            ];
        }
        foreach ($plugins as $key => $value) {
            Task::addPlugin($value);
        }
    }

    /**
     * @desc 自定加载类
     * @param string $className 类名
     */
    private function loadClass($name)
    {
        static $loaded = [];
        if(isset($loaded[$name])) return ;

        foreach ($this->_loadDir as $dir) {
            $file = $dir.str_replace('_','/',$name).'.php';
            if(file_exists($file)) {
                include $file;
                $loaded[$name] = true;
                break;
            }
        }
    }
}
