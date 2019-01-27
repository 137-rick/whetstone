<?php

namespace WhetStone\Stone\Driver\Redis;

/**
 * 基础新版Redis驱动做的
 * 新版本明确服务器不可回应时抛出异常
 *
 * Class Redis
 * @package WhetStone\Stone\Driver\Redis
 */
class Redis
{
    private $config = null;

    private $dbName = "";

    private $redis = null;

    public function __construct($dbname, $config)
    {
        $this->dbName = $dbname;
        $this->config = $config;

        //do connect
        $this->reconnect();
    }

    //if connection is broken reconnect
    public function checkConnection()
    {
        try {
            if ($this->redis->ping() != "+PONG") {
                $this->reconnect();
            }
        } catch (\Exception $e) {
            $this->reconnect();
        }
    }

    public function reconnect()
    {
        $this->redis = new \Redis();

        $this->redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        $this->redis->setOption(\Redis::OPT_READ_TIMEOUT, -1);

        //prefix
        if (isset($this->config["prefix"]) && !empty($this->config["prefix"])) {
            $this->redis->setOption(\Redis::OPT_PREFIX, $this->config["prefix"]);
        }

        //auth
        if (isset($this->config["auth"]) && !empty($this->config["auth"])) {
            if ($this->redis->auth($this->config["auth"]) == FALSE) {
                throw new \Exception("Redis auth fail.dbname:" . $this->dbName, -23);
            }
        }

        //db
        if (isset($this->config["db"]) && !empty($this->config["db"])) {
            $this->redis->select($this->config["db"]);
        }

        //connect the server
        $ret = $this->redis->connect($this->config["host"], $this->config["port"], $this->config["timeout"] ?? 0);
        if (!$ret) {
            throw new \Exception("connect Redis Server fail:" . $this->redis->getLastError(), -24);
        }

    }

    public function __call($name, $arguments)
    {
        //check is work well
        $this->checkConnection();

        //do the cmd，如果刚检测完还报错，那。。。
        return call_user_func($this->redis, $arguments);
    }
}