<?php
/**
 * @Author: linzj@500wan.com
 * @Desc: 异常处理类
 * @Date:   2015-12-28 14:39:14
 * @Last Modified by:   linzj
 * @Last Modified time: 2018-12-07 11:27:01
 */
namespace Leonphp\Mvc;

class Exception
{
    public static function exceptionHandler($ex)
    {
        if(Store::get('DEBUG')){
            echo('<pre>'.$ex.'</pre>');
        }
        error_log($ex);
    }

    public static function errorHandler($err_no, $err_str, $err_file, $err_line){
        if(Store::get('DEBUG')){
            echo('code:['.$err_no.'] msg:['.iconv("GB2312","UTF-8",$err_str).'] file:['.$err_file.':'.$err_line.']');
        }
        error_log('code:['.$err_no.'] msg:['.iconv("GB2312","UTF-8",$err_str).'] file:['.$err_file.':'.$err_line.']');
    }

    public static function fatalErrorHandler(){
        if ($e = error_get_last()) {
            self::ErrorHandler($e['type'], $e['message'], $e['file'], $e['line']);
        }
    }
}
?>
