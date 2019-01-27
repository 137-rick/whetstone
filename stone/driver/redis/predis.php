<?php

namespace WhetStone\Stone\Driver\Redis;

/**
 * 基础PRedis驱动做的
 * todo:Predis支持哨兵和cluster，目前这里没有做任何支持
 *
 * Class Redis
 * @package WhetStone\Stone\Driver\PRedis
 */
class PRedis
{
    private $config = null;

    //配置名称
    private $dbName = "";

    //redis 对象
    private $redis = null;

    /**
     *
     * PRedis constructor.
     * @param string $dbname 本配置名称
     * @param array $config 配置具体
     */
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
        $config = array(
            "scheme" => "tcp",
            "host" => $this->config["host"],
            "port" => $this->config["port"],
        );

        $option = array();

        //prefix
        if (isset($this->config["prefix"]) && !empty($this->config["prefix"])) {
            $option["prefix"] =  $this->config["prefix"];
        }

        //db
        if (isset($this->config["db"]) && !empty($this->config["db"])) {
            $config["database"] =  $this->config["db"];
        }

        //auth
        if (isset($this->config["auth"]) && !empty($this->config["auth"])) {
            $config["password"] =  $this->config["auth"];
        }

        //timeout
        $config["timeout"] = $this->config["timeout"] ?? 5.0;
        $config["read_write_timeout"] = $config["timeout"];
        //new predis
        $this->redis = new \Predis\Client($config, $option);

    }

    public function __call($name, $arguments)
    {
        //check is work well
        $this->checkConnection();

        //do the cmd，如果刚检测完还报错，那。。。
        return call_user_func($this->redis, $arguments);
    }
}