<?php

namespace WhetStone\Stone;

/**
 * 所有业务配置都在这里管理
 * Class Config
 * @package WhetStone\Stone
 */
class Config
{

    private static $config = array();

    public static function loadAllConfig()
    {
        $configPath = dirname(__DIR__) . "/config/";
        $fileList   = glob($configPath . "*.php");

        foreach ($fileList as $file) {
            $fileName = basename($file, ".php");
            self::loadConfig($fileName);
        }
    }

    public static function loadConfig($name)
    {
        $configPath = dirname(__DIR__) . "/config/";

        if (file_exists($configPath . $name . ".php")) {
            self::$config[$name] = include($configPath . $name . ".php");
            return;
        }

        throw new \Exception("load Config was not found", -118);
    }

    public static function getConfig($name)
    {
        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }

        throw new \Exception("Config was not found", -119);
    }
}