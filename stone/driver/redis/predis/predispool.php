<?php

namespace WhetStone\Stone\Driver\Redis\Predis;

use WhetStone\Stone\Driver\Pool;

class PredisPool extends Pool
{

    protected $_dbName;

    public function __construct(int $maxObjCount = 20, float $waitTimeout = 3.0, string $dbname = "default", array $config = array())
    {
        $this->_dbName = $dbname;
        parent::__construct($maxObjCount, $waitTimeout, $config);
    }

    public function getDriverObj(int $shard)
    {
        if(!isset($this->_config["connection"][$shard]) || empty($this->_config["connection"][$shard]) ){
            throw new \Exception("PRedis getDriver Obj Shard $shard config fail",-523);
        }
        return new \WhetStone\Stone\Driver\Redis\PRedis\PRedis($this->_dbName, $this->_config["connection"][$shard]);
    }

    public function onError($obj, $e)
    {

    }
}