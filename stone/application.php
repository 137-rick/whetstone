<?php

namespace WhetStone\Stone;

use WhetStone\Stone\Server\Event;

define('STONE_ROOT', dirname(__DIR__) . "/");
ini_set('default_socket_timeout', 60);

/**
 * 框架初始化类
 * Class Application
 * @package WhetStone\Stone
 */
class Application
{

    public function __construct()
    {

        /*
        // change autoload to composer
        //register the autoload
        spl_autoload_register(array(
            $this,
            "AutoLoadHandel"
        ));
        */

        //require autoload for composer
        if (file_exists(STONE_ROOT . "/vendor/autoload.php")) {
            require_once(STONE_ROOT . "/vendor/autoload.php");
        }

        //default exception will handle by framework
        set_exception_handler(array(
            $this,
            "ExceptionHandle"
        ));

        //worker shutdown will invoke for check if is wrong
        register_shutdown_function(array(
            $this,
            "ShutDownHandle"
        ));
    }

    /**
     * autoload process
     * 已经停用了，使用composer autoload
     * 留这里是未来使用的
     * @param $className
     * @throws \Exception
     */
    function AutoLoadHandel($className)
    {
        //只接受小写文件路径
        $className = strtolower($className);

        $className = str_replace("\\", "/", $className);
        $classPath = trim($className, "/") . ".php";

        //去掉根路径
        if (stripos($classPath, "whetstone/") === 0) {
            $classPath = substr($classPath, 9);
        }

        $classPath = STONE_ROOT . $classPath;

        //文件存在加载
        if (file_exists($classPath)) {
            require_once $classPath;
            return;
        }

        throw new \Exception("Class Not Found...", -12);

    }

    /**
     * 统一未拦截exception处理
     * @param $e
     * @return bool
     */
    function ExceptionHandle($e)
    {

        //如果没有注册处理，默认输出到屏幕
        if (Event::checkRegisted("exception")) {
            var_dump("Exception Was Founded:");
            var_dump($e->getMessage(),$e->getCode());
            var_dump($e->getTraceAsString());
            return true;
        }

        //如果有注册，那么触发
        Event::fire("exception", array(
            "exception" => $e,
        ));

    }

    /**
     * 服务关闭时清理
     */
    function ShutDownHandle()
    {
        Event::fire("exit", array());
    }

}

new Application();