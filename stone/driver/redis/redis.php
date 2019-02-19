<?php

namespace WhetStone\Stone\Driver\Redis;

/**
 * Redis驱动类
 * Class Redis
 * @package WhetStone\Stone\Driver\Redis
 */
class Redis
{

    private static $connection = array();

    public static function Factory($db, $driver = "predis")
    {

        //直接返回
        if (isset(self::$connection[$db]) && !empty(self::$connection[$db])) {
            return self::$connection[$db];
        }

        //check config
        $config = \WhetStone\Stone\Config::getConfig("redis");
        if (!isset($config[$db]) || empty($config[$db])) {
            throw new \Exception("Redis Config $db Not found!", -521);
        }

        //默认连接池最大数
        if(!isset($config[$db]["pool"]) || $config[$db]["pool"] <= 0){
            $config[$db]["pool"] = 20;
        }

        //driver select
        if ($driver == "phpredis") {
            $pool = new \WhetStone\Stone\Driver\Redis\PHPRedis\RedisPool($config[$db]["pool"], 3.0, $db, $config[$db]);
        } else if ($driver == "predis") {
            $pool = new \WhetStone\Stone\Driver\Redis\Predis\PRedisPool($config[$db]["pool"], 3.0, $db, $config[$db]);
        }else{
            throw new \Exception("unknow redis driver type:$driver"-522);
        }

        self::$connection[$db] = $pool;
        return $pool;
    }
}