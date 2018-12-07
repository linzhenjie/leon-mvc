<?php
/**
 * @Author: linzj@500wan.com
 * @Desc: Cookie 管理类
 * @Date: 2015-12-28 14:39:14
 * @Last Modified by:   linzj
 * @Last Modified time: 2018-04-11 09:49:32
 */

class Esun_Cookie
{
    private static $_lifetime  = 3600;
    private static $_path      = '/';
    private static $_domain    = null;
    private static $_secure    = null;
    private static $_cookie = array();
    private static $_instance = null;

    public function __construct()
    {
        //从环境变量中获取的cookie 的作用域
        self::$_lifetime = ini_get('session.cookie_lifetime') ? ini_get('session.cookie_lifetime') : self::$_lifetime;
        self::$_path     = ini_get('session.cookie_path') ? ini_get('session.cookie_path') : self::$_path;
        self::$_domain   = ini_get('session.cookie_domain') ? ini_get('session.cookie_domain') : self::$_domain;
        self::$_secure   = ini_get('session.cookie_secure') ? ini_get('session.cookie_secure') : self::$_secure;

        //只初始化一次，后续修改$_COOKIE值对该变量不其作用
        self::$_cookie   = $_COOKIE;
    }

    public static function getInstance()
    {
        if (static::$_instance === null) {
            static::$_instance = new static;
        }
        return static::$_instance;
    }

    /**
     * 获取cookie值
     * @param string $key COOKIE键
     * 特殊符号将转为HTML实体
     */
    public static function get($key,$default = null)
    {
        self::getInstance();
        return self::has($key) ? htmlspecialchars(self::$_cookie[$key]) : $default;
    }

    /**
     * 设置cookie值
     * @param string $key
     * @param string $value
     * @param int $lifetime
     * @param string $path
     * @param string $domain
     * @param bool $secure
     */
    public static function set($key, $value, $lifetime = null, $path = null, $domain = null, $secure = null)
    {
        $obj = self::getInstance();
        $lifetime = $lifetime === null ? self::$_lifetime : $lifetime;
        $path   = $path   === null ? self::$_path   : $path;
        $domain = $domain === null ? self::$_domain : $domain;
        $secure = $secure === null ? self::$_secure : $secure;
        //Warning：调用setcookie之前不能有输出
        if( setcookie($key, $value, time()+$lifetime, $path, $domain, $secure))
        {
            self::$_cookie[$key] = $value;
            return TRUE;
        }
        Logger_Log::error('浏览器cookie功能被禁，请开启此功能');
        return FALSE;
    }

    /**
     * 检查是否存在
     * @param string $key
     */
    public static function has($key)
    {
        self::getInstance();
        return array_key_exists($key,self::$_cookie);
    }

    /**
     * 删除cookie键
     * @param string $key
     */
    public static function delete($key,$path = null, $domain = null)
    {
        self::set($key,null,-1,$path,$domain);
    }

    /**
     * 清除全部cookie
     */
    public static function clear()
    {
        self::getInstance();
        foreach (self::$_cookie as $key => $value)
        {
            self::delete($key);
        }
    }
}
