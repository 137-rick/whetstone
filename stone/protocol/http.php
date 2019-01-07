<?php

namespace WhetStone\Stone\Protocol;

/**
 * Http协议回调封装
 * Class Http
 * @package WhetStone\Stone\Protocol
 */
class Http {


    public function __construct($server,$config)
    {
        $this->_server = $server;
        $this->_config = $config;

        //sub listen event
        $this->_server->getServer()->on('Request', array($this,"onRequest"));
    }

    public function onRequest($request, $response){
        $response->end("yes");

    }

}