<?php
namespace Leonphp\Mvc;
use Leonphp\Pattern\Singleton;

class Input
{
    use Singleton;

    private $_args;
    private $_handle;

	/**
	 * 构造函数
	 * @param string $type 类型
	 */
    private function  __construct()
    {
        if(isset($_GET)){
            $this->_args['get'] = $_GET;
            unset($_GET);
        }
        if(isset($_POST)){
            $this->_args['post'] = $_POST;
            unset($_POST);
        }
        if(isset($_REQUEST)){
            $this->_args['request'] = $_REQUEST;
            unset($_REQUEST);
        }
        //处理json的请求包
        if(!empty($_SERVER['CONTENT_TYPE']) && strcasecmp($_SERVER['CONTENT_TYPE'],'application/json') > 0){
            $input = json_decode(file_get_contents('php://input'),true);
            if(!empty($input) && is_array($input)) {
                $method =!empty($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'request';
                $this->_args[$method] = $input;
            }
        }
        $this->setInput();
    }

    public function setInput($type = 'request')
    {
        $this->_handle = $this->_args[$type];
        return $this;
    }

    public function __call($func, $args)
    {
        $transfer = [
            'get_int' => function($value){
                $temp = intval($value);
                return strval($temp) !== $value? null : $temp;
            },
            'get_float' => function($value){
                $temp = floatval($value);
                return $value===0.0 ? null : $temp;
            },
            'get_string' => function($value){
                $temp = strval($value);
                return $value==='' ? null : $temp;
            },
            'get_array' => function($value){
                $temp = is_array($value) ? $value : (array)$value;
                return !empty($temp) ? $temp : null;
            },
        ];
        $key   = $args[0];
        $def = isset($args[1])? $args[1] : null; //默认值
        $value = null;
        if(isset($this->_handle[$key])){
            $value = trim($this->_handle[$key]);
            $value = $transfer[$func]($value);
        }
        return $value === null ? $def : $value;
    }

    /**
     * 是否存在某key
     * @param string $key
     */
    public function hasKey($key)
    {
        return array_key_exists($key,$this->_handle);
    }

    public function get_params($array)
    {
        $ret = array();
        foreach($array as $key => $value){
            if(is_int($key)){
              $ret[$value] = $this->get_string($value);
            }else if(is_string($value)){
               $ret[$key] = $this->get_string($key,$value);
            }else if(is_float($value)){
               $ret[$key] = $this->get_float($key,$value);
            }else if(is_int($value)){
               $ret[$key] = $this->get_int($key,$value);
            }
        }
        return $ret;
    }

}
