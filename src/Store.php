<?php
/**
 * @Author: linzj@500wan.com
 * @Desc: 静态存储类
 * @Date:   2015-12-28 14:39:14
 * @Last Modified by:   linzj
 * @Last Modified time: 2018-06-25 10:03:22
 */
namespace Esunphp\Mvc;
use Esunphp\Pattern\Singleton;

class Store
{
    use Singleton;

    private static $_store    = array();

    /**
     * 获得配置信息
     *
     * @param string $key 配置文件的key
     * @return string or array
     * 特殊符号将转为HTML实体
     */
    public static function get($key = null,$default = null)
    {
        self::getInstance();

        if ($key === null) {
            return self::$_store;
        }
        return array_key_exists($key,self::$_store) ? self::$_store[$key] : $default;
    }

    /**
     * @desc 加载配置信息
     *
     * @param  string|array $key 配置数组
     * @param  string $value 配置值
     *
     * @throws 配置信息必须是字符串或数组型变量
     *
     * @return void
     */
    public static function set($key,$value = null)
    {
        self::getInstance();

        if (is_array($key)) {
            self::$_store = self::_merge(self::$_store, $key);
        }elseif(is_string($key)){
            self::$_store[$key] = $value;
        }else{
            throw new \Exception('$store must be an array or string variable!',500);
        }
    }

    /**
     * @desc 递归合并配置信息
     *
     * @param array $arr1 数组1
     * @param array $arr2 数组2
     *
     * @return array
     */
    private static function _merge(array $arr1, array $arr2)
    {
        foreach ($arr2 as $key => $value) {
            if (isset($arr1[$key]) && is_array($value)) {
                $arr1[$key] = self::_merge($arr1[$key], $arr2[$key]);
            } else {
                $arr1[$key] = $value;
            }
        }
        return $arr1;
    }

}
