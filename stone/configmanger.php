<?php

namespace WhetStone\Stone;

/**
 * 所有业务配置都在这里管理
 * Class ConfigManager
 * @package WhetStone\Stone
 */
class ConfigManager{

    private static $config = array();

    public function loadConfig($name){
        $configPath = dirname(dirname(__DIR__))."/config/";
        if(file_exists($configPath.$name.".php")){
            self::$config[$name] = include $configPath.$name.".php";
        }
        throw new \Exception("load Config was not found",-118);
    }

    public function getConfig($name){
        if(isset(self::$config[$name])){
            return self::$config[$name];
        }

        throw new \Exception("Config was not found",-119);
    }
}