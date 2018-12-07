<?php
/**
 * @Author: linzj@500wan.com
 * @Desc: 路由类
 * @Date:   2015-12-28 14:39:14
 * @Last Modified by:   linzj
 * @Last Modified time: 2018-06-25 14:00:52
 */
namespace Esunphp\Mvc;
use Esunphp\Pattern\Singleton;

class Router
{
    use Singleton;

    // 使用中的路由信息
    private $useRouter = array(
        'ctrl'  => 'home',
        'act'   => 'main',
        'path'  => array(),
    );

    private $_controller = 'home';
    private $_action = 'main';
    private $_params = [];

    // 路由规则
    public $rules = [];

    /**
     * @desc 构造函数
     *
     * @param array or null $defRouter 默认的路由信息
     *
     * @return void
     */
    private function __construct()
    {
        //默认路由
        $defRouter = Store::get('DEFAULT_ROUTER');
        if(!empty($defRouter)) {
            list($this->_controller,$this->_action) = explode('/', $defRouter);
        }
        //路由规则
        $this->rules = Store::get('ROUTER_RULES');

        //XSS注入处理
        $temp = strtoupper(urldecode(urldecode($_SERVER['REQUEST_URI'])));
        if(strpos($temp, '<') !== false || strpos($temp, '"') !== false) {
            throw new \Exception('XSS Code Found !',403);
        }

        $this->analyzeUrl();
    }

    /**
     * @desc 解析路由
     * @return array
     */
    private function analyzeUrl()
    {
        if(isset($_REQUEST['c'])){
            $this->_controller = strip_tags($_REQUEST['c']);
        }
        if(isset($_REQUEST['a'])){
            $this->_action = strip_tags($_REQUEST['a']);
        }

        // 分析PATHINFO信息
        if(!isset($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = '';
            foreach (array('ORIG_PATH_INFO','REDIRECT_PATH_INFO','REDIRECT_URL') as $type){
                if(!empty($_SERVER[$type])) {
                    if((0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME']))){
                        $_SERVER['PATH_INFO'] = substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME']));
                    }else{
                        $_SERVER['PATH_INFO'] = $_SERVER[$type];
                    }
                    break;
                }
            }
        }

        // 解析PATHINFO信息
        if(empty($_SERVER['PATH_INFO'])) return ;

        $path = $_SERVER['PATH_INFO'];
        $part =  pathinfo($path);
        $ext  = isset($part['extension']) ? strtolower($part['extension']) : '';
        $allow_ext = Store::get('URL_SUFFIX_ALLOW');
        if($ext && array_search($ext, $allow_ext) === false) {
            throw new \Exception('Url Suffix Deny',404);
        }else if($ext){
            $path = rtrim($path,'.'.$ext);
        }
        $path = trim($path,'/');

        //解析路由
        foreach ($this->rules as $rule => $route) {
            //强制要求正则以/开头，以便扩展
            if(strpos($rule, '/')===0 && preg_match($rule, $path, $matches)) {
                $url   = is_array($route) ? $route[0] : $route;
                $url   = preg_replace('/\$(\d+)/e','$matches[\\1]', $url);
                if(stripos($url, '/') === 0 || substr($url, 0, 4) == 'http://'){ //绝对路径或者http url则跳转
                    $http_code = (is_array($route) && isset($route[1])) ? $route[1] : 301;
                    header('Location: '.$url, true, $http_code);
                    exit;
                }else{
                    $path = $url;
                    break;
                }
            }
        }

        //解析路径
        $info   = parse_url($path);
        $paths  = !empty($info['path']) ? explode('/',strip_tags($info['path'])) : array();
        $var    = array();
        if(!empty($info['query'])) parse_str($info['query'],$var);
        if(!empty($paths[0])){
            $this->_controller = array_shift($paths);
        }
        if(!empty($paths[0])){
            $this->_action = array_shift($paths);
        }
        // 解析剩余的URL参数
        $count = count($paths) ;
        if($count > 0) {
            $this->_params = $paths;
            for ($i=0; $i < $count; $i+=2) {
                $var[ $paths[$i] ] = isset($paths[$i+1]) ? $paths[$i+1] : '';
            }
            $_GET     = array_merge($var, $_GET);
            $_REQUEST = array_merge($var, $_REQUEST);
        }
    }


    public function __get($key)
    {
        $t = '_'.$key;
        return $this->$t;
    }
}
