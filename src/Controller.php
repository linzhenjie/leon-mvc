<?php
/**
 * @Author: linzj@500wan.com
 * @Desc: 路由类
 * @Date:   2015-12-28 14:39:14
 * @Last Modified by:   linzj
 * @Last Modified time: 2018-12-07 11:27:00
 */
namespace Leonphp\Mvc;

class Controller
{
    public $controller    = null;
    public $action        = null;

    private static $_instance = array();
    private static $_execCount = 0;

    public static function getInstance($key = 0) //不带参数，默认取第一个
    {
        if (!isset(self::$_instance[$key])) {
            $class = ucfirst(strtolower($key));
            $path  = Store::get('CONTROLLER_PATH').$class.'.php';
            if(!is_file($path)){
                throw new \Exception('Controller not found '.$path,404);
            }
            include_once $path;
            $ctrl_ins = new \ReflectionClass($class);
            //检测类是否是该类子类
            if(!$ctrl_ins->isSubclassOf(__CLASS__)){
                throw new \Exception($class.' Don\'t extends Leonphp\Mvc\Controller',500);
            }
            self::$_instance[$key] = new $class();
            self::$_instance[$key]->controller = $key;
            self::$_instance[$key]->init();
        }
        return self::$_instance[$key];
    }

    protected function __construct()
    {

    }

    protected function init()
    {

    }
    /**
     * 动作转发执行（转发不超过5次）
     * @param string $controller //要转给动作的控制器，如果为空, 则转给当前控制器
     * @param string $action     //要转给的动作, 如果为空, 则转给当前动作
     * @param array  $params     //附加的参数
     */
    final public function forward()
    {
        self::$_execCount++;
        if(self::$_execCount > 5) return;
        $num = func_num_args();
        $args = func_get_args();
        $obj = $this;
        $action = $this->action;
        $params = array();
        if($num == 1) {
            $params = $args[0];
        }elseif($num == 2){
            if(is_array($args[1])){
                $action = $args[0];
                $params = $args[1];
            }else{
                $obj = $this->getInstance($args[0]);
                $action = $args[1];
            }
        }elseif($num == 3) {
            $obj = $this->getInstance($args[0]);
            $action = $args[1];
            $params = $args[2];
        }
        if(!method_exists($obj,$action)){
            throw new \Exception(get_class($obj).' class '.$action.' Action not found',404);
        }
        $obj->action = $action;
        call_user_func_array(array($obj,$action),$params);
    }
    /**
     * 重定向
     * @param string $url //地址
     */
    final public function redirect($url)
    {
        header('Location:'.$url,false);
        exit();
    }

    public function assign($key, $value = null)
    {
        return View::getInstance($this)->assign($key,$value);
    }

    public function render()
    {
        return View::getInstance($this)->render(func_get_args());
    }

    public function input($type = 'request')
    {
        return Input::getInstance()->setInput($type);
    }

    public function json()
    {
        $num = func_num_args();
        $args = func_get_args();
        $data = array('code'=>0,'msg'=>'','data'=>[]);
        if($num == 1) {
            if(is_array($args[0])){
                $data['code'] = isset($args[0]['code']) ? $args[0]['code'] : $data['code'];
                $data['msg'] = isset($args[0]['msg']) ? $args[0]['msg'] : $data['msg'];
                $data['data'] = isset($args[0]['data']) ? $args[0]['data'] : $data['data'];
            }else if(is_string($args[0])){
                $data['code'] = 1;
                $data['msg'] = $args[0];
            }else {
                $data['code'] = $args[0];
            }
        }elseif($num == 2){
            $data['code'] = $args[0];
            if(is_array($args[1])){
                $data['data'] = $args[1];
            }else {
                $data['msg'] = $args[1];
            }
        }elseif($num == 3) {
            $data = array('code'=> $args[0],'msg'=>$args[1], 'data'=>$args[2] );
        }
        json_encode($data);exit;
    }

    public function __destruct()
    {

    }
}
