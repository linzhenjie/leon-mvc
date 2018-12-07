<?php
/**
 * @Author: linzj@500wan.com
 * @Desc: Session 管理类
 * @Date:   2015-12-28 14:39:14
 * @Last Modified by:   linzj
 * @Last Modified time: 2018-06-25 15:23:26
 */
namespace Esunphp\Mvc;

use Esunphp\Pattern\Singleton;

class Session
{
    use Singleton;
    private $started            = FALSE;
    const SESSION_NAME          = 'SESSIONID';
    const SESSION_CACHE_LIMITER = 'private,must-revalidate,max-age=604800';
    const SESSION_TIMEOUT       = 604800;
    const SESSION_DOMAIN        = null;

    private function __construct()
    {
        //获取服务端配置的session信息
        $session_name          = Store::get('SESSION_NAME',self::SESSION_NAME);
        $session_cache_limiter = Store::get('SESSION_CACHE_LIMITER',self::SESSION_CACHE_LIMITER);
        $session_timeout       = Store::get('SESSION_TIMEOUT',self::SESSION_TIMEOUT);
        $session_domain        = Store::get('SESSION_DOMAIN',self::SESSION_DOMAIN);
        session_name($session_name);
        session_cache_limiter($session_cache_limiter);
        session_set_cookie_params($session_timeout,'/',$session_domain);
        $this->started = @ session_start();
        if(!$this->started) {
            //启动失败，用文件方式
            ini_set('session.save_handler', 'files');
            ini_set('session.save_path', sys_get_temp_dir().'/session');
            session_start();
        }
    }

    /**
     * getInstance同名函数，用于初始化
     */
    public static function getSessionId()
    {
        self::start();
        return @ session_id();
    }

    /**
     * 初始化,getInstance同名函数
     */
    public static function start()
    {
        return self::getInstance();
    }

    /**
     * 保存会话
     * @param string $key
     * @param string $value
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * 追加会话
     * @param string $key
     * @param string $array
     */
    public static function append($key, $value)
    {
        self::start();
        if(!empty($_SESSION[$key])) {
            $_SESSION[$key] = array_merge($_SESSION[$key],$value);
        }
    }

    /**
     * 获取会话
     * @param  string $key
     * @param  string $default
     * @return string
     */
    public static function get($key,$default = null)
    {
        self::start();
        return static::has($key) ? $_SESSION[$key] : $default;
    }

    /**
     * 检查会话是否存在
     * @param  string $key
     * @return boolean
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION) ? array_key_exists($key, $_SESSION) : FALSE;
    }

    /**
     * 删除会话
     * @param  string $key
     */
    public static function delete($key)
    {
        self::start();
        if(static::has($key)){
            unset($_SESSION[$key]);
        }
    }

    /**
     * 清空会话
     */
    public static function clear()
    {
        self::start();
        session_destroy();
        unset( $_SESSION );
    }
}


