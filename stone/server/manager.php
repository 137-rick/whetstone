<?php

namespace WhetStone\Stone\Server;

class Manager
{

    private $config = null;

    private $params = null;

    public function __construct($param,$config)
    {
        $this->params = $param;
        $this->config = $config;
    }

    public function start()
    {
        var_dump(1111);
    }
}