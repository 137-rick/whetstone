<?php

namespace WhetStone\Stone\Server;

class Http
{
    private $_config = null;

    private $_server = null;

    public function __construct($config)
    {
        $this->_config = $config;
        $this->_server = new \Swoole\Http\Server($config["server"]["host"], $config["server"]["port"]);
        //set option
        $this->_server->set($config["swoole"]);
    }

    public function getServer()
    {
        return $this->_server;
    }

    public function start(){
        $this->_server->start();
    }
}