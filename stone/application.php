<?php

namespace WhetStone\Stone;

define('STONE_ROOT', dirname(__DIR__) . "/");

class Application
{

    public function __construct()
    {

        //register the autoload
        spl_autoload_register(array(
            $this,
            "AutoLoadHandel"
        ));

        //require autoload for composer
        if (file_exists(STONE_ROOT . "/vendor/autoload.php")) {
            require_once(STONE_ROOT . "/vendor/autoload.php");
        }

        //exception will handle by framework
        set_exception_handler(array(
            $this,
            "ExceptionHandle"
        ));

        //worker shutdown will process
        register_shutdown_function(array(
            $this,
            "ShutDownHandle"
        ));
    }

    /**
     * autoload process
     * @param $className
     */
    function AutoLoadHandel($className)
    {
        $className = strtolower($className);
        $classPath = trim($className, "/") . ".php";

        //剩下内容用/拼合成一个字符串
        if (file_exists($classPath)) {
            require_once $classPath;
        } else {
            throw new Exception("Class Not Found...", -12);
        }

    }


    function ShutDownHandle()
    {

    }

    function ExceptionHandle()
    {

    }

    function run()
    {

    }
}

new Application();