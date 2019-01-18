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

    /**
     * 加载conf下所有配置文件
     * 用于初始化，如不初始化
     * 获取时会报找不到配置
     * @throws \Exception
     */
    public static function loadAllConfig()
    {
        $configPath = dirname(__DIR__) . "/config/";
        $fileList   = glob($configPath . "*.php");

        foreach ($fileList as $file) {
            $fileName = basename($file, ".php");
            self::loadConfig($fileName);
        }
    }

    /**
     * 加载指定配置文件
     * @param $name
     * @throws \Exception
     */
    public static function loadConfig($name)
    {
        $configPath = dirname(__DIR__) . "/config/";

        if (file_exists($configPath . $name . ".php")) {
            self::$config[$name] = include($configPath . $name . ".php");
            return;
        }

        throw new \Exception("load Config was not found ".$name, -118);
    }

    /**
     * 获取配置内内容
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public static function getConfig($name)
    {
        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }

        throw new \Exception("Config was not found ".$name, -119);
    }
}