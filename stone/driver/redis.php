<?php

namespace WhetStone\Stone\Driver;

class Redis extends Pool{

    protected $_dbName;

    public function __construct(int $maxObjCount = 20, float $waitTimeout = 3.0,
                                string $dbname = "default",array $config = array())
    {
        $this->_dbName = $dbname;
        parent::__construct($maxObjCount, $waitTimeout, $config);
    }

    public function getDriverObj()
    {
        return new Redis\Redis($this->_dbName,$this->_config);
    }

    public function onError($obj, $e)
    {

    }
}