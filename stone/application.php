<?php

namespace WhetStone\Stone;

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
     * @param $className
     * @throws \Exception
     */
    function AutoLoadHandel($className)
    {
        //只接受小写文件路径
        $className = strtolower($className);

        $className = str_replace("\\","/",$className);
        $classPath = trim($className, "/") . ".php";

        //去掉根路径
        if(stripos($classPath,"whetstone/") === 0){
            $classPath = substr($classPath,9);
        }

        $classPath = STONE_ROOT . $classPath;

        //文件存在加载
        if (file_exists($classPath)) {
            require_once $classPath;
            return;
        }

        throw new \Exception("Class Not Found...", -12);

    }

    function ExceptionHandle($e)
    {
        //if(php_sapi_name() == "cli"){
        var_dump("Exception Founded:");
        var_dump($e->getMessage());
        var_dump($e->getCode());
        var_dump($e->getTraceAsString());
        //}
        Event::fire("exception", array(
            "exception"  => $e,
        ));

    }

    function ShutDownHandle()
    {
        Event::fire("exit", array());
    }

}

new Application();