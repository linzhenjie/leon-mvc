<?php
namespace Esunphp\Mvc;

use Esunphp\Pattern\Multiton;

class View
{
    use Multiton;
    use Block;

    private $_tempPath;
    private $_controller;
    private $_action;
    private $_layout;
    private $_params = array();

    /**
     * @desc 构造函数
     */
    private function __construct(Controller $ctrlObj)
    {
        $this->_tempPath = Store::get('VIEW_PATH');
        $this->_controller = $ctrlObj->controller;
        $this->_action = $ctrlObj->action;
    }
    public function __set($key,$value)
    {
        $this->_params[$key] = $value;
    }
    //获取参数
    public function _getParam(array $args)
    {
        $param = array(
            'path' => $this->_controller.DIRECTORY_SEPARATOR.$this->_action,
            'data' => array(),
            'echo' => true,
            'type' => 'html',
        );
        //string,array,bool,string ==> path,data,echo,type
        //string,array,bool ==> path,data,echo
        //string,array ==> path,data
        //string ==> path
        //string,bool,string ==> path,echo,type
        //string,bool ==> path
        //string,string ==> path,type
        //array,bool,string ==> data,echo,type
        //array,bool ==> data,echo
        //array ==> data
        //array,string ==> data,type
        //bool,string ==> echo,type
        //bool  ==> echo
        $num = count($args);
        if($num > 0){
            if(is_string($args[0])) $param['path'] = $args[0];
            else if(is_array($args[0])) $param['data'] = $args[0];
            else if(is_bool($args[0])) $param['echo'] = $args[0];
        }
        if($num > 1){
            if(is_string($args[1])) $param['type'] = $args[1];
            else if(is_array($args[1])) $param['data'] = $args[1];
            else if(is_bool($args[1])) $param['echo'] = $args[1];
        }
        if($num > 2){
            if(is_string($args[2])) $param['type'] = $args[2];
            else if(is_bool($args[2])) $param['echo'] = $args[2];
        }
        if($num > 3){
            if(is_string($args[3])) $param['type'] = $args[3];
        }
        $param['data']['_controller'] = $this->_controller;
        $param['data']['_action'] = $this->_action;
        return $param;
    }
    //获取input参数
    public function input($type = 'request')
    {
        return Input::getInstance($type)->setInput($type);
    }

    //渲染视图
    public function render($args)
    {
        $param = $this->_getParam($args);
        if(!headers_sent()) {
            $header = '';
            switch ($param['type']) {
                case 'html':   $header  = 'text/html'   ;break;
                case 'json':   $header  = 'text/json'   ;break;
                case 'bitmap': $header  = 'text/bitmap' ;break;
                case 'xml' :   $header  = 'text/xml'   ;break;
                default    :   $header  = 'text/html'   ;break;
            }
            header('Content-Type:'.$header.'; charset=utf-8');
        }

        //获取内容
        ob_start();

        $this->display($param['path'],$param['data'],$param['type']);
        $display = ob_get_contents();
        ob_end_clean();

        //布局输出
        if(!empty($this->_layout))
        {
            return $this->display($this->_layout,array(
                '__main__'    => trim($display)
            ));
        }else if($param['echo'] === FALSE)
        {
            return $display;
        }
        echo $display;
        return ;
    }

    //设置参数
    public function assign($key,$value = null)
    {
        if($value === null && is_array($key)){
            foreach ($key as $k => $v) {
                $this->_params[$k] = $v;
            }
        }else{
            $this->_params[$key] = $value;
        }
    }

    //渲染模板
    public function display($__file,array $__data = array(),$__type = 'html')
    {
        //注册变量
        foreach ($__data as $key => $value)
        {
            $this->_params[$key] = $value;
        }
        if($__type == 'json'){
            //json输出
            echo json_encode($this->_params);
        }else if($__type == 'bitmap'){

        }else if($__type == 'xml'){

        }else{
            //html输出
            $tmpName = $this->_tempPath.strtolower($__file).'.php';
            if (!empty($this->_params))
            {
                extract($this->_params, EXTR_OVERWRITE);
            }
            $res = include($tmpName);
            if (!$res)
            {
                throw new \Exception('Template file not found ' . $tmpName, 404);
            }
        }
    }

    //模板输出
    public function layout($file,array $__data = array())
    {
        $this->_layout = $file;
        //注册变量
        foreach ($__data as $key => $value)
        {
            $this->_params[$key] = $value;
        }
    }


    public function get($name,$default = '')
    {
        return !empty($name) ? $name : $default;
    }
}

trait Block
{
    private $_blocks = [];
    private $_lastBlockName;

    //块状模板
    public function beginBlock($name)
    {
        ob_start();
        $this->_lastBlockName = $name;
    }

    public function endBlock()
    {
        $display = ob_get_contents();
        ob_end_clean();
        if(!empty($this->_lastBlockName))
        {
            $name = $this->_lastBlockName;
            if(!empty($this->block[$name]))
            {
                $this->block[$name] .= $display;
            }else{
                $this->block[$name] = $display;
            }
            $this->_lastBlockName = null;
        }
    }

    public function hasBlock($name)
    {
        return !empty($this->block[$name]);
    }

    public function echoBlock($name,$echo = true)
    {
        if(!empty($this->block[$name]))
        {
            if($echo){
                echo $this->block[$name];
            }else{
                return $this->block[$name];
            }
        }
    }
}
